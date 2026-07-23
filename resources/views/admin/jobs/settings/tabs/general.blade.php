<div class="row">
    <div class="col-12 col-md-6">
        <form action="{{ getAdminPanelUrl('/jobs/settings') }}" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="page" value="general">
            <input type="hidden" name="name" value="{{ \App\Models\Setting::$jobsGeneralSettingsName }}">
            <input type="hidden" name="locale" value="{{ \App\Models\Setting::$defaultSettingsLocale }}">


            @php
                $switches = [
                    'status',
                    'login_required_to_view_salaries',
                    'enable_search_input',
                    'enable_negotiable_salary_option',
                    'show_employer_contact',
                    'show_student_contact',
                    'display_applied_users',
                ];
            @endphp

            @foreach($switches as $switchName)
                <div class="form-group custom-switches-stacked">
                    <label class="custom-switch pl-0 d-flex align-items-center">
                        <input type="hidden" name="value[{{ $switchName }}]" value="">
                        <input type="checkbox" name="value[{{ $switchName }}]" id="jobsSettingSwitch_{{ $switchName }}" value="1" {{ (!empty($generalSettingValues) and !empty($generalSettingValues[$switchName])) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                        <span class="custom-switch-indicator"></span>
                        <label class="custom-switch-description mb-0 cursor-pointer" for="jobsSettingSwitch_{{ $switchName }}">{{ trans("update.{$switchName}") }}</label>
                    </label>
                    <div class="text-gray-500 text-small">{{ trans("update.job_setting_switch_{$switchName}_hint") }}</div>
                </div>
            @endforeach


            <div class="form-group">
                <label>{{ trans('update.jobs_expiration') }} ({{ trans('public.days') }})</label>
                <input type="number" name="value[jobs_expiration]" value="{{ (!empty($generalSettingValues) and !empty($generalSettingValues['jobs_expiration'])) ? $generalSettingValues['jobs_expiration'] : old('jobs_expiration') }}" class="form-control"/>
                <div class="text-gray-500 text-small mt-1">{{ trans('update.jobs_expiration_setting_input_hint') }}</div>
            </div>


            <div class="text-right">
                <button type="submit" class="btn btn-primary mt-1">{{ trans('admin/main.submit') }}</button>
            </div>
        </form>
    </div>
</div>
