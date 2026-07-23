<div class="job-details position-relative">
    <div class="job-details__mask"></div>

    <div class="position-relative bg-primary p-4 rounded-16 z-index-2">
        <div class="px-4 py-8">
            <h4 class="font-14 font-weight-bold text-white">{{ trans('update.job_applicant') }}</h4>
        </div>

        <div class="position-relative bg-white rounded-12 p-16 mt-8 ">

            <div class="d-flex align-items-center">
                <div class="position-relative">

                    <div class="d-flex-center size-56 rounded-circle bg-gray-200">
                        <div class="position-relative d-flex-center size-48 rounded-circle">
                            <img src="{{ $job->getIcon() }}" alt="{{ $job->title }}" class="img-cover rounded-circle">
                        </div>
                    </div>
                </div>
                <div class="ml-16">
                    <h5 class="font-14 font-weight-bold">{{ $job->title }}</h5>
                    <p class="font-12 text-gray-500 mt-4">{{ trans('public.by') }} {{ $job->creator->full_name }}</p>
                </div>
            </div>

            <div class="d-flex flex-column gap-16 flex-lg-row gap-lg-4 align-items-lg-center justify-content-lg-between mt-24 pt-16 border-top-gray-200">

                {{-- Employment Type --}}
                @if(!empty($job->employmentType))
                    <div class="d-flex align-items-center">
                        <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                            @svg("iconsax-{$job->employmentType->getIconText()}", ['height' => 20, 'width' => 20, 'class' => 'icons text-gray-500'])
                        </div>

                        <div class="ml-8">
                            <span class="d-block font-12 text-gray-400">{{ trans('update.employment_type') }}</span>
                            <span class="d-block font-14 font-weight-bold text-gray-500 mt-4">{{ $job->employmentType->title }}</span>
                        </div>
                    </div>
                @endif

                {{-- Experience Level --}}
                @if(!empty($job->experienceLevel))
                    <div class="d-flex align-items-center">
                        <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                            @svg("iconsax-{$job->experienceLevel->getIconText()}", ['height' => 20, 'width' => 20, 'class' => 'icons text-gray-500'])
                        </div>

                        <div class="ml-8">
                            <span class="d-block font-12 text-gray-400">{{ trans('update.experience_level') }}</span>
                            <span class="d-block font-14 font-weight-bold text-gray-500 mt-4">{{ $job->experienceLevel->title }}</span>
                        </div>
                    </div>
                @endif

                {{-- Work Arrangement --}}
                <div class="d-flex align-items-center">
                    <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                        @svg("iconsax-lin-{$job->getWorkArrangementIconText()}", ['height' => 20, 'width' => 20, 'class' => 'icons text-gray-500'])
                    </div>

                    <div class="ml-8">
                        <span class="d-block font-12 text-gray-400">{{ trans('update.work_arrangement') }}</span>
                        <span class="d-block font-14 font-weight-bold text-gray-500 mt-4">{{ trans("update.{$job->work_arrangement}") }}</span>
                    </div>
                </div>

                {{-- Salary --}}
                <div class="d-flex align-items-center">
                    <div class="d-flex-center size-40 rounded-circle bg-gray-100">
                        <x-iconsax-lin-dollar-circle class="icons text-gray-500" width="20px" height="20px"/>
                    </div>
                    <div class="ml-8">
                        <span class="d-block font-12 text-gray-400">{{ trans('update.salary') }}</span>
                        <div class="d-flex align-items-center gap-4 font-14 font-weight-bold text-gray-500 mt-4">
                            @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                                @if($job->payment_type == "fixed_amount")
                                    <span class="">{{ handlePrice($job->fixed_salary) }}</span>
                                @else
                                    <span class="">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                                @endif

                                <span class="font-12 font-weight-400">/{{ trans("update.{$job->payment_period}") }}</span>
                            @else
                                <span class="">{{ trans('update.negotiable') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
