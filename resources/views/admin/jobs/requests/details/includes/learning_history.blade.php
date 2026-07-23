@if(!empty($jobRequest->learningCourses) and $jobRequest->learningCourses->isNotEmpty())
    <h3 class="mt-24 font-16">{{ trans('update.learning_history') }}</h3>
    <p class="mt-4 font-12 text-gray-500">{{ trans('update.review_the_applicant’s_course_progress_and_learning_history') }}</p>

    <div class="row">
        @foreach($jobRequest->learningCourses as $userCourse)
            @php
                $percent = $userCourse->getProgress(true, $jobRequest->user);
                $courseIcon = $userCourse->getIcon();
            @endphp

            <div class="col-12 col-md-6 mt-16">
                <a href="{{ $userCourse->getUrl() }}" target="_blank" class="d-flex align-items-center p-16 rounded-12 border-gray-200">
                    @if(!empty($courseIcon))
                        <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                            <img src="{{ $courseIcon }}" alt="{{ $userCourse->title }}" class="img-cover rounded-circle">
                        </div>
                    @else
                        <div class="">
                            <x-iconsax-bul-video-play class="icons text-primary" width="24px" height="24px"/>
                        </div>
                    @endif

                    <div class="ml-8">
                        <h6 class="font-12 text-dark">{{ $userCourse->title }}</h6>
                        <p class="mt-4 font-12 text-gray-500">{{ ($percent > 99) ? trans('update.completed') : trans('update.percent_progress', ['percent' => $percent]) }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif
