@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl("/jobs") }}">{{trans('update.jobs')}}</a></div>
                <div class="breadcrumb-item">{{ trans('update.apply_requests') }}</div>
            </div>
        </div>

        <div class="section-body">
            {{-- Top Stats --}}
            @include('admin.jobs.requests.lists.top_stats')

            {{-- Filters --}}
            @include('admin.jobs.requests.lists.filters')


            <div class="card">
                <div class="card-header justify-content-between">
                    <div>
                        <h5 class="font-14 mb-0">{{ trans('update.job_application_requests') }}</h5>
                        <p class="font-12 mt-4 mb-0 text-gray-500">{{ trans('update.monitor_and_respond_to_job_application_requests_efficiently') }}</p>
                    </div>

                    <div class="d-flex align-items-center gap-12">

                        @can('admin_jobs_lists_export_excel')
                            <a href="{{ getAdminPanelUrl("/jobs/requests/export-excel?". http_build_query(request()->all())) }}" class="btn bg-white bg-hover-gray-100 border-gray-400 text-gray-500">
                                <x-iconsax-lin-import-2 class="icons text-gray-500" width="18px" height="18px"/>
                                <span class="ml-4 font-12">{{ trans('admin/main.export_xls') }}</span>
                            </a>
                        @endcan

                    </div>
                </div>

                <div class="card-body p-0">
                    <div>
                        <table class="table custom-table font-14 ">
                            <tr>
                                <th class="text-left">{{trans('update.job')}}</th>
                                <th class="text-left">{{trans('update.applicant')}}</th>
                                <th class="text-left">{{trans('update.shared_information')}}</th>
                                <th>{{trans('update.request_date')}}</th>
                                <th>{{trans('admin/main.status')}}</th>
                                <th width="120">{{trans('admin/main.actions')}}</th>
                            </tr>

                            @foreach($jobRequests as $jobRequestRow)
                                @include('admin.jobs.requests.lists.table_items', ['jobRequest' => $jobRequestRow])
                            @endforeach
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    {{ $jobRequests->appends(request()->input())->links() }}
                </div>

            </div>

        </div>
    </section>
@endsection

@push('scripts_bottom')

    <script src="/assets/admin/js/parts/job_requests.min.js"></script>
@endpush
