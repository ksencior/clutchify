let notifOpened = false;
let chatOpened = false;
console.log("notifications.js loaded", Date.now());
function fetchNotifications() {
    fetch('src/apis/get_notifications.php')
        .then(res => res.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Błąd parsowania JSON:", err, text);
                return;
            }

            const container = document.querySelector('.notifications-menu');
            container.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<p>Brak powiadomień.</p>';
                return;
            }

            data.forEach(notif => {
                const div = document.createElement('div');
                div.classList.add('notification');
                if (notif.type === "team-request") {
                    div.innerHTML = `
                        <p>${notif.content}</p>
                        <div class="notif-options">
                            <i class="fa-regular fa-circle-check accept-request" data-id="${notif.id}"></i>
                            <i class="fa-regular fa-circle-xmark decline-request" data-id="${notif.id}"></i>
                        </div>
                    `;
                } else {
                    div.innerHTML = `
                        <p>${notif.content}</p>
                        <div class="notif-options">
                            <i class="fa-regular fa-circle-check mark-read" data-id="${notif.id}"></i>
                        </div>
                    `;
                }
                container.appendChild(div);
            });
        })
        .catch(err => {
            console.error("Błąd pobierania notyfikacji:", err);
        });
}

function respondToTeamRequest(notifId, accept) {
    fetch('src/apis/respond_team_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notifId, accept })
    })
    .then(res => res.text())
    .then(text => {
        console.log("RAW RESPONSE:", text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            alert("❌ Serwer zwrócił nieprawidłową odpowiedź");
            return;
        }

        if (!data.success) {
            console.log("Odpowiedź negatywna:", data.message);
            alert("❗ " + data.message);
            fetchNotifications();
            return;
        }

        alert("✅ " + data.message);
        fetchNotifications();
    })
    .catch(err => {
        console.error("FETCH ERROR:", err);
        alert("❌ Błąd połączenia z serwerem");
    });
}

function closeNotifications() {
    const notifMenu = document.querySelector('.notifications-menu');
    notifMenu.classList.remove('active');
    notifMenu.addEventListener('transitionend', function handler() {
        notifMenu.style.display = "none";
        notifMenu.removeEventListener('transitionend', handler);
        notifOpened = false;
    });
}

function closeChat() {
    const chatWindow = document.querySelector('.team-chat-window');
    chatWindow.classList.remove('active');
    chatWindow.addEventListener('transitionend', function handler() {
        chatWindow.style.display = "none";
        chatWindow.removeEventListener('transitionend', handler);
        chatOpened = false;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const notificationsBtn = document.getElementById('notifications');
    const chatBtn = document.getElementById('team-chat')? document.getElementById('team-chat') : null;

    notificationsBtn.addEventListener('click', function() {
        if (notifOpened) {
            closeNotifications();
        } else {
            if (chatOpened) closeChat();
            const notifMenu = document.querySelector('.notifications-menu');
            notifMenu.style.display = "flex";
            requestAnimationFrame(() => {
                notifMenu.classList.add('active');
                notifOpened = true;
                fetchNotifications();
            });
        }
    });

    if (chatBtn) {
        chatBtn.addEventListener('click', function() {
        if (chatOpened) {
            closeChat();
        } else {
            if (notifOpened) closeNotifications();
            const chatWindow = document.querySelector('.team-chat-window');
            chatWindow.style.display = "flex";
            requestAnimationFrame(() => {
                chatWindow.classList.add('active');
                chatOpened = true;
                loadMessages();
            });
        }
    });
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('accept-request')) {
            const notifId = e.target.dataset.id;
            respondToTeamRequest(notifId, true);
        }
        if (e.target.classList.contains('decline-request')) {
            const notifId = e.target.dataset.id;
            respondToTeamRequest(notifId, false);
        }
        if (e.target.classList.contains('mark-read')) {
            const notifId = e.target.dataset.id;
            markAsRead(notifId);
        }
    });
});

