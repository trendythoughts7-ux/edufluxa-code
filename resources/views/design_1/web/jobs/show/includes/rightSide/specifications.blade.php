<div class="job-right-side-section position-relative mt-28">
    <div class="job-right-side-section__mask"></div>

    <div class="position-relative card-before-line bg-white rounded-24 p-16 z-index-2">
        <h4 class="font-14 font-weight-bold">{{ trans('update.job_specifications') }}</h4>

        <div class="d-flex align-items-center justify-content-between mt-16">
            <div class="d-flex align-items-center font-14 text-gray-500">
                <x-iconsax-lin-profile-2user class="icons text-gray-500" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('update.total_vacancies') }}</span>
            </div>

            <span class="">{{ $job->hiring_count }}</span>
        </div>

        @if(!empty(getJobsGeneralSettings("display_applied_users")))
            <div class="d-flex align-items-center justify-content-between mt-16">
                <div class="d-flex align-items-center font-14 text-gray-500">
                    <x-iconsax-lin-profile-tick class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="ml-4">{{ trans('update.applied_users') }}</span>
                </div>

                <span class="">{{ $job->job_requests_count }}</span>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mt-16">
            <div class="d-flex align-items-center font-14 text-gray-500">
                <x-iconsax-lin-calendar-2 class="icons text-gray-500" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('update.created_date') }}</span>
            </div>

            <span class="">{{ dateTimeFormat($job->created_at, 'j M Y | H:i') }}</span>
        </div>


        @if(!empty($job->hiring_end_date))
            <div class="d-flex align-items-center justify-content-between mt-16">
                <div class="d-flex align-items-center font-14 text-gray-500">
                    <x-iconsax-lin-calendar-tick class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="ml-4">{{ trans('webinars.end_date') }}</span>
                </div>

                <span class="">{{ dateTimeFormat($job->hiring_end_date, 'j M Y | H:i') }}</span>
            </div>
        @endif

        @php
            $jobExpireDate = $job->getExpiryDate();
        @endphp

        @if(!empty($jobExpireDate))
            <div class="d-flex align-items-center justify-content-between mt-16">
                <div class="d-flex align-items-center font-14 text-gray-500">
                    <x-iconsax-lin-calendar-remove class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="ml-4">{{ trans('update.expiry_date') }}</span>
                </div>

                <span class="">{{ dateTimeFormat($jobExpireDate, 'j M Y | H:i') }}</span>
            </div>
        @endif

        @if(!empty($job->employmentType))
            <div class="d-flex align-items-center justify-content-between mt-16">
                <div class="d-flex align-items-center font-14 text-gray-500">
                    <x-iconsax-lin-briefcase class="icons text-gray-500" width="20px" height="20px"/>
                    <span class="ml-4">{{ trans('update.employment_type') }}</span>
                </div>

                <span class="">{{ $job->employmentType->title }}</span>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mt-16">
            <div class="d-flex align-items-center font-14 text-gray-500">
                <x-iconsax-lin-people class="icons text-gray-500" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('update.company_size') }}</span>
            </div>

            <span class="">{{ $job->company_size }}</span>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-16">
            <div class="d-flex align-items-center font-14 text-gray-500">
                <x-iconsax-lin-monitor class="icons text-gray-500" width="20px" height="20px"/>
                <span class="ml-4">{{ trans('update.work_arrangement') }}</span>
            </div>

            <span class="">{{ trans("update.{$job->work_arrangement}") }}</span>
        </div>


    </div>
</div>
