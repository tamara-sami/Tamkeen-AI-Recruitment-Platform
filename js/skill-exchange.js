document.addEventListener("DOMContentLoaded", function () {
    const messenger = document.getElementById("learningMessenger");
    const messengerToggle = document.getElementById("messengerToggle");
    const messengerIcon = document.getElementById("messengerIcon");

    const conversationList = document.getElementById("conversationList");
    const chatPanel = document.getElementById("chatPanel");

    const chatWithName = document.getElementById("chatWithName");
    const chatMessages = document.getElementById("chatMessages");

    const chatReceiverId = document.getElementById("chatReceiverId");
    const chatPostId = document.getElementById("chatPostId");

    const chatForm = document.getElementById("chatForm");
    const chatMessageInput = document.getElementById("chatMessageInput");

    if (messengerToggle && messenger && messengerIcon) {
        messengerToggle.addEventListener("click", () => {
            messenger.classList.toggle("collapsed");
            messengerIcon.classList.toggle("rotate");
        });
    }

    document.querySelectorAll(".open-chat-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            if (!messenger) return;

            messenger.classList.remove("collapsed");

            openChat(
                btn.dataset.receiverId,
                btn.dataset.postId,
                btn.dataset.name
            );
        });
    });

    document.querySelectorAll(".conversation-item").forEach(item => {
        item.addEventListener("click", () => {
            openChat(item.dataset.userId, 0, item.dataset.name);
        });
    });

    const backBtn = document.getElementById("backToConversations");

    if (backBtn) {
        backBtn.addEventListener("click", () => {
            chatPanel.classList.add("d-none");
            conversationList.classList.remove("d-none");
            refreshConversations();
        });
    }

    function updateBadge(unreadCount) {
        const badge = document.querySelector(".message-badge");

        if (!badge) return;

        if (unreadCount > 0) {
            badge.textContent = unreadCount;
        } else {
            badge.remove();
        }
    }

    function openChat(receiverId, postId, name) {
        if (!chatReceiverId || !chatPostId || !chatWithName || !conversationList || !chatPanel) return;

        chatReceiverId.value = receiverId;
        chatPostId.value = postId;
        chatWithName.textContent = name;

        conversationList.classList.add("d-none");
        chatPanel.classList.remove("d-none");

        fetch("get-learning-chat.php?user_id=" + encodeURIComponent(receiverId))
            .then(res => res.json())
            .then(data => {
                chatMessages.innerHTML = "";

                if (!data.success) return;

                updateBadge(data.unread_count || 0);

                data.messages.forEach(msg => {
                    const mine = String(msg.sender_id) === String(window.currentUserId);

                    const div = document.createElement("div");
                    div.className = mine ? "chat-bubble mine" : "chat-bubble";

                    div.innerHTML = `
                        <strong class="chat-sender">
                            ${mine ? "You" : escapeHtml(msg.sender_name)}
                        </strong>
                        <div>${escapeHtml(msg.message)}</div>
                        <small>${escapeHtml(msg.created_at)}</small>
                    `;

                    chatMessages.appendChild(div);
                });

                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
    }

    function refreshConversations() {
        if (!conversationList) return;

        fetch("get-learning-conversations.php")
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                conversationList.innerHTML = "";

                if (data.conversations.length === 0) {
                    conversationList.innerHTML = `
                        <div class="message-item text-muted small">
                            No conversations yet.
                        </div>
                    `;
                    return;
                }

                data.conversations.forEach(conv => {
                    const div = document.createElement("div");

                    div.className = "message-item conversation-item";
                    div.dataset.userId = conv.other_user_id;
                    div.dataset.name = conv.full_name;

                    div.innerHTML = `
                        <strong>${escapeHtml(conv.full_name)}</strong>
                        <p class="small text-muted mb-0">
                            ${escapeHtml(conv.message.substring(0, 55))}...
                        </p>
                    `;

                    div.addEventListener("click", () => {
                        openChat(conv.other_user_id, 0, conv.full_name);
                    });

                    conversationList.appendChild(div);
                });
            });
    }

    if (chatForm) {
        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const message = chatMessageInput.value.trim();

            if (message === "") return;

            const formData = new FormData();

            formData.append("receiver_id", chatReceiverId.value);
            formData.append("post_id", chatPostId.value);
            formData.append("message", message);

            fetch("send-learning-message.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    chatMessageInput.value = "";
                    refreshConversations();
                    openChat(chatReceiverId.value, chatPostId.value, chatWithName.textContent);
                });
        });
    }

    const exchangeForm = document.getElementById("exchangePostForm");

    if (exchangeForm) {
        exchangeForm.addEventListener("submit", function (e) {
            let valid = true;

            const type = document.getElementById("learningTypeSelect");
            const title = document.getElementById("exchangeTitle");
            const content = document.getElementById("exchangeContent");
            const category = document.getElementById("exchangeCategory");
            const btn = document.getElementById("exchangeSubmitBtn");

            const typeError = document.getElementById("typeError");
            const titleError = document.getElementById("titleError");
            const contentError = document.getElementById("contentError");
            const categoryError = document.getElementById("categoryError");

            [typeError, titleError, contentError, categoryError].forEach(error => {
                if (error) error.classList.add("d-none");
            });

            if (type && type.value.trim() === "") {
                typeError.classList.remove("d-none");
                valid = false;
            }

            if (title && title.value.trim() === "") {
                titleError.classList.remove("d-none");
                valid = false;
            }

            if (content && content.value.trim() === "") {
                contentError.classList.remove("d-none");
                valid = false;
            }

            if (category && category.value.trim() === "") {
                categoryError.classList.remove("d-none");
                valid = false;
            }

            if (!valid) {
                e.preventDefault();

                if (btn) {
                    btn.textContent = "Please Complete Required Fields";
                }
            }
        });
    }

    setTimeout(() => {
        const alertBox = document.querySelector(".alert-success");

        if (alertBox) {
            alertBox.remove();
        }

        if (window.location.search.includes("posted=1")) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }, 2500);

    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
});