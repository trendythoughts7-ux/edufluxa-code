@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    {{-- Top Stats --}}
    @include('design_1.panel.jobs.my_requests.top_stats')


    @if(!empty($jobRequests) and !$jobRequests->isEmpty())
        <div class="bg-white pt-16 rounded-24 mt-20">

            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('update.job_application_history') }}</h3>
                    <p class="mt-4 text-gray-500">{{ trans('update.access_and_manage_your_job_application_requests') }}</p>
                </div>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.jobs.my_requests.filters')

            {{-- List Table --}}
            <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/jobs/my-requests">
                <table class="table panel-table">
                    <thead>
                    <tr>
                        <th class="text-left">{{ trans('update.job_position') }}</th>
                        <th class="text-left">{{ trans('update.employer') }}</th>
                        <th class="text-center">{{ trans('update.employment_type') }}</th>
                        <th class="text-center">{{ trans('update.experience_level') }}</th>
                        <th class="text-center">{{ trans('update.work_arrangement') }}</th>
                        <th class="text-center">{{ trans('update.salary') }}</th>
                        <th class="text-center">{{ trans('update.request_date') }}</th>
                        <th class="text-center">{{ trans('public.status') }}</th>
                        <th class="text-right">{{ trans('update.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="js-table-body-lists">
                    @foreach($jobRequests as $jobRequestRow)
                        @include('design_1.panel.jobs.my_requests.table_items', ['jobRequest' => $jobRequestRow])
                    @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer" data-container-items=".js-table-body-lists">
                    {!! $pagination !!}
                </div>
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'my_job_requests.svg',
            'title' => trans('update.my_job_requests_no_result'),
            'hint' => nl2br(trans('update.my_job_requests_no_result_hint')),
        ])
    @endif

@endsection

@push('scripts_bottom')

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    <script src="/assets/design_1/js/panel/job_requests.min.js"></script>
@endpush
