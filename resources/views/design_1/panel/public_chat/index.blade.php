@extends('design_1.panel.layouts.panel')
@push('styles_top')
    <link rel="stylesheet" href="{{ getDesign1StylePath("public_chat") }}">
@endpush
@section('content')
    @if(!empty($conversations) and !$conversations->isEmpty())
        <div class="row">
            <div class="col-12 col-lg-4">
                @include('design_1.panel.public_chat.lists')
            </div>
            <div class="col-12 col-lg-8 mt-20 mt-lg-0">
                @include('design_1.panel.public_chat.messages')
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'support_tickets.svg',
            'title' => trans('update.no_conversations'),
            'hint' => nl2br(trans('update.public_chat_no_conversations_hint')),
            'extraClass' => 'mt-0',
        ])
    @endif
@endsection
@push('scripts_bottom')
    <script>
        window.publicChatConfig = {
            activeConversationId: {{ !empty($activeConversation) ? $activeConversation->id : 0 }},
            currentUserId: {{ auth()->id() }},
            lastMessageId: {{ (!empty($activeConversation) && $messages->count() > 0) ? $messages->last()->id : 0 }},
            translations: {
                noConversations: '{{ trans("update.no_conversations") }}'
            }
        };
    </script>
    <script src="{{ getDesign1ScriptPath("public_chat") }}"></script>
@endpush
