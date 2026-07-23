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

    {{-- Employer --}}
    <td class="text-left">
        <div class="d-flex align-items-center">
            <div class="size-48 bg-gray-100 rounded-circle">
                <img src="{{ $job->creator->getAvatar(48) }}" alt="{{ $job->creator->full_name }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-12">
                <div class="">{{ $job->creator->full_name }}</div>

                @if(!empty($job->creator->email))
                    <div class="mt-4 font-12 text-gray-500">{{ $job->creator->email }}</div>
                @endif

                @if(!empty($job->creator->mobile))
                    <div class="mt-4 font-12 text-gray-500">+{{ $job->creator->mobile }}</div>
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

    {{-- Apply Requests --}}
    <td class="text-center">
        {{ $job->job_requests_count }}
    </td>

    {{-- Created Date --}}
    <td class="text-center">
        <span>{{ dateTimeFormat($job->created_at, 'j M Y H:i') }}</span>
    </td>

    {{-- Expiry/End Date --}}
    <td class="text-center">
        @php
            $expireDate = $job->getExpiryDate();
        @endphp

        <span>{{ !empty($expireDate) ? dateTimeFormat($expireDate, 'j M Y H:i') : '-' }}</span>
    </td>

    {{-- Status --}}
    <td>
        @switch($job->status)
            @case('publish')
                @if(!empty($expireDate) and $expireDate < time())
                    <span class="badge-status text-danger bg-danger-30">{{ trans('update.expired') }}</span>
                @else
                    <span class="badge-status text-primary bg-primary-30">{{ trans('public.published') }}</span>
                @endif
                @break
            @case('draft')
                <span class="badge-status text-dark bg-dark-30">{{ trans('admin/main.is_draft') }}</span>
                @break
            @case('pending')
                <span class="badge-status text-warning bg-warning-30">{{ trans('update.pending_review') }}</span>
                @break
            @case('unpublish')
                <span class="badge-status text-danger bg-danger-30">{{ trans('admin/main.unpublished') }}</span>
                @break
            @case('canceled')
                <span class="badge-status text-danger bg-danger-30">{{ trans('update.canceled') }}</span>
                @break
            @case('rejected')
                <span class="badge-status text-danger bg-danger-30">{{ trans('update.rejected') }}</span>
                @break
        @endswitch
    </td>

    {{-- Actions --}}
    <td>
        <div class="btn-group dropdown table-actions position-relative">
            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
            </button>

            <div class="dropdown-menu dropdown-menu-right">

                @if(!in_array($job->status, ['canceled', 'draft', 'pending']))
                    @can('admin_jobs_requests_history')
                        <a href="{{ getAdminPanelUrl("/jobs/requests?job_id={$job->id}") }}" target="_blank" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                            <x-iconsax-lin-briefcase class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                            <span class="text-gray-500 font-14">{{ trans('update.apply_requests') }}</span>
                        </a>
                    @endcan

                    @can('admin_job_send_notification')
                        <a href="{{ getAdminPanelUrl("/jobs/{$job->id}/send-notification") }}" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                            <x-iconsax-lin-notification-bing class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                            <span class="text-gray-500 font-14">{{ trans('notification.send_notification') }}</span>
                        </a>
                    @endcan
                @endif

                @if($job->status !== "publish")
                    @include('admin.includes.delete_button',[
                       'url' => getAdminPanelUrl("/jobs/{$job->id}/publish"),
                       'btnClass' => 'dropdown-item text-success mb-3 py-3 px-0 font-14',
                       'btnText' => trans("admin/main.publish"),
                       'btnIcon' => 'tick-square',
                       'iconType' => 'lin',
                       'iconClass' => 'text-success mr-2',
                    ])
                @endif

                @if($job->status == 'pending')
                    @include('admin.includes.delete_button',[
                       'url' => getAdminPanelUrl("/jobs/{$job->id}/reject"),
                       'btnClass' => 'dropdown-item  text-danger mb-3 py-3 px-0 font-14',
                       'btnText' => trans("admin/main.reject"),
                       'btnIcon' => 'close-square',
                       'iconType' => 'lin',
                       'iconClass' => 'text-danger mr-2',
                    ])

                    @include('admin.includes.delete_button',[
                       'url' => getAdminPanelUrl("/jobs/{$job->id}/unpublish"),
                       'btnClass' => 'dropdown-item text-danger mb-3 py-3 px-0 font-14',
                       'btnText' => trans("admin/main.unpublish"),
                       'btnIcon' => 'gallery-slash',
                       'iconType' => 'lin',
                       'iconClass' => 'text-danger mr-2',
                    ])
                @endif


                @can('admin_jobs_create')
                    <a href="{{ getAdminPanelUrl('/jobs/'. $job->id .'/edit') }}" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                        <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                        <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                    </a>
                @endcan

                @if($job->status != "canceled")
                    @include('admin.includes.delete_button',[
                       'url' => getAdminPanelUrl("/jobs/{$job->id}/cancel"),
                       'btnClass' => 'dropdown-item text-danger mb-3 py-3 px-0 font-14',
                       'btnText' => trans("admin/main.cancel"),
                       'btnIcon' => 'tick-square',
                       'iconType' => 'lin',
                       'iconClass' => 'text-danger mr-2',
                    ])
                @endif

                @can('admin_jobs_delete')
                    @include('admin.includes.delete_button',[
                        'url' => getAdminPanelUrl("/jobs/{$job->id}/delete"),
                        'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                        'btnText' => trans("admin/main.delete"),
                        'btnIcon' => 'trash',
                        'iconType' => 'lin',
                        'iconClass' => 'text-danger mr-2',
                     ])
                @endcan
            </div>
        </div>
    </td>

</tr>
