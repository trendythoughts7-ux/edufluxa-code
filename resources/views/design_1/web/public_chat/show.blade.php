@extends("design_1.web.layouts.app")

@push("styles_top")
    <style>
        .public-chat-page {
            max-width: 800px;
            margin: 0 auto;
        }

        .public-chat-page__header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            background: #fff;
            border-radius: 16px 16px 0 0;
            border: 1px solid #e5e7eb;
            border-bottom: none;
        }

        .public-chat-page__header-info h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .public-chat-page__header-info p {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0 0;
        }

        .public-chat-page__messages {
            min-height: 400px;
            max-height: 60vh;
            overflow-y: auto;
            padding: 20px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .public-chat-page__input {
            display: flex;
            gap: 8px;
            padding: 16px 24px;
            background: #fff;
            border-radius: 0 0 16px 16px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .public-chat-page__input input {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 24px;
            padding: 10px 16px;
            font-size: 14px;
            outline: none;
        }

        .public-chat-page__input input:focus {
            border-color: #3b82f6;
        }

        .public-chat-page__input button {
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 24px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .public-chat-page__input button:hover {
            background: #2563eb;
        }

        .pchat-msg {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        .pchat-msg--mine {
            align-self: flex-end;
            background: #3b82f6;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .pchat-msg--theirs {
            align-self: flex-start;
            background: #fff;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 4px;
        }

        .pchat-msg__name {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 2px;
            opacity: 0.8;
        }

        .pchat-msg__time {
            font-size: 10px;
            opacity: 0.6;
            margin-top: 4px;
            text-align: right;
        }

        .pchat-empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            color: #9ca3af;
            font-size: 14px;
            padding: 40px;
        }
    </style>
@endpush

@section("content")
    <div class="container mt-80 pb-120">
        <div class="public-chat-page">

            {{-- Header --}}
            <div class="public-chat-page__header">
                <img src="{{ $instructor->getAvatar(48) }}" alt="{{ $instructor->full_name }}"
                    class="size-48 rounded-circle">
                <div class="public-chat-page__header-info">
                    <h2>{{ trans('update.chat_with_instructor') }} — {{ $instructor->full_name }}</h2>
                    <p>{{ ucfirst($sourceType) }}: {{ $source->title }}</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="public-chat-page__messages" id="chatMessages">
                @if($messages->count() > 0)
                    @foreach($messages as $msg)
                        <div class="pchat-msg {{ $msg->sender_id == auth()->id() ? 'pchat-msg--mine' : 'pchat-msg--theirs' }}"
                            data-id="{{ $msg->id }}">
                            @if($msg->sender_id != auth()->id())
                                <div class="pchat-msg__name">{{ $msg->sender->full_name ?? '' }}</div>
                            @endif
                            <div>{{ $msg->message }}</div>
                            <div class="pchat-msg__time">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="pchat-empty-state" id="emptyState">{{ trans('update.no_messages_yet') }}</div>
                @endif
            </div>

            {{-- Input --}}
            <div class="public-chat-page__input">
                <input type="text" id="messageInput" placeholder="{{ trans('update.type_your_message') }}"
                    autocomplete="off">
                <button id="sendBtn">{{ trans('update.send_message') }}</button>
            </div>

        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        (function () {
            var slug = '{{ $source->slug }}';
            var sourceType = '{{ $sourceType }}';
            var currentUserId = {{ auth()->id() }};
            var chatMessages = document.getElementById('chatMessages');
            var messageInput = document.getElementById('messageInput');
            var sendBtn = document.getElementById('sendBtn');
            var lastMessageId = {{ $messages->count() > 0 ? $messages->last()->id : 0 }};
            var csrfToken = document.querySelector('meta[name="csrf-token"]');

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            function sendMessage() {
                var message = messageInput.value.trim();
                if (!message) return;

                messageInput.value = '';

                fetch('/course/public-chat/' + slug + '/' + sourceType + '/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message: message })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            pollMessages();
                        }
                    })
                    .catch(function (err) { console.error(err); });
            }

            sendBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); sendMessage(); }
            });

            function pollMessages() {
                fetch('/course/public-chat/' + slug + '/' + sourceType + '/messages?after=' + lastMessageId, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.messages && data.messages.length > 0) {
                            var emptyState = document.getElementById('emptyState');
                            if (emptyState) emptyState.remove();

                            data.messages.forEach(function (msg) {
                                if (msg.id > lastMessageId) {
                                    var div = document.createElement('div');
                                    div.className = 'pchat-msg ' + (msg.sender_id == currentUserId ? 'pchat-msg--mine' : 'pchat-msg--theirs');
                                    div.setAttribute('data-id', msg.id);

                                    var html = '';
                                    if (msg.sender_id != currentUserId) {
                                        html += '<div class="pchat-msg__name">' + msg.sender_name + '</div>';
                                    }
                                    html += '<div>' + msg.message + '</div>';
                                    var date = new Date(msg.created_at);
                                    html += '<div class="pchat-msg__time">' + date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0') + '</div>';
                                    div.innerHTML = html;
                                    chatMessages.appendChild(div);
                                    lastMessageId = msg.id;
                                }
                            });
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    })
                    .catch(function () { });
            }

            setInterval(pollMessages, 1500);
        })();
    </script>
@endpush