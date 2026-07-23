@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    {{-- Job --}}
    <div class="sold-tickets-event-card position-relative mb-20">
        <div class="sold-tickets-event-card__mask"></div>
        <div class="position-relative z-index-2 d-flex align-items-center justify-content-between p-20 rounded-16 bg-white ">
            <div class="d-flex align-items-center">
                <div class="size-64">
                    <img src="{{ $job->getIcon() }}" alt="{{ $job->title }}" class="img-cover">
                </div>
                <div class="ml-8">
                    <h4 class="font-14">{{ $job->title }}</h4>
                    <p class="font-12 text-gray-500 mt-4">{{ $job->category->title }}</p>
                </div>
            </div>

            <a href="{{ $job->getUrl() }}" target="_blank" class="d-flex align-items-center gap-4 text-primary font-12">
                <span class="font-weight-bold">{{ trans('update.view_job_page') }}</span>
                <x-iconsax-lin-arrow-right class="icons text-primary" width="16" height="16"/>
            </a>
        </div>
    </div>

    {{-- Top Stats --}}
    @include('design_1.panel.jobs.requests.lists.top_stats')


    @if(!empty($jobRequests) and !$jobRequests->isEmpty())
        <div class="bg-white pt-16 rounded-24 mt-20">

            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('update.job_application_requests') }}</h3>
                    <p class="mt-4 text-gray-500">{{ trans('update.monitor_and_respond_to_job_application_requests_efficiently') }}</p>
                </div>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.jobs.requests.lists.filters')

            {{-- List Table --}}
            <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/jobs/{{ $job->id }}/requests">
                <table class="table panel-table">
                    <thead>
                    <tr>
                        <th class="text-left">{{ trans('update.applicant') }}</th>
                        <th class="text-left">{{ trans('update.shared_information') }}</th>
                        <th class="text-center">{{ trans('update.request_date') }}</th>
                        <th class="text-center">{{ trans('public.status') }}</th>
                        <th class="text-right">{{ trans('update.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="js-table-body-lists">
                    @foreach($jobRequests as $jobRequestRow)
                        @include('design_1.panel.jobs.requests.lists.table_items', ['jobRequest' => $jobRequestRow])
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
            'file_name' => 'job_requests.svg',
            'title' => trans('update.job_requests_no_result'),
            'hint' => nl2br(trans('update.job_requests_no_result_hint')),
        ])
    @endif

@endsection

@push('scripts_bottom')

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    <script src="/assets/design_1/js/panel/job_requests.min.js"></script>
@endpush
