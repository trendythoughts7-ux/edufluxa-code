@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{!empty($job) ? trans('update.edit_job'): trans('update.new_job') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl("/jobs") }}">{{ trans('update.jobs') }}</a>
                </div>
                <div class="breadcrumb-item">{{!empty($job) ?trans('/admin/main.edit'): trans('admin/main.new') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="card">
                <div class="card-body">

                    <form method="post" action="{{ getAdminPanelUrl("/jobs/") }}{{ !empty($job) ? $job->id.'/update' : 'store' }}" enctype="multipart/form-data" class="webinar-form">
                        {{ csrf_field() }}
                        <input type="hidden" name="status" value="{{ !empty($job) ? $job->status : 'draft' }}"/>

                        {{-- Basic Information --}}
                        @include('admin.jobs.create.includes.basic_information')

                        {{-- Additional Information --}}
                        @include('admin.jobs.create.includes.additional_information')

                        {{-- Categories --}}
                        @include('admin.jobs.create.includes.categories')

                        @if(!empty($job))

                            {{-- Prerequisites --}}
                            @include('admin.jobs.create.includes.prerequisites')

                            {{-- Related Course --}}
                            @include('admin.webinars.relatedCourse.add_related_course', [
                                    'relatedCourseItemId' => $job->id,
                                     'relatedCourseItemType' => 'job',
                                     'relatedCourses' => $job->relatedCourses
                                ])

                            {{-- FAQs --}}
                            @include('admin.jobs.create.includes.faqs')

                            {{-- Extra Descriptions --}}
                            @include('admin.jobs.create.includes.extra_descriptions')

                            {{-- Location --}}
                            @include('admin.components.location', ['specificLocation' => $job->specificLocation])

                        @endif


                        <section class="mt-3">
                            <h2 class="section-title after-line">{{ trans('public.message_to_reviewer') }}</h2>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mt-15">
                                        <textarea name="message_for_reviewer" rows="10" class="form-control">{{ (!empty($job) && $job->message_for_reviewer) ? $job->message_for_reviewer : old('message_for_reviewer') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="js-form-action-btn btn btn-success" data-status="{{ !empty($job) ? 'publish' : 'draft' }}">{{ !empty($job) ? trans('admin/main.save_and_publish') : trans('admin/main.save_and_continue') }}</button>

                                @if(!empty($job))
                                    <button type="button" class="js-form-action-btn btn btn-warning" data-status="draft">{{ trans('public.draft') }}</button>

                                    <button type="button" class="js-form-action-btn btn btn-danger" data-status="unpublish">{{ ($job->status == "publish") ? trans('update.unpublish') : trans('public.reject') }}</button>

                                    @include('admin.includes.delete_button',[
                                            'url' => getAdminPanelUrl().'/jobs/'. $job->id .'/delete',
                                            'btnText' => trans('public.delete'),
                                            'hideDefaultClass' => true,
                                            'btnClass' => 'btn btn-danger'
                                            ])
                                @endif
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var saveLang = '{{ trans('public.save') }}';
        var closeLang = '{{ trans('public.close') }}';
    </script>

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>

    <script src="/assets/admin/js/parts/create_job.min.js"></script>
@endpush
