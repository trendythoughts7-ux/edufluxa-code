<div class="panel-course-grid-card position-relative">
    <div class="panel-course-grid-card__image position-relative rounded-16 bg-gray-100">
        <img src="{{ $webinar->getImage() }}" alt="" class="img-cover rounded-16">

        <span class="is-live-course-icon d-flex-center px-12 rounded-circle bg-white font-12 font-weight-bold text-success">
            {{ trans('panel.purchased') }}
        </span>
    </div>

    <div class="panel-course-grid-card__body position-relative px-16 pb-12">
        <div class="panel-course-grid-card__content is-favorites-card d-flex flex-column bg-white p-12 rounded-16">

            <a href="{{ $webinar->getUrl() }}" target="_blank">
                <h3 class="panel-course-grid-card__title font-14 text-dark">{{ $webinar->title }}</h3>
            </a>

            @include("design_1.web.components.rate", [
                    'rate' => round($webinar->getRate(),1),
                    'rateCount' => $webinar->reviews->count(),
                    'rateClassName' => 'mt-12',
                ])

            <div class="d-flex align-items-center my-16 ">
                <div class="size-32 rounded-circle bg-gray-100">
                    <img src="{{ $webinar->teacher->getAvatar(32) }}" alt="{{ $webinar->teacher->full_name }}" class="img-cover rounded-circle">
                </div>
                <div class="ml-8">
                    <h6 class="font-12 font-weight-bold">{{ $webinar->teacher->full_name }}</h6>
                    <p class="mt-2 font-12 text-gray-500">{{ $webinar->category->title ?? '' }}</p>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between font-12 text-gray-500 mb-12">
                <div class="d-flex align-items-center">
                    <x-iconsax-lin-people class="icons text-gray-500" width="16px" height="16px"/>
                    <span class="ml-2">{{ $webinar->sales_count }} {{ trans('public.students') }}</span>
                </div>
                <div class="d-flex align-items-center">
                    <span>{{ trans('panel.purchase_date') }}: {{ dateTimeFormat($webinar->purchase_date, 'Y-m-d', false) }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-auto border-top-gray-100 pt-12">
                <a href="{{ $webinar->getUrl() }}" target="_blank" class="btn btn-sm btn-success w-100 text-center">
                    {{ trans('panel.start_learning') }}
                </a>
            </div>
        </div>
    </div>
</div>
