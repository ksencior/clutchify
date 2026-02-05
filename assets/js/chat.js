function loadMessages() {
    fetch('src/apis/get_messages.php')
        .then(res => res.json())
        .then(data => {
            const messagesDiv = document.querySelector('.team-chat-window .chat-messages');
            messagesDiv.innerHTML = '';

            data.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('chat-message');
                div.innerHTML = `
                    <img src="${msg.avatar_url || 'img/avatar_default.png'}" alt="avatar">
                    <strong>${msg.username}</strong>: ${msg.message}
                `;
                messagesDiv.appendChild(div);
            });

            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('.chat-input input');
    const sendButton = document.querySelector('.send-chat');

    function sendMessage() {
        const message = input.value.trim();
        if (!message) return;

        const formData = new FormData();
        formData.append('message', message);

        fetch('src/apis/send_message.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            input.value = '';
            loadMessages();
        });
    }

    sendButton.addEventListener('click', sendMessage);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // żeby nie dodać nowej linii w input
            sendMessage();
        }
    });

    // odświeżanie co 5 sekund
    setInterval(() => {
        if (chatOpened) loadMessages();
    }, 1000);
});

