<?php

namespace App\Services\App;

use App\Mixins\Cashback\CashbackRules;
use App\Models\Discount;
use App\Models\Product;

class WebCartPricingEngineService
{
    public function calculatePrice($carts, $user, $discountCoupon = null)
    {
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $commission = 0;

        $taxIsDifferent = false;

        foreach ($carts as $cart) {
            $orderPrices = $this->handleOrderPrices($cart, $user, $taxIsDifferent, $discountCoupon);
            $subTotal += $orderPrices['sub_total'];
            $totalDiscount += $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice += $orderPrices['tax_price'];
            $commission += $orderPrices['commission'];
            $commissionPrice += $orderPrices['commission_price'];

            if (!$taxIsDifferent) {
                $taxIsDifferent = $orderPrices['tax_is_different'];
            }
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $subTotalWithoutDiscount = $subTotal - $totalDiscount;
        $productDeliveryFee = $this->calculateProductDeliveryFee($carts);

        if (($subTotalWithoutDiscount + $productDeliveryFee) <= 0) {
            $taxPrice = 0;
            $tax = 0;
        }

        $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        if ($total < 0) {
            $total = 0;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'total' => round($total, 2),
            'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent
        ];
    }

    public function getTotalCashbackAmount($carts, $user, $subTotal)
    {
        $amount = 0;

        if (getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $applyPerItemRules = [];

            foreach ($carts as $cart) {
                $rules = $cashbackRulesMixin->getRulesByItem($cart);

                if (!empty($rules) and count($rules)) {
                    foreach ($rules as $rule) {
                        if (empty($rule->min_amount) or $rule->min_amount <= $subTotal) {
                            if ($rule->amount_type == "fixed_amount") {
                                if ($rule->apply_cashback_per_item and $rule->apply_cashback_per_item > 0) {
                                    $amount += $rule->amount;
                                } else {
                                    $applyPerItemRules[$rule->id] = $rule;
                                }
                            } else if ($rule->amount_type == "percent") {
                                $itemOrder = $this->handleOrderPrices($cart, $user);
                                $itemPrice = $itemOrder['sub_total'];

                                if (!empty($itemOrder['total_discount'])) {
                                    $itemPrice = $itemPrice - $itemOrder['total_discount'];
                                }

                                $ruleAmount = $rule->getAmount($itemPrice);

                                if (!empty($rule->max_amount) and $rule->max_amount < $ruleAmount) {
                                    $amount += $rule->max_amount;
                                } else {
                                    $amount += $ruleAmount;
                                }
                            }
                        }
                    }
                }
            }


            if (!empty($applyPerItemRules)) {
                foreach ($applyPerItemRules as $applyPerItemRule) {
                    $amount += $applyPerItemRule->amount;
                }
            }
        }

        return $amount;
    }

    public function handleOrderPrices(\App\Models\Cart $cart, $user, $taxIsDifferent = false, $discountCoupon = null)
    {
        $seller = $this->getSeller($cart);
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $priceWithoutDiscount = 0;

        if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
            $item = !empty($cart->webinar_id) ? $cart->webinar : $cart->bundle;
            $price = $item->price;
            $discount = $item->getDiscount($cart->ticket, $user);

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $source = !empty($cart->webinar_id) ? 'courses' : 'bundles';
            $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $price = $cart->reserveMeeting->paid_amount;
            $discount = $cart->reserveMeeting->getDiscountPrice($user);

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice('meetings', $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->product_order_id)) {
            $product = $cart->productOrder->product;

            if (!empty($product)) {
                $productQuantity = $cart->productOrder->quantity;
                $price = ($product->price * $productQuantity);
                $discount = $product->getDiscountPrice() * $productQuantity;

                $productTax = $product->getTax();

                $priceWithoutDiscount = $price - $discount;

                $taxIsDifferent = ($tax != $productTax);

                $tax = $productTax;
                if ($productTax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $productTax / 100;
                }

                // Product Commission
                if (isset($product->commission)) {
                    if ($product->commission_type == "percent") {
                        $commissionPrice += ($priceWithoutDiscount > 0 and $product->commission > 0) ? (($priceWithoutDiscount * $product->commission) / 100) : 0;
                    } else {
                        $commissionPrice += $product->commission;
                    }
                } else {
                    $source = ($product->type == Product::$physical) ? 'physical_products' : 'virtual_products';
                    $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            }
        } elseif (!empty($cart->installment_payment_id)) {
            $price = $cart->installmentPayment->amount;
            $discount = 0;

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            // Commission
            $installmentOrder = $cart->installmentPayment->installmentOrder;

            if (!empty($installmentOrder)) {
                $source = null;

                if (!empty($installmentOrder->webinar_id)) {
                    $source = "courses";
                } elseif (!empty($installmentOrder->bundle_id)) {
                    $source = "bundles";
                } elseif (!empty($installmentOrder->product_id) and !empty($installmentOrder->product)) {
                    if ($installmentOrder->product->type == Product::$physical) {
                        $source = "physical_products";
                    } else {
                        $source = "virtual_products";
                    }
                }

                if (!empty($source)) {
                    $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);
                }
            }

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->event_ticket_id)) {
            $quantity = $cart->quantity ?? 1;
            $eventTicket = $cart->eventTicket;
            $price = $eventTicket->price * $quantity;
            $discount = $eventTicket->getDiscountPrice() * $quantity;

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice("events", $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->meeting_package_id)) {
            $meetingPackage = $cart->meetingPackage;

            $price = $meetingPackage->price;
            $discount = $meetingPackage->getDiscountPrice();

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice("meeting_packages", $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        }

        if (!empty($discountCoupon)) {
            $totalDiscount += $this->getCouponDiscountByCartItem($discountCoupon, $cart, $user);
        }

        $userGroup = $user->getUserGroup();
        if (!empty($userGroup) and !empty($userGroup->discount) and $subTotal > 0) {
            $totalDiscount += ($subTotal * $userGroup->discount) / 100;
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $commission = ($commissionPrice > 0 and $priceWithoutDiscount > 0) ? (($commissionPrice / $priceWithoutDiscount) * 100) : 0;

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'tax_is_different' => $taxIsDifferent
        ];
    }

