@extends('design_1.web.layouts.app', ['appFooter' => false, 'appHeader' => false])

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("learning_page") }}">
@endpush

@section('content')
    <div class="learning-page d-flex">
        <div class="learning-page__main">
            {{-- Top Header --}}
            <div
                class="learning-page__top-header d-flex align-items-center justify-content-between w-100 bg-white py-16 pl-32 pr-24">
                <div>
                    <a href="{{ $course->getUrl() }}" class="font-16 font-weight-bold text-dark d-flex mb-4">
                        <span>{{ trans('update.chat_room') }}</span>
                    </a>
                    <div class="d-flex align-items-center">
                        <a href="{{ $course->getUrl() }}" class="text-gray-500 font-12">{{ $course->title }}</a>
                        <span class="mx-8 text-gray-300">/</span>
                        <span class="text-dark font-12">{{ trans('update.chat_room') }}</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-16">
                    {{-- Instructor Tools Dropdown --}}
                    @if($authUser->id == $course->teacher_id || $authUser->id == $course->creator_id || $authUser->isAdmin())
                        <div class="chat-tools-dropdown" id="chatToolsDropdown">
                            <button type="button" class="chat-tools-dropdown__btn" id="chatToolsBtn">
                                <x-iconsax-lin-setting-2 width="18px" height="18px" />
                                <span>{{ trans('update.chat_tools') }}</span>
                                <x-iconsax-lin-arrow-down width="14px" height="14px" />
                            </button>
                            <div class="chat-tools-dropdown__menu">
                                <button type="button" class="chat-tools-dropdown__item" id="toggleChatBtn">
                                    <x-iconsax-lin-lock width="18px" height="18px" />
                                    <span id="toggleChatText">{{ trans('update.close_chat_room') }}</span>
                                </button>
                                <button type="button" class="chat-tools-dropdown__item chat-tools-dropdown__item--danger"
                                    id="clearMessagesBtn">
                                    <x-iconsax-lin-trash width="18px" height="18px" />
                                    <span>{{ trans('update.clear_messages') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <a href="{{ $course->getLearningPageUrl() }}" class="btn btn-sm btn-primary rounded-pill">
                        <x-iconsax-lin-arrow-left width="16px" height="16px" />
                        <span class="ml-4">{{ trans('update.learning_page') }}</span>
                    </a>

                    <div class="js-toggle-show-learning-page-sidebar-drawer d-flex d-lg-none ml-16 cursor-pointer">
                        <x-iconsax-lin-menu class="icons text-gray-500" width="24px" height="24px" />
                    </div>
                </div>
            </div>

            {{-- Chat Messages Container --}}
            <div class="chat-room-main-content bg-gray-100" data-simplebar style="height: calc(100vh - 80px);"
                @if((!empty($isRtl))) data-simplebar-direction="rtl" @endif>
                <div class="chat-room-messages" id="chatMessages">
                    <div class="chat-empty-state" id="chatEmptyState">
                        <div class="chat-empty-state__icon">
                            <x-iconsax-lin-messages-3 class="text-primary" width="32px" height="32px" />
                        </div>
                        <h4>{{ trans('update.chat_room_empty_title') }}</h4>
                        <p>{{ trans('update.chat_room_empty_description') }}</p>
                    </div>
                    <div id="chatMessagesContainer"></div>
                </div>
            </div>

            {{-- Chat Input Footer --}}
            <div class="chat-input-footer" id="chatInputFooter">
                <div class="chat-closed-notice" id="chatClosedNotice" style="display: none;">
                    <x-iconsax-lin-lock width="16px" height="16px" />
                    <span>{{ trans('update.chat_room_closed_notice') }}</span>
                </div>

                <div class="chat-reply-preview" id="replyPreview">
                    <span id="replyPreviewText"></span>
                    <button type="button" class="chat-reply-preview__close" id="cancelReply">
                        <x-iconsax-lin-close-circle width="16px" height="16px" />
                    </button>
                </div>

                {{-- File Preview --}}
                <div class="chat-file-preview" id="filePreview" style="display: none;">
                    <div class="chat-file-preview__info">
                        <x-iconsax-lin-document width="20px" height="20px" />
                        <span id="filePreviewName"></span>
                        <span id="filePreviewSize" class="text-gray-500"></span>
                    </div>
                    <button type="button" class="chat-file-preview__remove" id="removeFile">
                        <x-iconsax-lin-close-circle width="16px" height="16px" />
                    </button>
                </div>

                {{-- Voice Recording Indicator --}}
                <div class="chat-voice-recording" id="voiceRecording" style="display: none;">
                    <div class="chat-voice-recording__indicator">
                        <span class="chat-voice-recording__dot"></span>
                        <span id="recordingTime">00:00</span>
                    </div>
                    <div class="chat-voice-recording__buttons">
                        <button type="button" class="chat-voice-recording__cancel" id="cancelRecording">
                            <x-iconsax-lin-close-circle width="20px" height="20px" />
                        </button>
                        <button type="button" class="chat-voice-recording__send" id="sendRecording">
                            <x-iconsax-lin-send-2 width="20px" height="20px" />
                        </button>
                    </div>
                </div>

                <div class="chat-input-wrapper" id="chatInputWrapper">
                    {{-- Hidden File Input --}}
                    <input type="file" id="fileInput" style="display: none;"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.avi,.webm">

                    <input type="text" id="messageInput" placeholder="{{ trans('update.type_your_message') }}"
                        autocomplete="off">

                    {{-- Attachment Button --}}
                    <button type="button" class="chat-action-btn" id="attachBtn" title="{{ trans('update.attach_file') }}">
                        <x-iconsax-lin-attach-circle width="22px" height="22px" />
                    </button>

                    {{-- Voice Record Button --}}
                    <button type="button" class="chat-action-btn" id="voiceBtn" title="{{ trans('update.record_voice') }}">
                        <x-iconsax-lin-microphone-2 width="22px" height="22px" />
                    </button>

                    <button type="button" class="chat-send-btn" id="sendMessageBtn">
                        <span>{{ trans('update.send') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Video Modal --}}
        <div class="chat-video-modal" id="videoModal" style="display: none;">
            <div class="chat-video-modal__overlay" id="videoModalClose"></div>
            <div class="chat-video-modal__content">
                <button type="button" class="chat-video-modal__close" id="videoModalCloseBtn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <video id="videoModalPlayer" controls playsinline></video>
            </div>
        </div>

        {{-- Sidebar - Online Users --}}
        <div id="learningPageSidebar" class="learning-page__sidebar" style="border-left: 1px solid #e5e7eb;">
            <div class="learning-page__sidebar-header px-16 border-bottom-gray-200">
                <div class="js-toggle-show-learning-page-sidebar-drawer cursor-pointer">
                    <x-iconsax-lin-add class="icons close-icon text-gray-500" width="28px" height="28px" />
                </div>
            </div>

            <div class="learning-page__sidebar-content py-12" data-simplebar @if((!empty($isRtl)))
            data-simplebar-direction="rtl" @endif>
                <div class="px-12">
                    {{-- User Info Card --}}
                    <div class="card-with-mask position-relative mb-24">
                        <div class="mask-8-white bg-primary-20"></div>
                        <div class="position-relative z-index-2 bg-primary p-8 pb-12 rounded-16">
                            <div class="d-flex align-items-center justify-content-between bg-white rounded-12">
                                <div class="d-flex align-items-center p-12">
                                    <div class="size-40 rounded-circle">
                                        <img src="{{ $authUser->getAvatar(40) }}" alt="{{ $authUser->full_name }}"
                                            class="img-cover rounded-circle">
                                    </div>
                                    <div class="ml-8">
                                        <h4 class="font-14 text-dark">{{ $authUser->full_name }}</h4>
                                        <p class="mt-2 font-12 text-gray-500">{{ $authUser->role->caption ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Online Users Section --}}
                    <div class="mt-16">
                        <h4 class="font-14 font-weight-bold text-dark mb-12 d-flex align-items-center">
                            <x-iconsax-lin-people class="icons text-primary mr-4" width="20px" height="20px" />
                            {{ trans('update.online_users') }}
                            <span class="text-gray-400 font-weight-normal ml-4" id="onlineUsersCount"></span>
                        </h4>

                        <div id="onlineUsersList">
                            <div class="text-center py-16 text-gray-500 font-12">
                                {{ trans('update.loading') }}...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script type="text/javascript" src="/assets/default/vendors/simplebar/simplebar.min.js"></script>
    <script src="{{ getDesign1ScriptPath("learning_page") }}"></script>
    <script>
        window.chatRoomConfig = {
            courseSlug: '{{ $course->slug }}',
            currentUserId: {{ auth()->id() }},
            isInstructor: {{ ($authUser->id == $course->teacher_id || $authUser->id == $course->creator_id || $authUser->isAdmin()) ? 'true' : 'false' }},
            translations: {
                reply: '{{ trans("update.reply") }}',
                edit: '{{ trans("update.edit") }}',
                delete: '{{ trans("update.delete") }}',
                save: '{{ trans("update.save") }}',
                cancel: '{{ trans("update.cancel") }}',
                edited: '{{ trans("update.edited") }}',
                confirmDeleteMessage: '{{ trans("update.confirm_delete_message") }}',
                downloadFile: '{{ trans("update.download_file") }}',
                openChatRoom: '{{ trans("update.open_chat_room") }}',
                closeChatRoom: '{{ trans("update.close_chat_room") }}',
                noOnlineUsers: '{{ trans("update.no_online_users") }}',
                errorSendingMessage: '{{ trans("update.error_sending_message") }}',
                confirmClearMessages: '{{ trans("update.confirm_clear_messages") }}',
                fileTooLarge: '{{ trans("update.file_too_large") }}',
                videoTooLarge: '{{ trans("update.video_too_large") }}',
                microphoneAccessDenied: '{{ trans("update.microphone_access_denied") }}',
                typing: '{{ trans("update.typing") }}'
            }
        };
    </script>
    <script src="{{ getDesign1ScriptPath("chat_room") }}"></script>
@endpush