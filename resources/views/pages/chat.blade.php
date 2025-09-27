@extends('layouts.dashboard')

@php
$user = auth()->user();
$avatar = $user->profile
? asset($user->profile)
: asset('assets/avatars/' . rand(1, 17) . '.avif');
@endphp

@section('content')
<div class="page-content container-xxl p-3">
    <div class="row chat-wrapper">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row position-relative">
                        <div class="col-lg-4 chat-aside border-end-lg">
                            <div class="aside-content">
                                <div class="aside-header">
                                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <figure class="me-2 mb-0">
                                                <img src="{{ $avatar }}" class="img-sm rounded-circle" alt="profile">
                                                <div class="status online"></div>
                                            </figure>
                                            <div>
                                                <h6>{{ auth()->user()->name }}</h6>
                                                <p class="text-secondary fs-13px">Online</p>
                                            </div>
                                        </div>
                                    </div>
                                    <form class="search-form">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchForm" placeholder="Search here...">
                                            <span class="input-group-text bg-transparent">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" class="lucide lucide-search cursor-pointer">
                                                    <path d="m21 21-4.34-4.34"></path>
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                </svg>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="aside-body">
                                    <!-- <p class="text-secondary mb-1">Active</p> -->
                                    <ul class="list-unstyled chat-list px-1 mt-4" id="chat-user-list"></ul>
                                    <p class="text-center text-muted mt-3" id="no-users-message" style="display:none;">No users found.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 chat-content">
                            <div class="chat-header border-bottom pb-2">
                                <div class="d-flex align-items-center">
                                    <i class="d-lg-none me-2 text-secondary" id="backToUserList" style="cursor:pointer;" data-lucide="corner-up-left"></i>
                                    <figure class="mb-0 me-2">
                                        <img src="{{ asset('assets/dashboard/images/noprofile.png') }}" class="img-sm rounded-circle" alt="image">
                                        <div class="status online"></div>
                                    </figure>
                                    <div>
                                        <p id="chat-user-name">Select a user</p>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-body ps ps--active-x ps--active-y">
                                <ul class="messages"></ul>
                            </div>
                            <div class="chat-footer d-flex">
                                <input type="text" class="form-control rounded-pill me-2" id="chatForm" placeholder="Type a message">
                                <button type="button" class="btn btn-primary btn-icon rounded-circle" id="sendBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" class="lucide lucide-send">
                                        <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path>
                                        <path d="m21.854 2.147-10.94 10.939"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentRecipientId = null;

    function loadUsers(isInitialLoad = false) {
        $.get('/chat/users', function(users) {
            const $userList = $('#chat-user-list');
            $('#no-users-message').hide();
            $userList.empty();

            users.forEach((user) => {
                const profile = user.profile ? user.profile : `/assets/avatars/${Math.floor(Math.random() * 17 + 1)}.avif`;
                const lastMessage = user.last_message?.text || 'Click to chat';
                const lastTime = user.last_message?.created_at ?
                    dayjs(user.last_message.created_at).fromNow() :
                    '';

                $userList.append(`
                <li class="chat-item pe-1 mb-1" data-id="${user.id}" data-name="${user.name}" data-profile="${profile}">
                    <a href="javascript:;" class="d-flex align-items-center">
                        <figure class="mb-0 me-2">
                            <img src="${profile}" class="img-xs rounded-circle" alt="user">
                            <div class="status ${user.online ? 'online' : 'offline'}"></div>
                        </figure>
                        <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                            <div>
                                <p class="text-body fw-bold mb-0">${user.name}</p>
                                <p class="text-secondary fs-13px mb-1 text-truncate" style="max-width: 150px;">${lastMessage}</p>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">${lastTime}</p>
                            </div>
                        </div>
                    </a>
                </li>
            `);
            });

            $('.chat-item').off('click').on('click', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                const userProfile = $(this).data('profile');

                $('#chat-user-name').text(userName);
                $(".chat-header figure img").attr('src', userProfile);

                currentRecipientId = userId;
                toggleToChatView();
                loadMessages(userId);
            });

            if (isInitialLoad && users.length > 0 && currentRecipientId === null) {
                $('.chat-item').first().trigger('click');
            }
        });
    }

    let chatScrollbar = null;

    function loadMessages(recipientId) {
        currentRecipientId = recipientId;

        $.get(`/chat/messages/${recipientId}`, function(messages) {
            const $chatBody = $(".chat-body .messages");
            const chatBodyEl = document.querySelector('.chat-content .chat-body');

            // Clear old scrollbar
            if (chatScrollbar) {
                chatScrollbar.destroy();
                chatScrollbar = null;
            }

            $chatBody.empty();
            chatBodyEl.scrollTop = 0;

            if (messages.length === 0) {
                $chatBody.append(`
                <div class="d-flex justify-content-center align-items-center text-muted w-100 h-100" style="min-height: 300px;">
                    <p class="m-0">No messages found.</p>
                </div>
            `);
                return;
            }

            messages.forEach(msg => {
                const isMe = msg.sender_id === CURRENT_USER_ID;
                const profile = msg.sender_profile ? msg.sender_profile : `/assets/avatars/${Math.floor(Math.random() * 17 + 1)}.avif`;

                $chatBody.append(`
                <li class="message-item ${isMe ? 'me' : 'friend'}">
                    <img src="${profile}" class="img-xs rounded-circle" alt="avatar">
                    <div class="content">
                        <div class="message">
                            <div class="bubble">
                                <p>${msg.text ?? ''}</p>
                            </div>
                            <span>${new Date(msg.created_at).toLocaleTimeString()}</span>
                        </div>
                    </div>
                </li>
            `);
            });

            // Only apply scrollbar if there are more than 5 messages
            if (messages.length > 5) {
                chatScrollbar = new PerfectScrollbar(chatBodyEl);
                chatBodyEl.scrollTop = chatBodyEl.scrollHeight;
            } else {
                chatBodyEl.scrollTop = 0;
            }
        });
    }

    function toggleToChatView() {
        if (window.innerWidth < 992) {
            $('.chat-aside').hide();
            $('.chat-content').show();
        }
    }

    function toggleToUserList() {
        if (window.innerWidth < 992) {
            $('.chat-content').hide();
            $('.chat-aside').show();
        }
    }

    function filterUsers(keyword) {
        let hasMatch = false;

        $('#chat-user-list .chat-item').each(function() {
            const name = $(this).data('name')?.toLowerCase() || '';
            const isMatch = name.includes(keyword.toLowerCase());

            $(this).toggle(isMatch);

            if (isMatch) hasMatch = true;
        });

        if (hasMatch) {
            $('#no-users-message').hide();
        } else {
            $('#no-users-message').show();
        }
    }

    // Optional: debounce for better performance
    function debounce(func, delay) {
        let timer;
        return function() {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    const debouncedSearch = debounce(function() {
        const keyword = $('#searchForm').val();
        filterUsers(keyword);
    }, 200);

    // Shared send logic
    function sendMessage() {
        const message = $('#chatForm').val().trim();
        if (!message || !currentRecipientId) return;

        $.post('/chat/send', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            recipient_id: currentRecipientId,
            text: message
        }, function() {
            $('#chatForm').val('');
            loadUsers(false); // dont trigger auto select true trigger auto select
            loadMessages(currentRecipientId);
            refreshRecentMessages();
        });
    }

    $(document).ready(function() {
        loadUsers();

        // Bind search
        $('#searchForm').on('input', debouncedSearch);

        if (window.innerWidth < 992) {
            $('.chat-content').hide();
            $('.chat-aside').show();
        }

        // Bind toggle on chat item click
        $(document).on('click', '.chat-item', function() {
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            const userProfile = $(this).data('profile');

            $('#chat-user-name').text(userName);
            $(".chat-header figure img").attr('src', userProfile);

            toggleToChatView();
            loadMessages(userId);
        });

        // Bind back button
        $(document).on('click', '#backToUserList', function() {
            toggleToUserList();
        });


        // Handle click on send button
        $('#sendBtn').on('click', function() {
            sendMessage();
        });

        // Handle Enter key press inside chat input
        $('#chatForm').on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent newline
                sendMessage();
            }
        });

    });
</script>
@endpush