<h2 class="section-title after-line">{{ trans('public.basic_information') }}</h2>

<div class="row">
    <div class="col-12 col-md-5">
        @if(!empty(getGeneralSettings('content_translate')))
            <div class="form-group">
                <label class="input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class="form-control {{ !empty($job) ? 'js-edit-content-locale' : '' }}">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
                @error('locale')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->title : old('title') }}" class="form-control @error('title')  is-invalid @enderror" placeholder=""/>
            @error('title')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('admin/main.url') }}</label>
            <input type="text" name="slug" value="{{ !empty($job) ? $job->slug : old('slug') }}" class="form-control @error('slug')  is-invalid @enderror" placeholder=""/>
            <div class="text-gray-500 text-small mt-1">{{ trans('update.job_url_hint') }}</div>
            @error('slug')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group mt-15 ">
            <label class="input-label d-block">{{ trans('update.select_a_creator') }}</label>

            <select name="creator_id" data-search-option="except_user" class="form-control search-user-select22" data-placeholder="{{ trans('update.select_a_creator') }}">
                @if(!empty($job))
                    <option value="{{ $job->creator->id }}" selected>{{ $job->creator->full_name }}</option>
                @endif
            </select>

            @error('creator_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.thumbnail_image') }}</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="thumbnail" data-preview="holder">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="thumbnail" id="thumbnail" value="{{ !empty($job) ? $job->thumbnail : old('thumbnail') }}" class="form-control @error('thumbnail')  is-invalid @enderror"/>
                <div class="input-group-append">
                    <button type="button" class="input-group-text admin-file-view" data-input="thumbnail">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                @error('thumbnail')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>


        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.cover_image') }}</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="cover_image" data-preview="holder">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="cover_image" id="cover_image" value="{{ !empty($job) ? $job->cover_image : old('cover_image') }}" class="form-control @error('cover_image')  is-invalid @enderror"/>
                <div class="input-group-append">
                    <button type="button" class="input-group-text admin-file-view" data-input="cover_image">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                @error('cover_image')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('update.icon') }} ({{ trans('public.optional') }})</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="icon" data-preview="holder">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="icon" id="icon" value="{{ !empty($job) ? $job->icon : old('icon') }}" class="form-control @error('icon')  is-invalid @enderror"/>
                <div class="input-group-append">
                    <button type="button" class="input-group-text admin-file-view" data-input="icon">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                @error('icon')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group mt-25">
            <label class="input-label">{{ trans('public.demo_video') }} ({{ trans('public.optional') }})</label>

            @php
                $selectedVideoSource = (!empty($job) and !empty($job->video_demo_source)) ? $job->video_demo_source : null;
            @endphp

            <div class="">
                <label class="input-label font-12">{{ trans('public.source') }}</label>
                <select name="video_demo_source" class="js-video-demo-source form-control">
                    @foreach(getAvailableUploadFileSources() as $source)
                        @php
                            if($loop->first and empty($selectedVideoSource)) {
                                $selectedVideoSource = $source;
                            }
                        @endphp

                        <option value="{{ $source }}" {{ (!empty($job) and $job->video_demo_source == $source) ? 'selected' : '' }}>{{ trans('update.file_source_'.$source) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="js-video-demo-other-inputs form-group mt-0 {{ (!in_array($selectedVideoSource, \App\Enums\UploadSource::uploadItems)) ? '' : 'd-none' }}">
            <label class="input-label font-12">{{ trans('update.path') }}</label>
            <div class="input-group js-video-demo-path-input">
                <div class="input-group-prepend">
                    <button type="button" class="js-video-demo-path-upload input-group-text admin-file-manager {{ (empty($job) or empty($job->video_demo_source) or $job->video_demo_source == 'upload') ? '' : 'd-none' }}" data-input="demo_video" data-preview="holder">
                        <i class="fa fa-upload"></i>
                    </button>

                    <button type="button" class="js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left  {{ (empty($job) or empty($job->video_demo_source) or $job->video_demo_source == 'upload') ? 'd-none' : '' }}">
                        <i class="fa fa-link"></i>
                    </button>
                </div>
                <input type="text" name="video_demo" id="demo_video" value="{{ !empty($job) ? $job->video_demo : old('video_demo') }}" class="form-control @error('video_demo')  is-invalid @enderror"/>
                @error('video_demo')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="form-group js-video-demo-file-input {{ (in_array($selectedVideoSource, \App\Enums\UploadSource::uploadItems)) ? '' : 'd-none' }}">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
                <div class="custom-file js-ajax-s3_file">
                    <input type="file" name="video_demo_file" class="custom-file-input cursor-pointer" id="video_demo_file" accept="video/*">
                    <label class="custom-file-label cursor-pointer" for="video_demo_file">{{ trans('update.choose_file') }}</label>
                </div>

                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.seo_description') }}</label>
            <input type="text" name="seo_description" value="{{ (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->seo_description : old('seo_description') }}" class="form-control @error('seo_description')  is-invalid @enderror"/>
            <div class="text-gray-500 text-small mt-1">{{ trans('admin/main.seo_description_hint') }}</div>
            @error('seo_description')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.summary') }}</label>
            <textarea name="summary" rows="5" class="form-control @error('summary')  is-invalid @enderror" placeholder="{{ trans('update.job_summary_placeholder') }}">{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->summary : old('summary')  !!}</textarea>
            @error('summary')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>

        {{-- Important Notes --}}
        <div class="form-group ">
            <label class="input-label">{{ trans('update.important_notes') }}</label>
            <textarea name="important_notes" rows="5" class="form-control @error('important_notes')  is-invalid @enderror">{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->important_notes : old('important_notes')  !!}</textarea>
            @error('important_notes')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror

            <div class="text-gray-500 mt-8 font-12">{{ trans('update.job_important_notes_input_hint') }}</div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group mt-15">
            <label class="input-label">{{ trans('public.description') }}</label>
            <textarea id="summernote" name="description" class="form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! (!empty($job) and !empty($job->translate($locale))) ? $job->translate($locale)->description : old('description')  !!}</textarea>
            @error('description')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
    </div>
</div>
