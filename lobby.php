<?php 
session_start();
include_once 'src/connect_db.php';
date_default_timezone_set("Europe/Warsaw");

error_reporting(E_ALL);
ini_set('display_errors', 1);
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

if (!empty($_GET['id']) && isset($_GET['id'])) {
    $lobbyId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT l.*, m.*, 
    t1.nazwa AS team1_name, 
    t2.nazwa AS team2_name, 
    t1.id AS team1_id, 
    t2.id AS team2_id, 
    t1.leader_id AS team1_leader, 
    t2.leader_id AS team2_leader
    FROM lobbies l 
    JOIN mecze m ON l.mecz_id = m.id 
    JOIN teams t1 ON m.team1 = t1.id
    JOIN teams t2 ON m.team2 = t2.id
    WHERE l.id = :lid");
    $stmt->execute([':lid' => $lobbyId]);
    $lobby = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lobby) {
        header('Location: play.php');
        exit;
    }
} 

function renderPlayers($pdo, $teamId, $leaderId, $side) {
    $stmt = $pdo->prepare("SELECT id, username, avatar_url FROM users WHERE team_id = :tid");
    $stmt->execute([':tid' => $teamId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$players) return;

    // przerzucamy lidera na początek
    usort($players, function($a, $b) use ($leaderId) {
        if ($a['id'] == $leaderId) return -1;
        if ($b['id'] == $leaderId) return 1;
        return 0;
    });

    foreach ($players as $player) {
        $avatar = $player['avatar_url'] ? htmlspecialchars($player['avatar_url']) : 'assets/img/avatar_default.png';
        $username = htmlspecialchars($player['username']);
        $isLeader = $player['id'] == $leaderId;

        echo "<div class='lobby-player {$side}' data-userid='{$player['id']}'>
                <img src='{$avatar}' alt=''>
                <p>{$username} &nbsp;".($isLeader ? " <i class='fa-solid fa-crown' style='color:gold'></i>" : "")."</p>
              </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZSN Champions III</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?=time()?>">
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js"></script>
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
        <?php include 'src/navbar.php'; ?>
        <div class="content">
            <div class="lobby-title">
                <h1 class="glowing"><?= htmlspecialchars($lobby['team1_name']) ?></h1>
                <h1 class="glowing">VS</h1>
                <h1 class="glowing"><?= htmlspecialchars($lobby['team2_name']) ?></h1>
            </div>
            <div class="lobby-container">
                <div class="player-list-lobby team1">
                    <h3><?= htmlspecialchars($lobby['team1_name']) ?></h3>
                    <?php renderPlayers($pdo, $lobby['team1_id'], $lobby['team1_leader'], 'left'); ?>
                </div>
                <?php
                    $maps = ["Mirage", "Dust II", "Inferno", "Overpass", "Anubis", "Nuke", "Ancient"];
                    $boType = "BO3"; // <- na razie na sztywno, później z bazy: $lobby['bo_type']
                ?>
                <div class="lobby-info">
                    <h3>Map Veto (<?= $boType ?>)</h3>
                    <div id="map-veto">
                        <p><span id="veto-stage">Team1 ban</span></p>
                        <p>Pozostało: <span id="veto-timer">30</span>s</p>
                        <div class="timer-bar">
                            <div class="timer-fill" id="timer-fill"></div>
                        </div>
                        <div class="map-pool">
                            <?php foreach ($maps as $map): ?>
                                <?php $mapNameFormatted = str_replace(' ', '', $map) ?>
                                <button class="map-btn" data-map="<?= $map ?>">
                                    <img src="assets/img/maps/<?= strtolower($mapNameFormatted) ?>.jpeg?v=10112025">
                                    <span><?= $mapNameFormatted ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="veto-log">
                            <ul id="veto-history">
                                <li>Test</li>
                            </ul>
                        </div>
                    </div>
                    <div id="match-setup" style="display:none; text-align:center; margin-top:20px;">
                        <h3>Wybrane mapy</h3>
                        <div id="final-maps" class="map-pool"></div>
                        <p id="match-status">Oczekiwanie na graczy..</p>
                        <div class="server-info" style="margin-top:20px;">
                            <div style="display:flex; justify-content:center; align-items:center; gap:10px; margin-top:10px;">
                                <input type="text" id="server-ip" value="" readonly>
                                <button id="copy-ip" class="btn">Kopiuj IP</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="player-list-lobby team2">
                    <h3><?= htmlspecialchars($lobby['team2_name']) ?></h3>
                    <?php renderPlayers($pdo, $lobby['team2_id'], $lobby['team2_leader'], 'right'); ?>
                </div>
            </div>
            <div class="game-container">
                <h3 id="score"></h3>
                <div id="current-map"></div>
                <div class="game-info">
                    <div class="scoreboard left">
                        <table>
                            <tr>
                                <th><i class="fa-solid fa-user"></i></th>
                                <th><i class="fa-solid fa-crosshairs"></i></th>
                                <th><i class="fa-solid fa-skull-crossbones"></th>
                                <th><i class="fa-solid fa-handshake"></i></th>
                                <th>K/D</th>
                                <th><i class="fa-solid fa-bullseye"></th>
                                <th>HS%</th>
                                <th><i class="fa-solid fa-star"></i></th>
                            </tr>
                        </table>
                    </div>
                    <div class="scoreboard right">
                        <table>
                            <tr>
                                <th><i class="fa-solid fa-user"></i></th>
                                <th><i class="fa-solid fa-crosshairs"></i></th>
                                <th><i class="fa-solid fa-skull-crossbones"></th>
                                <th><i class="fa-solid fa-handshake"></i></th>
                                <th>K/D</th>
                                <th><i class="fa-solid fa-bullseye"></th>
                                <th>HS%</th>
                                <th><i class="fa-solid fa-star"></i></th>
                            </tr>
                        </table>
                    </div>
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
    document.addEventListener("DOMContentLoaded", () => {
        const vetoStage = document.getElementById("veto-stage");
        const vetoHistory = document.getElementById("veto-history");
        const mapButtons = document.querySelectorAll(".map-btn");
        const playerCards = document.querySelectorAll(".lobby-player");
        const matchStatus = document.getElementById("match-status");
        const mapVeto = document.getElementById("map-veto");
        const content = document.querySelector(`.content`);
        const playerlistleft = document.querySelector(`.player-list-lobby.team1`);
        const playerlistright = document.querySelector(`.player-list-lobby.team2`);
        const userTeam = "<?= $_SESSION['team_id'] == $lobby['team1_id'] ? 'Team1' : 'Team2' ?>";
        const userId = <?= (int)$_SESSION['id'] ?>;
        const team1Leader = <?= (int)$lobby['team1_leader'] ?>;
        const team2Leader = <?= (int)$lobby['team2_leader'] ?>;
        const isLeader = (userTeam === "Team1" && userId === team1Leader) || (userTeam === "Team2" && userId === team2Leader);

        let vetoInterval = 1000;
        let vetoTimer;
        let gameTimer;

        let stages = [
            {action: "ban", team: "Team1"},
            {action: "ban", team: "Team2"},
            {action: "pick", team: "Team1"},
            {action: "pick", team: "Team2"},
            {action: "ban", team: "Team1"},
            {action: "ban", team: "Team2"},
            {action: "decider", team: "System"}
        ];

        const lobbyId = <?= $lobbyId ?>;
        let currentStage = 0;
        let availableMaps = Array.from(mapButtons).map(b => b.dataset.map);

        async function fetchVeto() {
            try {
                const res = await fetch(`src/get_veto.php?lobby_id=${lobbyId}`);
                const data = await res.json();
                if (!data.success && data.success !== undefined) {
                    console.error('get_veto error', data);
                    return;
                }
                // odśwież widok
                vetoHistory.innerHTML = "";
                // przywróć klasy
                mapButtons.forEach(btn => {
                    btn.classList.remove('banned', 'picked');
                    btn.disabled = false;
                });

                availableMaps = Array.from(mapButtons).map(b => b.dataset.map);
                currentStage = data.current_stage;

                data.veto.forEach(v => {
                    const el = document.querySelector(`[data-map="${v.map_name}"]`);
                    let who = "";

                    if (v.team === "System") {
                        who = "System";
                    } else if (v.team === userTeam) {
                        who = "Twoja drużyna";
                    } else {
                        who = "Przeciwnik";
                    }

                    if (v.action === "ban" && el) {
                        el.classList.add("banned");
                        el.disabled = true;
                        availableMaps = availableMaps.filter(m => m !== v.map_name);
                        vetoHistory.innerHTML += `<li>${who} banuje ${v.map_name}</li>`;
                    } else if (v.action === "pick" && el) {
                        el.classList.add("picked");
                        el.disabled = true;
                        availableMaps = availableMaps.filter(m => m !== v.map_name);
                        vetoHistory.innerHTML += `<li>${who} wybiera ${v.map_name}</li>`;
                    } else if (v.action === "decider") {
                        vetoHistory.innerHTML += `<li>Decider: ${v.map_name}</li>`;
                        el.classList.add("picked");
                        el.disabled = true;
                    }
                });

                if (currentStage < stages.length) {
                    const stage = stages[currentStage];
                    let who = "";
                    
                    if (stage.team === "System") {
                        who = "System wybiera mapę decydującą";
                        content.classList.remove("enemy-turn");
                        content.classList.remove("your-turn");
                        vetoStage.style.color = "#FFFFFF";
                    } else if (stage.team === userTeam) {
                        who = `Twoja drużyna ${stage.action === "ban" ? "banuje" : "wybiera"}`;
                        content.classList.add("your-turn");
                        content.classList.remove("enemy-turn");
                        vetoStage.style.color = `${stage.action === "ban" ? "#FF4444" : "#44FF44"}`;
                    } else {
                        who = `Przeciwnik ${stage.action === "ban" ? "banuje" : "wybiera"}`;
                        content.classList.add("enemy-turn");
                        content.classList.remove("your-turn");
                        vetoStage.style.color = "#FFFFFF";
                    }
                    if (stage.team === "Team1") {
                        playerlistleft.classList.add("turn");
                        playerlistright.classList.remove("turn");
                    } else if (stage.team === "Team2") {
                        playerlistright.classList.add("turn");
                        playerlistleft.classList.remove("turn");
                    } else {
                        playerlistleft.classList.remove("turn");
                        playerlistright.classList.remove("turn");
                    }
                    vetoStage.textContent = who;
                    const remaining = 30 - (data.elapsed || 0);
                    document.getElementById("veto-timer").textContent = remaining > 0 ? remaining : 0;
                    const fill = document.getElementById("timer-fill");
                    const percent = (remaining / 30) * 100;
                    fill.style.width = percent + "%";

                    if (stage.action === "decider" && availableMaps.length === 1) {
                        try {
                            const res = await fetch("src/save_veto.php", {
                                method: "POST",
                                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                                body: new URLSearchParams({
                                    lobby_id: lobbyId,
                                    stage: currentStage,
                                    action: "decider",
                                    team: "System",
                                    map: availableMaps[0]
                                })
                            });
                            const json = await res.json();
                            if (!json.success) {
                                console.warn("auto-decider fail:", json);
                            } else {
                                await fetchVeto();
                            }
                        } catch (err) {
                            console.error("auto-decider error:", err);
                        }
                    }
                } else {
                    vetoStage.textContent = "Mapa decydująca!";
                    document.getElementById("veto-timer").textContent = "0";
                    
                    if (currentStage >= stages.length) {
                        const matchSetup = document.getElementById("match-setup");
                        const finalMaps = document.getElementById("final-maps");

                        mapVeto.style.display = "none";
                        finalMaps.innerHTML = "";
                        data.veto
                            .filter(v => v.action === "pick" || v.action === "decider")
                            .forEach(v => {
                                mapNameFormatted = v.map_name.replaceAll(' ', '')
                                finalMaps.innerHTML += `
                                    <div class="map-picked"}">
                                        <img src="assets/img/maps/${mapNameFormatted.toLowerCase()}.jpeg?v=10112025">
                                        <span>${v.map_name}</span>
                                    </div>`;
                            });

                        // Pokaż sekcję setup natychmiast
                        matchSetup.style.display = "block";
                        
                        // Ustaw komunikat na czas oczekiwania
                        matchStatus.textContent = "Przygotowywanie serwera..."; // <-- Nowy tekst statusu
                        
                        // Ukryj pole IP i przycisk (są puste, więc wystarczy upewnić się, że nie wyświetlają niczego ważnego)
                        // Będą one widoczne, ale puste, dopóki fetchGame ich nie wypełni
                        document.getElementById("server-ip").value = "";
                        
                        // przełącz z veto na game
                        clearInterval(vetoTimer);
                        console.log("Polling veto OFF. Waiting 10s to start game polling...");

                        // Użyj setTimeout, aby opóźnić start pollingu o 10 sekund
                        setTimeout(() => {
                            console.log("Starting game polling and server IP fetch after 10s.");
                            
                            // Tekst statusu zostanie zaktualizowany przez fetchGame na "Oczekiwanie na graczy..."
                            fetchGame(); // Pierwsze pobranie po 10 sekundach
                            gameTimer = setInterval(fetchGame, 10000); // Polling co 10 sekund
                        }, 10000); // 10000 ms = 10 sekund
                    }
                }
            } catch (err) {
                console.error("fetchVeto error:", err);
            }
        }

        async function fetchGame() {
            try {
                const res = await fetch(`src/get_game.php?lobby_id=${lobbyId}`);
                const data = await res.json();
                if (!data.success) {
                    console.error('get_game error', data);
                    return;
                }
                const lobbyContainer = document.querySelector(".lobby-container");
                const gameContainer = document.querySelector(".game-container");
                const serverIpInput = document.getElementById("server-ip");

                const currentMapDiv = document.getElementById("current-map");
                const leftTable = document.querySelector(".scoreboard.left table tbody");
                const rightTable = document.querySelector(".scoreboard.right table tbody");
                console.log('Fetched game phase:', data.phase);
                if (data.phase === "waiting") {
                    lobbyContainer.style.display = "flex";
                    gameContainer.style.display = "none";
                    const readyUntil = new Date(data.ready_until).getTime();
                    const now = new Date().getTime();
                    const diff = Math.max(0, Math.floor((readyUntil - now) / 1000));

                    matchStatus.textContent = `Oczekiwanie na rozpoczęcie gry...`;
                    serverIpInput.value = data.server_ip;
                    return;
                }

                if (data.phase === "walkover") {
                    lobbyContainer.style.display = "none";
                    gameContainer.style.display = "flex";
                    const msg = data.winner === <?= $lobby['team1_id'] ?> 
                        ? "Walkower! Wygrywa Twoja drużyna 💪"
                        : "Walkower! Przeciwnik wygrywa 😔";
                    document.getElementById("score").textContent = msg;
                    return;
                }

                if (data.phase === "playing") {
                    lobbyContainer.style.display = "none";
                    gameContainer.style.display = "flex";
                    document.getElementById("score").textContent =
                        `${data.score.team1} : ${data.score.team2}`;
                    currentMapDiv.innerHTML = `
                            <div class="map-picked active started">
                                <img src="assets/img/maps/${data.active_map_name.toLowerCase()}.jpeg?v=10112025">
                                <span>${data.active_map_name}</span>
                            </div>`;
                    // fetch statsy
                    fetch(`src/get_match_stats.php?lobby_id=${lobbyId}`)
                        .then(r => r.json())
                        .then(stats => {
                            if (!stats.success) {console.error(stats.error); return};

                            // wyczyść stare wiersze
                            leftTable.innerHTML = `
                                <tr>
                                    <th><i class="fa-solid fa-user"></i></th>
                                    <th><i class="fa-solid fa-crosshairs"></i></th>
                                    <th><i class="fa-solid fa-skull-crossbones"></th>
                                    <th><i class="fa-solid fa-handshake"></i></th>
                                    <th>K/D</th>
                                    <th><i class="fa-solid fa-bullseye"></th>
                                    <th>HS%</th>
                                    <th><i class="fa-solid fa-star"></i></th>
                                </tr>`;
                            rightTable.innerHTML = leftTable.innerHTML;

                            stats.players.forEach(p => {
                                const kd = p.deaths > 0 ? (p.kills / p.deaths).toFixed(2) : p.kills.toFixed(2);
                                const hsPerc = p.kills > 0 ? Math.round((p.headshots / p.kills) * 100) : 0;

                                const row = `
                                    <tr>
                                        <td class="stats-player"><img src="${p.avatar_url}"><a href="profile.php?id=${p.user_id}">${p.username}</a></td>
                                        <td>${p.kills}</td>
                                        <td>${p.deaths}</td>
                                        <td>${p.assists}</td>
                                        <td>${kd}</td>
                                        <td>${p.headshots}</td>
                                        <td>${hsPerc}%</td>
                                        <td>${p.mvps}</td>
                                    </tr>
                                `;
                                if (p.team_id == <?= $lobby['team1_id'] ?>) {
                                    leftTable.innerHTML += row;
                                } else {
                                    rightTable.innerHTML += row;
                                }
                            });
                        })
                        .catch(err => console.error("stats fetch error", err));
                    }
            } catch (err) {
                console.error("fetchGame error:", err);
            }
        }

        // kliknięcie mapy -> zapis via save_veto (serwer sprawdzi stage)
        mapButtons.forEach(btn => {
            if (!isLeader) return;
            btn.addEventListener("click", async () => {
                console.log('click', btn.dataset.map);
                if (currentStage >= stages.length) return;
                const stage = stages[currentStage];
                const map = btn.dataset.map;
                if (stage.team !== userTeam && stage.action !== "decider") return;
                if (!availableMaps.includes(map)) return;

                try {
                    const res = await fetch("src/save_veto.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            lobby_id: lobbyId,
                            stage: currentStage,
                            action: stage.action,
                            team: stage.team,
                            map: map
                        })
                    });
                    const json = await res.json();
                    if (!json.success) {
                        console.warn("save_veto:", json);
                    }
                    await fetchVeto();
                } catch (err) {
                    console.error("save_veto error:", err);
                }
            });
        });

        playerCards.forEach(card => {
            card.addEventListener("click", () => {
                const uid = card.dataset.userid;
                if (!uid) return;
                if (uid == userId) {
                     window.location.href = `profile.php`; 
                     return; 
                } else {
                    window.location.href = `profile.php?id=${uid}`; 
                }
            });
        });

        document.getElementById("copy-ip").addEventListener("click", () => {
            const ip = document.getElementById("server-ip");
            ip.select();
            ip.setSelectionRange(0, 99999);
            navigator.clipboard.writeText('connect '+ ip.value).then(() => {
                alert("IP skopiowane: " + ip.value);
            });
        });

        // polling co 2s
        fetchVeto();
        vetoTimer = setInterval(fetchVeto, vetoInterval);
    });
    </script>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>
