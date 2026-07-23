@php
    $lastApplication = $job->jobRequests->first();
@endphp

<div class="d-grid grid-columns-2 grid-lg-columns-3 gap-24 mt-16">
    {{-- c --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-profile-2user class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $job->jobRequests->count() }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.applied_users') }}</span>
        </div>
    </div>

    {{-- Pending Users --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-profile class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $job->jobRequests->where('status', 'pending')->count() }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.pending_users') }}</span>
        </div>
    </div>

    {{-- Approved Users --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-profile-tick class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $job->jobRequests->where('status', 'approved')->count() }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.approved_users') }}</span>
        </div>
    </div>

    {{-- Views --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-eye class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ shortNumbers($job->visits_count) }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.views') }}</span>
        </div>
    </div>

    {{-- Last Application --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-video-circle class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ !empty($lastApplication) ? dateTimeFormat($lastApplication->created_at, 'j M Y') : '-' }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.last_application') }}</span>
        </div>
    </div>

    @php
        $expiryDate = $job->getExpiryDate();
    @endphp

    {{-- Expiry Date --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-video-circle class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ !empty($expiryDate) ? dateTimeFormat($expiryDate, 'j M Y') : '-' }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.expiry_date') }}</span>
        </div>
    </div>

</div>
