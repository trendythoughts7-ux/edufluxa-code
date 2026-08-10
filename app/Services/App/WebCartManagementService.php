<?php

namespace App\Services\App;

use App\Http\Controllers\Web\traits\RegionsDataByUser;
use App\Models\Cart;
use App\Models\CartDiscount;
use App\Models\Discount;
use App\Models\Product;

class WebCartManagementService
{
    use RegionsDataByUser;

    protected $webCartPricingEngineService;

    public function __construct(WebCartPricingEngineService $webCartPricingEngineService)
    {
        $this->webCartPricingEngineService = $webCartPricingEngineService;
    }

    public function prepareCartOverview($user)
    {
        $carts = Cart::where('creator_id', $user->id)
            ->with([
                'user',
                'webinar',
                'installmentPayment',
                'reserveMeeting' => function ($query) {
                    $query->with([
                        'meeting',
                        'meetingTime'
                    ]);
                },
                'ticket',
                'productOrder' => function ($query) {
                    $query->whereHas('product');
                    $query->with(['product']);
                }
            ])
            ->get();

        if (!$carts->isEmpty()) {
            $calculate = $this->webCartPricingEngineService->calculatePrice($carts, $user);

            $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

            $deliveryEstimateTime = 0;

            if (count($hasPhysicalProduct)) {
                foreach ($hasPhysicalProduct as $physicalProductCart) {
                    if (!empty($physicalProductCart->productOrder) and
                        !empty($physicalProductCart->productOrder->product) and
                        !empty($physicalProductCart->productOrder->product->delivery_estimated_time) and
                        $physicalProductCart->productOrder->product->delivery_estimated_time > $deliveryEstimateTime
                    ) {
                        $deliveryEstimateTime = $physicalProductCart->productOrder->product->delivery_estimated_time;
                    }
                }
            }

            if (!empty($calculate)) {

                $totalCashbackAmount = $this->webCartPricingEngineService->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                $cartDiscount = CartDiscount::query()
                    ->where('show_only_on_empty_cart', false)
                    ->where('enable', true)
                    ->first();

                $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'user' => $user,
                    'carts' => $carts,
                    'calculatePrices' => $calculate,
                    'userGroup' => $user->getUserGroup(),
                    'hasPhysicalProduct' => (count($hasPhysicalProduct) > 0),
                    'deliveryEstimateTime' => $deliveryEstimateTime,
                    'totalCashbackAmount' => $totalCashbackAmount,
                    'cartDiscount' => $cartDiscount,
                ];

                $data = array_merge($data, $this->getLocationsData($user));

                return [
                    'view' => 'design_1.web.cart.overview.index',
                    'data' => $data,
                ];
            }
        } else {
            $cartDiscount = CartDiscount::query()->where('enable', true)->first();

            if (!empty($cartDiscount)) {
                $data = [
                    'pageTitle' => trans('update.cart_is_empty'),
                    'cartDiscount' => $cartDiscount,
                ];

                return [
                    'view' => 'design_1.web.cart.empty.index',
                    'data' => $data,
                ];
            }
        }

        return null;
    }

    public function validateCoupon($user, $couponCode)
    {
        $discountCoupon = Discount::where('code', $couponCode)->first();

        if (!empty($discountCoupon)) {
            $checkDiscount = $discountCoupon->checkValidDiscount();
            if ($checkDiscount != 'ok') {
                return [
                    'success' => false,
                    'error' => [
                        'title' => trans('public.request_failed'),
                        'msg' => $checkDiscount,
                    ],
                ];
            }

            $carts = Cart::where('creator_id', $user->id)->get();

            if (!$carts->isEmpty()) {
                $calculate = $this->webCartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);

                if (!empty($calculate)) {
                    $calculate['discountCoupon'] = $discountCoupon;

                    $data = [
                        'calculatePrices' => $calculate,
                    ];

                    $html = view()->make('design_1.web.cart.overview.includes.summary', $data)->render();

                    return [
                        'success' => true,
                        'html' => $html,
                    ];
                }
            }
        }

        return [
            'success' => false,
            'error' => [
                'title' => trans('public.request_failed'),
                'msg' => trans('cart.coupon_invalid'),
            ],
        ];
    }
}
