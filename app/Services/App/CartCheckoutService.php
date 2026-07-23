<?php

namespace App\Services\App;

use App\Http\Controllers\Api\Panel\PaymentsController;
use App\Models\Api\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\ProductOrder;
use Illuminate\Http\Request;

class CartCheckoutService
{
    protected CartPricingEngineService $cartPricingEngineService;
    protected PaymentsController $paymentsController;

    public function __construct(CartPricingEngineService $cartPricingEngineService, PaymentsController $paymentsController)
    {
        $this->cartPricingEngineService = $cartPricingEngineService;
        $this->paymentsController = $paymentsController;
    }

    public function checkout($user, $discountId)
    {
        $paymentChannels = PaymentChannel::where('status', 'active')->get();

        $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or !$discountCoupon->checkValidDiscount()) {
            $discountCoupon = null;
        }

        $carts = Cart::where('creator_id', $user->id)
            ->get();

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->cartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);

            $order = $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon);

            if (!empty($order) and $order->total_amount > 0) {
                $razorpay = false;
                foreach ($paymentChannels as $paymentChannel) {
                    if ($paymentChannel->class_name == 'Razorpay') {
                        $razorpay = true;
                    }
                }

                $data = [
                    'paymentChannels' => $paymentChannels,
                    'carts' => $carts->map(function ($cart) {
                        return $cart->details;
                    }),
                    'user_group' => $user->userGroup ? $user->userGroup->group : null,
                    'order' => $order,
                    'count' => $carts->count(),
                    'userCharge' => $user->getAccountingCharge(),
                    'razorpay' => $razorpay,
                    'amounts' => $calculate,
                ];

                return ['status' => 'checkout', 'data' => $data];
            } else {
                return $this->handlePaymentOrderWithZeroTotalAmount($order);
            }
        }

        return ['status' => 'empty_cart'];
    }

    public function createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon = null)
    {
        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $calculate["sub_total"],
            'tax' => $calculate["tax_price"],
            'total_discount' => $calculate["total_discount"],
            'total_amount' => $calculate["total"],
            'created_at' => time(),
        ]);

        foreach ($carts as $cart) {
            $price = 0;
            $discount = 0;
            $discountCouponPrice = 0;
            $sellerUser = null;

            if (!empty($cart->webinar_id)) {
                $price = $cart->webinar->price;
                $discount = $cart->webinar->getDiscount($cart->ticket, $user);
                $sellerUser = $cart->webinar->creator;
            } elseif (!empty($cart->reserve_meeting_id)) {
                $price = $cart->reserveMeeting->paid_amount;
                $discount = $price * $cart->reserveMeeting->discount / 100;
                $sellerUser = $cart->reserveMeeting->meeting->creator;
            }

            if (!empty($discountCoupon)) {
                if ($discountCoupon->discount_type == Discount::$discountTypeFixedAmount) {
                    $discountCouponPrice = $discountCoupon->amount;
                } else {
                    $couponAmount = $price * $discountCoupon->percent / 100;

                    if (!empty($discountCoupon->amount) and $couponAmount > $discountCoupon->amount) {
                        $discountCouponPrice += $discountCoupon->amount;
                    } else {
                        $discountCouponPrice += $couponAmount;
                    }
                }
            }


            $financialSettings = getFinancialSettings();
            $commission = $financialSettings['commission'] ?? 0;
            $tax = $financialSettings['tax'] ?? 0;

            if (!empty($sellerUser)) {
                $commission = $sellerUser->getCommission();
            }

            $allDiscountPrice = $discount + $discountCouponPrice;
            $subAmount = $price - $allDiscountPrice;

            if ($allDiscountPrice > $price) {
                $subAmount = 0;
            }

            $taxPrice = ($tax and $subAmount > 0) ? ($subAmount * $tax) / 100 : 0;
            $commissionPrice = $subAmount > 0 ? ($subAmount * $commission) / 100 : 0;
            $totalAmount = $subAmount + $taxPrice;

            $ticket = $cart->ticket;
            if (!empty($ticket) and !$ticket->isValid()) {
                $ticket = null;
            }

            OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'webinar_id' => $cart->webinar_id ?? null,
                'reserve_meeting_id' => $cart->reserve_meeting_id ?? null,
                'subscribe_id' => $cart->subscribe_id ?? null,
                'promotion_id' => $cart->promotion_id ?? null,
                'ticket_id' => !empty($ticket) ? $ticket->id : null,
                'discount_id' => $discountCoupon ? $discountCoupon->id : null,
                'amount' => $price,
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'tax_price' => $taxPrice,
                'commission' => $commission,
                'commission_price' => $commissionPrice,
                'discount' => $discount + $discountCouponPrice,
                'created_at' => time(),
            ]);
        }

        return $order;
    }

    public function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $this->paymentsController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid
        ]);

        return ['status' => 'zero_total_paid', 'order' => $order];
    }

    public function updateProductOrders(Request $request, $carts, $user)
    {
        $data = $request->all();

        foreach ($carts as $cart) {
            if (!empty($cart->product_order_id)) {
                ProductOrder::where('id', $cart->product_order_id)
                    ->where('buyer_id', $user->id)
                    ->update([
                        'message_to_seller' => $data['message_to_seller'],
                    ]);
            }
        }

        $user->update([
            'country_id' => $data['country_id'] ?? $user->country_id,
            'province_id' => $data['province_id'] ?? $user->province_id,
            'city_id' => $data['city_id'] ?? $user->city_id,
            'district_id' => $data['district_id'] ?? $user->district_id,
            'address' => $data['address'] ?? $user->address,
        ]);
    }
}
