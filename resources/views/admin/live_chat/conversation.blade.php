@extends('admin.layouts.app')

@push('styles_top')
    <style>
        .chat-container {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .chat-message {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .chat-message.me {
            align-items: flex-end;
        }
        .chat-message .message-info {
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }
        .chat-message .message-bubble {
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 70%;
            position: relative;
        }
        .chat-message.instructor .message-bubble {
            background: #e1f5fe;
            color: #01579b;
            border-bottom-left-radius: 2px;
        }
        .chat-message.student .message-bubble {
            background: #f1f1f1;
            color: #333;
            border-bottom-right-radius: 2px;
            align-self: flex-start;
        }
        .reply-box {
            background: #eee;
            padding: 5px 10px;
            border-left: 3px solid #ccc;
            margin-bottom: 5px;
            font-size: 11px;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/live-chat/public-chat">{{ trans('update.public_chat') }}</a></div>
                <div class="breadcrumb-item">View Conversation</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Conversation Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="chat-container">
                                @foreach($messages as $message)
                                    @php
                                        $isInstructor = ($message->sender_id == $conversation->instructor_id);
                                    @endphp
                                    <div class="chat-message {{ $isInstructor ? 'instructor me' : 'student' }}">
                                        <div class="message-info">
                                            <strong>{{ $message->sender->full_name }}</strong> 
                                            <span class="ml-2">{{ dateTimeFormat($message->created_at->timestamp, 'j M Y | H:i') }}</span>
                                        </div>
                                        <div class="message-bubble">
                                            @if($message->replyTo)
                                                <div class="reply-box">
                                                    <strong>Replying to {{ $message->replyTo->sender->full_name }}:</strong><br>
                                                    {{ \Illuminate\Support\Str::limit($message->replyTo->message, 50) }}
                                                </div>
                                            @endif
                                            {{ $message->message }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>Participants</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="font-weight-bold font-14 mb-0">{{ $conversation->source_title }}</label>
                                <p class="text-gray-500 font-14">{{ ucfirst($conversation->source_type) }}</p>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 mt-16">
                                <img src="{{ $conversation->instructor->getAvatar(50) }}" width="50" height="50" class="rounded-circle mr-3">
                                <div>
                                    <h6 class="mb-0">{{ $conversation->instructor->full_name }}</h6>
                                    <span class="text-small text-gray-500">Instructor</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <img src="{{ $conversation->user->getAvatar(50) }}" width="50" height="50" class="rounded-circle mr-3">
                                <div>
                                    <h6 class="mb-0">{{ $conversation->user->full_name }}</h6>
                                    <span class="text-small text-gray-500">Student</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
