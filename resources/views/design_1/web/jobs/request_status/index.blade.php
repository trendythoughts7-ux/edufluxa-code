@extends('design_1.web.layouts.app')

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("system_status_pages") }}">
@endpush

@section("content")
    <section class="container position-relative my-120">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-8">
                <div class="system-status-page-section position-relative">
                    <div class="system-status-page-section__mask"></div>

                    <div class="position-relative d-flex-center flex-column bg-white rounded-32 px-24 py-100 text-center z-index-2">
                        <div class="">
                            <img src="/assets/design_1/img/jobs/job_request_status.svg" alt="{{ trans('update.job_request_status') }}" class="img-fluid" height="300">
                        </div>

                        @if($jobRequest->status == "approved")
                            <h1 class="mt-16 font-16">{{ trans('update.job_request_status_approved_title') }}</h1>
                        @elseif($jobRequest->status == "rejected")
                            <h1 class="mt-16 font-16">{{ trans('update.job_request_status_rejected_title') }}</h1>
                        @else
                            <h1 class="mt-16 font-16">{{ trans('update.job_request_status_pending_title') }}</h1>
                            <p class="mt-4 text-gray-500">{{ trans('update.job_request_status_pending_msg') }}</p>
                        @endif

                        @if(!empty($jobRequest->jobResponseReason))
                            <div class="mt-4 text-gray-500">{{ $jobRequest->jobResponseReason->title }}</div>
                        @endif

                        @if(!empty($jobRequest->reason_description))
                            <div class="mt-8 text-gray-500">{!! nl2br($jobRequest->reason_description) !!}</div>
                        @endif

                        <a href="/panel/jobs/my-requests" class="btn btn-primary btn-lg mt-16">{{ trans('update.application_requests') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
