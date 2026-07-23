<div class="position-relative jobs-lists-filters">
    <div class="jobs-lists-filters__mask"></div>

    <div id="leftFiltersAccordion" class="position-relative card-before-line card-before-line__4-12 bg-white py-16 rounded-24 z-index-2">
        <div class="font-14 font-weight-bold text-dark px-16">
            {{ trans('update.job_categories') }}
        </div>


        @foreach($jobCategories as $jobCategory)
            @if(!empty($jobCategory->subCategories) and count($jobCategory->subCategories))
                <div class="accordion border-bottom-gray-100 mt-16 px-16 pb-16">
                    <div class="accordion__title d-flex align-items-center justify-content-between">
                        <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersCategory{{ $jobCategory->id }}" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                            {{ $jobCategory->title }}
                        </div>

                        <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersCategory{{ $jobCategory->id }}" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                            <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                        </span>
                    </div>

                    <div id="leftFiltersCategory{{ $jobCategory->id }}" class="accordion__collapse pt-0 mt-0 border-0 {{ $loop->first ? 'show' : '' }}" role="tabpanel">
                        <div class="pl-8">
                            @foreach($jobCategory->subCategories as $subCategory)
                                <div class="js-get-view-data-by-tab mt-16 cursor-pointer {{ (request()->get("category_id") == $subCategory->id) ? 'active' :'' }}" data-filter-name="category_id" data-filter-value="{{ $subCategory->id }}" data-container-id="listsContainer">{{ $subCategory->title }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="js-get-view-data-by-tab mt-16 px-16 cursor-pointer font-weight-bold {{ (request()->get("category_id") == $jobCategory->id) ? 'active' :'' }}" data-filter-name="category_id" data-filter-value="{{ $jobCategory->id }}" data-container-id="listsContainer">{{ $jobCategory->title }}</div>
            @endif
        @endforeach
    </div>
</div>


<div class="position-relative jobs-lists-filters mt-28">
    <div class="jobs-lists-filters__mask"></div>

    <div id="leftFiltersAccordion" class="position-relative bg-white py-16 rounded-24 z-index-2">

        {{-- Filter Jobs --}}
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersJobTypes" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('update.filter_jobs') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersJobTypes" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="leftFiltersJobTypes" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                {{-- Employment Type --}}
                @if(!empty($employmentTypes) and $employmentTypes->isNotEmpty())
                    <div class="form-group mt-24">
                        <label class="form-group-label">{{ trans('update.employment_type') }}</label>
                        <select name="employment_type" class="form-control select2">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($employmentTypes as $employmentType)
                                <option value="{{ $employmentType->id }}">{{ $employmentType->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Experience Level --}}
                @if(!empty($experienceLevels) and $experienceLevels->isNotEmpty())
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.experience_level') }}</label>
                        <select name="experience_level" class="form-control select2">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($experienceLevels as $experienceLevel)
                                <option value="{{ $experienceLevel->id }}">{{ $experienceLevel->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Work Arrangement --}}
                <div class="form-group mb-0 mt-20">
                    <label class="form-group-label">{{ trans('update.work_arrangement') }}</label>
                    <select name="work_arrangement" class="form-control select2">
                        <option value="">{{ trans('public.all') }}</option>

                        @foreach(['on_site', 'remote', 'hybrid'] as $workArrangement)
                            <option value="{{ $workArrangement }}">{{ trans("update.{$workArrangement}") }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>


        {{-- Location --}}
        <div class="accordion card-before-line card-before-line__4-12 py-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersLocation" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('update.location') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersLocation" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="leftFiltersLocation" class="js-jobs-location-filter accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">

                <div class="form-group  mt-24">
                    <label class="form-group-label">{{ trans('update.country') }}</label>

                    <select name="country_id" class="js-ajax-country_id js-country-selection form-control select2" data-regions-parent="js-jobs-location-filter" data-map-zoom="5">
                        <option value="">{{ trans('update.choose_a_country') }}</option>

                        @if(!empty($countries))
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ (request()->get('country_id') == $country->id) ? 'selected' : '' }}>{{ $country->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group ">
                    <label class="form-group-label">{{ trans('update.state') }}</label>

                    <select
                            name="province_id"
                            class="js-ajax-province_id js-state-selection form-control select2"
                            data-regions-parent="js-jobs-location-filter"
                            data-map-zoom="8"
                            {{ empty($provinces) ? 'disabled' : '' }}
                    >
                        <option value="">{{ trans('update.choose_a_state') }}</option>

                        @if(!empty($provinces))
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ (request()->get('province_id') == $province->id) ? 'selected' : '' }}>{{ $province->title }}</option>
                            @endforeach
                        @endif

                    </select>

                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group ">
                    <label class="form-group-label">{{ trans('update.city') }}</label>

                    <select name="city_id"
                            class="js-ajax-city_id js-city-selection form-control select2"
                            data-regions-parent="js-jobs-location-filter"
                            data-map-zoom="12"
                            {{ empty($cities) ? 'disabled' : '' }}
                    >
                        <option value="">{{ trans('update.choose_a_city') }}</option>

                        @if(!empty($cities))
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ (request()->get('city_id') == $city->id) ? 'selected' : '' }}>{{ $city->title }}</option>
                            @endforeach
                        @endif

                    </select>

                    <div class="invalid-feedback"></div>
                </div>

            </div>
        </div>

        {{-- More Options --}}
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersMoreOptions" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('site.more_options') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersMoreOptions" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            @php
                if (!empty(getJobsGeneralSettings("enable_negotiable_salary_option"))) {
                    $moreOptionsItems = ['featured_jobs', 'negotiable_salary'];
                } else {
                    $moreOptionsItems = ['featured_jobs'];
                }
            @endphp

            <div id="leftFiltersMoreOptions" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                @foreach($moreOptionsItems as $moreOption)
                    <div class="custom-control custom-checkbox {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                        <input type="checkbox" name="more_options[]" value="{{ $moreOption }}" id="filter_more_option_{{ $moreOption }}" class="custom-control-input">
                        <label class="custom-control__label cursor-pointer" for="filter_more_option_{{ $moreOption }}">{{ trans('update.'.$moreOption) }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Prices Filters --}}
        <div class="accordion card-before-line card-before-line__4-12 p-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersPrices" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('update.salary_range') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersPrices" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="leftFiltersPrices" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">

                <div class="d-flex align-items-center mt-16">
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ trans('update.free') }}" class="js-filters-min-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                    <div class="mx-4"></div>
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ handlePrice($filterMaxPrice) }}" class="js-filters-max-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                </div>

                <div
                        class="course-list-price-range range wrunner-value-bottom no-bottom-value-note mt-8"
                        id="priceRange"
                        data-minLimit="0"
                        data-maxLimit="{{ $filterMaxPrice }}"
                        data-step="{{ ($filterMaxPrice < 100) ? 2 : (($filterMaxPrice < 500) ? 50 : 100) }}"
                >
                    <input type="hidden" name="min_price" value="" class="js-range-input-view-data">
                    <input type="hidden" name="max_price" value="" class="js-range-input-view-data">
                </div>

            </div>
        </div>

        {{-- Filter Jobs --}}
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#leftFiltersJobEmployer" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('update.employer') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#leftFiltersJobEmployer" data-parent="#leftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="leftFiltersJobEmployer" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">

                <div class="form-group mt-24 ">
                    <label class="form-group-label" for="employer_type">{{ trans('update.employer_type') }}</label>

                    <select name="role" id="employer_type" class="form-control select2" data-minimum-results-for-search="Infinity">
                        <option value="">{{ trans('public.all') }}</option>
                        <option value="{{ \App\Models\Role::$teacher }}" {{ (request()->get('role') == \App\Models\Role::$teacher) ? 'selected' : '' }}>{{ trans('public.instructor') }}</option>
                        <option value="{{ \App\Models\Role::$organization }}" {{ (request()->get('role') == \App\Models\Role::$organization) ? 'selected' : '' }}>{{ trans('home.organization') }}</option>
                    </select>
                </div>

                <div class="form-group mb-0 ">
                    <label class="form-group-label">{{ trans('update.employer') }}</label>
                    <select name="employer" class="form-control searchable-select bg-white" data-allow-clear="true" data-placeholder="{{ trans('update.search_and_select_employer') }}"
                            data-api-path="/users/search"
                            data-item-column-name="full_name"
                            data-option=""
                    >

                    </select>
                </div>

            </div>
        </div>

    </div>
</div>
