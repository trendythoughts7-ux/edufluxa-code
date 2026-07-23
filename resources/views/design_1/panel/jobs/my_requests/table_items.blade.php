@php
    $job = $jobRequest->job;
@endphp

<tr>
    {{-- Job Position --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $job->getIcon() }}" alt="{{ $job->title }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <a href="{{ $job->getUrl() }}" target="_blank" class="text-dark">{{ $job->title }}</a>
                <div class="mt-4 font-12 text-gray-500">{{ $job->category->title }}</div>
            </div>
        </div>
    </td>

    {{-- Employer --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $job->creator->getAvatar(48) }}" alt="{{ $job->creator->full_name }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <div class="">{{ $job->creator->full_name }}</div>

                @if(!empty(getJobsGeneralSettings("show_employer_contact")))
                    @if(!empty($job->creator->email))
                        <div class="mt-4 font-12 text-gray-500">{{ $job->creator->email }}</div>
                    @endif

                    @if(!empty($job->creator->mobile))
                        <div class="mt-4 font-12 text-gray-500">+{{ $job->creator->mobile }}</div>
                    @endif
                @endif
            </div>
        </div>
    </td>

    {{-- Employment Type --}}
    <td class="text-center">
        {{ !empty($job->employmentType) ? $job->employmentType->title : '-' }}
    </td>

    {{-- Experience Level --}}
    <td class="text-center">
        {{ !empty($job->experienceLevel) ? $job->experienceLevel->title : '-' }}
    </td>

    {{-- Work Arrangement --}}
    <td class="text-center">
        {{ trans("update.{$job->work_arrangement}") }}
    </td>

    {{-- Salary --}}
    <td class="text-center">
        <div class="d-flex align-items-center gap-2 text-dark">
            @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                @if($job->payment_type == "fixed_amount")
                    <span class="">{{ handlePrice($job->fixed_salary) }}</span>
                @else
                    <span class="">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                @endif

                <span class="">/{{ trans("update.{$job->payment_period}") }}</span>
            @else
                <span class="">{{ trans('update.negotiable') }}</span>
            @endif
        </div>
    </td>

    {{-- Request Date --}}
    <td class="text-center">
        <span>{{ dateTimeFormat($jobRequest->created_at, 'j M Y') }}</span>
    </td>

    {{-- Status --}}
    <td class="text-center">
        @if($jobRequest->status == "approved")
            <div class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">{{ trans('update.approved') }}</div>
        @elseif($jobRequest->status == "rejected")
            <div class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">{{ trans('update.rejected') }}</div>
        @else
            <div class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">{{ trans('update.pending') }}</div>
        @endif
    </td>

    {{-- Actions --}}
    <td class="text-right">

        <div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
            <button type="button" class="d-flex-center size-36 bg-gray border-gray-200 rounded-10">
                <x-iconsax-lin-more class="icons text-gray-500" width="18"/>
            </button>

            <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220 dropdown-menu-top-32">
                <ul class="my-8">

                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="/panel/jobs/my-requests/{{ $jobRequest->id }}/details" target="_blank" class="">{{ trans('update.view_request') }}</a>
                    </li>

                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="/panel/jobs/my-requests/{{ $jobRequest->id }}/status" target="_blank" class="">{{ trans('update.view_status') }}</a>
                    </li>

                    @if(!empty(getJobsGeneralSettings("show_employer_contact")) and $jobRequest->status == "approved")
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/jobs/my-requests/{{ $jobRequest->id }}/get-employer-contact"
                               data-title="{{ trans('update.employer_contact_details') }}"
                               class="js-view-employer-contact">{{ trans('update.employer_contact_details') }}</a>
                        </li>
                    @endif

                    @if($jobRequest->status == "pending")
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/jobs/my-requests/{{ $jobRequest->id }}/delete" class="delete-action text-danger">{{ trans('public.delete') }}</a>
                        </li>
                    @endif

                </ul>
            </div>
        </div>

    </td>

</tr>
