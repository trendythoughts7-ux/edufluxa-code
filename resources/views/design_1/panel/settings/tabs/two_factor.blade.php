<div class="custom-tabs-content active">
    <div class="bg-white rounded-16 py-16 px-16 border-gray-200">
        <h3 class="font-14 font-weight-bold">{{ trans('update.two_factor_authentication') }}</h3>
        <p class="text-gray-500 font-12 mt-4">{{ trans('update.two_factor_authentication_description') }}</p>

        <div id="tfa-app" class="mt-16" data-enabled="{{ $twoFactorEnabled ? '1' : '0' }}">

            {{-- STATE: disabled, not yet started --}}
            <div id="tfa-state-disabled" style="{{ $twoFactorEnabled ? 'display:none;' : '' }}">
                <button type="button" id="tfa-start-enroll" class="btn btn-primary">
                    {{ trans('update.enable_two_factor_authentication') }}
                </button>
            </div>

            {{-- STATE: enrollment in progress (QR + confirm code) --}}
            <div id="tfa-state-enroll" style="display:none;" class="mt-16">
                <div id="tfa-qr-wrapper" class="d-flex-center"></div>
                <p class="text-gray-500 font-12 mt-8">{{ trans('update.two_factor_scan_qr_instructions') }}</p>

                <div class="mt-16" style="max-width:280px;">
                    <label class="font-12 font-weight-bold">{{ trans('update.enter_6_digit_code') }}</label>
                    <input type="text" id="tfa-confirm-code" class="form-control mt-4" maxlength="6" inputmode="numeric" autocomplete="one-time-code">
                </div>

                <div id="tfa-confirm-error" class="text-danger font-12 mt-8" style="display:none;"></div>

                <button type="button" id="tfa-confirm-btn" class="btn btn-primary mt-16">
                    {{ trans('update.confirm_and_enable') }}
                </button>
                <button type="button" id="tfa-cancel-enroll" class="btn btn-outline-secondary mt-16">
                    {{ trans('public.cancel') }}
                </button>
            </div>

            {{-- STATE: recovery codes, shown once right after activation --}}
            <div id="tfa-state-recovery" style="display:none;" class="mt-16">
                <p class="font-weight-bold">{{ trans('update.save_your_recovery_codes') }}</p>
                <p class="text-gray-500 font-12">{{ trans('update.recovery_codes_shown_once_warning') }}</p>
                <div id="tfa-recovery-codes-list" class="bg-gray p-16 rounded-10 mt-8"
                     style="font-family:monospace; line-height:2;"></div>
                <button type="button" id="tfa-recovery-done-btn" class="btn btn-primary mt-16">
                    {{ trans('public.done') }}
                </button>
            </div>

            {{-- STATE: already enabled --}}
            <div id="tfa-state-enabled" style="{{ $twoFactorEnabled ? '' : 'display:none;' }}" class="mt-16">
                <div class="d-flex align-items-center gap-8">
                    <x-iconsax-lin-shield-tick class="icons text-success" width="20px" height="20px"/>
                    <span class="text-success font-weight-bold">{{ trans('update.two_factor_is_enabled') }}</span>
                </div>

                <div class="mt-16" style="max-width:320px;">
                    <label class="font-12 font-weight-bold">{{ trans('public.current_password') }}</label>
                    <input type="password" id="tfa-disable-password" class="form-control mt-4" autocomplete="current-password">
                </div>
                <div id="tfa-disable-error" class="text-danger font-12 mt-8" style="display:none;"></div>

                <button type="button" id="tfa-disable-btn" class="btn btn-danger mt-16">
                    {{ trans('update.disable_two_factor_authentication') }}
                </button>
            </div>

        </div>
    </div>
</div>
