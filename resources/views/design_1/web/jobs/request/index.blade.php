@extends('design_1.web.layouts.app')

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("job_request_form") }}">
@endpush

@section("content")
    <form action="{{ $job->getRequestUrl() }}" method="post">
        {{ csrf_field() }}

        <div class="container mt-56 mb-120">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">

                    <div class="d-flex-center flex-column text-center">
                        <h1 class="font-32 font-weight-bold">{{ trans('update.apply_for_this_job') }}</h1>
                        <p class="mt-8 font-16 text-gray-500">{{ trans('update.apply_for_this_job_in_request_page_hint') }}</p>
                    </div>

                    <div class="card-with-mask position-relative">
                        <div class="mask-8-white rounded-32 z-index-1"></div>

                        <div class="position-relative z-index-3 bg-white p-16 rounded-32 mt-24 ">
                            {{-- Job Details --}}
                            @include("design_1.web.jobs.request.includes.job_details")

                            {{-- Notes --}}
                            @php
                                $jobsApplicationFormSetting = getJobsApplicationFormSettings();
                            @endphp

                            @if(!empty($jobsApplicationFormSetting))
                                <div class="mt-36">
                                    @if(!empty($jobsApplicationFormSetting['title']))
                                        <h2 class="font-16 text-dark">{{ $jobsApplicationFormSetting['title'] }}</h2>
                                    @endif

                                    @if(!empty($jobsApplicationFormSetting['description']))
                                        <p class="mt-8 text-gray-500">{!! nl2br($jobsApplicationFormSetting['description']) !!}</p>
                                    @endif

                                    @if(!empty($jobsApplicationFormSetting['image']))
                                        <div class="d-flex-center mt-24">
                                            <img src="{{ $jobsApplicationFormSetting['image'] }}" alt="{{ trans('public.image') }}" class="img-fluid" height="156px">
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Select Resume --}}
                            @include("design_1.web.jobs.request.includes.resumes")

                            {{-- Select Certificates --}}
                            @include("design_1.web.jobs.request.includes.certificates")

                            {{-- Learning History --}}
                            @include("design_1.web.jobs.request.includes.learning_history")

                            {{-- Additional Description --}}
                            <h3 class="mt-24 font-16">{{ trans('update.additional_description') }}</h3>
                            <p class="mt-4 font-12 text-gray-500">{{ trans('update.submit_additional_information_to_the_employer') }}</p>

                            <div class="form-group mt-24">
                                <label class="form-group-label">{{ trans('public.description') }}</label>
                                <textarea name="description" class="form-control" rows="6"></textarea>
                            </div>

                            {{-- Attachments --}}
                            @include("design_1.web.jobs.request.includes.attachments")

                            <div class="d-flex align-items-center justify-content-between mt-16 pt-16 border-top-gray-200">
                                <a href="{{ $job->getUrl() }}" class="btn btn-lg bg-gray-300 text-gray-500">{{ trans('update.back') }}</a>

                                <button type="button" class="js-submit-application-btn btn btn-primary btn-lg">{{ trans('update.submit_application') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection

@push("scripts_bottom")
    <script>
        var titleLang = '{{ trans('public.title') }}';
        var attachmentLang = '{{ trans('update.attachment') }}';
        var uploadIcon = `<x-iconsax-lin-export class="icons text-gray-border" width="24px" height="24px"/>`;
        var closeIcon = `<x-iconsax-lin-add class="close-icon text-white" width="25px" height="25px"/>`;
    </script>

    <script src="{{ getDesign1ScriptPath("job_request_form") }}"></script>
@endpush
