<div class="bg-white p-16 rounded-24">

    @if(!empty($activeConversation))
        <div class="d-flex flex-column">
            <div class="bg-gray-100 p-16 rounded-12">

                <div class="d-flex align-items-center">
                    <div class="size-40 rounded-circle bg-gray-300">
                        <img src="{{ $activeConversation->other_party ? $activeConversation->other_party->getAvatar(40) : '' }}" alt="" class="img-cover rounded-circle">
                    </div>
                    <div class="ml-8">
                        <h6 class="font-14 font-weight-bold text-gray-500">{{ $activeConversation->other_party->full_name ?? '' }}</h6>

                        <div class="d-flex align-items-center mt-8">
                            <span class="font-12 text-gray-500">{{ ucfirst($activeConversation->source_type) }}: {{ $activeConversation->source_title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="conversationsCard" class="support-conversation-messages pchat-messages-area pt-16" data-simplebar @if((!empty($isRtl))) data-simplebar-direction="rtl" @endif>
                @if($messages->count() > 0)
                    @foreach($messages as $msg)
                        <div class="pchat-msg-wrap {{ $msg->sender_id == auth()->id() ? 'pchat-msg-wrap--mine' : 'pchat-msg-wrap--theirs' }}" data-id="{{ $msg->id }}" data-sender="{{ $msg->sender->full_name ?? '' }}" data-text="{{ \Illuminate\Support\Str::limit($msg->message, 80) }}">
                            <div class="pchat-msg__header">
                                <span class="pchat-msg__sender">{{ $msg->sender->full_name ?? '' }}</span>
                                <span class="pchat-msg__time">{{ dateTimeFormat(strtotime($msg->created_at), 'H:i') }}</span>
                            </div>
                            <div class="pchat-msg__bubble">
                                @if($msg->replyTo)
                                    <div class="pchat-msg__quoted">
                                        <div class="pchat-msg__quoted-name">{{ $msg->replyTo->sender->full_name ?? '' }}</div>
                                        <div class="pchat-msg__quoted-text">{{ \Illuminate\Support\Str::limit($msg->replyTo->message, 80) }}</div>
                                    </div>
                                @endif
                                <div>{{ $msg->message }}</div>
                            </div>
                            <div class="pchat-msg__actions">
                                <button class="pchat-msg__reply-btn" onclick="setReply(this)">Reply</button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="public-chat-empty" id="emptyState">{{ trans('update.no_messages_yet') }}</div>
                @endif
            </div>

            <div class="reply-bar" id="replyBar">
                <div class="reply-bar__info">
                    <div class="reply-bar__name" id="replyBarName"></div>
                    <div class="reply-bar__text" id="replyBarText"></div>
                </div>
                <button class="reply-bar__close" onclick="cancelReply()">&times;</button>
            </div>

            <div class="d-flex align-items-center pt-28 border-top-gray-100">
                <input type="hidden" id="replyToId" value="">
                <div class="form-group mb-0 flex-1">
                    <label class="form-group-label">{{ trans('update.type_your_message') }}</label>
                    <input type="text" id="replyInput" class="form-control" autocomplete="off">
                </div>

                <button type="button" id="sendReplyBtn" class="btn btn-primary size-48 rounded-circle ml-20 p-0">
                    <x-iconsax-lin-send-2 class="icons text-white" width="24px" height="24px"/>
                </button>
            </div>

        </div>
    @else

        @include('design_1.panel.includes.no-result',[
            'file_name' => 'support_tickets.svg',
            'title' => trans('update.select_conversation'),
            'hint' => nl2br(trans('update.select_a_conversation_to_start_chatting')),
            'extraClass' => 'mt-0',
        ])
    @endif

</div>