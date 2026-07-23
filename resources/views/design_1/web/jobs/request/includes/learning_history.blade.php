@if(!empty($userCourses) and $userCourses->isNotEmpty())
    <h3 class="mt-24 font-16">{{ trans('update.share_your_learning_history') }}</h3>
    <p class="mt-4 font-12 text-gray-500">{{ trans('update.share_your_enrolled_courses_and_current_learning_progress') }}</p>

    <div class="row">
        @foreach($userCourses as $userCourse)
            @php
                $percent = $userCourse->getProgress(true, $authUser);
                $courseIcon = $userCourse->getIcon();
            @endphp

            <div class="col-12 col-md-6 mt-16">
                <div class="custom-input-button with-active-text position-relative">
                    <input type="checkbox" name="courses[]" id="courses_{{ $userCourse->id }}" value="{{ $userCourse->id }}">
                    <label for="courses_{{ $userCourse->id }}" class="position-relative justify-content-start p-16 rounded-12">

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
                            <h6 class="font-12">{{ $userCourse->title }}</h6>
                            <p class="mt-8 font-12 text-gray-500">{{ ($percent > 99) ? trans('update.completed') : trans('update.percent_progress', ['percent' => $percent]) }}</p>
                        </div>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
@endif
