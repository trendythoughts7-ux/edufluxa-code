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
            <section class="card mt-32">
                <div class="card-body pb-4">
                    <form class="mb-0">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.search')}} (Source Title)</label>
                                    <input type="text" name="source_title" value="{{ request()->get('source_title') }}" class="form-control text-center">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.start_date')}}</label>
                                    <input type="date" name="from" value="{{ request()->get('from') }}" class="text-center form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.end_date')}}</label>
                                    <input type="date" name="to" value="{{ request()->get('to') }}" class="text-center form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{trans('admin/main.type')}}</label>
                                <select name="type" class="form-control">
                                    <option value="">{{trans('admin/main.all_types')}}</option>
                                    <option value="course" {{ request()->get('type') == 'course' ? 'selected' : '' }}>{{trans('update.course')}}</option>
                                    <option value="bundle" {{ request()->get('type') == 'bundle' ? 'selected' : '' }}>{{trans('update.bundle')}}</option>
                                    <option value="event" {{ request()->get('type') == 'event' ? 'selected' : '' }}>{{trans('update.event')}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.instructor')}}</label>
                                    <select name="teacher_ids[]" multiple="multiple" data-search-option="just_teacher_role" class="form-control search-user-select2" data-placeholder="Search teachers">
                                        @if(!empty($teachers) and $teachers->count() > 0)
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" selected>{{ $teacher->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{trans('admin/main.student')}}</label>
                                    <select name="student_ids[]" multiple="multiple" data-search-option="just_student_role" class="form-control search-user-select2" data-placeholder="Search students">
                                        @if(!empty($students) and $students->count() > 0)
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" selected>{{ $student->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">{{trans('admin/main.show_results')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table custom-table font-14">
                                    <tr>
                                        <th class="text-left">{{trans('admin/main.instructor')}}</th>
                                        <th class="text-left">{{trans('admin/main.student')}}</th>
                                        <th class="text-left">Source</th>
                                        <th class="text-left">Latest Message</th>
                                        <th class="text-center">{{trans('admin/main.last_update')}}</th>
                                        <th class="text-center">{{trans('admin/main.actions')}}</th>
                                    </tr>
                                    @foreach($conversations as $conversation)
                                        <tr>
                                            <td class="text-left">
                                                @if(!empty($conversation->instructor))
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $conversation->instructor->getAvatar(40) }}" class="avatar mr-2">
                                                        <a class="text-dark ml-8" href="{{ $conversation->instructor->getProfileUrl() }}" target="_blank">{{ $conversation->instructor->full_name }}</a>
                                                    </div>
                                                @else - @endif
                                            </td>
                                            <td class="text-left">
                                                @if(!empty($conversation->user))
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $conversation->user->getAvatar(40) }}"  class="avatar mr-2">
                                                        <a class="text-dark ml-8" href="{{ $conversation->user->getProfileUrl() }}" target="_blank">{{ $conversation->user->full_name }}</a>
                                                    </div>
                                                @else - @endif
                                            </td>
                                            <td class="text-left">
                                                <span class="text-dark">{{ $conversation->source_title }}</span>
                                                <span class="d-block font-12 text-gray-500">{{ ucfirst($conversation->source_type) }}</span>
                                            </td>
                                            <td class="text-left">
                                                @if(!empty($conversation->latestMessage))
                                                    <span class="text-gray-500 font-12">{{ \Illuminate\Support\Str::limit($conversation->latestMessage->message, 50) }}</span>
                                                @else - @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($conversation->latestMessage))
                                                    {{ dateTimeFormat($conversation->latestMessage->created_at->timestamp, 'j M Y | H:i') }}
                                                @else
                                                    {{ dateTimeFormat($conversation->updated_at->timestamp, 'j M Y | H:i') }}
                                                @endif
                                            </td>
                                            <td class="text-center" width="50">
                                                <div class="btn-group dropdown table-actions position-relative">
                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a href="{{ getAdminPanelUrl() }}/live-chat/public-chat/{{ $conversation->id }}/view" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                            <x-iconsax-lin-messages-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                            <span class="text-gray-500 font-14">View Conversation</span>
                                                        </a>

                                                        @include('admin.includes.delete_button', [
                                                            'url' => getAdminPanelUrl().'/live-chat/public-chat/'.$conversation->id.'/delete',
                                                            'btnClass' => 'dropdown-item text-danger d-flex align-items-center py-3 px-0 gap-4',
                                                            'btnText' => 'Delete Conversation',
                                                            'btnIcon' => 'trash',
                                                            'iconType' => 'lin',
                                                            'iconClass' => 'text-danger mr-2',
                                                            'hideDefaultClass' => true
                                                        ])
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            {{ $conversations->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
