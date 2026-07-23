<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\RegionsDataByUser;
use App\Mixins\Cashback\CashbackRules;
use App\Models\Cart;
use App\Models\CartDiscount;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Services\App\WebCartPricingEngineService;
use App\Services\App\WebCartManagementService;
use App\Services\App\WebCartCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    use RegionsDataByUser;

    protected $webCartPricingEngineService;
    protected $webCartManagementService;
    protected $webCartCheckoutService;

    public function __construct(WebCartPricingEngineService $webCartPricingEngineService, WebCartManagementService $webCartManagementService, WebCartCheckoutService $webCartCheckoutService)
    {
        $this->webCartPricingEngineService = $webCartPricingEngineService;
        $this->webCartManagementService = $webCartManagementService;
        $this->webCartCheckoutService = $webCartCheckoutService;
    }

    public function index()
    {
        $user = auth()->user();
        $result = $this->webCartManagementService->prepareCartOverview($user);
        if (!empty($result)) {
            return view($result['view'], $result['data']);
        }
        return redirect('/');
    }


    public function couponValidate(Request $request)
    {
        $user = auth()->user();
        $result = $this->webCartManagementService->validateCoupon($user, $request->get('coupon'));
        if ($result['success']) {
            return response()->json([
                'code' => 200,
                'html' => $result['html'],
            ]);
        }
        return response()->json([
            'error' => $result['error'],
        ], 422);
    }


    

    

    

    public function calculatePrice($carts, $user, $discountCoupon = null)
    {
        return $this->webCartPricingEngineService->calculatePrice($carts, $user, $discountCoupon);
    }

    public function checkout(Request $request, $carts = null)
    {
        $user = auth()->user();
        $result = $this->webCartCheckoutService->checkout($request, $user, $carts);

        switch ($result['action']) {
            case 'view':
                return view($result['view'], $result['data']);
            case 'redirect_zero_amount':
                return redirect('/payments/status?t=' . $result['order']->id);
            case 'redirect':
            default:
                return redirect($result['url'] ?? '/cart');
        }
    }

    private function getTotalCashbackAmount($carts, $user, $subTotal)
    {
        return $this->webCartPricingEngineService->getTotalCashbackAmount($carts, $user, $subTotal);
    }

    

    

    
}
