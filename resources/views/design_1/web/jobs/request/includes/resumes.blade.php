<h3 class="mt-24 font-16">{{ trans('update.select_a_resume') }}</h3>
<p class="mt-4 font-12 text-gray-500">{{ trans('update.select_from_existing_resume_files_or_upload_a_new_one') }}</p>

<div class="row form-group mb-0">
    @if(!empty($userResumes) and $userResumes->isNotEmpty())
        @foreach($userResumes as $userResume)
            <div class="col-12 col-md-6 mt-16">
                <div class="custom-input-button with-active-text position-relative">
                    <input type="radio" name="resume" id="resume_{{ $userResume->id }}" value="{{ $userResume->id }}">
                    <label for="resume_{{ $userResume->id }}" class="position-relative justify-content-start p-16 rounded-12">
                        <div class="">
                            <x-iconsax-bul-note-2 class="icons text-primary" width="24px" height="24px"/>
                        </div>
                        <div class="ml-8">
                            <h6 class="font-12">{{ $userResume->title }}</h6>
                            <p class="mt-8 font-12 text-gray-500">{{ trans('update.uploaded_date', ['date' => dateTimeFormat($userResume->created_at, 'j M Y')]) }}</p>
                        </div>
                    </label>
                </div>
            </div>
        @endforeach
    @endif

    <div class="js-ajax-resume col-12 col-md-6 mt-16">
        <div class="custom-input-button with-active-text position-relative">
            <input type="radio" name="resume" id="resume_new_upload" value="new_upload">
            <label for="resume_new_upload" class="position-relative justify-content-start p-16 rounded-12">
                <div class="">
                    <x-iconsax-bul-add-circle class="icons text-primary" width="24px" height="24px"/>
                </div>
                <div class="ml-8">
                    <h6 class="font-12">{{ trans('update.upload_a_new_resume') }}</h6>
                    <p class="mt-8 font-12 text-gray-500">{{ trans('update.upload_your_resume_instantly') }}</p>
                </div>
            </label>
        </div>
    </div>

    <div class="col-12 invalid-feedback mt-8"></div>
</div>

<div class="js-upload-new-resume-card d-none mt-16">
    <h6 class="font-12">{{ trans('update.upload_a_new_resume') }}</h6>

    <div class="row mt-8">
        <div class="col-12 col-md-6 mt-16">
            <div class="form-group mb-0">
                <label class="form-group-label">{{ trans('public.title') }}</label>
                <input type="text" name="new_resume_title" class="js-ajax-new_resume_title form-control" value="">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="col-12 col-md-6 mt-16">
            <div class="form-group mb-0">
                <div class="custom-file bg-white">
                    <input type="file" name="new_resume_attach" class="js-ajax-upload-file-input js-ajax-new_resume_attach custom-file-input" data-upload-name="new_resume_attach" id="attachResumeFile">
                    <span class="custom-file-text">{{ trans('update.select_resume_file') }}</span>
                    <label class="custom-file-label" for="attachResumeFile">{{ trans('update.browse') }}</label>
                </div>

                <div class="invalid-feedback d-block"></div>
            </div>
        </div>
    </div>
</div>
