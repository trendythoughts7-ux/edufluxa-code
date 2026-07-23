@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

<div class="bg-white rounded-16 p-16 mt-32">

    <h3 class="font-14 font-weight-bold my-24">{{ trans('public.basic_information') }}</h3>

    @include('design_1.panel.includes.locale.locale_select',[
        'itemRow' => !empty($job) ? $job : null,
        'withoutReloadLocale' => false,
        'extraClass' => ''
    ])

    <div class="form-group">
        <label class="form-group-label is-required">{{ trans('public.title') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <input type="text" name="title" class="form-control @error('title')  is-invalid @enderror" value="{{ (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->title : old('title') }}" placeholder=""/>
        @error('title')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group mt-15">
        <label class="form-group-label">{{ trans('public.seo_description') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <input type="text" name="seo_description" class="form-control @error('seo_description')  is-invalid @enderror " value="{{ (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->seo_description : old('seo_description') }}" placeholder="{{ trans('forms.50_160_characters_preferred') }}"/>
        @error('seo_description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>


    <div class="form-group mb-0">
        <h3 class="font-14 font-weight-bold position-relative d-inline-flex is-required">{{ trans('update.thumbnail_&_cover') }}</h3>
    </div>


    <div class="row">

        @include('design_1.panel.jobs.create.includes.media',[
            'media' => !empty($job) ? $job->thumbnail : null,
            'mediaName' => 'thumbnail',
            'mediaTitle' => trans('update.thumbnail'),
        ])

        @include('design_1.panel.jobs.create.includes.media',[
            'media' => !empty($job) ? $job->cover_image : null,
            'mediaName' => 'cover_image',
            'mediaTitle' => trans('public.cover_image'),
        ])


        <div class="col-12 mt-8">
            @error('thumbnail')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror

            @error('cover_image')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>

    {{-- Event Icon --}}
    <h3 class="font-14 font-weight-bold mt-24 mb-16">{{ trans('update.job_icon') }} ({{ trans('public.optional') }})</h3>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.icon') }}</label>

                <div class="custom-file bg-white">
                    <input type="file" name="icon" class="js-ajax-upload-file-input js-ajax-icon custom-file-input" data-upload-name="icon" id="iconInput" accept="image/*">
                    <span class="custom-file-text">{{ (!empty($job) and !empty($job->icon)) ? getFileNameByPath($job->icon) : '' }}</span>
                    <label class="custom-file-label" for="iconInput">{{ trans('update.browse') }}</label>
                </div>

                @error('icon')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

                @if(!empty($job) and !empty($job->icon))
                    <a href="{{ url($job->icon) }}" target="_blank" class="text-danger mt-4 font-12">{{ trans('update.preview') }}</a>
                @endif
            </div>
        </div>
    </div>


    {{-- Video --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('public.demo_video') }} ({{ trans('public.optional') }})</h3>

    <div class="js-inputs-with-source row">
        @php
            $selectedVideoSource = (!empty($job) and !empty($job->video_demo_source)) ? $job->video_demo_source : null;
        @endphp

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.video_source') }}</label>
                <select name="video_demo_source" class="js-upload-source-input form-control @error('video_demo_source') is-invalid @enderror">
                    @foreach(getAvailableUploadFileSources() as $source)
                        @php
                            if($loop->first and empty($selectedVideoSource)) {
                                $selectedVideoSource = $source;
                            }
                        @endphp

                        <option value="{{ $source }}" {{ (!empty($job) and $job->video_demo_source == $source) ? 'selected' : '' }}>{{ trans('update.file_source_'.$source) }}</option>
                    @endforeach
                </select>

                @error('video_demo_source')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group js-online-upload {{ (!in_array($selectedVideoSource, \App\Enums\UploadSource::uploadItems)) ? '' : 'd-none' }}">
                <span class="has-translation bg-transparent">
                    <x-iconsax-lin-link-21 class="icons text-gray-400" width="24px" height="24px"/>
                </span>
                <label class="form-group-label">{{ trans('update.path') }}</label>
                <input type="text" name="demo_video_path" class="form-control" value="{{ !empty($job) ? $job->video_demo : old('demo_video_path') }}" placeholder="{{ trans('update.insert_demo_video_link') }}">
            </div>

            <div class="form-group js-local-upload {{ (in_array($selectedVideoSource, \App\Enums\UploadSource::uploadItems)) ? '' : 'd-none' }}">
                <span class="has-translation bg-transparent">
                    <x-iconsax-lin-export class="icons text-gray-400" width="24px" height="24px"/>
                </span>

                <label class="form-group-label">{{ trans('update.upload_video') }}</label>
                <div class="custom-file bg-white">
                    <input type="file" name="demo_video_local" class="custom-file-input" id="demo_video_local" accept="video/*">
                    <span class="custom-file-text text-dark">{{ trans('update.select_a_video') }}</span>
                    <label class="custom-file-label bg-gray-100" for="demo_video_local">{{ trans('update.browse') }}</label>
                </div>
            </div>
        </div>

    </div>

    {{-- Summary --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('public.summary') }}</h3>

    <div class="form-group bg-white-editor">
        <label class="form-group-label">{{ trans('public.summary') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <textarea name="summary" rows="5" class="form-control @error('summary')  is-invalid @enderror" placeholder="{{ trans('update.job_summary_placeholder') }}">{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->summary : old('summary')  !!}</textarea>
        @error('summary')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    {{-- Description --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('public.description') }}</h3>

    <div class="form-group bg-white-editor">
        <label class="form-group-label is-required">{{ trans('public.description') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <textarea name="description" class="main-summernote form-control @error('description')  is-invalid @enderror" data-height="400" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->description : old('description')  !!}</textarea>
        @error('description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    {{-- Important Notes --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('update.important_notes') }}</h3>

    <div class="form-group bg-white-editor">
        <label class="form-group-label">{{ trans('update.important_notes') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <textarea name="important_notes" rows="5" class="form-control @error('important_notes')  is-invalid @enderror" >{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->important_notes : old('important_notes')  !!}</textarea>
        @error('important_notes')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror

        <div class="text-gray-500 mt-8 font-12">{{ trans('update.job_important_notes_input_hint') }}</div>
    </div>

</div>

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
@endpush
