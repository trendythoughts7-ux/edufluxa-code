@extends('design_1.web.layouts.app', ['appFooter' => false])

@push('styles_top')
    <link rel="stylesheet" href="{{ getDesign1StylePath("create-course") }}">
@endpush

@section('content')
    <form method="post" action="/panel/jobs/{{ !empty($job) ? $job->id .'/update' : 'store' }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input type="hidden" name="current_step" value="{{ !empty($currentStep) ? $currentStep : 1 }}">
        <input type="hidden" name="draft" value="no" id="forDraft"/>
        <input type="hidden" name="get_next" value="no" id="getNext"/>
        <input type="hidden" name="get_step" value="0" id="getStep"/>


        <div class="container mt-80 pb-100">
            {{-- Progress --}}
            @include('design_1.panel.jobs.create.includes.progress')

            {{-- Steps Inputs --}}
            @include("design_1.panel.jobs.create.steps.step_{$currentStep}")
        </div>


        {{-- Bottom Actions --}}
        @include('design_1.panel.jobs.create.includes.bottom_actions')

    </form>
@endsection

@push('scripts_bottom')

    <script src="/assets/design_1/js/panel/create_job.min.js"></script>
@endpush
