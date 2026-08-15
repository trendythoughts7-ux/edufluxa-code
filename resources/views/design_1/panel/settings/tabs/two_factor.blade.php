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
            {{-- STATE: recovery codes, shown once right after activation OR after regeneration --}}
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

                <div class="mt-16 pt-16" style="border-top:1px solid #eee;">
                    <p class="font-12 text-gray-500">{{ trans('update.regenerate_recovery_codes_description') }}</p>
                    <div style="max-width:320px;">
                        <label class="font-12 font-weight-bold">{{ trans('public.current_password') }}</label>
                        <input type="password" id="tfa-regenerate-password" class="form-control mt-4" autocomplete="current-password">
                    </div>
                    <div id="tfa-regenerate-error" class="text-danger font-12 mt-8" style="display:none;"></div>
                    <button type="button" id="tfa-regenerate-btn" class="btn btn-outline-secondary mt-16">
                        {{ trans('update.regenerate_recovery_codes') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts_bottom')
    <script>
        (function () {
            'use strict';

            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

            var app = document.getElementById('tfa-app');
            if (!app) return;

            var stateDisabled = document.getElementById('tfa-state-disabled');
            var stateEnroll = document.getElementById('tfa-state-enroll');
            var stateRecovery = document.getElementById('tfa-state-recovery');
            var stateEnabled = document.getElementById('tfa-state-enabled');

            var startEnrollBtn = document.getElementById('tfa-start-enroll');
            var cancelEnrollBtn = document.getElementById('tfa-cancel-enroll');
            var confirmBtn = document.getElementById('tfa-confirm-btn');
            var confirmCodeInput = document.getElementById('tfa-confirm-code');
            var confirmError = document.getElementById('tfa-confirm-error');
            var qrWrapper = document.getElementById('tfa-qr-wrapper');

            var recoveryDoneBtn = document.getElementById('tfa-recovery-done-btn');
            var recoveryList = document.getElementById('tfa-recovery-codes-list');

            var disableBtn = document.getElementById('tfa-disable-btn');
            var disablePasswordInput = document.getElementById('tfa-disable-password');
            var disableError = document.getElementById('tfa-disable-error');

            var regenerateBtn = document.getElementById('tfa-regenerate-btn');
            var regeneratePasswordInput = document.getElementById('tfa-regenerate-password');
            var regenerateError = document.getElementById('tfa-regenerate-error');

            function showOnly(el) {
                [stateDisabled, stateEnroll, stateRecovery, stateEnabled].forEach(function (s) {
                    if (s) s.style.display = (s === el) ? '' : 'none';
                });
            }

            function setBtnLoading(btn, loading) {
                if (!btn) return;
                btn.disabled = loading;
            }

            function postJson(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(body || {})
                }).then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, status: res.status, data: data };
                    });
                });
            }

            function renderRecoveryCodes(codes) {
                recoveryList.innerHTML = '';
                (codes || []).forEach(function (rc) {
                    var line = document.createElement('div');
                    line.textContent = rc;
                    recoveryList.appendChild(line);
                });
            }

            // --- Start enrollment ---
            if (startEnrollBtn) {
                startEnrollBtn.addEventListener('click', function () {
                    setBtnLoading(startEnrollBtn, true);
                    postJson('/panel/setting/2fa/enroll').then(function (result) {
                        setBtnLoading(startEnrollBtn, false);
                        if (result.ok && result.data.success) {
                            qrWrapper.innerHTML = result.data.qr_code_svg;
                            confirmCodeInput.value = '';
                            confirmError.style.display = 'none';
                            showOnly(stateEnroll);
                        } else {
                            alert(result.data.message || 'Could not start enrollment. Please try again.');
                        }
                    }).catch(function () {
                        setBtnLoading(startEnrollBtn, false);
                        alert('Network error. Please try again.');
                    });
                });
            }

            // --- Cancel enrollment (client-side only; pending secret stays disabled
            //     server-side and is overwritten automatically on next enroll()) ---
            if (cancelEnrollBtn) {
                cancelEnrollBtn.addEventListener('click', function () {
                    showOnly(stateDisabled);
                });
            }

            // --- Confirm code, activate 2FA ---
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    var code = (confirmCodeInput.value || '').trim();
                    confirmError.style.display = 'none';

                    if (!/^\d{6}$/.test(code)) {
                        confirmError.textContent = 'Enter the 6-digit code from your authenticator app.';
                        confirmError.style.display = '';
                        return;
                    }

                    setBtnLoading(confirmBtn, true);
                    postJson('/panel/setting/2fa/confirm', { one_time_password: code }).then(function (result) {
                        setBtnLoading(confirmBtn, false);
                        if (result.ok && result.data.success) {
                            renderRecoveryCodes(result.data.recovery_codes);
                            showOnly(stateRecovery);
                        } else {
                            confirmError.textContent = result.data.message || 'The code entered was incorrect. Please try again.';
                            confirmError.style.display = '';
                        }
                    }).catch(function () {
                        setBtnLoading(confirmBtn, false);
                        confirmError.textContent = 'Network error. Please try again.';
                        confirmError.style.display = '';
                    });
                });
            }

            // --- Done reading recovery codes -> show enabled state ---
            if (recoveryDoneBtn) {
                recoveryDoneBtn.addEventListener('click', function () {
                    showOnly(stateEnabled);
                });
            }

            // --- Disable 2FA ---
            if (disableBtn) {
                disableBtn.addEventListener('click', function () {
                    var password = disablePasswordInput.value || '';
                    disableError.style.display = 'none';

                    if (!password) {
                        disableError.textContent = 'Enter your current password.';
                        disableError.style.display = '';
                        return;
                    }

                    setBtnLoading(disableBtn, true);
                    postJson('/panel/setting/2fa/disable', { password: password }).then(function (result) {
                        setBtnLoading(disableBtn, false);
                        if (result.ok && result.data.success) {
                            disablePasswordInput.value = '';
                            showOnly(stateDisabled);
                        } else {
                            disableError.textContent = result.data.message || 'Incorrect password.';
                            disableError.style.display = '';
                        }
                    }).catch(function () {
                        setBtnLoading(disableBtn, false);
                        disableError.textContent = 'Network error. Please try again.';
                        disableError.style.display = '';
                    });
                });
            }

            // --- Regenerate recovery codes ---
            if (regenerateBtn) {
                regenerateBtn.addEventListener('click', function () {
                    var password = regeneratePasswordInput.value || '';
                    regenerateError.style.display = 'none';

                    if (!password) {
                        regenerateError.textContent = 'Enter your current password.';
                        regenerateError.style.display = '';
                        return;
                    }

                    setBtnLoading(regenerateBtn, true);
                    postJson('/panel/setting/2fa/recovery-codes/regenerate', { password: password }).then(function (result) {
                        setBtnLoading(regenerateBtn, false);
                        if (result.ok && result.data.success) {
                            regeneratePasswordInput.value = '';
                            renderRecoveryCodes(result.data.recovery_codes);
                            showOnly(stateRecovery);
                        } else {
                            regenerateError.textContent = result.data.message || 'Incorrect password.';
                            regenerateError.style.display = '';
                        }
                    }).catch(function () {
                        setBtnLoading(regenerateBtn, false);
                        regenerateError.textContent = 'Network error. Please try again.';
                        regenerateError.style.display = '';
                    });
                });
            }
        })();
    </script>
@endpush
