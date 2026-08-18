<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Mixins\Logs\UserLoginHistoryMixin;
use App\Models\Api\UserFirebaseSessions;
use App\User;
use App\Models\Api\TwoFactorPendingToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FAQRCode\Google2FA;

class LoginController extends Controller
{
    private $username;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|string|numeric',
            'password' => 'required|string|min:6',
        ];

        if ($this->username() == 'email') {
            $rules['username'] = 'required|string|email';
        }
        validateParam($request->all(), $rules);

        return $this->attemptLogin($request);

    }

    public function username()
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (empty($this->username)) {
            $this->username = 'mobile';
            if (preg_match($email_regex, request('username', null))) {
                $this->username = 'email';
            }
        }
        return $this->username;
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = [
            $this->username() => $request->get('username'),
            'password' => $request->get('password')
        ];


        if (!$token = auth('api')->attempt($credentials)) {
            return apiResponse2(0, 'incorrect', trans('auth.incorrect'));
        }
        return $this->afterLogged($request, $token);
    }

    public function afterLogged(Request $request, $token, $verify = false, $skipTwoFactor = false)
    {
        $user = auth('api')->user();

        if ($user->ban) {
            $time = time();
            $endBan = $user->ban_end_at;
            if (!empty($endBan) and $endBan > $time) {
                auth('api')->logout();
                return apiResponse2(0, 'banned_account', trans('auth.banned_account'));
            } elseif (!empty($endBan) and $endBan < $time) {
                $user->update([
                    'ban' => false,
                    'ban_start_at' => null,
                    'ban_end_at' => null,
                ]);
            }

        }

        if ($user->status != User::$active and !$verify) {
            // auth('api')->logout();
            auth('api')->logout();
            //  dd(apiAuth());
            $verificationController = new VerificationController();
            $checkConfirmed = $verificationController->checkConfirmed($user, $this->username(), $request->input('username'));

            if ($checkConfirmed['status'] == 'send') {

                return apiResponse2(0, 'not_verified', trans('api.auth.not_verified'));

            } elseif ($checkConfirmed['status'] == 'verified') {
                $user->update([
                    'status' => User::$active,
                ]);
            }
        } elseif ($verify) {
            $user->update([
                'status' => User::$active,
            ]);

        }

        if ($user->status != User::$active) {
            \auth('api')->logout();
            return apiResponse2(0, 'inactive_account', trans('auth.inactive_account'));
        }
        $checkLoginDeviceLimit = $this->checkLoginDeviceLimit($user);
        if ($checkLoginDeviceLimit != "ok") {
            \auth('api')->logout();
            return apiResponse2(0, 'limit_account', trans('auth.limit_account'));
        }

        if (!$skipTwoFactor && $user->needsTwoFactor() && $user->hasTwoFactorEnabled()) {
            \auth('api')->logout();

            TwoFactorPendingToken::where('user_id', $user->id)->delete();

            $rawToken = bin2hex(random_bytes(32));
            TwoFactorPendingToken::create([
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $rawToken),
                'expires_at' => now()->addMinutes(5),
            ]);

            return apiResponse2(0, 'two_factor_required', trans('update.two_step_verification'), [
                'temp_token' => $rawToken,
                'expires_in' => 300,
            ]);
        }

        $profile_completion = [];
        $data  ['token'] = $token;
        $data['user_id'] = $user->id;
        if (!$user->full_name) {
            $profile_completion[] = 'full_name';
            $data['profile_completion'] = $profile_completion;
        }

        UserFirebaseSessions::create([
            "user_id"=>$user->id,
            "token"=>$token,
            "ip"=>$request->getClientIp(),
            "fcm_token"=>"",
        ]);
        $userLoginHistoryMixin = new UserLoginHistoryMixin();
        $userLoginHistoryMixin->storeUserLoginHistory($user);
        $user->update([
            'logged_count' => $user->logged_count + 1
        ]);

        return apiResponse2(1, 'login', trans('auth.login'), $data);


    }

    public function verifyTwoFactor(Request $request)
    {
        $rules = [
            'temp_token' => 'required|string',
        ];
        validateParam($request->all(), $rules);

        $tempToken = $request->get('temp_token');
        $code = $request->get('one_time_password');
        $recoveryCode = $request->get('recovery_code');

        $tokenHash = hash('sha256', $tempToken);

        $pending = TwoFactorPendingToken::where('token_hash', $tokenHash)
            ->where('expires_at', '>', now())
            ->first();

        if (empty($pending)) {
            return apiResponse2(0, 'two_factor_invalid', trans('auth.failed'));
        }

        $user = \App\Models\Api\User::find($pending->user_id);

        if (empty($user) || !$user->needsTwoFactor() || !$user->hasTwoFactorEnabled()) {
            return apiResponse2(0, 'two_factor_invalid', trans('auth.failed'));
        }

        $verified = false;

        if (!empty($code)) {
            $google2fa = new Google2FA();
            $verified = $google2fa->verifyKey($user->google2fa_secret, $code);
        } elseif (!empty($recoveryCode)) {
            $storedCodes = $user->two_factor_recovery_codes;

            if (!empty($storedCodes) && is_array($storedCodes)) {
                foreach ($storedCodes as $index => $hashedCode) {
                    if (Hash::check($recoveryCode, $hashedCode)) {
                        $verified = true;
                        unset($storedCodes[$index]);
                        $user->two_factor_recovery_codes = array_values($storedCodes);
                        $user->save();
                        break;
                    }
                }
            }
        }

        if (!$verified) {
            return apiResponse2(0, 'two_factor_invalid', trans('update.verification_code_is_invalid'));
        }

        $pending->delete();

        $newToken = auth('api')->login($user);

        return $this->afterLogged($request, $newToken, false, true);
    }

    public function logout()
    {
        $user = auth('api')->user();
        $currentToken = (string) \Tymon\JWTAuth\Facades\JWTAuth::getToken();
        auth('api')->logout();
        if (!apiAuth()) {
            $user->update([
                'logged_count' => $user->logged_count - 1
            ]);
            $session = UserFirebaseSessions::where('token', $currentToken)->first();
            if ($session) {
                $session->delete();
            }
            $userLoginHistoryMixin = new UserLoginHistoryMixin();
            $userLoginHistoryMixin->storeUserLogoutHistory($user);
            return apiResponse2(1, 'logout', trans('auth.logout'));
        }
        return apiResponse2(0, 'failed', trans('auth.logout.failed'));
    }
    private function checkLoginDeviceLimit($user)
    {
        $securitySettings = getGeneralSecuritySettings();

        if (!empty($securitySettings) and !empty($securitySettings['login_device_limit'])) {
            $limitCount = !empty($securitySettings['number_of_allowed_devices']) ? $securitySettings['number_of_allowed_devices'] : 1;

            $count = $user->logged_count;

            if ($count >= $limitCount) {
                return "no";
            }
        }

        return 'ok';
    }


}
