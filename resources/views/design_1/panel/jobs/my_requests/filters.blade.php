<form action="" method="get" class="px-16">
    <div class="row mt-24">

        <div class="col-12 col-lg-3">
            <div class="form-group">
                <label class="form-group-label">{{ trans('public.search') }}</label>
                <input type="text" name="search" class="form-control" placeholder="{{ trans('update.search_in_job_positions') }}">
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="form-group ">
                <label class="form-group-label">{{ trans('update.employer') }}</label>
                <select name="employer_id" class="form-control select2">
                    <option value="">{{ trans('public.all') }}</option>

                    @foreach($employers as $employer)
                        <option value="{{ $employer->id }}" @if(request()->get('employer_id') == $employer->id) selected @endif>{{ $employer->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="col-12 col-lg-3">
            <div class="form-group">
                <span class="has-translation bg-transparent"><x-iconsax-lin-calendar-2 class="text-gray-500" width="24px" height="24px"/></span>
                <label class="form-group-label">{{ trans('update.request_start_date') }}</label>
                <input type="text" name="start_date" class="form-control datepicker js-default-init-date-picker" data-format="YYYY/MM/DD">
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="form-group">
                <span class="has-translation bg-transparent"><x-iconsax-lin-calendar-2 class="text-gray-500" width="24px" height="24px"/></span>
                <label class="form-group-label">{{ trans('update.request_end_date') }}</label>
                <input type="text" name="end_date" class="form-control datepicker js-default-init-date-picker" data-format="YYYY/MM/DD">
            </div>
        </div>


        <div class="col-12 col-lg-3">
            <div class="form-group ">
                <label class="form-group-label">{{ trans('update.employment_type') }}</label>
                <select name="employment_type_id" class="form-control select2">
                    <option value="">{{ trans('public.all') }}</option>

                    @foreach($employmentTypes as $employmentType)
                        <option value="{{ $employmentType->id }}">{{ $employmentType->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="form-group">
                <label class="form-group-label">{{ trans('public.status') }}</label>
                <select class="form-control select2" id="status" name="status" data-minimum-results-for-search="Infinity">
                    <option value="">{{ trans('public.all') }}</option>

                    @foreach(['pending','approved','rejected'] as $status)
                        <option value="{{ $status }}" >{{ trans("update.{$status}") }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $sorts = [
                'request_date_asc',
                'request_date_desc',
                'salary_asc',
                'salary_desc',
            ];
        @endphp

        <div class="col-6 col-lg-3">
            <div class="form-group ">
                <label class="form-group-label">{{ trans('filters') }}</label>
                <select name="sort" class="form-control select2">
                    <option value="">{{ trans('public.all') }}</option>

                    @foreach($sorts as $sort)
                        <option value="{{ $sort }}">{{ trans("update.{$sort}") }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-3 ml-auto">
            <button type="button" data-container-id="tableListContainer" class="js-get-view-data-by-form btn btn-primary btn-lg btn-block">{{ trans('filter') }}</button>
        </div>
    </div>
</form>
