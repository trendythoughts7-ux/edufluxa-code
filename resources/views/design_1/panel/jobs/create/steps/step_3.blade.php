@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


<div class="bg-white rounded-16 p-16 mt-32">

    {{-- Employment Type --}}
    <div class="form-group">
        <label class="form-group-label">{{ trans('update.employment_type') }}</label>
        <select name="employment_type_id" class="form-control select2 @error('employment_type') is-invalid @enderror">
            <option value="" disabled selected>{{ trans('update.select_employment_type') }}</option>

            @if(!empty($employmentTypes))
                @foreach($employmentTypes as $employmentType)
                    <option value="{{ $employmentType->id }}" {{ ($job->employment_type_id == $employmentType->id) ? 'selected' : '' }}>{{ $employmentType->title }}</option>
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
                    <option value="{{ $experienceLevel->id }}" {{ ($job->experience_level_id == $experienceLevel->id) ? 'selected' : '' }}>{{ $experienceLevel->title }}</option>
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
                <option value="{{ $workArrangement }}" {{ ($job->work_arrangement == $workArrangement) ? 'selected' : '' }}>{{ trans("update.{$workArrangement}") }}</option>
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
                <option value="{{ $paymentType }}" {{ ($job->payment_type == $paymentType) ? 'selected' : '' }}>{{ trans("update.{$paymentType}") }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">@error('payment_type') {{ $message }} @enderror</div>
    </div>

    {{-- Price --}}
    <div class="form-group js-fixed-salary-field {{ ($job->payment_type == "fixed_amount") ? '' : 'd-none' }}">
        <label class="form-group-label">{{ trans('update.fixed_salary') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
        <input type="text" name="fixed_salary" value="{{ ($job->fixed_salary > 0) ? convertPriceToUserCurrency($job->fixed_salary) : old('fixed_salary') }}" class="form-control @error('fixed_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

        <div class="invalid-feedback">
            @error('fixed_salary') {{ $message }} @enderror
        </div>
    </div>

    {{-- Range Price --}}
    <div class="js-range-salary-field {{ ($job->payment_type == "range") ? '' : 'd-none' }}">
        <div class="form-group">
            <label class="form-group-label">{{ trans('update.min_salary') }}</label>
            <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
            <input type="text" name="min_salary" value="{{ ($job->min_salary > 0) ? convertPriceToUserCurrency($job->min_salary) : old('min_salary') }}" class="form-control @error('min_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

            <div class="invalid-feedback">
                @error('min_salary') {{ $message }} @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-group-label">{{ trans('update.max_salary') }}</label>
            <span class="has-translation bg-gray-300 rounded-8 p-8 text-gray-500">{{ $currency }}</span>
            <input type="text" name="max_salary" value="{{ ($job->max_salary > 0) ? convertPriceToUserCurrency($job->max_salary) : old('max_salary') }}" class="form-control @error('max_salary')  is-invalid @enderror" oninput="validatePrice(this)"/>

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
                <option value="{{ $paymentPeriod }}" {{ ($job->payment_period == $paymentPeriod) ? 'selected' : '' }}>{{ trans("update.{$paymentPeriod}") }}</option>
            @endforeach
        </select>

        <div class="invalid-feedback">@error('payment_period') {{ $message }} @enderror</div>
    </div>

    {{-- Location --}}
    @include('design_1.panel.jobs.create.includes.location')

</div>


@push('scripts_bottom')

    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
        var selectRegionDefaultVal = '';
        var selectStateLang = '{{ trans('update.choose_a_state') }}';
        var selectCityLang = '{{ trans('update.choose_a_city') }}';
        var selectDistrictLang = '{{ trans('update.all_districts') }}';
    </script>

    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>
    <script src="{{ getDesign1ScriptPath("leaflet_map") }}"></script>
    <script src="/assets/design_1/js/parts/get_regions.min.js"></script>
@endpush
