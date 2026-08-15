<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorController extends Controller
{
    protected function google2fa(): Google2FA
    {
        return new Google2FA();
    }

    protected function assertEligible()
    {
        $user = auth()->user();
        if (empty($user) || !$user->needsTwoFactor()) {
            abort(403, 'Two-factor authentication is not applicable to this account.');
        }
        return $user;
    }

    /**
     * Step 1: generate a new (unconfirmed) secret + QR code.
     */
    public function enroll(Request $request)
    {
        $user = $this->assertEligible();

        $google2fa = $this->google2fa();
        $secret = $google2fa->generateSecretKey();

        // Store secret but keep disabled until confirmed
        $user->google2fa_secret = $secret;
        $user->google2fa_enabled = false;
        $user->two_factor_recovery_codes = null;
        $user->save();

        $qrCodeSvg = $google2fa->getQRCodeInline(
            'EduFluxa',
            $user->email,
            $secret
        );

        return response()->json([
            'success' => true,
            'qr_code_svg' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    /**
     * Step 2: confirm the code from the authenticator app, activate 2FA,
     * issue recovery codes (shown once).
     */
    public function confirm(Request $request)
    {
        $user = $this->assertEligible();

        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);

        if (empty($user->google2fa_secret)) {
            return response()->json([
                'success' => false,
                'message' => 'No pending enrollment found. Please start enrollment again.',
            ], 422);
        }

        $google2fa = $this->google2fa();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'The code entered was incorrect. Please try again.',
            ], 422);
        }

        $codes = $this->generateRecoveryCodes();

        $user->google2fa_enabled = true;
        $user->two_factor_recovery_codes = $codes['hashed'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been enabled.',
            'recovery_codes' => $codes['plain'],
        ]);
    }

    /**
     * Disable 2FA — requires current password re-entry.
     */
    public function disable(Request $request)
    {
        $user = $this->assertEligible();

        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 422);
        }

        $user->google2fa_secret = null;
        $user->google2fa_enabled = false;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    /**
     * Regenerate recovery codes — requires current password re-entry.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $this->assertEligible();

        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 422);
        }

        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled.',
            ], 422);
        }

        $codes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $codes['hashed'];
        $user->save();

        return response()->json([
            'success' => true,
            'recovery_codes' => $codes['plain'],
        ]);
    }

    protected function generateRecoveryCodes(int $count = 10): array
    {
        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $code = Str::upper(Str::random(5)) . '-' . Str::upper(Str::random(5));
            $plain[] = $code;
            $hashed[] = Hash::make($code);
        }

        return ['plain' => $plain, 'hashed' => $hashed];
    }
}
