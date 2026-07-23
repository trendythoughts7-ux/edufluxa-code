@extends('design_1.panel.layouts.panel')

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/chartjs/chart.min.css"/>
@endpush

@section('content')
    {{-- Top Stats --}}
    @include('design_1.panel.jobs.my_jobs.top_stats')

    {{-- List Table --}}
    @if(!empty($jobs) and $jobs->isNotEmpty())
        <div id="tableListContainer" class="" data-view-data-path="/panel/jobs">
            <div class="js-page-jobs-lists row mt-20">
                @foreach($jobs as $jobItem)
                    <div class="col-12 col-lg-6 mb-32">
                        @include("design_1.panel.jobs.my_jobs.job_card.index", ['job' => $jobItem])
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer"
                 data-container-items=".js-page-jobs-lists">
                {!! $pagination !!}
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'job_list.svg',
            'title' => trans('update.my_jobs_lists_no_result_title'),
            'hint' =>  trans('update.my_jobs_lists_no_result_hint') ,
            'btn' => ['url' => '/panel/jobs/new','text' => trans('update.create_a_job') ]
        ])
    @endif


@endsection

@push('scripts_bottom')

    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>
@endpush
