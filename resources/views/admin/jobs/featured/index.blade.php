@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('update.featured_jobs') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('update.featured_jobs') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">


                            <form action="{{ getAdminPanelUrl() }}/settings/main" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="page" value="general">
                                <input type="hidden" name="name" value="{{ \App\Models\Setting::$jobsFeaturedSettingsName }}">


                                <div class="row">
                                    <div class="col-12 col-md-6">

                                        <div class="form-group mt-3">
                                            <label class="input-label">{{ trans('update.featured_jobs') }}</label>
                                            <select name="value[featured_jobs][]" multiple="multiple" class="form-control select2" data-placeholder="{{ trans('update.select_a_job') }}">

                                                @foreach($jobs as $job)
                                                    <option value="{{ $job->id }}" {{ (!empty($settingValues) and !empty($settingValues['featured_jobs']) and in_array($job->id, $settingValues['featured_jobs'])) ? 'selected' : '' }}>{{ $job->title }}</option>
                                                @endforeach
                                            </select>

                                            <p class="font-12 text-gray-500 mt-2">{{ trans('update.featured_jobs_input_settings_hint') }}</p>
                                        </div>



                                    </div>
                                </div>

                                <div class="col-6 text-right">
                                <button type="submit" class="btn btn-primary mt-1">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

