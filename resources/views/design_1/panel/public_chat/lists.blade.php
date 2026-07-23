<div class="bg-white py-16 rounded-16">
    <div class="px-16 mb-16">
        <h4 class="font-16">{{ trans('panel.conversations') }}</h4>

        <div class="d-flex align-items-center mt-16">
            <div class="conversation-search-box flex-1 form-group d-flex align-items-center mb-0 rounded-12 bg-gray-100 py-4 px-8">
                <input type="text" id="convSearch" class="form-control flex-1 bg-transparent border-0" placeholder="{{ trans('public.search') }}" onkeyup="filterConversations()">

                <button type="button" class="btn-transparent ml-8 p-4">
                    <x-iconsax-lin-search-normal class="icons text-gray-400" width="16px" height="16px"/>
                </button>
            </div>
        </div>
    </div>

    <div class="support-conversation-card" id="conversationList" data-simplebar @if((!empty($isRtl))) data-simplebar-direction="rtl" @endif>

        @if($conversations->count() > 0)
            @foreach($conversations as $conv)
                <a href="/panel/public-chat/{{ $conv->id }}" class="js-conversation-lists">
                    <div class="d-flex align-items-center px-16 py-12 support-conversation-item {{ (!empty($activeConversation) && $activeConversation->id == $conv->id) ? 'active' : '' }}" data-id="{{ $conv->id }}" data-name="{{ $conv->other_party->full_name ?? '' }}">
                        <div class="size-48 rounded-circle mb-16 bg-gray-200">
                            <img src="{{ $conv->other_party ? $conv->other_party->getAvatar() : '' }}"
                                 alt=""
                                 class="js-avatar-img img-cover rounded-circle">
                        </div>

                        <div class="ml-8 flex-1 min-width-0">
                            <h6 class="font-14 text-dark">{{ $conv->other_party->full_name ?? '' }}</h6>
                            @if($conv->latestMessage)
                                <p class="font-12 mt-6 text-gray-500" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ \Illuminate\Support\Str::limit($conv->latestMessage->message, 40) }}</p>
                            @endif

                            <div class="d-flex align-items-center mt-8">
                                <span class="font-12 text-gray-500" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:340px;">{{ truncate($conv->source_title, 60) }}</span>
                                @if($conv->latestMessage)
                                    <span class="size-4 rounded-circle bg-gray-300 mx-8 flex-shrink-0"></span>
                                    <span class="font-12 text-gray-500 flex-shrink-0">{{ dateTimeFormat(strtotime($conv->latestMessage->created_at), 'j M Y | H:i') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($conv->unread > 0)
                            <span class="d-flex-center size-24 rounded-circle bg-primary font-10 text-white font-weight-bold flex-shrink-0">{{ $conv->unread }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        @else
            <div class="text-center py-40 text-gray-500 font-12" id="noConversations">{{ trans('update.no_conversations') }}</div>
        @endif

    </div>
</div>