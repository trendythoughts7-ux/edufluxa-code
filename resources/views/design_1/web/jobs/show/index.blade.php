@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("css_stars") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("reviews_and_comments") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("show_job") }}">
@endpush


@section("content")
    <div class="container position-relative mt-80 pb-120 ">

        {{-- Hero --}}
        @include('design_1.web.jobs.show.includes.hero')

        <div class="d-flex flex-column flex-lg-row gap-24">
            {{-- Contant and Tabs --}}
            <div class="job-body-side position-relative job-body-card flex-1">
                @php
                    $activePageTab = request()->get("tab", 'information');
                @endphp

                <div class="custom-tabs mt-16">
                    <div class="job-tabs-card position-relative">
                        <div class="job-tabs-card__mask"></div>

                        <div class="position-relative d-flex align-items-center gap-20 gap-lg-40 flex-wrap bg-white px-16 px-lg-20 rounded-12 z-index-2 w-100">
                            <div id="showJobAboutTab" class="navbar-item d-flex-center cursor-pointer {{ ($activePageTab == "information") ? 'active' : '' }}" data-tab-toggle data-tab-href="#aboutJobTab">
                                <span class="">{{ trans('update.about_job') }}</span>
                            </div>

                            <div class="navbar-item d-flex-center cursor-pointer {{ ($activePageTab == "comments") ? 'active' : '' }}" data-tab-toggle data-tab-href="#commentsTab">
                                <span class="ml-4">{{ trans('panel.comments') }}</span>

                                <span class="job-tab-counter d-flex-center p-4 rounded-8 ml-4 font-12">
                                    {{ (!empty($jobComments) and !empty($jobComments['comments_count'])) ? $jobComments['comments_count'] : 0 }}
                                </span>
                            </div>

                        </div>
                    </div>

                    <div class="custom-tabs-body mt-16">

                        <div class="custom-tabs-content {{ ($activePageTab == "information") ? 'active' : '' }}" id="aboutJobTab">
                            @include('design_1.web.jobs.show.tabs.about')
                        </div>

                        <div class="custom-tabs-content {{ ($activePageTab == "comments") ? 'active' : '' }}" id="commentsTab">
                            @include('design_1.web.jobs.show.tabs.comments')
                        </div>

                    </div>

                </div>


                {{-- Ads Bannaer --}}
                @include('design_1.web.components.advertising_banners.page_banner')
                {{-- ./ Ads Bannaer --}}
            </div>

            {{-- Right Side --}}
            <div class="job-right-side position-relative">
                @include('design_1.web.jobs.show.includes.right_side')

                {{-- Sidebar ads Banner --}}
                @include('design_1.web.components.advertising_banners.sidebar_banner')
            </div>
        </div>
    </div>

    {{-- Bottom Fixed --}}
    @include("design_1.web.jobs.show.includes.bottom_fixed_card")

@endsection



@push('scripts_bottom')
    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var closeLang = '{{ trans('public.close') }}';
        var shareLang = '{{ trans('public.share') }}';
        var reportCommentLang = '{{ trans('update.report_comment') }}';
        var reportJobLang = '{{ trans('update.report_job') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var jobDemoLang = '{{ trans('public.demo_video') }}';
    </script>

    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>

    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
    <script src="{{ getDesign1ScriptPath("leaflet_map") }}"></script>
    <script src="{{ getDesign1ScriptPath("video_player_helpers") }}"></script>

    <script src="{{ getDesign1ScriptPath("comments") }}"></script>
    <script src="{{ getDesign1ScriptPath("show_job") }}"></script>
@endpush
