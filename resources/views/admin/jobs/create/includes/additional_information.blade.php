<h2 class="section-title after-line mt-24">{{ trans('public.additional_information') }}</h2>

<div class="row">
    <div class="col-12 col-md-6">

        <div class="form-group">
            <label class="form-group-label">{{ trans('update.recruitment_end_date') }}</label>
            <span class="has-translation bg-transparent text-gray-500"><x-iconsax-lin-calendar-2 class="text-gray-border" width="24px" height="24px"/></span>
            <input type="text" name="hiring_end_date" value="{{ (!empty($job) and $job->hiring_end_date) ? dateTimeFormat($job->hiring_end_date, 'Y-m-d H:i', false) : old('hiring_end_date') }}" class="form-control @error('hiring_end_date')  is-invalid @enderror datetimepicker js-default-init-date-picker" aria-describedby="dateInputGroupPrepend" autocomplete="off"/>

            @error('hiring_end_date')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>


        <div class="form-group">
            <label class="form-group-label">{{ trans('update.company_size') }}</label>
            <input type="number" name="company_size" value="{{ !empty($job) ? $job->company_size : old('company_size') }}" class="form-control @error('company_size')  is-invalid @enderror"/>
            @error('company_size')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
            <div class="text-gray-500 mt-8 font-12">{{ trans('update.job_company_size_input_hint') }}</div>
        </div>


        {{-- Employment Type --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.employment_type') }}</label>
            <select name="employment_type_id" class="form-control select2 @error('employment_type') is-invalid @enderror">
                <option value="" disabled selected>{{ trans('update.select_employment_type') }}</option>

                @if(!empty($employmentTypes))
                    @foreach($employmentTypes as $employmentType)
                        <option value="{{ $employmentType->id }}" {{ (!empty($job) and $job->employment_type_id == $employmentType->id) ? 'selected' : '' }}>{{ $employmentType->title }}</option>
                    @endforeach
                @endif
            </select>

            <div class="invalid-feedback">@error('employment_type') {{ $message }} @enderror</div>
        </div>

        {{-- Experience Level --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.experience_level') }}</label>
            <select name="experience_level_id" class="form-control select2 @error('experience_level') is-invalid @enderror">
                <option value="" disabled selected>{{ trans('update.select_experience_level') }}</option>

                @if(!empty($experienceLevels))
                    @foreach($experienceLevels as $experienceLevel)
                        <option value="{{ $experienceLevel->id }}" {{ (!empty($job) and $job->experience_level_id == $experienceLevel->id) ? 'selected' : '' }}>{{ $experienceLevel->title }}</option>
                    @endforeach
                @endif
            </select>

            <div class="invalid-feedback">@error('experience_level') {{ $message }} @enderror</div>
        </div>

        {{-- Work Arrangement --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.work_arrangement') }}</label>
            <select name="work_arrangement" class="form-control select2 @error('work_arrangement') is-invalid @enderror" data-minimum-results-for-search="Infinity">
                @foreach(['on_site', 'remote', 'hybrid'] as $workArrangement)
                    <option value="{{ $workArrangement }}" {{ (!empty($job) and $job->work_arrangement == $workArrangement) ? 'selected' : '' }}>{{ trans("update.{$workArrangement}") }}</option>
                @endforeach
            </select>

            <div class="invalid-feedback">@error('work_arrangement') {{ $message }} @enderror</div>
        </div>

        {{-- Total Vacancies --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.total_vacancies') }}</label>
            <input type="number" name="hiring_count" value="{{ !empty($job) ? $job->hiring_count : old('hiring_count') }}" class="form-control @error('hiring_count')  is-invalid @enderror"/>
            @error('hiring_count')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
            <div class="text-gray-500 mt-8 font-12">{{ trans('update.job_total_vacancies_input_hint') }}</div>
        </div>

        <h3 class="font-14 font-weight-bold mb-24">{{ trans('update.salary_options') }}</h3>

        {{-- Payment Type --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.payment_type') }}</label>
            <select name="payment_type" class="js-job-payment-type-select form-control select2 @error('payment_type') is-invalid @enderror" data-minimum-results-for-search="Infinity">
                @foreach(getAvailableJobSalaryOptions() as $paymentType)
                    <option value="{{ $paymentType }}" {{ (!empty($job) and $job->payment_type == $paymentType) ? 'selected' : '' }}>{{ trans("update.{$paymentType}") }}</option>
                @endforeach
            </select>

            <div class="invalid-feedback">@error('payment_type') {{ $message }} @enderror</div>
        </div>

        {{-- Price --}}
        <div class="form-group js-fixed-salary-field {{ (empty($job) or $job->payment_type == "fixed_amount") ? '' : 'd-none' }}">
            <label class="form-group-label">{{ trans('update.fixed_salary') }}</label>
            <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
            <input type="text" name="fixed_salary" value="{{ (!empty($job) and $job->fixed_salary > 0) ? convertPriceToUserCurrency($job->fixed_salary) : old('fixed_salary') }}" class="form-control @error('fixed_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

            <div class="invalid-feedback">
                @error('fixed_salary') {{ $message }} @enderror
            </div>
        </div>

        {{-- Range Price --}}
        <div class="js-range-salary-field {{ (!empty($job) and $job->payment_type == "range") ? '' : 'd-none' }}">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.min_salary') }}</label>
                <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
                <input type="text" name="min_salary" value="{{ (!empty($job) and $job->min_salary > 0) ? convertPriceToUserCurrency($job->min_salary) : old('min_salary') }}" class="form-control @error('min_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

                <div class="invalid-feedback">
                    @error('min_salary') {{ $message }} @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-group-label">{{ trans('update.max_salary') }}</label>
                <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
                <input type="text" name="max_salary" value="{{ (!empty($job) and $job->max_salary > 0) ? convertPriceToUserCurrency($job->max_salary) : old('max_salary') }}" class="form-control @error('max_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

                <div class="invalid-feedback">
                    @error('max_salary') {{ $message }} @enderror
                </div>
            </div>
        </div>

        {{-- Payment Period --}}
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.payment_period') }}</label>
            <select name="payment_period" class="form-control select2 @error('payment_period') is-invalid @enderror" data-minimum-results-for-search="Infinity">
                @foreach(['hour', 'day', 'month', 'year', 'project'] as $paymentPeriod)
                    <option value="{{ $paymentPeriod }}" {{ (!empty($job) and $job->payment_period == $paymentPeriod) ? 'selected' : '' }}>{{ trans("update.{$paymentPeriod}") }}</option>
                @endforeach
            </select>

            <div class="invalid-feedback">@error('payment_period') {{ $message }} @enderror</div>
        </div>

        <div class="form-group mt-30 d-flex align-items-center justify-content-between">
            <label class="cursor-pointer" for="privateSwitch">{{ trans('webinars.private') }}</label>
            <div class="custom-control custom-switch">
                <input type="checkbox" name="private" class="custom-control-input" id="privateSwitch" {{ (!empty($job) and $job->private) ? 'checked' : ''  }}>
                <label class="custom-control-label" for="privateSwitch"></label>
            </div>
        </div>

        {{-- Product Badges --}}
        @if(!empty($job))
            @include('admin.product_badges.content_include', ['itemTarget' => $job])
        @endif

        @php
            $jobTags = !empty($job) ? $job->tags->pluck('title')->toArray() : [];
        @endphp

        <div class="form-group mt-15">
            <label class="input-label d-block">{{ trans('public.tags') }}</label>
            <input type="text" name="tags" data-max-tag="5" value="{{ !empty($jobTags) ? implode(',', $jobTags) : '' }}" class="form-control inputtags" placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('admin/main.max') }} : 5)"/>
        </div>

    </div>
</div>
