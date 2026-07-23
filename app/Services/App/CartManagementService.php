<?php

namespace App\Services\App;

use App\Http\Resources\CartResource;
use App\Models\Api\Cart;
use App\Models\Product;
use App\Models\ReserveMeeting;

class CartManagementService
{
    protected $cartPricingEngineService;

    public function __construct(CartPricingEngineService $cartPricingEngineService)
    {
        $this->cartPricingEngineService = $cartPricingEngineService;
    }

    /**
     * Builds the cart payload for a user: items, calculated amounts,
     * total cashback, and user group. Mirrors original CartController::index()
     * behavior exactly, including the unused $deliveryEstimateTime computation
     * (dead code preserved as-is, not fixed here).
     */
    public function index($user)
    {
        $carts = Cart::where('creator_id', $user->id)
            ->with([
                'productOrder' => function ($query) {
                    $query->whereHas('product');
                }
            ])
            ->get();
        $cartt = null;

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->cartPricingEngineService->calculatePrice($carts, $user);

            $totalCashbackAmount = $this->cartPricingEngineService->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

            $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

            $deliveryEstimateTime = 0;

            if (!empty($hasPhysicalProduct) and count($hasPhysicalProduct)) {
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

                $cartt = [
                    'items' => CartResource::collection($carts),
                    'amounts' => $calculate,
                    'totalCashbackAmount' => $totalCashbackAmount,
                    'user_group' => $user->userGroup ? $user->userGroup->group : null,
                ];

            }
        }

        return $cartt;
    }

    /**
     * Deletes a cart item (and its associated reserve meeting, if any)
     * belonging to the given user. Mirrors original CartController::destroy() exactly.
     * Returns true on success; throws (via abort_unless) on not-found, same as original.
     */
    public function destroy($id, $user_id)
    {
        $cart = Cart::where('id', $id)
            ->where('creator_id', $user_id)
            ->first();
        abort_unless($cart, 404);

        if (!empty($cart->reserve_meeting_id)) {
            $reserve = ReserveMeeting::where('id', $cart->reserve_meeting_id)
                ->where('user_id', $user_id)
                ->first();

            if (!empty($reserve)) {
                $reserve->delete();
            }
        }

        $cart->delete();

        return true;
    }
}
