<section class="card mt-32">
    <div class="card-body pb-4">
        <form method="get" class="mb-0">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="input-label">{{trans('admin/main.search')}}</label>
                        <input name="search" type="text" class="form-control" value="{{ request()->get('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="input-label">{{trans('admin/main.created_date')}}</label>
                        <div class="input-group">
                            <input type="date" id="created_date" class="text-center form-control" name="created_date" value="{{ request()->get('created_date') }}" placeholder="">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="input-label">{{trans('update.employer')}}</label>
                        <select name="employer_ids[]" multiple="multiple" data-search-option="" class="form-control search-user-select2"
                                data-placeholder="{{ trans('public.search_employers') }}">

                            @if(!empty($employers) and $employers->count() > 0)
                                @foreach($employers as $employer)
                                    <option value="{{ $employer->id }}" selected>{{ $employer->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0 ">
                        <label class="">{{ trans('update.employment_type') }}</label>
                        <select name="employment_type_id" class="form-control select2">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($employmentTypes as $employmentType)
                                <option value="{{ $employmentType->id }}">{{ $employmentType->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0 ">
                        <label class="">{{ trans('update.work_arrangement') }}</label>
                        <select name="work_arrangement" class="form-control">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach(['on_site', 'remote', 'hybrid'] as $workArrangement)
                                <option value="{{ $workArrangement }}">{{ trans("update.{$workArrangement}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="input-label">{{trans('public.status')}}</label>
                        <select name="status" data-plugin-selectTwo class="form-control populate">
                            <option value="">{{trans('admin/main.all_status')}}</option>

                            @foreach(['pending','approved','rejected','draft','expired'] as $status)
                                <option value="{{ $status }}" {{ (request()->get('status') == $status) ? 'selected' : '' }}>{{ trans("update.{$status}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @php
                    $sorts = [
                        'create_date_asc',
                        'create_date_desc',
                        'apply_requests_asc',
                        'apply_requests_desc',
                        'expiry_end_date_asc',
                        'expiry_end_date_desc',
                        'salary_asc',
                        'salary_desc',
                    ];
                @endphp

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="input-label">{{trans('admin/main.filters')}}</label>
                        <select name="sort" data-plugin-selectTwo class="form-control populate">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($sorts as $sort)
                                <option value="{{ $sort }}" {{ (request()->get('sort') == $sort) ? 'selected' : '' }}>{{ trans("update.{$sort}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="col-md-3 d-flex align-items-center mt-16">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">{{trans('admin/main.show_results')}}</button>
                </div>
            </div>
        </form>
    </div>
</section>
