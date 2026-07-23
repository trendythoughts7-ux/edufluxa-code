@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
@endpush

<div class="bg-white rounded-16 p-16 mt-32">
    <h3 class="font-14 font-weight-bold">{{ trans('update.taxonomy') }}</h3>

    <div class="form-group  mt-24">
        <label class="form-group-label is-required">{{ trans('public.category') }}</label>

        <select name="category_id" id="jobCategories" class="select2 @error('category_id')  is-invalid @enderror">
            <option {{ (!empty($job) and !empty($job->category_id)) ? '' : 'selected' }} disabled>{{ trans('public.choose_category') }}</option>

            @foreach($jobCategories as $jobCategory)
                @if(!empty($jobCategory->subCategories) and $jobCategory->subCategories->count() > 0)
                    <optgroup label="{{  $jobCategory->title }}">
                        @foreach($jobCategory->subCategories as $subCategory)
                            <option value="{{ $subCategory->id }}" {{ ((!empty($job) and $job->category_id == $subCategory->id) or old('category_id') == $subCategory->id) ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $jobCategory->id }}" {{ ((!empty($job) and $job->category_id == $jobCategory->id) or old('category_id') == $jobCategory->id) ? 'selected' : '' }}>{{ $jobCategory->title }}</option>
                @endif
            @endforeach
        </select>

        @error('category_id')
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
        @enderror
    </div>


    <div class="mt-24 {{ (!empty($jobCategoryFilters) and count($jobCategoryFilters)) ? '' : 'd-none' }}" id="categoriesFiltersContainer">
        <h3 class="font-14 font-weight-bold">{{ trans('public.category_filters') }}</h3>

        <div id="categoriesFiltersCard" class="row">
            @if(!empty($jobCategoryFilters) and count($jobCategoryFilters))
                @include('design_1.panel.jobs.create.includes.category_filters', [
                    'jobCategoryFilters' => $jobCategoryFilters,
                    'jobFilterOptions' => (!empty($job->filterOptions) and $job->filterOptions->isNotEmpty()) ? $job->filterOptions->pluck('filter_option_id')->toArray() : [],
                ])
            @endif
        </div>
    </div>

    {{-- More Options --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('site.more_options') }}</h3>

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

    <div class="form-group d-flex align-items-center">
        <div class="custom-switch mr-8">
            <input id="privateSwitch" type="checkbox" name="private" class="custom-control-input" {{ (!empty($job) and $job->private) ? 'checked' : '' }}>
            <label class="custom-control-label cursor-pointer" for="privateSwitch"></label>
        </div>

        <div class="">
            <label class="cursor-pointer" for="privateSwitch">{{ trans('webinars.private') }}</label>
        </div>
    </div>

    @php
        $jobTags = !empty($job) ? $job->tags->pluck('title')->toArray() : [];
    @endphp

    <div class="form-group tagsinput-bg-white mt-15">
        <label class="form-group-label d-block">{{ trans('public.tags') }}</label>
        <input type="text" name="tags" data-max-tag="5" value="{{ !empty($jobTags) ? implode(',', $jobTags) : '' }}" class="form-control inputtags" placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('forms.max') }} : 5)"/>
    </div>

</div>


@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
@endpush
