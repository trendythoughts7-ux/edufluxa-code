@extends('design_1.panel.layouts.panel')

@push('styles_top')

@endpush

@section('content')

    @if(!empty($bookmarks) and !$bookmarks->isEmpty())
        <div class="bg-white pt-16 rounded-24 mt-20">

            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('update.bookmarks') }}</h3>
                    <p class="mt-4 text-gray-500">{{ trans('update.review_your_bookmarked_job_positions') }}</p>
                </div>
            </div>


            {{-- List Table --}}
            <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/jobs/my-bookmarks">
                <table class="table panel-table">
                    <thead>
                    <tr>
                        <th class="text-left">{{ trans('update.job_position') }}</th>
                        <th class="text-left">{{ trans('update.employer') }}</th>
                        <th class="text-center">{{ trans('update.employment_type') }}</th>
                        <th class="text-center">{{ trans('update.experience_level') }}</th>
                        <th class="text-center">{{ trans('update.work_arrangement') }}</th>
                        <th class="text-center">{{ trans('update.salary') }}</th>
                        <th class="text-center">{{ trans('update.created_date') }}</th>
                        <th class="text-right">{{ trans('update.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="js-table-body-lists">
                    @foreach($bookmarks as $bookmarkRow)
                        @include('design_1.panel.jobs.my_bookmarks.table_items', ['bookmark' => $bookmarkRow])
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
            'file_name' => 'my_bookmarked_jobs.svg',
            'title' => trans('update.my_bookmarked_jobs_no_result'),
            'hint' => nl2br(trans('update.my_bookmarked_jobs_no_result_hint')),
        ])
    @endif

@endsection

@push('scripts_bottom')

    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

@endpush
