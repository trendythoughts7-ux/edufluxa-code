@php
    $heroSettings = getJobsHeroContentSettings();
@endphp

@if(!empty($heroSettings))
    @php
        $heroBackground = !empty($heroSettings['light_mode_background']) ? $heroSettings['light_mode_background'] : null;
        $heroDarkBackground = !empty($heroSettings['dark_mode_background']) ? $heroSettings['dark_mode_background'] : null;
    @endphp

    <div class="jobs-lists-hero container position-relative rounded-32 mt-84">
        <div class="jobs-lists-hero__bg-wrapper rounded-32 light-only" @if(!empty($heroBackground)) style="background-image: url({{ $heroBackground }})" @endif></div>
        <div class="jobs-lists-hero__bg-wrapper rounded-32 dark-only" @if(!empty($heroDarkBackground)) style="background-image: url({{ $heroDarkBackground }})" @endif></div>

        <div class="position-relative z-index-2 d-flex-center flex-column text-center w-100 h-100">
            <div class="row justify-content-center w-100">

                <div class="col-12 col-lg-10 position-relative">
                    @if(!empty($heroSettings['badge_title']))
                        <div class="jobs-lists-hero__badge-box d-inline-flex-center py-8 px-16 rounded-32 bg-primary font-12 text-white">{{ $heroSettings['badge_title'] }}</div>
                    @endif


                    <h1 class="jobs-lists-hero__title text-dark d-inline-flex-center flex-column gap-4">
                        @if(!empty($heroSettings['title_line_1']))
                            <span class="">{{ $heroSettings['title_line_1'] }}</span>
                        @endif

                        @if(!empty($heroSettings['title_line_2']))
                            <span class="">{{ $heroSettings['title_line_2'] }}</span>
                        @endif
                    </h1>
                </div>{{-- col-10 --}}

                <div class="col-12 col-lg-6 position-relative">
                    @if(!empty($heroSettings['subtitle']))
                        <p class="mt-20 font-16 text-gray-500">{!! nl2br($heroSettings['subtitle']) !!}</p>
                    @endif

                    @if(!empty(getJobsGeneralSettings("enable_search_input")))
                        <div class="jobs-lists-hero__search-box position-relative mt-48">
                            <div class="jobs-lists-hero__search-box-mask"></div>

                            <form action="" method="get" class="position-relative z-index-3 d-flex align-items-center gap-4 mb-0 bg-white rounded-36 p-12">
                                <input type="text" name="search" class="" placeholder="{{ trans('update.what_job_are_you_looking_for?') }}" value="{{ request()->get('search') }}">

                                <button type="submit" class="btn btn-primary btn-lg rounded-32">{{ trans('public.search') }}</button>
                            </form>
                        </div>
                    @endif

                    @if(!empty($heroSettings['special_items']) and is_array($heroSettings['special_items']) and count($heroSettings['special_items']))
                        <div class="d-flex-center flex-column mt-40">
                            <h5 class="font-14">{{ trans('update.or_explore_popular_jobs') }}</h5>

                            <div class="d-flex align-items-center flex-wrap gap-16 mt-20">
                                @foreach($heroSettings['special_items'] as $specificLinkData)
                                    @if(!empty($specificLinkData['title']) and !empty($specificLinkData['url']))
                                        <a href="{{ $specificLinkData['url'] }}" target="_blank" class="font-weight-bold text-gray-500">{{ $specificLinkData['title'] }}</a>

                                        @if(!$loop->last)
                                            <div class="jobs-lists-hero__circle-dot-separator"></div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>{{-- col-6 --}}
            </div>{{-- row --}}

            {{-- floating_image_1 --}}
            @if(!empty($heroSettings['floating_image_1']))
                <div class="jobs-lists-hero__floating-image-1">
                    <img src="{{ $heroSettings['floating_image_1'] }}" alt="{{ trans('update.floating_image') }} #1" class="img-fluid">
                </div>
            @endif

            {{-- floating_image_2 --}}
            @if(!empty($heroSettings['floating_image_2']))
                <div class="jobs-lists-hero__floating-image-2">
                    <img src="{{ $heroSettings['floating_image_2'] }}" alt="{{ trans('update.floating_image') }} #2" class="img-fluid">
                </div>
            @endif

        </div>
    </div>
@endif
