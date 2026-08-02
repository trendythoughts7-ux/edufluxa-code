<?php

namespace App\Services\App;

use App\Http\Controllers\Web\PaymentController;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WebCartCheckoutService
{
    protected $webCartPricingEngineService;
    protected $paymentController;

    public function __construct(WebCartPricingEngineService $webCartPricingEngineService, PaymentController $paymentController)
    {
        $this->webCartPricingEngineService = $webCartPricingEngineService;
        $this->paymentController = $paymentController;
    }

    private function handleRemoveUserPendingOrders($user)
    {
        $userPendingOrderIds = Order::query()->where('user_id', $user->id)
            ->where('status', Order::$pending)
            ->pluck('id')
            ->toArray();
        OrderItem::query()->whereIn('order_id', $userPendingOrderIds)
            ->where('user_id', $user->id)
            ->delete();
        Order::query()->where('user_id', $user->id)
            ->where('status', Order::$pending)
            ->delete();
    }

    private function updateProductOrders(Request $request, $carts, $user)
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

    /**
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $carts
     */
    private function createOrderAndOrderItems(\Illuminate\Database\Eloquent\Collection $carts, $calculate, $user, $discountCoupon = null)
    {
        $totalAmount = $calculate["total"];

        $orderTotalDiscount = $calculate["total_discount"];
        if ($orderTotalDiscount > $calculate["sub_total"]) {
            $orderTotalDiscount = $calculate["sub_total"];
        }

        // Remove User Pending Orders
        $this->handleRemoveUserPendingOrders($user);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $calculate["sub_total"],
            'tax' => ($totalAmount > 0) ? $calculate["tax_price"] : 0, // when total is 0 tax get 0
            'total_discount' => $orderTotalDiscount,
            'total_amount' => ($totalAmount > 0) ? $totalAmount : 0,
            'product_delivery_fee' => $calculate["product_delivery_fee"] ?? null,
            'created_at' => time(),
        ]);

        $productsFee = $this->webCartPricingEngineService->productDeliveryFeeBySeller($carts);
        $sellersProductsCount = $this->webCartPricingEngineService->physicalProductCountBySeller($carts);
        $taxIsDifferent = false;

        foreach ($carts as $cart) {

            $orderPrices = $this->webCartPricingEngineService->handleOrderPrices($cart, $user, $taxIsDifferent, $discountCoupon);
            $price = $orderPrices['sub_total'];
            $totalDiscount = $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice = $orderPrices['tax_price'];
            $commission = $orderPrices['commission'];
            $commissionPrice = $orderPrices['commission_price'];

            $productDeliveryFee = 0;
            if (!empty($cart->product_order_id)) {
                $product = $cart->productOrder->product;

                if (!empty($product) and $product->isPhysical() and !empty($productsFee[$product->creator_id])) {
                    $productDeliveryFee = $productsFee[$product->creator_id];
                }

                $sellerProductCount = !empty($sellersProductsCount[$product->creator_id]) ? $sellersProductsCount[$product->creator_id] : 1;

                $productDeliveryFee = $productDeliveryFee > 0 ? $productDeliveryFee / $sellerProductCount : 0;
            }

            $subTotalWithoutDiscount = $price - $totalDiscount;
            $totalAmount = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

            $ticket = $cart->ticket;
            if (!empty($ticket) and !$ticket->isValid()) {
                $ticket = null;
            }

            if ($totalDiscount > $price) {
                $totalDiscount = $price;
            }

            if ($totalAmount <= 0) {
                $taxPrice = 0;
                $commissionPrice = 0;
            }

            OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'webinar_id' => $cart->webinar_id ?? null,
                'bundle_id' => $cart->bundle_id ?? null,
                'event_ticket_id' => $cart->event_ticket_id ?? null,
                'product_id' => (!empty($cart->product_order_id) and !empty($cart->productOrder->product)) ? $cart->productOrder->product->id : null,
                'product_order_id' => (!empty($cart->product_order_id)) ? $cart->product_order_id : null,
                'reserve_meeting_id' => $cart->reserve_meeting_id ?? null,
                'meeting_package_id' => $cart->meeting_package_id ?? null,
                'subscribe_id' => $cart->subscribe_id ?? null,
                'promotion_id' => $cart->promotion_id ?? null,
                'gift_id' => $cart->gift_id ?? null,
                'installment_payment_id' => $cart->installment_payment_id ?? null,
                'ticket_id' => !empty($ticket) ? $ticket->id : null,
                'discount_id' => $discountCoupon ? $discountCoupon->id : null,
                'quantity' => $cart->quantity ?? 1,
                'amount' => $price,
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'tax_price' => $taxPrice,
                'commission' => $commission,
                'commission_price' => $commissionPrice,
                'product_delivery_fee' => $productDeliveryFee,
                'discount' => $totalDiscount,
                'created_at' => time(),
            ]);
        }

        return $order;
    }

    private function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);
        $this->paymentController->setPaymentAccounting($order);
        $order->update([
            'status' => Order::$paid
        ]);
        return $order;
    }

    public function checkout(Request $request, $user, $carts = null)
    {
        if (empty($carts)) {
            $carts = Cart::where('creator_id', $user->id)
                ->get();
        }

        $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);
        $checkAddressValidation = (count($hasPhysicalProduct) > 0);

        if (empty(getStoreSettings('show_address_selection_in_cart')) or !empty(getStoreSettings('take_address_selection_optional'))) {
            $checkAddressValidation = false;
        }

        Validator::make($request->all(), [
            'country_id' => Rule::requiredIf($checkAddressValidation),
            'province_id' => Rule::requiredIf($checkAddressValidation),
            'city_id' => Rule::requiredIf($checkAddressValidation),
            'district_id' => Rule::requiredIf($checkAddressValidation),
            'address' => Rule::requiredIf($checkAddressValidation),
        ])->validate();

        $discountId = $request->input('discount_id');

        $paymentChannels = PaymentChannel::where('status', 'active')->get();

        $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->webCartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);

            $order = $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon);

            if (count($hasPhysicalProduct) > 0) {
                $this->updateProductOrders($request, $carts, $user);
            }

            if (!empty($order) and $order->total_amount > 0) {
                $totalCashbackAmount = $this->webCartPricingEngineService->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                $data = [
                    'pageTitle' => trans('public.checkout_page_title'),
                    'paymentChannels' => $paymentChannels,
                    'carts' => $carts,
                    'calculatePrices' => $calculate,
                    'userGroup' => $user->getUserGroup(),
                    'order' => $order,
                    'count' => $carts->count(),
                    'userCharge' => $user->getAccountingCharge(),
                    'totalCashbackAmount' => $totalCashbackAmount,
                    'previousUrl' => url()->previous(),
                    'offlineBanks' => \App\Models\OfflineBank::query()->orderBy('created_at', 'desc')->with(['specifications'])->get(),
                ];

                return [
                    'action' => 'view',
                    'view' => 'design_1.web.cart.payment.index',
                    'data' => $data,
                ];
            }

            $paidOrder = $this->handlePaymentOrderWithZeroTotalAmount($order);
            return [
                'action' => 'redirect_zero_amount',
                'order' => $paidOrder,
            ];
        }

        return [
            'action' => 'redirect',
            'url' => '/cart',
        ];
    }
}
