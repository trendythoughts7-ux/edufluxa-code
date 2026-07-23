@php
    $cardName = getThemeContentCardStyle("job");
@endphp

@push('styles_top')
    @if(empty($withoutStyles))
        <link rel="stylesheet" href="{{ getDesign1StylePath("job_cards/{$cardName}") }}">
    @endif
@endpush

@foreach($jobs as $job)
    <div class="{{ !empty($gridCardClassName) ? $gridCardClassName : '' }}">
        @include("design_1.web.jobs.components.cards.grids.{$cardName}", ['job' => $job])
    </div>
@endforeach
