@extends('design_1.web.auth.theme_1.layout')
@section('page_content')
    <form method="Post" action="/2fa/challenge" class="js-auth-page-form">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="pl-16 pt-16">
            <div class="font-16 font-weight-bold">{{ trans('update.two_step_verification') }} 🔐</div>
            <h1 class="font-24 mt-4 mb-32">{{ trans('update.enter_authentication_code') }}</h1>
            <div class="bg-gray-100 p-12 rounded-16 border-gray-300 mt-24 text-gray-500">
                {{ trans('update.two_factor_challenge_hint') }}
            </div>
            <div class="js-otp-section">
                <div class="mt-16 font-12 text-gray-500">{{ trans('update.verification_code') }}</div>
                <input type="text" name="one_time_password" maxlength="6" autocomplete="off" class="form-control mt-8" placeholder="000000">
            </div>
            <div class="js-recovery-section d-none mt-16">
                <div class="font-12 text-gray-500">{{ trans('update.recovery_code') }}</div>
                <input type="text" name="recovery_code" autocomplete="off" class="form-control mt-8" placeholder="XXXXX-XXXXX">
            </div>
            @error('code')
                <div class="invalid-feedback d-block mt-8">{{ $message }}</div>
            @enderror
            @error('error')
                <div class="invalid-feedback d-block mt-8">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary btn-lg btn-block mt-24">{{ trans('update.verify') }}</button>
        </div>
    </form>
    <div class="d-flex-center flex-column text-center mt-24">
        <button type="button" class="js-toggle-recovery-btn btn-transparent font-weight-bold mt-8 text-gray-500">
            {{ trans('update.use_a_recovery_code_instead') }}
        </button>
    </div>
@endsection
@push('scripts_bottom')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggleBtn = document.querySelector('.js-toggle-recovery-btn');
            var otpSection = document.querySelector('.js-otp-section');
            var recoverySection = document.querySelector('.js-recovery-section');
            var otpInput = otpSection ? otpSection.querySelector('input[name="one_time_password"]') : null;
            var recoveryInput = recoverySection ? recoverySection.querySelector('input[name="recovery_code"]') : null;
            var usingRecovery = false;

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    usingRecovery = !usingRecovery;
                    if (usingRecovery) {
                        otpSection.classList.add('d-none');
                        recoverySection.classList.remove('d-none');
                        if (otpInput) { otpInput.value = ''; }
                        toggleBtn.textContent = '{{ trans('update.use_authenticator_app_instead') }}';
                    } else {
                        recoverySection.classList.add('d-none');
                        otpSection.classList.remove('d-none');
                        if (recoveryInput) { recoveryInput.value = ''; }
                        toggleBtn.textContent = '{{ trans('update.use_a_recovery_code_instead') }}';
                    }
                });
            }
        });
    </script>
@endpush
