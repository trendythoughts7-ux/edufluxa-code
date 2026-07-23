@php
    $jobExpireDate = $job->getExpiryDate();
@endphp


<div class="job-grid-card-1 position-relative h-100">
    <div class="job-grid-card-1__mask"></div>

    <div class="position-relative d-flex flex-column z-index-3 bg-white p-16 rounded-16 w-100 h-100">
        <div class="d-flex align-items-center justify-content-between">
            {{-- Employment Type --}}
            @if(!empty($job->employmentType))
                <div class="d-flex-center gap-4 py-8 px-12 rounded-16 bg-gray-100 font-12 text-gray-500">
                    @svg("iconsax-{$job->employmentType->getIconText()}", ['height' => 16, 'width' => 16, 'class' => 'text-gray-500 opacity-50'])

                    <span class="">{{ $job->employmentType->title }}</span>
                </div>
            @endif

            <div class="d-flex-center size-64">
                <img src="{{ $job->getIcon() }}" alt="{{ $job->title }}" class="img-fluid">
            </div>
        </div>

        <a href="{{ $job->getUrl() }}" class="">
            <h3 class="font-16 mt-16 text-ellipsis text-dark">{{ $job->title }}</h3>
        </a>

        <div class="d-flex align-items-center gap-4 font-12 text-gray-500 mt-4">
            <span class="job-grid-card-1__before-line"></span>
            <span class="">{{ $job->category->title }}</span>
        </div>

        {{-- Summary --}}
        <div class="mt-16 text-gray-500">{{ truncate($job->summary, 155) }}</div>

        {{-- Creator --}}
        <a href="{{ $job->creator->getProfileUrl() }}" class="d-flex align-items-center my-16 text-dark">
            <div class="size-40 bg-gray-100 rounded-circle">
                <img src="{{ $job->creator->getAvatar(40) }}" alt="{{ $job->creator->full_name }}" class="img-cover rounded-circle">
            </div>
            <div class="ml-8">
                <h5 class="font-12 text-ellipsis">{{ $job->creator->full_name }}</h5>

                @include("design_1.web.components.rate", [
                    'rate' => round($job->creator->rates(), 1),
                    'rateClassName' => 'mt-4',
                ])
            </div>
        </a>

        {{-- Footer --}}
        <div class="d-flex align-items-center justify-content-between pt-16 mt-auto border-top-gray-100">
            <div class="d-flex flex-column">
                @if(auth()->guest() and !empty(getJobsGeneralSettings("login_required_to_view_salaries")))
                    <a href="/login" class="font-weight-bold text-gray-500">{{ trans('update.login_to_view_salary') }}</a>
                @else
                    @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                        <div class="d-flex align-items-center font-weight-bold text-dark">
                            @if($job->payment_type == "fixed_amount")
                                <span class="">{{ handlePrice($job->fixed_salary) }}</span>
                            @else
                                <span class="">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                            @endif

                            <span class="">/{{ trans("update.{$job->payment_period}") }}</span>
                        </div>
                    @else
                        <span class="font-weight-bold text-dark">{{ trans('update.negotiable') }}</span>
                    @endif
                @endif

                <div class="d-flex align-items-center gap-4 font-12 text-gray-500">
                    @if(!empty($job->specificLocation))
                        @php
                            $specificLocationTitle = $job->specificLocation->getFullAddress(false, false, true, false, false);
                        @endphp

                        <span class="">{{ $specificLocationTitle }},</span>
                    @endif

                    <span class="">{{ trans("update.{$job->work_arrangement}") }}</span>
                </div>
            </div>

            @if($jobExpireDate > time())
                <a href="{{ $job->getUrl() }}" class="btn btn-primary gap-4">
                    <span class="">{{ trans('update.apply') }}</span>
                    <x-iconsax-lin-arrow-right class="icons " width="16px" height="16px"/>
                </a>
            @endif
        </div>

    </div>
</div>

