<?php

namespace App\Services\App;

use App\Mixins\Logs\UserLoginHistoryMixin;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class CartWebCheckoutService
{
    public function webCheckoutGenerator($request)
    {
        return apiResponse2(1, 'generated', trans('api.link.generated'),
            [
                'link' => URL::signedRoute('my_api.web.checkout', [apiAuth()->id, 'discount_id' => $request->input('discount_id')])
                ,
            ]
        );
    }

    public function webCheckoutRender($request, $user)
    {
        $discount_id = $request->input('discount_id');
        Auth::login($user, true);

        $userLoginHistoryMixin = new UserLoginHistoryMixin();
        $userLoginHistoryMixin->storeUserLoginHistory($user);

        return $discount_id;
    }
}
