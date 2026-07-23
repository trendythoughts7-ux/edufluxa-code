<div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
    <div class="d-flex-center size-40 bg-white border-gray-200 rounded-8 cursor-pointer">
        <x-iconsax-lin-more class="icons text-gray-500" width="24px" height="24px"/>
    </div>

    <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220">
        <ul class="my-8">

            @can('panel_jobs_create')
                <li class="actions-dropdown__dropdown-menu-item">
                    <a href="/panel/jobs/{{ $job->id }}/edit" class="">{{ trans('public.edit') }}</a>
                </li>
            @endcan

            @php
                $expiryDate = $job->getExpiryDate();
            @endphp

            @if($expiryDate > time() and $job->status == "publish")
                <li class="actions-dropdown__dropdown-menu-item">
                    <a href="/panel/jobs/{{ $job->id }}/requests" class="">{{ trans('update.application_requests') }}</a>
                </li>
            @endif

            @if($job->creator_id == $authUser->id)
                @can('panel_jobs_create')
                    <li class="actions-dropdown__dropdown-menu-item">
                        @include('design_1.panel.includes.content_delete_btn', [
                            'deleteContentUrl' => "/panel/jobs/{$job->id}/delete",
                            'deleteContentClassName' => ' text-danger',
                            'deleteContentItem' => $job,
                            'deleteContentItemType' => "job",
                        ])
                    </li>
                @endcan
            @endif

        </ul>
    </div>
</div>
