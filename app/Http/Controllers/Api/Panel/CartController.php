<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\CartResource;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Logs\UserLoginHistoryMixin;
use App\Models\Product;
use App\Models\ProductOrder;
use App\User;
use Illuminate\Http\Request;
use App\Models\Api\Cart;
use App\Models\ReserveMeeting;
use App\Models\Api\Webinar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Discount;
use App\Models\PaymentChannel;
use App\Models\OrderItem;
use App\Services\App\CartPricingEngineService;
use App\Services\App\CartManagementService;
use App\Services\App\CartMutationService;
use App\Services\App\CartCheckoutService;
use App\Services\App\CartWebCheckoutService;
use App\Models\Order;
use Illuminate\Support\Facades\URL;


class CartController extends Controller
{
    protected $cartPricingEngineService;
    protected $cartManagementService;
    protected $cartMutationService;
    protected $cartCheckoutService;
    protected $cartWebCheckoutService;

    public function __construct(CartPricingEngineService $cartPricingEngineService, CartManagementService $cartManagementService, CartMutationService $cartMutationService, CartCheckoutService $cartCheckoutService, CartWebCheckoutService $cartWebCheckoutService)
    {
        $this->cartPricingEngineService = $cartPricingEngineService;
        $this->cartManagementService = $cartManagementService;
        $this->cartMutationService = $cartMutationService;
        $this->cartCheckoutService = $cartCheckoutService;
        $this->cartWebCheckoutService = $cartWebCheckoutService;
    }

    public function index()
    {
        $user = apiAuth();
        $cartt = $this->cartManagementService->index($user);

        return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
            'cart' => $cartt
        ]);
    }

    public function destroy($id)
    {
        $user_id = apiAuth()->id;
        $this->cartManagementService->destroy($id, $user_id);
        return apiResponse2(1, 'deleted', trans('api.public.deleted'));
    }

    public function store(Request $request)
    {
        $user = apiAuth();

        validateParam($request->all(),
            [
                'webinar_id' => ['required',
                    Rule::exists('webinars', 'id')->where('private', false)
                        ->where('status', 'active')
                ],
                'ticket_id' => 'nullable',
            ]
        );

        $webinar_id = $request->get('webinar_id');
        $ticket_id = $request->input('ticket_id');

        $result = $this->cartMutationService->store($user, $webinar_id, $ticket_id);

        if (!$result['success']) {
            return apiResponse2(0, $result['reason'], trans('api.course.purchase.' . $result['reason']));
        }

        return apiResponse2(1, 'stored', trans('api.public.store'));
    }

    public function validateCoupon(Request $request)
    {
        $user = apiAuth();
        $coupon = $request->get('coupon');

        $result = $this->cartMutationService->validateCoupon($user, $coupon);

        if ($result['status'] === 'invalid_coupon') {
            return apiResponse2(0, 'invalid', trans('api.cart.invalid_coupon'));
        }

        if ($result['status'] === 'valid') {
            return apiResponse2(1, 'valid', trans('api.cart.valid_coupon'), [
                'amounts' => $result['amounts'],
                'discount' => $result['discount'],
            ]);
        }

        return apiResponse2(0, 'invalid', trans('api.cart.is_empty'));
    }


    public function webCheckoutGenerator(Request $request)
    {
        return $this->cartWebCheckoutService->webCheckoutGenerator($request);
    }

    public function webCheckoutRender(Request $request, User $user)
    {
        $discount_id = $this->cartWebCheckoutService->webCheckoutRender($request, $user);

        return view('api.checkout', compact('discount_id'));
    }


    public function checkout(Request $request)
    {
        $user = apiAuth();
        $discountId = $request->input('discount_id');

        $result = $this->cartCheckoutService->checkout($user, $discountId);

        if ($result['status'] === 'empty_cart') {
            return apiResponse2(0, 'empty_cart', trans('api.payment.empty_cart'));
        }

        if ($result['status'] === 'zero_total_paid') {
            return apiResponse2(1, 'paid', trans('api.payment.paid'));
        }

        return apiResponse2(1, 'checkout', trans('api.cart.checkout'), $result['data']);
    }


    private function calculatePrice($carts, $user, $discountCoupon = null)
    {
        return $this->cartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);
    }

    private function getSeller($cart)
    {
        return $this->cartPricingEngineService->getSeller($cart);
    }

    private function getCommissionPrice($source, $itemPrice, $seller = null)
    {
        return $this->cartPricingEngineService->getCommissionPrice($source, $itemPrice, $seller);
    }


    private function handleOrderPrices($cart, $user, $taxIsDifferent = false)
    {
        return $this->cartPricingEngineService->handleOrderPrices($cart, $user, $taxIsDifferent);
    }

    private function productDeliveryFeeBySeller($carts)
    {
        return $this->cartPricingEngineService->productDeliveryFeeBySeller($carts);
    }

    private function productCountBySeller($carts)
    {
        return $this->cartPricingEngineService->productCountBySeller($carts);
    }

    private function calculateProductDeliveryFee($carts)
    {
        return $this->cartPricingEngineService->calculateProductDeliveryFee($carts);
    }
    private function getTotalCashbackAmount($carts, $user, $subTotal)
    {
        return $this->cartPricingEngineService->getTotalCashbackAmount($carts, $user, $subTotal);
    }

    private function getCouponDiscountByCartItem($couponDiscount, $cart, $user)
    {
        return $this->cartPricingEngineService->getCouponDiscountByCartItem($couponDiscount, $cart, $user);
    }

    private function taxIsDifferent($carts)
    {
        return $this->cartPricingEngineService->taxIsDifferent($carts);
    }

    private function handleDiscountPrice($discount, $carts, $subTotal)
    {
        return $this->cartPricingEngineService->handleDiscountPrice($discount, $carts, $subTotal);
    }
}
