@php
    $learningMaterialsExtraDescription = !empty($job->extraDescriptions) ? $job->extraDescriptions->where('type','learning_materials') : null;
    $companyLogosExtraDescription = !empty($job->extraDescriptions) ? $job->extraDescriptions->where('type','company_logos') : null;
    $requirementsExtraDescription = !empty($job->extraDescriptions) ? $job->extraDescriptions->where('type','requirements') : null;
@endphp


<div class="bg-white py-16 rounded-24">

    {{-- What will you learn --}}
    @if(!empty($learningMaterialsExtraDescription) and count($learningMaterialsExtraDescription))
        <div class="mb-32 px-16">
            <div class="job-extra-card bg-gray-100 p-12 rounded-12 mt-40">
                <div class="job-extra-card__title d-flex align-items-center justify-content-between p-16 rounded-12 border-dashed border-gray-200 bg-white">
                    <h3 class="font-16 font-weight-bold flex-1">{{ trans('update.job_learning_materials') }}</h3>

                    <div class="">
                        <x-iconsax-bul-verify class="icons text-primary" width="24px" height="24px"/>
                    </div>
                </div>

                <div class="d-grid grid-columns-auto grid-lg-columns-2 gap-12 mt-12">
                    @foreach($learningMaterialsExtraDescription as $learningMaterial)
                        <div class="d-flex align-items-center p-16 rounded-8 bg-white">
                            <div class="size-16">
                                <x-tick-icon class="icons text-primary" width="16px" height="16px"/>
                            </div>

                            <span class="flex-1 ml-4 font-14 text-gray-500">{{ $learningMaterial->value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- About job --}}
    @if($job->description)
        <div class="px-16">
            <h2 class="font-16 font-weight-bold">{{ trans('update.about_the_job') }}</h2>

            <div class="job-show-description mt-12 text-gray-500 line-height-1-5">
                {!! nl2br($job->description) !!}
            </div>
        </div>
    @endif

    {{-- Job Location --}}
    @if(!empty($job->specificLocation))
        <div class="px-16 mt-32">
            <h2 class="font-16 font-weight-bold">{{ trans('update.company_location') }}</h2>

            <div class="mt-16 p-16 rounded-16 border-gray-200">
                @if(!empty($job->specificLocation->geo_center))
                    <div class="region-map with-default-initial position-relative job-map-container bg-gray-200 rounded-8 mb-16" id="jobPageMap"
                         data-latitude="{{ $job->specificLocation->geo_center[0] }}"
                         data-longitude="{{ $job->specificLocation->geo_center[1] }}"
                         data-zoom="16"
                         data-dragging="false"
                         data-zoomControl="true"
                         data-scrollWheelZoom="true"
                    >
                        <img src="/assets/default/img/location.png" class="marker" width="40" height="40">
                    </div>
                @endif

                <div class="d-flex align-items-center">
                    <div class="d-flex-center size-48 rounded-12 bg-gray-300">
                        <x-iconsax-bul-location class="icons text-gray-500" width="24px" height="24px"/>
                    </div>
                    <div class="ml-8">
                        <h5 class="font-14">{{ trans('update.location') }}</h5>
                        <p class="font-12 text-gray-500 mt-2">{{ $job->specificLocation->getFullAddress() }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- Requirements --}}
    @if(!empty($requirementsExtraDescription) and count($requirementsExtraDescription))
        <div class="px-16 pb-28">
            <div class="job-extra-card bg-gray-100 p-12 pb-28 rounded-12 mt-32">
                <div class="job-extra-card__title d-flex align-items-center justify-content-between p-16 rounded-12 border-dashed border-gray-200 bg-white">
                    <h3 class="font-16 font-weight-bold">{{ trans('update.job_requirements') }}</h3>

                    <div class="size-24">
                        <x-iconsax-bul-task-square class="icons text-primary" width="24px" height="24px"/>
                    </div>
                </div>

                <div class="">
                    @foreach($requirementsExtraDescription as $requirementExtraDescription)
                        <div class="d-flex align-items-center {{ $loop->first ? 'mt-20' : 'mt-16' }}">
                            <div class="size-16">
                                <x-tick-icon class="icons text-primary" width="16px" height="16px"/>
                            </div>

                            <span class="flex-1 ml-4 font-14 text-gray-500">{{ $requirementExtraDescription->value }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="job-extra-card__float-img">
                    <img src="/assets/design_1/img/courses/requirements.svg" alt="{{ trans('update.requirements') }}" class="img-fluid">
                </div>
            </div>
        </div>
    @endif

    {{-- Trusted Companies --}}
    @if(!empty($companyLogosExtraDescription) and count($companyLogosExtraDescription))
        <div class="mt-32 p-16 pb-28 border-top-gray-200 border-bottom-gray-200">
            <div class="">
                <h2 class="font-16 font-weight-bold">{{ trans('update.trusted_companies') }}</h2>
                <p class="mt-4 font-12 text-gray-500">+3200 Companies trusted our jobs for their staff tutoring</p>
            </div>

            <div class="position-relative mt-16">
                <div class="swiper-container js-make-swiper job-trusted-companies-slider pb-0"
                     data-item="job-trusted-companies-slider"
                     data-autoplay="true"
                     data-loop="true"
                     data-breakpoints="1440:5.5,769:4.2,320:1.4"
                >
                    <div class="swiper-wrapper py-0 mx-16 mx-md-32">
                        @foreach($companyLogosExtraDescription as $companyLogo)
                            <div class="swiper-slide job-company-logos d-flex-center">
                                <img src="{{ $companyLogo->value }}" class="img-fluid" alt="{{ trans('update.company_logos') }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- job FAQ --}}
    @if(!empty($job->faqs) and $job->faqs->count() > 0)
        <div id="jobFAQParent" class="px-16 mt-32">
            <div class="">
                <h2 class="font-16 font-weight-bold">{{ trans('public.faq') }}</h2>
                <p class="mt-4 font-12 text-gray-500">{{ trans('update.check_frequently_asked_questions_about_this_job') }}</p>
            </div>

            @foreach($job->faqs as $faq)
                <div class="accordion p-20 rounded-12 border-gray-200 bg-white {{ $loop->first ? 'mt-16' : 'mt-20' }}">
                    <div class="accordion__title d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center cursor-pointer" href="#jobFAQ_{{ $faq->id }}" data-parent="#jobFAQParent" role="button" data-toggle="collapse">
                            <div class="size-24">
                                <x-iconsax-lin-message-question class="icons text-primary" width="24px" height="24px"/>
                            </div>

                            <div class="font-14 font-weight-bold ml-8">
                                {{ clean($faq->title,'title') }}
                            </div>
                        </div>

                        <div class="collapse-arrow-icon d-flex cursor-pointer" href="#jobFAQ_{{ $faq->id }}" data-parent="#jobFAQParent" role="button" data-toggle="collapse">
                            <x-iconsax-lin-arrow-up-1 class="icons text-gray-400" width="16px" height="16px"/>
                        </div>
                    </div>

                    <div id="jobFAQ_{{ $faq->id }}" class="accordion__collapse border-0 " role="tabpanel">
                        <div class="p-16 rounded-8 border-gray-200 text-gray-500 mt-8">
                            {{ clean($faq->answer,'answer') }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    @endif


    {{-- job Prerequisites --}}
    @if(!empty($job->prerequisites) and $job->prerequisites->count() > 0)
        <div class="px-16 mt-32">
            <div class="">
                <h2 class="font-16 font-weight-bold">{{ trans('public.prerequisites') }}</h2>
                <p class="mt-4 font-12 text-gray-500">{{ trans('update.we_suggest_passing_prerequisites_for_more_efficient_learning') }}</p>
            </div>

            <div class="row">
                @foreach($job->prerequisites as $prerequisite)
                    @if(!empty($prerequisite->course))
                        <div class="col-12 col-md-6 col-lg-3 mt-16">
                            @include('design_1.web.courses.show.includes.prerequisite',['courseItem' => $prerequisite->course])
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

</div>


{{-- About Instructor --}}
<div class="job-about-instructor-card position-relative mt-32 mt-lg-60">
    <div class="job-about-instructor-card__mask"></div>

    <div class="position-relative d-flex flex-column flex-lg-row align-items-start gap-24 bg-white px-16 rounded-24 z-index-3">
        <div class="job-about-instructor-card__details flex-1 py-16">
            <div class="d-flex align-items-center">
                <div class="position-relative d-flex-center size-80 rounded-12 bg-gray-200">
                    <img src="{{ $job->creator->getAvatar(80) }}" alt="{{ $job->creator->full_name }}" class="img-cover rounded-12">
                </div>

                <div class="ml-12 flex-1">
                    <a href="{{ $job->creator->getProfileUrl() }}" target="_blank" class="">
                        <h6 class="font-14 font-weight-bold text-dark">{{ $job->creator->full_name }}</h6>
                    </a>

                    @php
                        $jobInstructorRates = $job->creator->rates(true);
                    @endphp

                    @include('design_1.web.components.rate', [
                        'rate' => $jobInstructorRates['rate'],
                        'rateCount' => $jobInstructorRates['count'],
                        'rateClassName' => 'mt-4',
                    ])

                    <div class="d-flex align-items-center gap-12 mt-8">
                        <div class="d-flex align-items-center p-8 rounded-24 border-gray-200 bg-gray-100 text-gray-500 font-12">
                            <x-iconsax-lin-video-play class="icons text-gray-400" width="16px" height="16px"/>
                            <span class="mx-4 font-weight-bold">{{ $job->creator->getTeacherCoursesCount() }}</span>
                            <span class="">{{ trans('update.courses') }}</span>
                        </div>

                        <div class="d-flex align-items-center p-8 rounded-24 border-gray-200 bg-gray-100 text-gray-500 font-12">
                            <x-iconsax-lin-teacher class="icons text-gray-400" width="16px" height="16px"/>
                            <span class="mx-4 font-weight-bold">{{ $job->creator->getTeacherStudentsCount() }}</span>
                            <span class="">{{ trans('quiz.students') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-gray-500 line-height-1-5">{!! truncate($job->creator->about, 716) !!}</div>
        </div>

        <div class="job-about-instructor-card__secondary-img position-relative">
            <img src="{{ $job->creator->getProfileSecondaryImage() }}" alt="{{ $job->creator->full_name }}" class="img-cover">

            @if($job->creator->hasMeeting())
                <a href="{{ $job->creator->getMeetingReservationUrl() }}" target="_blank" class="job-about-instructor-card__book-meeting-btn d-inline-flex align-items-center gap-8 px-24 py-12 cursor-pointer">
                    <x-iconsax-lin-calendar-2 class="icons text-white" width="24px" height="24px"/>
                    <span class="text-white">{{ trans('public.book_a_meeting') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Similar Jobs --}}
@if(!empty($similarJobs) and $similarJobs->isNotEmpty())
    <div class="mt-48">
        <div class="">
            <h2 class="font-16 font-weight-bold">{{ trans('update.similar_jobs') }}</h2>
            <p class="mt-4 font-12 text-gray-500">{{ trans('update.you_might_also_be_interested_in_these_job_openings') }}</p>
        </div>

        <div class="row">
            @include('design_1.web.jobs.components.cards.grids.index',['jobs' => $similarJobs, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16"])
        </div>
    </div>
@endif

{{-- Related Courses --}}
@if(!empty($job->relatedCourses) and $job->relatedCourses->count() > 0)
    @php
        $relatedCourses = [];

        foreach($job->relatedCourses as $relatedCourse) {
            if(!empty($relatedCourse->course)) {
                $relatedCourses[] = $relatedCourse->course;
            }
        }
    @endphp

    @if(count($relatedCourses))
        <div class="mt-48">
            <div class="">
                <h2 class="font-16 font-weight-bold">{{ trans('update.related_courses') }}</h2>
                <p class="mt-4 font-12 text-gray-500">{{ trans('update.explore_courses_we_published_currently_and_enjoy_updated_information') }}</p>
            </div>

            <div class="row">
                @include('design_1.web.courses.components.cards.grids.index',['courses' => $relatedCourses, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16"])
            </div>
        </div>
    @endif
@endif

