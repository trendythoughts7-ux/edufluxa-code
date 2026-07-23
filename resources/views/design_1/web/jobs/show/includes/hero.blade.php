<div class="job-hero d-flex flex-column justify-content-end rounded-32 px-20 bg-gray-200">
    <div class="job-hero__mask rounded-32"></div>

    <img src="{{ $job->cover_image }}" class="job-hero__cover-img img-cover rounded-32" alt="{{ $job->title }}"/>

    <div class="job-hero__content position-relative z-index-3">
        @if(!empty($job->category))
            <div class="d-flex align-items-center text-white opacity-50">
                <a href="/jobs" class="text-white">{{ trans('update.jobs') }}</a>
                <x-iconsax-lin-arrow-right-1 class="icons text-white mx-2" width="16px" height="16px"/>
                <a href="{{ $job->category->getUrl() }}" class="text-white">{{ $job->category->title }}</a>
            </div>
        @endif

        <div class="d-flex align-items-center flex-wrap gap-12 mt-4">
            <h1 class="job-hero__title font-32 font-weight-bold text-white text-ellipsis">{{ $job->title }}</h1>

            @php
                $jobAllBadges = $job->allBadges();
            @endphp

            {{-- Badges --}}
            @if(count($jobAllBadges))
                <div class="d-flex flex-wrap align-items-center gap-12">
                    @foreach($jobAllBadges as $jobBadge)
                        <div class="d-flex-center gap-4 p-4 pr-8 rounded-32" style="background-color: {{ $jobBadge->background }}; color: {{ $jobBadge->color }};">
                            @if(!empty($jobBadge->icon))
                                <div class="size-24">
                                    <img src="{{ $jobBadge->icon }}" alt="{{ $jobBadge->title }}" class="img-cover">
                                </div>
                            @endif
                            <span class="font-12">{{ $jobBadge->title }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        @if(!empty($job->summary))
            <div class="mt-8 text-white opacity-50">{!! nl2br($job->summary) !!}</div>
        @endif

        {{-- Stat --}}
        <div class="d-flex align-items-center flex-wrap gap-24 mt-12">
            {{-- Employment Type --}}
            @if(!empty($job->employmentType))
                <div class="d-flex align-items-center gap-4 font-12 text-white">
                    @svg("iconsax-{$job->employmentType->getIconText()}", ['height' => 16, 'width' => 16, 'class' => 'text-white opacity-50'])

                    <span class=" font-weight-bold">{{ $job->employmentType->title }}</span>
                </div>
            @endif

            {{-- Experience Level --}}
            @if(!empty($job->experienceLevel))
                <div class="d-flex align-items-center gap-4 font-12 text-white">
                    @svg("iconsax-{$job->experienceLevel->getIconText()}", ['height' => 16, 'width' => 16, 'class' => 'text-white opacity-50'])

                    <span class=" font-weight-bold">{{ $job->experienceLevel->title }}</span>
                </div>
            @endif

            {{-- Location --}}
            @if(!empty($job->specificLocation))
                @php
                    $specificLocationTitle = $job->specificLocation->getFullAddress(false, true, true, false, false);
                @endphp

                <div class="d-flex align-items-center gap-4 font-12 text-white">
                    <x-iconsax-lin-location class="icons text-white opacity-50" width="16px" height="16px"/>
                    <span class=" font-weight-bold">{{ $specificLocationTitle }}</span>
                </div>
            @endif

            {{-- Work Arrangement --}}
            <div class="d-flex align-items-center gap-4 font-12 text-white">
                @svg("iconsax-lin-{$job->getWorkArrangementIconText()}", ['height' => 16, 'width' => 16, 'class' => 'text-white opacity-50'])
                <span class=" font-weight-bold">{{ trans("update.{$job->work_arrangement}") }}</span>
            </div>

        </div>

        <div class="d-flex align-items-center mt-20">
            <div class="size-40 rounded-circle">
                <img src="{{ $job->creator->getAvatar(40) }}" class="img-cover rounded-circle" alt="{{ $job->creator->full_name }}">
            </div>

            <div class="ml-8">
                <a href="{{ $job->creator->getProfileUrl() }}" target="_blank" class="font-14 font-weight-bold text-white">{{ $job->creator->full_name }}</a>
                <p class="mt-2 font-12 text-white">{{ $job->creator->role->caption }}</p>
            </div>
        </div>
    </div>
</div>
