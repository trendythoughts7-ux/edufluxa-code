@extends('design_1.panel.layouts.panel')

@section('content')
<div class="panel-webinars-purchases">
    <h1 class="page-title">{{ trans('panel.my_purchases') }}</h1>

    @if($webinars->count() > 0)
        <div class="row">
            @foreach($webinars as $webinar)
                @include('design_1.panel.webinar.purchases_grid_card', ['webinar' => $webinar])
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {!! $webinars->links('vendor.pagination.design_1') !!}
        </div>
    @else
        @include('design_1.panel.includes.no-result', [
            'file_name' => 'purchased_courses',
            'title' => trans('panel.no_result_purchases'),
            'hint' => trans('panel.no_result_purchases_hint'),
            'btn' => [
                'url' => url('/'),
                'text' => trans('panel.start_learning'),
            ],
        ])
    @endif
</div>
@endsection
