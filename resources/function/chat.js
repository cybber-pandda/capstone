let currentRecipientId = null;

// Load chat users (optional if tabs are static)
function loadUsers() {
    $.get('/chat/users', function(users) {
        console.log(users); // populate to UI if needed
    });
}

// Load messages when user is clicked
function loadMessages(recipientId) {
    currentRecipientId = recipientId;
    $.get(`/chat/messages/${recipientId}`, function(messages) {
        const $chatBody = $(".chat-body .messages");
        $chatBody.empty();

        messages.forEach(msg => {
            const isMe = msg.sender_id === CURRENT_USER_ID;
            $chatBody.append(`
                <li class="message-item ${isMe ? 'me' : 'friend'}">
                    <div class="content">
                        <div class="message">
                            <div class="bubble">
                                <p>${msg.text}</p>
                            </div>
                            <span>${new Date(msg.created_at).toLocaleTimeString()}</span>
                        </div>
                    </div>
                </li>
            `);
        });

        $(".chat-body").scrollTop($(".chat-body")[0].scrollHeight);
    });
}

// Send message
$("#chatForm").on("keypress", function(e) {
    if (e.which === 13 && currentRecipientId) {
        e.preventDefault();
        const message = $(this).val();

        $.post("/chat/send", {
            _token: $("meta[name='csrf-token']").attr("content"),
            recipient_id: currentRecipientId,
            text: message
        }, function (data) {
            $("#chatForm").val('');
            loadMessages(currentRecipientId);
        });
    }
});

// Sample trigger to load chat
// You should replace this with your actual click
$(".chat-item").on("click", function () {
    const userId = $(this).data("id"); // Set this in your HTML
    loadMessages(userId);
});
