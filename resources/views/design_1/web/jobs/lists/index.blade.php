@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("jobs_lists") }}">
@endpush

@section("content")
    <main class="pb-120">
        {{-- Hero --}}
        @include("design_1.web.jobs.lists.includes.hero")

        <form action="{{ $pageBasePath }}" class="js-get-view-data-by-timeout-change container mt-40" data-container-id="listsContainer">
            {{-- Top Filters --}}
            @include("design_1.web.jobs.lists.includes.top_filters")

            <div class="row">
                {{-- Left Side --}}
                <div class="col-12 col-lg-3 mt-28">
                    {{-- Featured Jobs --}}
                    @include("design_1.web.jobs.lists.includes.featured_jobs")

                    {{-- Left Filters --}}
                    @include("design_1.web.jobs.lists.includes.left_filters")
                </div>

                {{-- Courses Lists --}}
                <div class="col-12 col-lg-9 mt-4">
                    <div id="listsContainer" class="" data-body=".js-lists-body" data-view-data-path="{{ $pageBasePath }}">
                        <div class="js-lists-body row">
                            @include('design_1.web.jobs.components.cards.grids.index',['jobs' => $jobs, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-24"])
                        </div>

                        {{-- Pagination --}}
                        <div id="pagination" class="js-ajax-pagination" data-container-id="listsContainer" data-container-items=".js-lists-body">
                            {!! $pagination !!}
                        </div>
                    </div>


                    {{-- Seo Content --}}
                </div>
            </div>
        </form>
    </main>
@endsection


@push('scripts_bottom')
    <script>
        var selectRegionDefaultVal = '';
        var selectStateLang = '{{ trans('update.choose_a_state') }}';
        var selectCityLang = '{{ trans('update.choose_a_city') }}';
        var selectDistrictLang = '{{ trans('update.all_districts') }}';
    </script>

    <script src="/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>
    <script src="{{ getDesign1ScriptPath("get_regions") }}"></script>
    <script src="{{ getDesign1ScriptPath("range_slider_helpers") }}"></script>
    <script src="{{ getDesign1ScriptPath("jobs_lists") }}"></script>
@endpush
