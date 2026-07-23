<div class="js-resume-form" data-action="/panel/setting/resumes/{{ !empty($resume) ? $resume->id.'/update' : 'store' }}">
    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
    @if(!empty($user_id))
        <input type="hidden" name="user_id" value="{{ $user_id }}">
    @endif

    <div class="d-flex-center flex-column text-center">
        <img src="/assets/design_1/img/panel/settings/resume_modal.svg" alt="resume_modal" class="img-fluid" height="160px">

        <h4 class="mt-12 font-14">{{ trans('update.define_your_resume') }}</h4>
        <p class="mt-8 font-12 text-gray-500">{{ trans('update.define_your_resume_hint_in_modal') }}</p>
    </div>

    <div class="form-group mt-24">
        <label class="form-group-label">{{ trans('public.title') }}</label>
        <input type="text" name="title" class="js-ajax-title form-control" value="{{ !empty($resume) ? $resume->title : '' }}">
        <div class="invalid-feedback"></div>
    </div>

    <div class="form-group mb-0">
        <div class="custom-file bg-white">
            <input type="file" name="attach" class="js-ajax-upload-file-input js-ajax-attach custom-file-input" data-upload-name="attach" id="attachFile">
            <span class="custom-file-text">{{ (!empty($resume) and !empty($resume->attach)) ? getFileNameByPath($resume->attach) : trans('update.select_resume_file') }}</span>
            <label class="custom-file-label" for="attachFile">{{ trans('update.browse') }}</label>
        </div>

        <div class="invalid-feedback d-block"></div>
    </div>

</div>
