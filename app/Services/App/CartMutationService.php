<?php

namespace App\Services\App;

use App\Models\Api\Cart;
use App\Models\Api\Webinar;
use App\Models\Discount;

class CartMutationService
{
    protected $cartPricingEngineService;

    public function __construct(CartPricingEngineService $cartPricingEngineService)
    {
        $this->cartPricingEngineService = $cartPricingEngineService;
    }

    public function store($user, $webinar_id, $ticket_id)
    {
        $webinar = Webinar::find($webinar_id);

        $checkCourseForSale = $webinar->canAddToCart();

        if ($checkCourseForSale != 'ok') {
            return [
                'success' => false,
                'reason' => $checkCourseForSale,
            ];
        }

        $activeSpecialOffer = $webinar->activeSpecialOffer();

        Cart::updateOrCreate([
            'creator_id' => $user->id,
            'webinar_id' => $webinar_id,
        ], [
            'ticket_id' => $ticket_id,
            'special_offer_id' => !empty($activeSpecialOffer) ? $activeSpecialOffer->id : null,
            'created_at' => time()
        ]);

        return [
            'success' => true,
        ];
    }

    public function validateCoupon($user, $coupon)
    {
        $discountCoupon = Discount::where('code', $coupon)
            ->where('expired_at', '>', time())
            ->first();

        if (!$discountCoupon || !$discountCoupon->checkValidDiscount($user)) {
            return [
                'status' => 'invalid_coupon',
            ];
        }

        $carts = Cart::where('creator_id', $user->id)
            ->get();

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->cartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);

            if (!empty($calculate)) {
                return [
                    'status' => 'valid',
                    'amounts' => $calculate,
                    'discount' => $discountCoupon,
                ];
            }
        }

        return [
            'status' => 'empty',
        ];
    }
}
