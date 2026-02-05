<?php 
session_start();
include_once 'src/core/connect_db.php';
$teamID = 0;
$isLeader = false;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
}

if(empty($_SESSION['team_id'])) {
    header('Location: team_create.php');
}

if (isset($_GET['id']) && $_GET['id'] > 0) {
    $teamID = $_GET['id'];
} else {
    $teamID = $_SESSION['team_id'];
}

if (isset($teamID) && $teamID!=NULL) {
    try {
        $sql = "SELECT * FROM teams WHERE id=:teamid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':teamid'=>$teamID]);
        $team=$stmt->fetch(PDO::FETCH_ASSOC);

        if ($team) {
            $teamName = $team['nazwa'];
            $teamSkrot = $team['skrot'];
            $leaderId = $team['leader_id'];
            if($leaderId==$_SESSION['id']) {
                $isLeader = true;
            }
        } else {
            header('Location: team.php?id='.$_SESSION['team_id']);
        }
        $users = [];
        $leader = null;
        
        $sql = "SELECT * FROM users WHERE team_id = :teamid LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':teamid' => $teamID]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['id'] == $leaderId) {
                $leader = $row;
            } else {
                $users[] = $row;
            }
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE team_id = :teamid AND rezerwa = 1 LIMIT 1");
        $stmt->execute([':teamid' => $teamID]);
        $rezerwowy = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die($e->getMessage());
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE `type`='Zapisy'");
    $stmt->execute();

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $ending_at = $res['ending_at'];
    }

    $isRegistrationOpen = false;
    if (isset($ending_at)) {
        $now = new DateTime();
        $end = new DateTime($ending_at);
        if ($now < $end) {
            $isRegistrationOpen = true;
        }
    }
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZSN Champions III</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js" defer></script>
    <script src="assets/js/chat.js"></script>
    <script>
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

    </script>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <div class="team-info">
                <h1><?php echo "$teamName" ?></h1>
                <p class="skrot">(<?php echo $teamSkrot; ?>)</p>
                <div id="countdown-timer"></div>
                <?php if (!$isLeader && $isRegistrationOpen && isset($_SESSION['team_id']) && $_SESSION['team_id'] == $teamID): ?>
                        <button class="action-btn leave-team" id="leaveTeamButton">Opuść drużynę</button>
                    <?php endif; ?>
                <script>
                // Pass PHP ending_at to JS
                const endingAt = "<?= $ending_at ?>";
                const timer = document.getElementById("countdown-timer");
                function updateCountdown() {
                    const endTime = new Date(endingAt.replace(' ', 'T')).getTime();
                    const now = new Date().getTime();
                    let distance = endTime - now;

                    if (distance < 0) {
                        timer.innerHTML = "Zapisy zakończone!";
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    timer.innerHTML =
                        `${days} dni ${hours} godz. ${minutes} min ${seconds} sek`;
                }
                updateCountdown();
                setInterval(updateCountdown, 1000);
            </script>
                <div class="players">
                <?php
                    for ($i = 0; $i < 2; $i++) {
                        if (isset($users[$i])) {
                            echo '<div class="player-card" data-id="'.$users[$i]['id'].'">
                                <img src="'.($users[$i]['avatar_url'] ?? 'assets/img/avatar_default.png').'" alt="">
                                <h3>'.$users[$i]['username'].'</h3>';
                            if ($isLeader && $isRegistrationOpen) {
                                echo '<button class="action-btn kick-player" data-player-id="'.$users[$i]['id'].'" title="Wyrzuć gracza">
                                            <i class="fa-solid fa-user-minus"></i>
                                          </button>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="player-card empty">
                                <img src="assets/img/avatar_default.png" alt="">
                                <h3>Puste miejsce</h3>';
                            if ($isLeader) {
                                echo '<button class="invite-btn" data-team="'.$teamID.'">Zaproś</button>';
                            }
                            echo '</div>';
                        }
                    }

                    // Lider na środku
                    if ($leader) {
                        echo '<div class="player-card leader" data-id="'.$leader['id'].'">
                            <img src="'.($leader['avatar_url'] ?? 'assets/img/avatar_default.png').'" alt="">
                            <h3>'.$leader['username'].'</h3>
                            <p>Lider</p>
                        </div>';
                    } else {
                        echo '<div class="player-card leader empty">
                            <img src="assets/img/avatar_default.png" alt="">
                            <h3>Puste miejsce</h3>
                            <p>Lider</p>
                        </div>';
                    }

                    // Dwie kolejne karty
                    for ($i = 2; $i < 4; $i++) {
                        if (isset($users[$i])) {
                            echo '<div class="player-card" data-id="'.$users[$i]['id'].'">
                                <img src="'.($users[$i]['avatar_url'] ?? 'assets/img/avatar_default.png').'" alt="">
                                <h3>'.$users[$i]['username'].'</h3>';
                                if ($isLeader && $isRegistrationOpen) {
                                echo '<button class="action-btn kick-player" data-player-id="'.$users[$i]['id'].'" title="Wyrzuć gracza">
                                            <i class="fa-solid fa-user-minus"></i>
                                          </button>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="player-card empty">
                                <img src="assets/img/avatar_default.png" alt="">
                                <h3>Puste miejsce</h3>';
                            if ($isLeader) {
                                echo '<button class="invite-btn" data-team="'.$teamID.'">Zaproś</button>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <div class="rezerwa-info">
                    <p>W przypadku zmiany zawodnika na rezerwowego, proszę skontaktować się z administracją turnieju.</p>
                    <p>Zaproszone osoby mogą dołączyć do drużyny tylko z poziomu komputera (w powiadomieniach)</p>
                </div>
            </div>
        </div>
        <?php include 'src/views/partials/sidebar_load.php';?>
        <div class="notifications-menu"></div>
        <div class="team-chat-window">
            <div class="chat-header">
                <h3>Czat drużynowy</h3>
            </div>
            <div class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" placeholder="Napisz wiadomość...">
                <button class="send-chat"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
        <div class="player-invite">
            <div class="box">
                <h1 class="w-300 ls-2 f-upper">Zaproś graczy</h1><i class="fa-solid fa-xmark close-invite"></i>
                <input type="text" name="searchName" placeholder="Wyszukaj..">
                <div class="player-list"></div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.player-card').forEach((card) => {
            card.addEventListener('click', (e) => {
                let id = e.target.dataset.id;
                
                if (id != undefined) {
                    window.location.href = `profile.php?id=${id}`
                }
            })
        });

        function sendInvite(toUserId, teamId) {
            toUserId = parseInt(toUserId);
            if (isNaN(toUserId) || toUserId <= 0) {
                alert('Wystąpił błąd. Spróbuj ponownie później.');
                return;
            }
            fetch('src/apis/send_invite.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `to_user=${toUserId}&team_id=${teamId}`
            })
            .then(res => res.text())
            .then(response => {
                if(response === 'success') {
                    alert('Wysłano zaproszenie!');
                } else {
                    alert('Błąd: ' + response);
                }
            })
            .catch(err => {
                console.error("Błąd wysyłania zaproszenia:", err);
                alert('Wystąpił błąd');
            });
        }
        function fetchPlayers() {
            fetch('src/apis/get_players.php')
            .then(res => res.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    console.error("Błąd parsowania JSON:", err, text);
                    return;
                }

                const container = document.querySelector('.player-list');
                container.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<p>Brak graczy.</p>';
                    return;
                }

                data.forEach(player => {
                    const div = document.createElement('div');
                    div.classList.add('player');
                    div.dataset.userid = player.id;
                    div.innerHTML = `
                        <div class="p-info">
                            <img src="${player.avatar_url?player.avatar_url:'assets/img/avatar_default.png'}" alt="${player.id}">
                            <p class="w-300 f-upper">${player.username}</p>
                        </div>
                        <button class="zapros">+</button>
                    `;
                    container.appendChild(div);
                });
                // updateNotificationCount();
            })
            .catch(err => {
                console.error("Błąd pobierania notyfikacji:", err);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            let zaprosShown = false;
            document.querySelectorAll('button.invite-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if(!zaprosShown) {
                        const zaprosMenu = document.querySelector('.player-invite');
                        zaprosMenu.style.display = "flex";
                        requestAnimationFrame(() => {
                            zaprosMenu.classList.add('active')
                            zaprosShown = true;
                            fetchPlayers();
                        })
                    }
                })
            })
            document.querySelector('.close-invite').addEventListener('click', () => {
                if (zaprosShown) {
                    const zaprosMenu = document.querySelector('.player-invite');
                    zaprosMenu.classList.remove('active');
                    zaprosMenu.addEventListener('transitionend', function handler() {
                        zaprosMenu.style.display = "none";
                        zaprosMenu.removeEventListener('transitionend', handler);
                        zaprosShown = false;
                    })
                }
            })
            document.addEventListener('click', (e) => {
                if(e.target.classList.contains('zapros')) {
                    const toUserId = e.target.parentElement.dataset.userid;
                    const teamId = <?php echo $teamID; ?>;
                    sendInvite(toUserId, teamId);
                }
            });

            document.querySelectorAll('.kick-player').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation(); // Zapobiega przejściu do profilu
                    const playerId = btn.getAttribute('data-player-id');
                    if (confirm(`Czy na pewno chcesz wyrzucić tego gracza (ID: ${playerId}) z drużyny?`)) {
                        fetch('src/apis/kick_player.php', { // Będziesz musiał utworzyć ten plik!
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `player_id=${playerId}`
                        })
                        .then(res => res.json())
                        .then(response => {
                            if(response.success) {
                                alert('Gracz został wyrzucony.');
                                window.location.reload(); // Odśwież widok
                            } else {
                                alert('Błąd: ' + (response.message || 'Nie udało się wyrzucić gracza.'));
                            }
                        })
                        .catch(err => {
                            console.error("Błąd wyrzucania gracza:", err);
                            alert('Wystąpił błąd komunikacji.');
                        });
                    }
                });
            });

            // Obsługa Opuszczenia Drużyny (Gracz)
            const leaveTeamButton = document.getElementById('leaveTeamButton');
            if (leaveTeamButton) {
                leaveTeamButton.addEventListener('click', () => {
                    if (confirm('Czy na pewno chcesz opuścić drużynę? Nie będziesz mógł wrócić bez zaproszenia.')) {
                        fetch('src/apis/leave_team.php', { // Będziesz musiał utworzyć ten plik!
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            // Nie potrzeba ciała, bo ID użytkownika jest w sesji
                        })
                        .then(res => res.json())
                        .then(response => {
                            if(response.success) {
                                alert('Pomyślnie opuszczono drużynę.');
                                window.location.href = 'index.php'; // Przekierowanie
                            } else {
                                alert('Błąd: ' + (response.message || 'Nie udało się opuścić drużyny.'));
                            }
                        })
                        .catch(err => {
                            console.error("Błąd opuszczania drużyny:", err);
                            alert('Wystąpił błąd komunikacji.');
                        });
                    }
                });
            }
            const searchInput = document.querySelector('input[name="searchName"]');
            const playerList = document.querySelector('.player-list');

            const searchPlayers = debounce(function() {
                const query = searchInput.value.trim();

                if (query.length < 1) {
                    playerList.innerHTML = '<p>Wpisz nazwę gracza. Uwaga: Gracz musi mieć połączone konto steam!</p>';
                    return;
                }

                playerList.innerHTML = '<div class="loader">Szukam graczy...</div>';

                fetch('src/apis/search_players.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    playerList.innerHTML = '';
                    if (data.length === 0) {
                        playerList.innerHTML = '<p>Brak graczy.</p>';
                        return;
                    }

                    data.forEach(player => {
                        const div = document.createElement('div');
                        div.classList.add('player');
                        div.innerHTML = `
                            <div class="p-info">
                                <img src="${player.avatar_url ? player.avatar_url : 'assets/img/avatar_default.png'}" alt="">
                                <p class="w-300 f-upper">${player.username}</p>
                            </div>
                            <button class="zapros" data-id="${player.id}">+</button>
                        `;
                        playerList.appendChild(div);
                    });

                    playerList.querySelectorAll('button.zapros').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const playerId = btn.getAttribute('data-id');
                            fetch('src/apis/send_invite.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({ player_id: playerId, team_id: <?php echo $teamID; ?> })
                            })
                            .then(res => res.json())
                            .then(resp => {
                                if (resp.success) {
                                    btn.disabled = true;
                                    btn.innerText = 'Wysłano';
                                } else {
                                    alert(resp.message || 'Błąd zaproszenia');
                                }
                            });
                        });
                    });
                })
                .catch(err => {
                    console.error("Błąd wyszukiwania:", err);
                    playerList.innerHTML = '<p>Wystąpił błąd.</p>';
                });
            }, 300); // 300ms debounce

            searchInput.addEventListener('input', searchPlayers);

        });
    </script>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>

