<?php 
include_once 'src/connect_db.php';
date_default_timezone_set("Europe/Warsaw");
// pseudo-cron: sprawdź czy trzeba wygenerować mecze
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE type = 'Zapisy' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event && strtotime($event['ending_at']) <= time()) {
        $check = $pdo->query("SELECT COUNT(*) FROM mecze WHERE round = 1")->fetchColumn();
        if ($check == 0) {
            // pobierz drużyny
            $stmt = $pdo->query("
                SELECT t.id
                FROM teams t
                JOIN users u ON t.id = u.team_id
                GROUP BY t.id
                HAVING COUNT(u.id) >= 5
            ");
            $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($teams) >= 2) {
                $pdo->beginTransaction();
                try {
                    shuffle($teams);
                    $matchNumber = 1;
                    $currentDate = new DateTime('next week');
                    $currentDate->modify('18:00');
                    $chunks = array_chunk($teams, 2);
                    foreach ($chunks as $pair) {
                        $team1 = $pair[0]['id'];
                        $team2 = isset($pair[1]) ? $pair[1]['id'] : null;

                        if ($team2 === null) {
                            $stmt = $pdo->prepare("
                                INSERT INTO mecze (team1, team2, round, match_number, termin, team1_wins, winner_id)
                                VALUES (?, NULL, 1, ?, ?, 2, ?)
                            ");
                            $stmt->execute([$team1, $matchNumber, $currentDate->format('Y-m-d H:i:s'), $team1]);
                        } else {
                            $stmt = $pdo->prepare("
                                INSERT INTO mecze (team1, team2, round, match_number, termin)
                                VALUES (?, ?, 1, ?, ?)
                            ");
                            $stmt->execute([$team1, $team2, $matchNumber, $currentDate->format('Y-m-d H:i:s')]);
                        }
                        $matchNumber++;
                        $currentDate->modify('+1 day');
                    }
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            }
        }
    }
} catch (PDOException $e) {
    error_log('PSEUDO CRON ERROR: ' . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['id']]);
    $userTeamId = $stmt->fetchColumn();
    $_SESSION['team_id'] = $userTeamId;
} catch (PDOException $e) {
    $userTeamId = null;
}

echo '
<div class="sidebar-right">
    <div class="profile">
        <img src="
';
echo (isset($_SESSION['avatar_url']) && !empty($_SESSION['avatar_url']))? $_SESSION['avatar_url'] : 'img/avatar_default.png'; 
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == true;
$isSpectator = isset($_SESSION['isSpectator']) && $_SESSION['isSpectator'] == true;
echo '
" alt="Avatar uzytkownika" class="avatar" title="Profil" onclick="window.location.href=\'profile.php\'">
    </div>
    <div class="options">
        <a href="settings.php" title="Ustawienia"><i class="fa-solid fa-gear"></i></a>';
    if ($isAdmin) {
        echo '<a href="admin.php" title="Panel admina"><i class="fa-solid fa-user-shield"></i></a>';
    }
    if ($isSpectator) {
        echo '<a href="spectate.php" title="Oglądaj"><i class="fa-solid fa-eye"></i></a>';
    }
    echo '<div class="line"></div>
        <a id="notifications" title="Powiadomienia"><i class="fa-solid fa-bell"></i></a>';
    if (!$userTeamId) {
        echo '<a id="team-chat" style="display: none;"><i class="fa-solid fa-user-group"></i></a>';
    } else {
        echo '<a id="team-chat" title="Czat drużynowy"><i class="fa-solid fa-user-group"></i></a>';
    }
    echo '
        <div class="line"></div>
    </div>
    <div class="socials">
        <a href="https://discord.gg/VCdBre9fVv" title="Discord" target="_blank" style="justify-self: flex-end;"><img class="discord-button" src="img/discord-icon.jpg" style="width: 100%; border-radius: 8px;"></a>
    </div>
</div>
';

?>