<?php 
session_start();
include_once 'src/connect_db.php';
date_default_timezone_set("Europe/Warsaw");
$isLeader = false;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
}

try {
    $stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $_SESSION['id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $teamID = $res['team_id'];
    }
} catch (PDOException $e) {
    echo $e->getMessage(); exit;
}

if(empty($_SESSION['team_id'])) {
    header('Location: team_create.php');
}

$nearestMatch = null;
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

        $stmt = $pdo->prepare("SELECT 
            m.*, 
            t1.nazwa AS team1_name, 
            t2.nazwa AS team2_name 
        FROM mecze m
        JOIN teams t1 ON m.team1 = t1.id
        JOIN teams t2 ON m.team2 = t2.id
        WHERE (m.team1 = :teamId1 OR m.team2 = :teamId2) AND m.winner_id IS NULL
        ORDER BY m.termin ASC
        LIMIT 1");
        $stmt->execute([':teamId1' => $teamID, ':teamId2' => $teamID]);
        $nearestMatch = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($nearestMatch) {
            $stmt = $pdo->prepare("SELECT id FROM lobbies WHERE mecz_id = :mid");
            $stmt->execute([':mid' => $nearestMatch['id']]);
            $lobby = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($lobby) {
                header('Location: lobby.php?id='.$lobby['id']);
            }

            $stmt = $pdo->prepare("SELECT user_id FROM ready_players WHERE mecz_id = ? AND user_id = ?");
            $stmt->execute([$nearestMatch['id'], $_SESSION['id']]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $isReady = true;
            } else {
                $isReady = false;
            }
        } else {
            $isReady = false;
            $readyCountTeam = 0;
            $readyCountAll = 0;
        }


    } catch (PDOException $e) {
        die($e);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZSN Champions III</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="src/notifications.js"></script>
    <script src="src/chat.js"></script>
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
        <?php include 'src/navbar.php'; ?>
        <div class="content">
            <div class="team-info">
                <h1 style="width: 100%; display: block; text-align: center;">Zagraj mecz</h1>
                <h3><?php 
                    if ($nearestMatch) {
                        echo htmlspecialchars($nearestMatch['team1_name']). ' VS '. htmlspecialchars($nearestMatch['team2_name']);
                    }
                ?></h3>
                <?php if (!$nearestMatch): ?>
                    <div id="countdown-timer">Brak zaplanowanych meczy dla Twojej drużyny.</div>
                <?php else: ?>
                    <div id="countdown-timer"></div>
                    <script>
                        console.log("Nearest match:", <?= json_encode($nearestMatch) ?>);
                        const endingAt = "<?= $nearestMatch['termin'] ?>";
                        const timer = document.getElementById("countdown-timer");
                        function updateCountdown() {
                            const endTime = new Date(endingAt.replace(' ', 'T')).getTime();
                            const now = new Date().getTime();
                            let distance = endTime - now;

                            if (distance < 0) {
                                timer.innerHTML = "Oczekiwanie...";
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
                <?php endif; ?>
                <div class="players">
                <?php
                    for ($i = 0; $i < 2; $i++) {
                        if (isset($users[$i])) {
                            echo '<div class="player-card" data-id="'.$users[$i]['id'].'">
                                <img src="'.($users[$i]['avatar_url'] ?? 'img/avatar_default.png').'" alt="">
                                <h3>'.$users[$i]['username'].'</h3>
                            </div>';
                        } else {
                            echo '<div class="player-card empty">
                                <img src="img/avatar_default.png" alt="">
                                <h3>Puste miejsce</h3>';
                            echo '</div>';
                        }
                    }

                    // Lider na środku
                    if ($leader) {
                        echo '<div class="player-card leader" data-id="'.$leader['id'].'">
                            <img src="'.($leader['avatar_url'] ?? 'img/avatar_default.png').'" alt="">
                            <h3>'.$leader['username'].'</h3>
                            <p>Lider</p>
                        </div>';
                    } else {
                        echo '<div class="player-card leader empty">
                            <img src="img/avatar_default.png" alt="">
                            <h3>Puste miejsce</h3>
                            <p>Lider</p>
                        </div>';
                    }

                    // Dwie kolejne karty
                    for ($i = 2; $i < 4; $i++) {
                        if (isset($users[$i])) {
                            echo '<div class="player-card" data-id="'.$users[$i]['id'].'">
                                <img src="'.($users[$i]['avatar_url'] ?? 'img/avatar_default.png').'" alt="">
                                <h3>'.$users[$i]['username'].'</h3>
                            </div>';
                        } else {
                            echo '<div class="player-card empty">
                                <img src="img/avatar_default.png" alt="">
                                <h3>Puste miejsce</h3>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="play-menu">
                <?php
                    // switch ($nearestMatch['round']) {
                    //     case 1: $roundName = "BO3"; break;
                    //     case 2: $roundName = "BO3"; break;
                    //     case 3: $roundName = "BO3"; break;
                    //     case 4: $roundName = "BO3"; break;
                    //     case 5: $roundName = "BO5"; break;
                    //     default: $roundName = "-"; break;
                    // }
                    $roundName = "BO3";
                    if ($nearestMatch){
                        try {
                            $stmt = $pdo->prepare("SELECT 
                                                    COUNT(*) AS ready_count_all,
                                                    SUM(CASE WHEN r.team_id = :teamId THEN 1 ELSE 0 END) AS ready_count_team
                                                FROM ready_players r
                                                WHERE r.mecz_id = :matchId
                                            ");
                                            $stmt->execute([
                                                ':matchId' => $nearestMatch['id'],
                                                ':teamId'  => $teamID
                                            ]);
                                            $readyInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                                            $readyCountAll  = $readyInfo['ready_count_all'] ?? 0;
                                            $readyCountTeam = $readyInfo['ready_count_team'] ?? 0;
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }
                    }

                    $buttonEnabled = false;
                    $buttonTitle = "Gotowość można zgłosić na 15 minut przed meczem";
                    if ($nearestMatch) {
                        if (strtotime($nearestMatch['termin']) - time() <= 15 * 60) {
                            $buttonEnabled = true;
                            $buttonTitle = "";
                        }
                    } else {
                        $buttonEnabled = false;
                        $buttonTitle = "Brak meczów do zagrania.";
                    }
                ?>
                <p>Tryb gry: <?= $roundName ?></p>
                <button class="play-btn" data-match="<?= $nearestMatch['id']? $nearestMatch['id'] : "NULL" ?>" 
                    <?= ($buttonTitle!="")? "title='$buttonTitle'" : ''?> 
                    <?= $buttonEnabled? '' : ' disabled' ?>>
                    <?=  $isReady? '✔ Gotowy' : 'Gotowość' ?>
                </button>
                <p class="ready-counter">Gotowi gracze: <?= $readyCountTeam ?>/5</p>
            </div>
            <div class="upcoming-matches full">
                <h1>Nadchodzące mecze</h1>
                <div class="matches-list">
                <?php 
                if (empty($_SESSION['team_id']) || $_SESSION['team_id'] == NULL) {
                    echo '<p>Nie jesteś w żadnej drużynie.</p>';
                }
                try{
                    $sql = $pdo->prepare("
                        SELECT 
                            m.*, 
                            t1.nazwa AS team1_name, 
                            t2.nazwa AS team2_name 
                        FROM mecze m
                        JOIN teams t1 ON m.team1 = t1.id
                        JOIN teams t2 ON m.team2 = t2.id
                        WHERE (m.team1 = :teamId1 OR m.team2 = :teamId2)
                        AND m.termin > NOW()
                        ORDER BY m.termin ASC
                    ");
                    $sql->execute([':teamId1' => $_SESSION['team_id'], ':teamId2' => $_SESSION['team_id']]);
                    $anyMatches=false;
                    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                        echo '
                        <div class="match">
                            <div class="teams-versus">
                                <h3>'.htmlspecialchars($row['team1_name']).'</h3>
                                <h1> VS </h1>
                                <h3>'.htmlspecialchars($row['team2_name']).'</h3>
                            </div>
                            <h3>'.date('d.m.Y H:i', strtotime($row['termin'])).'</h3>
                        </div>
                        ';
                        $anyMatches=true;
                    }
                    if (!$anyMatches) {
                        echo '<p>Brak nadchodzących meczy.</p>';
                    }
                } catch (PDOException $e) {
                    echo "$e"; exit;
                }
                ?>
                </div>
            </div>
        </div>
        <?php include 'src/sidebar_load.php';?>
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
                let card = e.target.closest('.player-card');
                let id = card?.dataset.id;
                if (id != undefined) {
                    window.location.href = `profile.php?id=${id}`
                }
            })
        });

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.querySelector('.play-btn');
            const readyInfo = document.querySelector('.ready-counter');
            const matchId = btn ? btn.dataset.match : null;

            function updateStatus() {
                if (!matchId || matchId == "NULL") return;
                fetch('src/get_ready_status.php?mecz_id=' + matchId)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            readyInfo.textContent = 
                                `Gotowi gracze: ${data.ready_count_team}/5 (łącznie: ${data.ready_count_all}/${data.total_players})`;

                            if (data.is_started == true) {
                                window.location.href = "lobby.php?id=" + data.lobby_id;
                            }
                        }
                    })
                    .catch(err => console.error("Błąd fetch status:", err));
            }

            if (btn) {
                btn.addEventListener('click', () => {
                const matchId = btn.dataset.match;
                if (matchId == "NULL") return;
                fetch('src/ready.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'mecz_id=' + encodeURIComponent(matchId)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // zmiana tekstu przycisku
                        if (data.ready) {
                            btn.textContent = "✔ Gotowy";
                            btn.classList.add("active");
                        } else {
                            btn.textContent = "Gotowość";
                            btn.classList.remove("active");
                        }

                        // odświeżenie licznika gotowych graczy
                        const counter = document.querySelector('.play-menu p.ready-counter');
                        if (counter) {
                            counter.textContent = `Gotowi gracze: ${data.ready_count_team}/5`;
                        }

                        if (data.ready_count_all == 10) {
                            window.location.href = "lobby.php?id=" + matchId;
                        }
                    } else {
                        alert(data.message || "Błąd!");
                    }
                })
                .catch(err => console.error("Fetch error:", err));
            });
            }

            setInterval(updateStatus, 1000);
            updateStatus();
        });
    </script>
    <script src="src/mobile-menu.js"></script>
</body>
</html>