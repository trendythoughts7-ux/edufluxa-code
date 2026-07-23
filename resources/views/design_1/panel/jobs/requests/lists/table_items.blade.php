<tr>

    {{-- Applicant --}}
    <td class="text-left">
        <div class="d-flex align-items-center gap-24 gap-lg-64">
            <div class="d-flex align-items-center">
                <div class="size-48 bg-gray-100 rounded-circle">
                    <img src="{{ $jobRequest->user->getAvatar(48) }}" alt="{{ $jobRequest->user->full_name }}" class="img-cover rounded-circle">
                </div>
                <div class="ml-12">
                    <div class="">{{ $jobRequest->user->full_name }}</div>

                    @if(!empty(getJobsGeneralSettings("show_student_contact")))
                        @if(!empty($jobRequest->user->email))
                            <div class="mt-4 font-12 text-gray-500">{{ $jobRequest->user->email }}</div>
                        @endif

                        @if(!empty($jobRequest->user->mobile))
                            <div class="mt-4 font-12 text-gray-500">+{{ $jobRequest->user->mobile }}</div>
                        @endif
                    @endif
                </div>
            </div>


            {{-- Pin --}}
            <div class="js-pin-job-request cursor-pointer {{ $jobRequest->pinned ? 'text-warning' : 'text-gray-500' }}"
                 data-tippy-content="{{ trans('update.pin') }}"
                 data-path="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/togglePin"
            >
                <x-iconsax-bol-star class="icons " width="24px" height="24px"/>
            </div>
        </div>
    </td>

    {{-- Shared Information --}}
    <td class="text-left">
        @php
            $sharedInformation = $jobRequest->getSharedInformation();
        @endphp

        @if(!empty($sharedInformation))
            <span class="">{{ implode(',', $sharedInformation) }}</span>
        @else
            -
        @endif
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
                        <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/details" target="_blank" class="">{{ trans('update.view_request') }}</a>
                    </li>

                    @if(!empty(getJobsGeneralSettings("show_student_contact")))
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/get-user-contact"
                               data-title="{{ trans('update.applicant_contact_details') }}"
                               class="js-view-employer-contact">{{ trans('update.applicant_contact_details') }}</a>
                        </li>
                    @endif

                    @if(in_array($jobRequest->status, ["rejected", "pending"]))
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/moderate/get-modal?type=approve" data-title="{{ trans('update.job_application') }}" class="js-job-request-moderate-action text-success">
                                {{ trans('update.approve') }}
                            </a>
                        </li>
                    @endif

                    @if(in_array($jobRequest->status, ["approved", "pending"]))
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/moderate/get-modal?type=reject" data-title="{{ trans('update.job_application') }}" class="js-job-request-moderate-action delete-action text-danger">
                                {{ trans('update.reject') }}
                            </a>
                        </li>
                    @endif


                </ul>
            </div>
        </div>

    </td>

</tr>
