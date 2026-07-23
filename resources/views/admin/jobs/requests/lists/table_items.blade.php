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
                <div class="mt-4 font-12 text-gray-500">{{ !empty($job->category) ? $job->category->title : '-' }}</div>
            </div>
        </div>
    </td>

    {{-- Applicant --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $jobRequest->user->getAvatar(48) }}" alt="{{ $jobRequest->user->full_name }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <div class="">{{ $jobRequest->user->full_name }}</div>

                @if(!empty($jobRequest->user->email))
                    <div class="mt-4 font-12 text-gray-500">{{ $jobRequest->user->email }}</div>
                @endif

                @if(!empty($jobRequest->user->mobile))
                    <div class="mt-4 font-12 text-gray-500">+{{ $jobRequest->user->mobile }}</div>
                @endif
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
        <span>{{ dateTimeFormat($jobRequest->created_at, 'j M Y H:i') }}</span>
    </td>

    {{-- Status --}}
    <td class="text-center">
        @if($jobRequest->status == "approved")
            <div class="badge-status bg-success-30 font-12 text-success">{{ trans('update.approved') }}</div>
        @elseif($jobRequest->status == "rejected")
            <div class="badge-status bg-danger-30 font-12 text-danger">{{ trans('update.rejected') }}</div>
        @else
            <div class="badge-status bg-warning-30 font-12 text-warning">{{ trans('update.pending') }}</div>
        @endif
    </td>

    {{-- Actions --}}
    <td>
        <div class="btn-group dropdown table-actions position-relative">
            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
            </button>

            <div class="dropdown-menu dropdown-menu-right">

                @can('admin_jobs_requests_details')
                    <a href="{{ getAdminPanelUrl("/jobs/requests/{$jobRequest->id}/details") }}" target="_blank" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                        <x-iconsax-lin-eye class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                        <span class="text-gray-500 font-14">{{ trans('update.view_request') }}</span>
                    </a>
                @endcan

                @if(in_array($jobRequest->status, ["rejected", "pending"]))
                    <a href="{{ getAdminPanelUrl("/jobs/requests/{$jobRequest->id}/moderate/get-modal?type=approve") }}"
                       data-title="{{ trans('update.job_application') }}"
                       class="js-job-request-moderate-action dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4"
                    >
                        <x-iconsax-lin-tick-square class="icons text-success mr-2" width="18px" height="18px"/>
                        <span class="text-success font-14">{{ trans('update.approve') }}</span>
                    </a>
                @endif

                @if(in_array($jobRequest->status, ["approved", "pending"]))
                    <a href="{{ getAdminPanelUrl("/jobs/requests/{$jobRequest->id}/moderate/get-modal?type=reject") }}"
                       data-title="{{ trans('update.job_application') }}"
                       class="js-job-request-moderate-action dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4"
                    >
                        <x-iconsax-lin-box-remove class="icons text-danger mr-2" width="18px" height="18px"/>
                        <span class="text-danger font-14">{{ trans('update.reject') }}</span>
                    </a>
                @endif


                @include('admin.includes.delete_button',[
                    'url' => getAdminPanelUrl("/jobs/requests/{$jobRequest->id}/delete"),
                    'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                    'btnText' => trans("admin/main.delete"),
                    'btnIcon' => 'trash',
                    'iconType' => 'lin',
                    'iconClass' => 'text-danger mr-2',
                 ])

            </div>
        </div>
    </td>

</tr>
