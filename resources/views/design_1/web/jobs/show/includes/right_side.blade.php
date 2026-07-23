@php
    $jobExpireDate = $job->getExpiryDate();
@endphp


<div class="job-right-side-section position-relative">
    <div class="job-right-side-section__mask"></div>

    <div class="position-relative bg-white rounded-24 pb-24 z-index-2">

        {{-- Thumbnail --}}
        <div class="job-right-side__thumbnail position-relative bg-gray-200">
            <img src="{{ $job->thumbnail }}" class="img-cover" alt="{{ $job->title }}">

            @if($job->video_demo)
                <div id="jobDemoVideoBtn" class="has-video-icon d-flex-center size-64 rounded-circle cursor-pointer"
                     data-video-path="{{ $job->video_demo_source == 'upload' ?  url($job->video_demo) : $job->video_demo }}"
                     data-video-source="{{ $job->video_demo_source }}"
                     data-thumbnail="{{ $job->thumbnail }}"
                >
                    <x-iconsax-bol-play class="icons text-white" width="24px" height="24px"/>
                </div>
            @endif
        </div>

        {{-- Price --}}
        <div class="d-flex-center gap-4 font-24 font-weight-bold text-dark mt-20">
            @if(auth()->guest() and !empty(getJobsGeneralSettings("login_required_to_view_salaries")))
                <a href="/login" class="font-16 text-dark">{{ trans('update.login_to_view_salary') }}</a>
            @else
                @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                    @if($job->payment_type == "fixed_amount")
                        <span class="">{{ handlePrice($job->fixed_salary) }}</span>
                    @else
                        <span class="">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                    @endif

                    <span class="font-14 text-gray-500 font-weight-400">/{{ trans("update.{$job->payment_period}") }}</span>
                @else
                    <span class="">{{ trans('update.negotiable') }}</span>
                @endif
            @endif
        </div>

        <div class="js-apply-for-job-actions-card px-16">
            {{-- Actions --}}
            @if(!empty($userRequestedToThisJob))
                <a href="/panel/jobs/my-requests/{{ $userRequestedToThisJob->id }}/status" class="btn btn-primary btn-block btn-xlg mt-20">
                    <span class="">{{ trans('update.view_status') }}</span>
                </a>
            @elseif($jobExpireDate > time())
                <a href="{{ $job->getRequestUrl() }}" class="btn btn-primary btn-block btn-xlg mt-20">
                    <span class="">{{ trans('update.apply_for_job') }}</span>
                </a>
            @else
                <button type="button" class="btn bg-gray-300 btn-block btn-xlg mt-20 text-gray-500 cursor-default">{{ trans('update.apply_for_job') }}</button>
            @endif

            @if($job->work_arrangement == "remote")
                <div class="d-flex-center gap-4 mt-12 font-12 text-gray-500">
                    <x-iconsax-lin-monitor class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="">{{ trans('update.remote_job_opportunity') }}</span>
                </div>
            @endif

            {{-- Important Points --}}
            @if(!empty($job->important_notes))
                <div class="bg-gray-200 mt-20 p-12 rounded-12">
                    <h4 class="text-gray-500">{{ trans('update.important_notes') }}</h4>
                    <p class="mt-8 text-gray-500">{!! nl2br($job->important_notes) !!}</p>
                </div>
            @endif

            {{-- Share & Bookmark --}}
            <div class="d-flex align-items-center justify-content-around mt-16 p-12 rounded-12 border-dashed border-gray-200">

                <a @if(auth()->guest()) href="/login" @else href="{{ $job->getUrl() }}/favorite" id="favoriteToggle" @endif class="d-flex-center flex-column font-12 {{ !empty($isFavorite) ? 'text-primary' : 'text-gray-500' }}">
                    <x-iconsax-lin-archive-tick class="icons {{ !empty($isFavorite) ? 'text-primary' : 'text-gray-500' }}" width="20px" height="20px"/>
                    <span class="mt-2">{{ trans('update.bookmark') }}</span>
                </a>

                <div class="js-share-job d-flex-center flex-column text-gray-500 font-12 cursor-pointer" data-path="/jobs/{{ $job->slug }}/share-modal">
                    <x-iconsax-lin-share class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="mt-2">{{ trans('public.share') }}</span>
                </div>
            </div>

            {{-- Report --}}
            <div class="mt-24 text-center">
                @if(auth()->guest())
                    <a href="/login" class="font-12 text-gray-500">{{ trans('update.report_abuse') }}</a>
                @else
                    <button type="button" class="js-report-job font-12 text-gray-500 btn-transparent" data-path="{{ $job->getUrl() }}/report-modal">{{ trans('update.report_abuse') }}</button>
                @endif
            </div>

        </div>
    </div>
</div>


{{-- Course Specifications --}}
@include("design_1.web.jobs.show.includes.rightSide.specifications")

{{-- creator --}}
@include("design_1.web.jobs.show.includes.rightSide.creator", ['userRow' => $job->creator])


{{-- tags --}}
@if($job->tags->count() > 0)
    <div class="job-right-side-section position-relative mt-28">
        <div class="job-right-side-section__mask"></div>

        <div class="position-relative card-before-line bg-white rounded-24 p-16 z-index-2">
            <h4 class="font-14 font-weight-bold">{{ trans('public.tags') }}</h4>

            <div class="d-flex gap-12 flex-wrap mt-16">
                @foreach($job->tags as $tag)
                    <a href="/tags/jobs/{{ urlencode($tag->title) }}" target="_blank" class="d-flex-center p-10 rounded-8 bg-gray-100 font-12 text-gray-500 text-center">{{ $tag->title }}</a>
                @endforeach
            </div>
        </div>
    </div>
@endif
