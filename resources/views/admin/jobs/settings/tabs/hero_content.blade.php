<form action="{{ getAdminPanelUrl('/jobs/settings') }}" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="page" value="general">
    <input type="hidden" name="name" value="{{ \App\Models\Setting::$jobsHeroContentSettingsName }}">

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
                    <label class="input-label">{{ trans('update.light_mode_background') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="light_mode_background" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[light_mode_background]" id="light_mode_background" value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['light_mode_background'])) ? $heroContentSettingValues['light_mode_background'] : old('light_mode_background') }}" class="form-control"/>

                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="light_mode_background">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="input-label">{{ trans('update.dark_mode_background') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="dark_mode_background" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>

                        <input type="text" name="value[dark_mode_background]" id="dark_mode_background" value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['dark_mode_background'])) ? $heroContentSettingValues['dark_mode_background'] : old('dark_mode_background') }}" class="form-control"/>

                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="dark_mode_background">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ trans('update.title_line_1') }}</label>
                    <input type="text" name="value[title_line_1]" class="form-control " value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['title_line_1'])) ? $heroContentSettingValues['title_line_1'] : old('title_line_1') }}">
                </div>

                <div class="form-group">
                    <label>{{ trans('update.title_line_2') }}</label>
                    <input type="text" name="value[title_line_2]" class="form-control " value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['title_line_2'])) ? $heroContentSettingValues['title_line_2'] : old('title_line_2') }}">
                </div>

                <div class="form-group">
                    <label>{{ trans('update.subtitle') }}</label>
                    <input type="text" name="value[subtitle]" class="form-control " value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['subtitle'])) ? $heroContentSettingValues['subtitle'] : old('subtitle') }}">
                </div>

                <div class="form-group">
                    <label>{{ trans('update.badge_title') }}</label>
                    <input type="text" name="value[badge_title]" class="form-control " value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['badge_title'])) ? $heroContentSettingValues['badge_title'] : old('badge_title') }}">
                </div>

                <div class="form-group">
                    <label class="input-label">{{ trans('update.floating_image') }} #1</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="floating_image_1" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[floating_image_1]" id="floating_image_1" value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['floating_image_1'])) ? $heroContentSettingValues['floating_image_1'] : old('floating_image_1') }}" class="form-control"/>

                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="floating_image_1">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="input-label">{{ trans('update.floating_image') }} #2</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="floating_image_2" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[floating_image_2]" id="floating_image_2" value="{{ (!empty($heroContentSettingValues) and !empty($heroContentSettingValues['floating_image_2'])) ? $heroContentSettingValues['floating_image_2'] : old('floating_image_2') }}" class="form-control"/>

                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="floating_image_2">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            {{-- Information  --}}
            <div class="p-16 rounded-16 border-gray-200 ">
                <x-landingBuilder-addable-accordions
                    title="{{ trans('update.special_items') }}"
                    addText="{{ trans('update.add_item') }}"
                    hint=""
                    className="mb-0"
                    mainRow="js-special-item-main-card"
                >
                    @if(!empty($heroContentSettingValues) and !empty($heroContentSettingValues['special_items']) and count($heroContentSettingValues['special_items']))
                        @foreach($heroContentSettingValues['special_items'] as $sKey => $itemData)
                            @if($sKey != 'record')
                                <x-landingBuilder-accordion
                                    title="{{ (!empty($itemData) and !empty($itemData['title'])) ? $itemData['title'] : trans('update.new_item') }}"
                                    id="special_{{ $sKey }}"
                                    className=""
                                    show=""
                                >
                                    @include('admin.jobs.settings.tabs.special_items',['itemKey' => $sKey, 'specialData' => $itemData])
                                </x-landingBuilder-accordion>
                            @endif
                        @endforeach
                    @endif
                </x-landingBuilder-addable-accordions>
            </div>

        </div>

    </div>


    <button type="submit" class="btn btn-primary mt-16">{{ trans('admin/main.submit') }}</button>
</form>


<div class="js-special-item-main-card d-none">
    <x-landingBuilder-accordion
        title="{{ trans('update.new_item') }}"
        id="record"
        className=""
        show="true"
    >
        @include('admin.jobs.settings.tabs.special_items')
    </x-landingBuilder-accordion>
</div>

@push('scripts_bottom')
    <script src="/assets/design_1/landing_builder/js/components.min.js"></script>
@endpush
