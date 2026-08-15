<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorChallengeController extends Controller
{
    protected function google2fa(): Google2FA
    {
        return new Google2FA();
    }

    protected function getPendingUser(Request $request)
    {
        $pendingId = $request->session()->get('2fa_pending_user_id');
        $pendingAt = $request->session()->get('2fa_pending_at');

        if (empty($pendingId) || empty($pendingAt)) {
            return null;
        }

        // 5 minute expiry on the pending challenge
        if ((time() - (int)$pendingAt) > 300) {
            $request->session()->forget('2fa_pending_user_id');
            $request->session()->forget('2fa_pending_at');
            return null;
        }

        $user = User::find($pendingId);

        if (empty($user) || !$user->needsTwoFactor() || !$user->hasTwoFactorEnabled()) {
            return null;
        }

        return $user;
    }

    public function show(Request $request)
    {
        $user = $this->getPendingUser($request);

        if (empty($user)) {
            return redirect('/login')->withErrors([
                'error' => trans('auth.failed'),
            ]);
        }

        $data = [
            'pageTitle' => trans('update.two_step_verification'),
        ];

        $authTemplate = getThemeAuthenticationPagesStyleName();
        return view("design_1.web.auth.{$authTemplate}.two_factor_challenge.index", $data);
    }

    public function verify(Request $request)
    {
        $user = $this->getPendingUser($request);

        if (empty($user)) {
            return redirect('/login')->withErrors([
                'error' => trans('auth.failed'),
            ]);
        }

        $code = $request->get('one_time_password');
        $recoveryCode = $request->get('recovery_code');

        $verified = false;

        if (!empty($code)) {
            $google2fa = $this->google2fa();
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
            return back()->withErrors([
                'code' => trans('update.verification_code_is_invalid'),
            ]);
        }

        $request->session()->forget('2fa_pending_user_id');
        $request->session()->forget('2fa_pending_at');

        Auth::loginUsingId($user->id);

        $loginController = new LoginController();
        return $loginController->afterLogged($request, false, true);
    }
}
