<section class="card mt-32">
    <div class="card-body pb-4">
        <form method="get" class="mb-0">

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="input-label">{{trans('admin/main.search')}}</label>

                        <select name="job_id" class="form-control select2" data-allow-clear="true">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($filterJobs as $filterJob)
                                <option value="{{ $filterJob->id }}" {{ (request()->get('job_id') == $filterJob->id) ? 'selected' : '' }}>{{ $filterJob->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="input-label">{{trans('public.status')}}</label>
                        <select name="status" data-plugin-selectTwo class="form-control populate">
                            <option value="">{{trans('admin/main.all_status')}}</option>

                            @foreach(['pending','approved','rejected'] as $status)
                                <option value="{{ $status }}" {{ (request()->get('status') == $status) ? 'selected' : '' }}>{{ trans("update.{$status}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @php
                    $sorts = [
                        'request_date_asc',
                        'request_date_desc',
                    ];
                @endphp

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="input-label">{{trans('admin/main.filters')}}</label>
                        <select name="sort" data-plugin-selectTwo class="form-control populate">
                            <option value="">{{ trans('public.all') }}</option>

                            @foreach($sorts as $sort)
                                <option value="{{ $sort }}" {{ (request()->get('sort') == $sort) ? 'selected' : '' }}>{{ trans("update.{$sort}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="col-md-3 d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">{{trans('admin/main.show_results')}}</button>
                </div>
            </div>
        </form>
    </div>
</section>
