@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <ul class="nav nav-pills border-bottom-gray-200 pb-4" id="myTab3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">{{ trans('update.general') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="hero_content-tab" data-toggle="tab" href="#hero_content" role="tab" aria-controls="hero_content" aria-selected="true">{{ trans('update.hero_content') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" id="application_form-tab" data-toggle="tab" href="#application_form" role="tab" aria-controls="application_form" aria-selected="true">{{ trans('update.application_form') }}</a>
                                </li>
                            </ul>

                            <div class="tab-content" id="myTabContent2">
                                <div class="tab-pane mt-3 fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    @include('admin.jobs.settings.tabs.general')
                                </div>

                                <div class="tab-pane mt-3 fade" id="hero_content" role="tabpanel" aria-labelledby="hero_content-tab">
                                    @include('admin.jobs.settings.tabs.hero_content')
                                </div>

                                <div class="tab-pane mt-3 fade" id="application_form" role="tabpanel" aria-labelledby="application_form-tab">
                                    @include('admin.jobs.settings.tabs.application_form')
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
@endpush
