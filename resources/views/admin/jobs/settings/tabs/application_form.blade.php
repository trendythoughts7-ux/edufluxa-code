<form action="{{ getAdminPanelUrl('/jobs/settings') }}" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="page" value="general">
    <input type="hidden" name="name" value="{{ \App\Models\Setting::$jobsApplicationFormSettingsName }}">

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="p-16 rounded-16 border-gray-200 ">
                @if(!empty(getGeneralSettings('content_translate')))
                    <div class="form-group">
                        <label class="input-label">{{ trans('auth.language') }}</label>
                        <select name="locale" class="form-control js-edit-content-locale">
                            @foreach($userLanguages as $lang => $language)
                                <option value="{{ $lang }}" @if(mb_strtolower($selectedLocale) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
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


                <div class="form-group">
                    <label>{{ trans('public.title') }}</label>
                    <input type="text" name="value[title]" class="form-control " value="{{ (!empty($applicationFormSettingValues) and !empty($applicationFormSettingValues['title'])) ? $applicationFormSettingValues['title'] : old('title') }}">
                </div>

                <div class="form-group">
                    <label>{{ trans('admin/main.description') }}</label>
                    <textarea type="text" name="value[description]" class="form-control " rows="5">{{ (!empty($applicationFormSettingValues) and !empty($applicationFormSettingValues['description'])) ? $applicationFormSettingValues['description'] : old('description') }}</textarea>
                </div>


                <div class="form-group">
                    <label class="input-label">{{ trans('public.image') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="image" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[image]" id="image" value="{{ (!empty($applicationFormSettingValues) and !empty($applicationFormSettingValues['image'])) ? $applicationFormSettingValues['image'] : old('image') }}" class="form-control"/>

                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="image">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </div>


    </div>


    <button type="submit" class="btn btn-primary mt-16">{{ trans('admin/main.submit') }}</button>
</form>

