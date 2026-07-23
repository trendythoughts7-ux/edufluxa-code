<div class="panel-course-card-1 position-relative {{ !empty($isInvitedCoursesPage) ? 'is-invited-course-card' : '' }}">
    <div class="card-mask"></div>

    <a href="{{ $job->getUrl() }}" target="_blank" class="position-relative d-flex flex-column flex-lg-row gap-12 z-index-2 bg-white p-12 rounded-24">
        {{-- Image --}}
        <div class="panel-course-card-1__image position-relative rounded-16 bg-gray-100">
            <img src="{{ $job->thumbnail }}" alt="" class="img-cover rounded-16">
            {{-- Badges On Image --}}
            @include("design_1.panel.jobs.my_jobs.job_card.badges")

        </div>

        {{-- Content --}}
        <div class="panel-course-card-1__content flex-1 d-flex flex-column">
            <div class="bg-gray-100 p-16 rounded-16 mb-12">
                <div class="d-flex align-items-start justify-content-between gap-12">
                    <div class="">
                        <h3 class="font-16 text-dark">{{ truncate($job->title, 46) }}</h3>

                        <div class="font-12 text-gray-500 mt-8">{{ trans('public.in') }} {{ $job->category->title }}</div>
                    </div>
                </div>

                {{-- Stats --}}
                @include("design_1.panel.jobs.my_jobs.job_card.stats")
                {{-- End Stats --}}
            </div>

            {{-- Progress & Price --}}
            <div class="d-flex align-items-center justify-content-end mt-auto">

                @if(in_array($job->payment_type, ['fixed_amount', 'range']))
                    <div class="d-flex align-items-center gap-4">
                        @if($job->payment_type == "fixed_amount")
                            <span class="font-16 font-weight-bold text-primary">{{ handlePrice($job->fixed_salary) }}</span>
                        @else
                            <span class="font-16 font-weight-bold text-primary">{{ handlePrice($job->min_salary) }}-{{ handlePrice($job->max_salary) }}</span>
                        @endif

                        <span class="font-12 text-gray-500">/{{ trans("update.{$job->payment_period}") }}</span>
                    </div>
                @else
                    <span class="font-16 font-weight-bold text-primary">{{ trans('update.negotiable') }}</span>
                @endif
            </div>
        </div>
    </a>

    {{-- Actions Dropdown (positioned outside the link) --}}
    <div class="item-card-actions-dropdown-container">
        @include("design_1.panel.jobs.my_jobs.job_card.actions_dropdown")
    </div>
</div>
