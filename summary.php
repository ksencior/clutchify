<?php 
include_once 'src/core/connect_db.php';
date_default_timezone_set("Europe/Warsaw");

error_reporting(E_ALL);
ini_set('display_errors', 1);
$isLeader = false;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
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

if (!empty($_GET['id']) && isset($_GET['id'])) {
    $matchId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT m.*, 
    t1.nazwa AS team1_name, 
    t2.nazwa AS team2_name, 
    t1.id AS team1_id, 
    t2.id AS team2_id, 
    t1.leader_id AS team1_leader, 
    t2.leader_id AS team2_leader
    FROM mecze m 
    JOIN teams t1 ON m.team1 = t1.id
    JOIN teams t2 ON m.team2 = t2.id
    WHERE m.id = :mid AND winner_id IS NOT NULL");
    $stmt->execute([':mid' => $matchId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$match) {
        redirect_to('login.php');
        exit;
    }


    $demoArchivePath = __DIR__ . "/demos/match_{$matchId}_demos.tar";
    $demoArchiveExists = file_exists($demoArchivePath);

    $stmt = $pdo->prepare("SELECT * FROM finished_games WHERE match_id = :mid");
    $stmt->execute([':mid' => $matchId]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM finished_stats WHERE match_id = :mid");
    $stmt->execute([':mid' => $matchId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    redirect_to('login.php');
    exit;
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
    <title>Podsumowanie | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?=time()?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
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
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <div class="lobby-title">
                <h1 class="glowing"><?= htmlspecialchars($match['team1_name']) ?></h1>
                <h1 class="glowing">VS</h1>
                <h1 class="glowing"><?= htmlspecialchars($match['team2_name']) ?></h1>
            </div>
            <div class="lobby-container">
                <div class="player-list-lobby">
                    <h3><?= htmlspecialchars($match['team1_name']) ?></h3>
                    <?php renderPlayers($pdo, $match['team1_id'], $match['team1_leader'], 'left'); ?>
                </div>
                <div class="match-summary">
                    <h3 class="score"><?= $match['team1_wins'] ?>:<?= $match['team2_wins'] ?></h3>
                    <br>
                    <div class="demo-download" style="text-align:center; margin-top:20px;">
                        <?php if ($demoArchiveExists): ?>
                            <a href="src/apis/download_demo.php?id=<?= $matchId ?>" class="btn" style="padding:10px 20px; background:#333; color:white; border-radius:10px; text-decoration:none;">
                                <i class="fa-solid fa-file-archive"></i> Pobierz demka (.tar)
                            </a>
                        <?php else: ?>
                            <p style="color:#888;">Brak zapisanych demek dla tego meczu.</p>
                        <?php endif; ?>
                    </div>
                    <br>
                    <a href="#games-summary" class='scroll-link'>Przejdź do podsumowania</a>
                </div>
                <div class="player-list-lobby">
                    <h3><?= htmlspecialchars($match['team2_name']) ?></h3>
                    <?php renderPlayers($pdo, $match['team2_id'], $match['team2_leader'], 'right'); ?>
                </div>
            </div>
            <div class="games-summary" id="games-summary">
                <h2>Podsumowanie gier</h2>
                <?php if (count($games) === 0): ?>
                    <p>Brak zapisanych gier dla tego meczu.</p>
                <?php else: ?>
                    <?php foreach ($games as $game): ?>
                        <?php 
                            $mapNameFormatted = str_replace('de_', '', $game['map_name']);
                            $mapNameCodename = null;
                            if ($mapNameFormatted === 'dust2') { $mapNameCodename = 'dustii'; }
                        ?>
                        <div class="game-summary">
                            <div class="g-summary-title">
                                <div class="map-picked" style="width: 30%; margin: auto;">
                                    <img src="assets/img/maps/<?= $mapNameCodename? $mapNameCodename : $mapNameFormatted ?>.jpeg?v=10112025" style="filter: blur(2px); grayscale(50%);">
                                    <span><?= $mapNameFormatted ?></span>
                                </div>
                            </div>
                            <div class="map-mvp">
                                <?php 
                                $stmt = $pdo->prepare("
                                    SELECT user_id, kills, deaths, assists, headshots, mvps,
                                        ROUND( (kills + assists) / GREATEST(deaths, 1), 2) AS kda
                                    FROM finished_stats
                                    WHERE game_id = :game_id AND match_id = :mid
                                    ORDER BY kda DESC, kills DESC, mvps DESC
                                    LIMIT 3
                                ");
                                $stmt->execute([':game_id' => $game['game_id'], ':mid' => $matchId]);
                                $bestPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($bestPlayers as $index => $bp) {
                                    $playerInfo = $pdo->query('SELECT username, avatar_url FROM users WHERE id = ' . (int)$bp['user_id'])->fetch(PDO::FETCH_ASSOC);
                                    if (!$playerInfo['avatar_url']) {
                                        $playerInfo['avatar_url'] = 'assets/img/avatar_default.png';
                                    }

                                    $places = ['Pierwsze', 'Drugie', 'Trzecie'];
                                    $placeClass = ['first-place', 'second-place', 'third-place'];

                                    // kd i hs%
                                    $kd = $bp['deaths'] > 0 ? round($bp['kills'] / $bp['deaths'], 2) : $bp['kills'];
                                    $hsPercent = $bp['kills'] > 0 ? round(($bp['headshots'] / $bp['kills']) * 100, 1) : 0;

                                    // najwazniejsza cecha
                                    $dominant = 'kills';
                                    $maxValue = $bp['kills'];

                                    if ($kd > 2.0 && $kd > $maxValue) { 
                                        $dominant = 'kd'; 
                                        $maxValue = $kd; 
                                    }
                                    if ($hsPercent > 60 && $hsPercent > $maxValue) { 
                                        $dominant = 'hs'; 
                                        $maxValue = $hsPercent; 
                                    }
                                    if ($bp['mvps'] >= 3 && $bp['mvps'] > $maxValue) { 
                                        $dominant = 'mvps'; 
                                        $maxValue = $bp['mvps']; 
                                    }

                                    // 🔹 Definiujemy różne układy statystyk
                                    switch ($dominant) {
                                        case 'kd':
                                            $stats = [
                                                ['icon' => 'fa-solid fa-chart-line', 'label' => "{$kd} K/D"],
                                                ['icon' => 'fa-solid fa-crosshairs', 'label' => "{$bp['kills']} Fragów"],
                                                ['icon' => 'fa-solid fa-handshake', 'label' => "{$bp['assists']} Asyst"]
                                            ];
                                            break;
                                        case 'hs':
                                            $stats = [
                                                ['icon' => 'fa-solid fa-bullseye', 'label' => "{$hsPercent}% HS"],
                                                ['icon' => 'fa-solid fa-crosshairs', 'label' => "{$bp['kills']} Fragów"],
                                                ['icon' => 'fa-solid fa-star', 'label' => "{$bp['mvps']} MVP"]
                                            ];
                                            break;
                                        case 'mvps':
                                            $stats = [
                                                ['icon' => 'fa-solid fa-star', 'label' => "{$bp['mvps']} MVP"],
                                                ['icon' => 'fa-solid fa-crosshairs', 'label' => "{$bp['kills']} Fragów"],
                                                ['icon' => 'fa-solid fa-handshake', 'label' => "{$bp['assists']} Asyst"]
                                            ];
                                            break;
                                        default:
                                            $stats = [
                                                ['icon' => 'fa-solid fa-crosshairs', 'label' => "{$bp['kills']} Fragów"],
                                                ['icon' => 'fa-solid fa-skull-crossbones', 'label' => "{$bp['deaths']} Śmierci"],
                                                ['icon' => 'fa-solid fa-handshake', 'label' => "{$bp['assists']} Asyst"]
                                            ];
                                    }

                                    // 🔹 Generujemy HTML
                                    echo "<div class='mvp-card {$placeClass[$index]}'>
                                            <p class='place'>{$places[$index]} miejsce</p>
                                            <img src='".$playerInfo['avatar_url']."' alt='avatar'>
                                            <div class='right-info'>
                                                <h3>". htmlspecialchars($playerInfo['username']) ."</h3>
                                                <div class='best-stats'>";
                                                foreach ($stats as $s) {
                                                    echo "<div class='stat'><i class='{$s['icon']}'></i><span>{$s['label']}</span></div>";
                                                }
                                    echo        "</div>
                                            </div>
                                        </div>";
                                }
                                ?>
                            </div>
                            <div class="scoreboard">
                                <h3><?= htmlspecialchars($match['team1_name']) ?> - <?= $game['team1_score'] ?></h3>
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
                                    <?php
                                        // filtrujemy graczy tej drużyny i tej mapy
                                        $teamPlayers = array_filter($players, function($p) use ($game) {
                                            return $p['team_id'] == $game['team1_id'] && $p['game_id'] == $game['game_id'];
                                        });

                                        // sortujemy wg K/D, kills, mvps
                                        usort($teamPlayers, function($a, $b) {
                                            $kdA = $a['deaths'] > 0 ? $a['kills'] / $a['deaths'] : $a['kills'];
                                            $kdB = $b['deaths'] > 0 ? $b['kills'] / $b['deaths'] : $b['kills'];
                                            if ($kdA == $kdB) {
                                                if ($a['kills'] == $b['kills']) {
                                                    return $b['mvps'] <=> $a['mvps']; // najwięcej MVP
                                                }
                                                return $b['kills'] <=> $a['kills']; // najwięcej fragów
                                            }
                                            return $kdB <=> $kdA; // najwięcej K/D
                                        });

                                        // generujemy wiersze
                                        foreach ($teamPlayers as $player) {
                                            $kd = $player['deaths'] > 0 ? round($player['kills'] / $player['deaths'], 2) : $player['kills'];
                                            $hsPercent = $player['kills'] > 0 ? round(($player['headshots'] / $player['kills']) * 100, 2) : 0;
                                            $playerInfo = $pdo->query('SELECT username, avatar_url FROM users WHERE id = ' . (int)$player['user_id'])->fetch(PDO::FETCH_ASSOC);
                                            if (!$playerInfo['avatar_url']) {
                                                $playerInfo['avatar_url'] = 'assets/img/avatar_default.png';
                                            }
                                            echo "<tr>
                                                    <td class='stats-player' style='width: 80%'><img src='".$playerInfo['avatar_url']."'><a href='profile.php?id=".$player['user_id']."'>". htmlspecialchars($playerInfo['username']) ."</a></td>
                                                    <td>{$player['kills']}</td>
                                                    <td>{$player['deaths']}</td>
                                                    <td>{$player['assists']}</td>
                                                    <td>{$kd}</td>
                                                    <td>{$player['headshots']}</td>
                                                    <td>{$hsPercent}%</td>
                                                    <td>{$player['mvps']}</td>
                                                </tr>";
                                        }
                                        ?>
                                </table>
                            </div>
                            <div class="scoreboard">
                                <h3><?= htmlspecialchars($match['team2_name']) ?> - <?= $game['team2_score'] ?></h3>
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
                                    <?php
                                        // filtrujemy graczy tej drużyny i tej mapy
                                        $teamPlayers = array_filter($players, function($p) use ($game) {
                                            return $p['team_id'] == $game['team2_id'] && $p['game_id'] == $game['game_id'];
                                        });

                                        // sortujemy wg K/D, kills, mvps
                                        usort($teamPlayers, function($a, $b) {
                                            $kdA = $a['deaths'] > 0 ? $a['kills'] / $a['deaths'] : $a['kills'];
                                            $kdB = $b['deaths'] > 0 ? $b['kills'] / $b['deaths'] : $b['kills'];
                                            if ($kdA == $kdB) {
                                                if ($a['kills'] == $b['kills']) {
                                                    return $b['mvps'] <=> $a['mvps']; // najwięcej MVP
                                                }
                                                return $b['kills'] <=> $a['kills']; // najwięcej fragów
                                            }
                                            return $kdB <=> $kdA; // najwięcej K/D
                                        });

                                        // generujemy wiersze
                                        foreach ($teamPlayers as $player) {
                                            $kd = $player['deaths'] > 0 ? round($player['kills'] / $player['deaths'], 2) : $player['kills'];
                                            $hsPercent = $player['kills'] > 0 ? round(($player['headshots'] / $player['kills']) * 100, 2) : 0;
                                            $playerInfo = $pdo->query('SELECT username, avatar_url FROM users WHERE id = ' . (int)$player['user_id'])->fetch(PDO::FETCH_ASSOC);
                                            if (!$playerInfo['avatar_url']) {
                                                $playerInfo['avatar_url'] = 'assets/img/avatar_default.png';
                                            }
                                            echo "<tr>
                                                    <td class='stats-player' style='width: 80%'><img src='".$playerInfo['avatar_url']."'><a href='profile.php?id=".$player['user_id']."'>". htmlspecialchars($playerInfo['username']) ."</a></td>
                                                    <td>{$player['kills']}</td>
                                                    <td>{$player['deaths']}</td>
                                                    <td>{$player['assists']}</td>
                                                    <td>{$kd}</td>
                                                    <td>{$player['headshots']}</td>
                                                    <td>{$hsPercent}%</td>
                                                    <td>{$player['mvps']}</td>
                                                </tr>";
                                        }
                                        ?>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    document.addEventListener("DOMContentLoaded", () => {
        const playerCards = document.querySelectorAll(".lobby-player");

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

        const link = document.querySelector(".scroll-link");
        const container = document.querySelector(".content");

        link.addEventListener("click", e => {
            e.preventDefault();
            const target = document.querySelector("#games-summary");
            container.scrollTo({
                top: target.offsetTop,
                behavior: "smooth"
            });
        });
    });
    </script>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>









