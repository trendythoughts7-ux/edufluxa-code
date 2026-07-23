@if($authUser->can('admin_live_chat'))
    <li class="menu-header">{{ trans('update.live_chat') }}</li>
@endif

@can('admin_live_chat')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/live-chat*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-messages-3 class="icons" width="24px" height="24px"/>
            <span>{{ trans('update.live_chat') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_live_chat_chat_rooms')
                <li class="{{ (request()->is(getAdminPanelUrl('/live-chat/chat-rooms*', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/live-chat/chat-rooms">{{ trans('update.chat_rooms') }}</a>
                </li>
            @endcan

            @can('admin_live_chat_public_chat')
                <li class="{{ (request()->is(getAdminPanelUrl('/live-chat/public-chat*', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/live-chat/public-chat">{{ trans('update.public_chats') }}</a>
                </li>
            @endcan

            @can('admin_live_chat_settings')
                <li class="{{ (request()->is(getAdminPanelUrl('/live-chat/settings*', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/live-chat/settings">{{ trans('admin/main.settings') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
