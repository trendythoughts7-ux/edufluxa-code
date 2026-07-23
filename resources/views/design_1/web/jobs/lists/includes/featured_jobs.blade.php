@if(!empty($featuredJobs) and $featuredJobs->isNotEmpty())
    <div class="jobs-lists-featured position-relative bg-primary rounded-16 py-16 mb-28">
        <div class="jobs-lists-featured__mask"></div>

        <div class="position-relative z-index-2">

            <div class="card-before-line card-before-line--white card-before-line__4-12 px-16">
                <h5 class="font-14 font-weight-bold text-white">{{ trans('update.featured_jobs') }}</h5>
            </div>

            <div class="swiper-container js-make-swiper featured-jobs-swiper pb-0"
                 data-item="featured-jobs-swiper"
                 data-autoplay="true"
                 data-breakpoints="1440:1,769:1,320:1"
                 data-navigation="true"
            >
                <div class="swiper-button-next jobs-lists-featured__slider-navigation rounded-circle bg-white-20">
                    <x-iconsax-lin-arrow-right class="icons text-white" width="16px" height="16px"/>
                </div>

                <div class="swiper-button-prev jobs-lists-featured__slider-navigation rounded-circle bg-white-20">
                    <x-iconsax-lin-arrow-left class="icons text-white" width="16px" height="16px"/>
                </div>

                <div class="swiper-wrapper py-32">
                    @foreach($featuredJobs as $featuredJob)
                        <div class="swiper-slide">
                            <a href="{{ $featuredJob->getUrl() }}" class="">
                                <div class="position-relative d-flex-center flex-column text-center">

                                    <div class="jobs-lists-featured__avatar size-80 rounded-circle">
                                        <img src="{{ $featuredJob->getIcon() }}" alt="{{ $featuredJob->title }}" class="position-relative img-cover rounded-circle">
                                    </div>

                                    <h6 class="mt-28 font-16 font-weight-bold text-white">{{ $featuredJob->title }}</h6>

                                    {{-- Employment Type --}}
                                    @if(!empty($featuredJob->employmentType))
                                        <div class="d-flex-center gap-4 font-12 text-white">
                                            @svg("iconsax-{$featuredJob->employmentType->getIconText()}", ['height' => 16, 'width' => 16, 'class' => 'text-white'])

                                            <span class="">{{ $featuredJob->employmentType->title }}</span>
                                        </div>
                                    @endif

                                    <div class="position-relative mt-16 d-flex align-items-center w-100">
                                        <div class="d-flex-center flex-column text-center flex-1">
                                            <div class="font-12 text-white">{{ trans('update.salary') }}</div>
                                            <div class="mt-4 text-ellipsis">
                                                @if(auth()->guest() and !empty(getJobsGeneralSettings("login_required_to_view_salaries")))
                                                    <span class="font-12 font-weight-bold text-white">{{ trans('update.login_to_view_salary') }}</span>
                                                @else
                                                    @if(in_array($featuredJob->payment_type, ['fixed_amount', 'range']))
                                                        <div class="d-flex align-items-center font-14 font-weight-bold text-white">
                                                            @if($featuredJob->payment_type == "fixed_amount")
                                                                <span class="">{{ handlePrice($featuredJob->fixed_salary) }}</span>
                                                            @else
                                                                <span class="">{{ handlePrice($featuredJob->min_salary) }}-{{ handlePrice($featuredJob->max_salary) }}</span>
                                                            @endif

                                                            <span class="">/{{ trans("update.{$featuredJob->payment_period}") }}</span>
                                                        </div>
                                                    @else
                                                        <span class="font-12 font-weight-bold text-white">{{ trans('update.negotiable') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <div class="jobs-lists-featured__divider"></div>

                                        <div class="d-flex-center flex-column text-center flex-1">
                                            <div class="font-12 text-white">{{ trans('update.location') }}</div>
                                            <div class="mt-4 font-14 font-weight-bold text-white">
                                                @if(!empty($featuredJob->specificLocation))
                                                    @php
                                                        $specificLocationTitle = $featuredJob->specificLocation->getFullAddress(false, false, true, false, false);
                                                    @endphp

                                                    <span class="">{{ $specificLocationTitle }}</span>
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