    private function handleDiscountPrice($discount, $carts, $subTotal)
    {
        $user = auth()->user();
        $totalDiscount = 0;

        foreach ($carts as $cart) {
            $totalDiscount += $this->getCouponDiscountByCartItem($discount, $cart, $user);
        }

        if ($discount->discount_type != Discount::$discountTypeFixedAmount and !empty($discount->max_amount) and $totalDiscount > $discount->max_amount) {
            $totalDiscount = $discount->max_amount;
        }

        return $totalDiscount;
    }

    private function getCouponDiscountByCartItem($couponDiscount, $cart, $user)
    {
        $applyDiscount = false;
        $percent = $couponDiscount->percent ?? 1;
        //$otherDiscounts = 0;
        $totalCouponDiscount = 0;
        $totalItemAmount = 0;

        if ($couponDiscount->source == Discount::$discountSourceCourse) {
            $discountWebinarsIds = $couponDiscount->discountCourses()->pluck('course_id')->toArray();
            $webinar = $cart->webinar;
            if (!empty($webinar) and (in_array($webinar->id, $discountWebinarsIds) or count($discountWebinarsIds) < 1)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceBundle) {
            $discountBundlesIds = $couponDiscount->discountBundles()->pluck('bundle_id')->toArray();
            $bundle = $cart->bundle;
            if (!empty($bundle) and (in_array($bundle->id, $discountBundlesIds) or count($discountBundlesIds) < 1)) {
                $totalItemAmount += $bundle->price;
                //$otherDiscounts += $bundle->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceEvent) {
            $discountEventsIds = $couponDiscount->discountEvents()->pluck('event_id')->toArray();
            $eventTicket = $cart->eventTicket;
            $quantity = $cart->quantity ?? 1;

            if (!empty($eventTicket) and (in_array($eventTicket->event_id, $discountEventsIds) or count($discountEventsIds) < 1)) {
                $totalItemAmount += $eventTicket->price * $quantity;
                //$otherDiscounts += $event->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceMeetingPackage) {
            $discountMeetingPackagesIds = $couponDiscount->discountMeetingPackages()->pluck('meeting_package_id')->toArray();
            $meetingPackage = $cart->meetingPackage;

            if (!empty($meetingPackage) and (in_array($meetingPackage->id, $discountMeetingPackagesIds) or count($discountMeetingPackagesIds) < 1)) {
                $totalItemAmount += $meetingPackage->price;
                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceProduct) {
            if (!empty($cart->productOrder)) {
                $product = $cart->productOrder->product;

                if (!empty($product) and ($couponDiscount->product_type == 'all' or $couponDiscount->product_type == $product->type)) {
                    $productQuantity = $cart->productOrder->quantity;
                    $totalItemAmount += ($product->price * $productQuantity);
                    //$otherDiscounts += $product->getDiscountPrice() * $productQuantity;

                    $applyDiscount = true;
                }
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceMeeting) {
            $reserveMeeting = $cart->reserveMeeting;
            if (!empty($reserveMeeting)) {
                $totalItemAmount += $reserveMeeting->paid_amount;
                //$otherDiscounts += $reserveMeeting->getDiscountPrice($user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceCategory) {
            $webinar = $cart->webinar;
            $categoriesIds = ($couponDiscount->discountCategories) ? $couponDiscount->discountCategories()->pluck('category_id')->toArray() : [];
            if (!empty($webinar) and in_array($webinar->category_id, $categoriesIds)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } else {
            // All Source
            $webinar = $cart->webinar;
            $bundle = $cart->bundle;
            $reserveMeeting = $cart->reserveMeeting;

            if (!empty($webinar)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }

            if (!empty($reserveMeeting)) {
                $totalItemAmount += $reserveMeeting->paid_amount;
                //$otherDiscounts += $reserveMeeting->getDiscountPrice($user);

                $applyDiscount = true;
            }

            if (!empty($bundle)) {
                $totalItemAmount += $bundle->price;
                //$otherDiscounts += $bundle->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }

            if (!empty($cart->productOrder)) {
                $product = $cart->productOrder->product;

                if (!empty($product)) {
                    $totalItemAmount += ($product->price * $cart->productOrder->quantity);
                    //$otherDiscounts += $product->getDiscountPrice();

                    $applyDiscount = true;
                }
            }

            $eventTicket = $cart->eventTicket;
            if (!empty($eventTicket)) {
                $quantity = $cart->quantity ?? 1;

                $totalItemAmount += $eventTicket->price * $quantity;
                $applyDiscount = true;
            }

            $meetingPackage = $cart->meetingPackage;
            if (!empty($meetingPackage)) {
                $totalItemAmount += $meetingPackage->price;
                $applyDiscount = true;
            }
        }


        if ($applyDiscount) {
            if ($couponDiscount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalCouponDiscount = ($totalItemAmount > $couponDiscount->amount) ? $couponDiscount->amount : $totalItemAmount;
            } else {
                $totalCouponDiscount = ($totalItemAmount > 0) ? $totalItemAmount * $percent / 100 : 0;
            }

            if ($couponDiscount->discount_type != Discount::$discountTypeFixedAmount and !empty($couponDiscount->max_amount) and $totalCouponDiscount > $couponDiscount->max_amount) {
                $totalCouponDiscount = $couponDiscount->max_amount;
            }
        }

        return $totalCouponDiscount;
    }

    private function taxIsDifferent($carts)
    {
        $cartHasWebinar = array_filter($carts->pluck('webinar_id')->toArray());
        $cartHasBundle = array_filter($carts->pluck('bundle_id')->toArray());
        $cartHasMeeting = array_filter($carts->pluck('reserve_meeting_id')->toArray());
        $cartHasInstallmentPayment = array_filter($carts->pluck('installment_payment_id')->toArray());

        return (count($cartHasWebinar) or count($cartHasBundle) or count($cartHasMeeting) or count($cartHasInstallmentPayment));
    }

    private function getSeller(\App\Models\Cart $cart)
    {
        $user = null;

        if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
            $user = $cart->webinar_id ? $cart->webinar->creator : $cart->bundle->creator;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $user = $cart->reserveMeeting->meeting->creator;
        } elseif (!empty($cart->product_order_id) and !empty($cart->productOrder)) {
            $user = $cart->productOrder->seller;
        } elseif (!empty($cart->event_ticket_id) and !empty($cart->eventTicket)) {
            $user = $cart->eventTicket->event->creator;
        } elseif (!empty($cart->meeting_package_id) and !empty($cart->meetingPackage)) {
            $user = $cart->meetingPackage->creator;
        }

        return $user;
    }

    private function getCommissionPrice($source, $itemPrice, $seller = null)
    {
        $hasSellerSpecificCommission = false;
        $commissionPrice = 0;

        if (!empty($seller)) {
            $userCommission = $seller->commissions()->where('source', $source)->first();

            if (!empty($userCommission)) {
                $hasSellerSpecificCommission = true;
                $commissionPrice = $userCommission->calculatePrice($itemPrice);
            } else {
                $userGroup = $seller->getUserGroup();

                if (!empty($userGroup)) {
                    $groupCommission = $userGroup->commissions()->where('source', $source)->first();

                    if (!empty($groupCommission)) {
                        $hasSellerSpecificCommission = true;
                        $commissionPrice = $groupCommission->calculatePrice($itemPrice);
                    }
                }
            }
        }

        if (!$hasSellerSpecificCommission) {
            // Get System Default Commission

            $commissionSettings = getCommissionSettings();

            if (!empty($commissionSettings) and !empty($commissionSettings[$source]) and !empty($commissionSettings[$source]['type']) and !empty($commissionSettings[$source]['value'])) {
                $type = $commissionSettings[$source]['type'];
                $value = $commissionSettings[$source]['value'];

                if ($type == "percent") {
                    $commissionPrice = $itemPrice > 0 ? (($itemPrice * $value) / 100) : 0;
                } else {
                    $commissionPrice = $value;
                }
            }
        }

        return $commissionPrice;
    }

    public function productDeliveryFeeBySeller($carts)
    {
        $productFee = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;
                /** @var \App\Models\Product $product */

                if (!empty($product->delivery_fee)) {
                    if (!empty($productFee[$product->creator_id]) and $productFee[$product->creator_id] < $product->delivery_fee) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    } else if (empty($productFee[$product->creator_id])) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    }
                }
            }
        }

        return $productFee;
    }

    public function physicalProductCountBySeller($carts)
    {
        $productCount = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if ($product->isPhysical()) {
                    if (!empty($productCount[$product->creator_id])) {
                        $productCount[$product->creator_id] += 1;
                    } else {
                        $productCount[$product->creator_id] = 1;
                    }
                }
            }
        }

        return $productCount;
    }

    private function calculateProductDeliveryFee($carts)
    {
        $fee = 0;

        if (!empty($carts)) {
            $productsFee = $this->productDeliveryFeeBySeller($carts);

            if (!empty($productsFee) and count($productsFee)) {
                $fee = array_sum($productsFee);
            }
        }

        return $fee;
    }
}
