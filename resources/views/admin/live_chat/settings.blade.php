@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item">{{ trans('update.live_chat') }}</div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl() }}/live-chat/settings" method="post">
                                {{ csrf_field() }}

                                {{-- Chat Room Settings --}}
                                <div class="mb-5">
                                    <h5>{{ trans('update.chat_room_settings') }}</h5>

                                    <div class="form-group custom-switches-stacked mt-3">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[enable_chat_room]" value="0">
                                            <input type="checkbox" name="value[enable_chat_room]" id="enableChatRoomSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['enable_chat_room']) and $itemValue['enable_chat_room']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="enableChatRoomSwitch">{{ trans('update.enable_chat_room') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.chat_room_settings_hint') }}</div>
                                    </div>

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[allow_chat_edit_messages]" value="0">
                                            <input type="checkbox" name="value[allow_chat_edit_messages]" id="allowChatEditSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['allow_chat_edit_messages']) and $itemValue['allow_chat_edit_messages']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="allowChatEditSwitch">{{ trans('update.allow_chat_edit_messages') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.allow_chat_edit_messages_hint') }}</div>
                                    </div>

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[allow_chat_delete_messages]" value="0">
                                            <input type="checkbox" name="value[allow_chat_delete_messages]" id="allowChatDeleteSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['allow_chat_delete_messages']) and $itemValue['allow_chat_delete_messages']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="allowChatDeleteSwitch">{{ trans('update.allow_chat_delete_messages') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.allow_chat_delete_messages_hint') }}</div>
                                    </div>

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[show_instructor_online_learning_page]" value="0">
                                            <input type="checkbox" name="value[show_instructor_online_learning_page]" id="showInstructorOnlineLearningSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['show_instructor_online_learning_page']) and $itemValue['show_instructor_online_learning_page']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="showInstructorOnlineLearningSwitch">{{ trans('update.show_instructor_online_learning_page') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.show_instructor_online_learning_page_hint') }}</div>
                                    </div>
                                </div>

                                {{-- Public Chat Settings --}}
                                <div class="mb-5">
                                    <h5 class="mb-3">{{ trans('update.public_chat_settings') }}</h5>

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[enable_public_chat_questions]" value="0">
                                            <input type="checkbox" name="value[enable_public_chat_questions]" id="enablePublicChatSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['enable_public_chat_questions']) and $itemValue['enable_public_chat_questions']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="enablePublicChatSwitch">{{ trans('update.enable_public_chat_questions') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.enable_public_chat_questions_hint') }}</div>
                                    </div>

                                    <div class="form-group custom-switches-stacked">
                                        <label class="custom-switch pl-0">
                                            <input type="hidden" name="value[show_instructor_online_course_page]" value="0">
                                            <input type="checkbox" name="value[show_instructor_online_course_page]" id="showInstructorOnlineCourseSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['show_instructor_online_course_page']) and $itemValue['show_instructor_online_course_page']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                            <span class="custom-switch-indicator"></span>
                                            <label class="custom-switch-description mb-0 cursor-pointer" for="showInstructorOnlineCourseSwitch">{{ trans('update.show_instructor_online_course_page') }}</label>
                                        </label>
                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.show_instructor_online_course_page_hint') }}</div>
                                    </div>

                                    <div class="form-group mt-3" id="publicChatCtaMethodWrapper">
                                        <label class="input-label">{{ trans('update.public_chat_cta_display_method') }}</label>
                                        <select name="value[public_chat_cta_display_method]" id="publicChatCtaMethodSelect" class="form-control">
                                            <option value="cta_card" {{ (!empty($itemValue) && !empty($itemValue['public_chat_cta_display_method']) && $itemValue['public_chat_cta_display_method'] == 'cta_card') ? 'selected' : '' }}>{{ trans('update.public_chat_cta_card') }}</option>
                                            <option value="float_cta" {{ (!empty($itemValue) && !empty($itemValue['public_chat_cta_display_method']) && $itemValue['public_chat_cta_display_method'] == 'float_cta') ? 'selected' : '' }}>{{ trans('update.public_chat_float_cta') }}</option>
                                            <option value="both" {{ (!empty($itemValue) && !empty($itemValue['public_chat_cta_display_method']) && $itemValue['public_chat_cta_display_method'] == 'both') ? 'selected' : '' }}>{{ trans('update.public_chat_cta_both') }}</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-3" id="publicChatFloatStyleWrapper" style="{{ (!empty($itemValue) && !empty($itemValue['public_chat_cta_display_method']) && in_array($itemValue['public_chat_cta_display_method'], ['float_cta', 'both'])) ? '' : 'display:none;' }}">
                                        <label class="input-label">{{ trans('update.public_chat_float_cta_style') }}</label>
                                        <select name="value[public_chat_float_cta_style]" class="form-control">
                                            <option value="style_1" {{ (!empty($itemValue) && !empty($itemValue['public_chat_float_cta_style']) && $itemValue['public_chat_float_cta_style'] == 'style_1') ? 'selected' : '' }}>{{ trans('update.public_chat_float_style_1') }}</option>
                                            <option value="style_2" {{ (!empty($itemValue) && !empty($itemValue['public_chat_float_cta_style']) && $itemValue['public_chat_float_cta_style'] == 'style_2') ? 'selected' : '' }}>{{ trans('update.public_chat_float_style_2') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var methodSelect = document.getElementById('publicChatCtaMethodSelect');
            var floatStyleWrapper = document.getElementById('publicChatFloatStyleWrapper');

            function toggleFloatStyle() {
                var val = methodSelect.value;
                if (val === 'float_cta' || val === 'both') {
                    floatStyleWrapper.style.display = '';
                } else {
                    floatStyleWrapper.style.display = 'none';
                }
            }

            if (methodSelect) {
                methodSelect.addEventListener('change', toggleFloatStyle);
            }
        });
    </script>
@endsection
