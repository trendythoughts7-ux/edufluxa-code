<div class="job-bottom-fixed-card bg-white">
    <div class="container d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between h-100">
        <div class="d-flex align-items-center mb-16 mb-lg-0">
            <div class="job-bottom-fixed-card__job-img rounded-8">
                <img src="{{ $job->thumbnail }}" class="img-cover rounded-8" alt="{{ $job->title }}">
            </div>
            <div class="ml-8">
                <div class="font-12 text-gray-500">{{ trans('update.you_are_viewing') }}</div>
                <div class="mt-4 font-14 font-weight-bold">{{ $job->title }}</div>
            </div>
        </div>

        @if(false)
            {{-- TODO:: --}}
            <a href="/panel/jobs/my-purchases" target="_blank" class="btn btn-primary btn-lg">{{ trans('update.view_application_result') }}</a>
        @else
            <button type="button" class="js-scroll-to-job-apply-btn btn btn-primary btn-lg">{{ trans('update.apply_for_job') }}</button>
        @endif
    </div>
</div>

<div class="job-bottom-fixed-card__progress">
    <div class="progress-line"></div>
</div>
