@extends('design_1.web.layouts.app')

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("job_request_form") }}">
@endpush


@section('content')
    <div class="container mt-56 mb-120">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                <div class="d-flex-center flex-column text-center">
                    <h1 class="font-32 font-weight-bold">{{ trans('update.job_application_request') }}</h1>
                    <a href="{{ $job->getUrl() }}" target="_blank" class="mt-8 font-16 text-gray-500">{{ $job->title }}</a>
                </div>

                <div class="card-with-mask position-relative">
                    <div class="mask-8-white rounded-32 z-index-1"></div>

                    <div class="position-relative z-index-3 bg-white p-16 rounded-32 mt-24 ">
                        {{-- Job Details --}}
                        @include("design_1.panel.jobs.requests.details.includes.job_details")

                        {{-- Resume --}}
                        @if(!empty($jobRequest->userResume))
                            <h3 class="mt-36 font-16">{{ trans('update.resume') }}</h3>
                            <p class="mt-4 font-12 text-gray-500">{{ trans('update.review_the_resume_submitted_by_the_applicant') }}</p>

                            <div class="d-flex gap-16 align-items-center justify-content-between p-16 rounded-12 border-gray-200 mt-16">
                                <div class="d-flex align-items-center">
                                    <div class="">
                                        <x-iconsax-bul-note-2 class="icons text-primary" width="24px" height="24px"/>
                                    </div>
                                    <div class="ml-8">
                                        <h6 class="font-12">{{ $jobRequest->userResume->title }}</h6>
                                        <p class="mt-4 font-12 text-gray-500">{{ $jobRequest->userResume->getFileSize() }}</p>
                                    </div>
                                </div>

                                <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/details/download-resume" class="d-flex-center" data-tippy-content="{{ trans('home.download') }}">
                                    <x-iconsax-lin-import-2 class="icons text-gray-500" width="24px" height="24px"/>
                                </a>
                            </div>
                        @endif

                        {{-- Certificates --}}
                        @include("design_1.panel.jobs.requests.details.includes.certificates")

                        {{-- Learning History --}}
                        @include("design_1.panel.jobs.requests.details.includes.learning_history")

                        {{-- Additional Description --}}
                        @if(!empty($jobRequest->description))
                            <h3 class="mt-24 font-16">{{ trans('update.additional_description') }}</h3>
                            <p class="mt-4 font-12 text-gray-500">{{ trans('update.review_the_additional_details_provided_by_the_applicant') }}</p>

                            <div class="mt-16 p-16 rounded-16 bg-gray-100 border-gray-300 text-gray-500">{!! nl2br($jobRequest->description) !!}</div>
                        @endif

                        {{-- Attachments --}}
                        @include("design_1.panel.jobs.requests.details.includes.attachments")

                        <div class="d-flex align-items-center justify-content-between gap-16 mt-16 pt-16 border-top-gray-100">
                            <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests" class="btn btn-lg bg-gray-300 text-gray-500">{{ trans('update.back') }}</a>

                            @if($job->creator_id == $authUser->id and $jobRequest->status == "pending")
                                <div class="d-flex align-items-center gap-24">

                                    <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/moderate/get-modal?type=reject" data-title="{{ trans('update.job_application') }}" class="js-job-request-moderate-action text-danger">
                                        {{ trans('update.reject') }}
                                    </a>

                                    <a href="/panel/jobs/{{ $jobRequest->job_id }}/requests/{{ $jobRequest->id }}/moderate/get-modal?type=approve" data-title="{{ trans('update.job_application') }}" class="js-job-request-moderate-action btn btn-xlg btn-primary">
                                        {{ trans('update.approve') }}
                                    </a>

                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/design_1/js/panel/job_requests.min.js"></script>
@endpush
