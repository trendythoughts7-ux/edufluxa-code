<div class="panel-course-card-1__badges d-flex flex-wrap gap-8">
    @if(!empty($job->deleteRequest) and $job->deleteRequest->status == "pending")
        <div class="d-flex-center badge bg-danger">
            <x-iconsax-lin-trash class="icons text-white" width="20px" height="20px"/>
            <span class="ml-4 font-12 text-white">{{ trans('update.removal_request_sent') }}</span>
        </div>
    @else
        @php
            $expiryDate = $job->getExpiryDate();
        @endphp

        @switch($job->status)
            @case('publish')
                @if(!empty($expiryDate) and $expiryDate < time())
                    <div class="d-flex-center badge bg-danger">
                        <x-iconsax-lin-calendar-remove class="icons text-white" width="20px" height="20px"/>
                        <span class="ml-4 font-12 text-white">{{ trans('update.expired') }}</span>
                    </div>
                @else
                    <div class="d-flex-center badge bg-primary">
                        <x-iconsax-lin-note-2 class="icons text-white" width="20px" height="20px"/>
                        <span class="ml-4 font-12 text-white">{{ trans('update.ongoing') }}</span>
                    </div>
                @endif

                @break
            @case('draft')
                <div class="d-flex-center badge bg-warning">
                    <x-iconsax-lin-note-2 class="icons text-white" width="20px" height="20px"/>
                    <span class="ml-4 font-12 text-white">{{ trans('public.draft') }}</span>
                </div>
                @break
            @case('pending')
                <div class="d-flex-center badge bg-warning">
                    <x-iconsax-lin-more-circle class="icons text-white" width="20px" height="20px"/>
                    <span class="ml-4 font-12 text-white">{{ trans('update.pending_review') }}</span>
                </div>
                @break
            @case('unpublish')
                <div class="d-flex-center badge bg-danger">
                    <x-iconsax-lin-note-2 class="icons text-white" width="20px" height="20px"/>
                    <span class="ml-4 font-12 text-white">{{ trans('update.unpublished') }}</span>
                </div>
                @break
            @case('rejected')
                <div class="d-flex-center badge bg-danger">
                    <x-iconsax-lin-clipboard-close class="icons text-white" width="20px" height="20px"/>
                    <span class="ml-4 font-12 text-white">{{ trans('public.rejected') }}</span>
                </div>
                @break
            @case('canceled')
                <div class="d-flex-center badge bg-danger">
                    <x-iconsax-lin-calendar-remove class="icons text-white" width="20px" height="20px"/>
                    <span class="ml-4 font-12 text-white">{{ trans('update.canceled') }}</span>
                </div>
                @break
        @endswitch
    @endif
</div>
