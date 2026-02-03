<?php
require_once "connect_db.php";

try {
    $lobbyId = (int)$_GET['lobby_id'];
$q = $pdo->prepare("SELECT * FROM games WHERE lobby_id = ? AND status IN ('waiting', 'playing') ORDER BY id DESC LIMIT 1");
$q->execute([$lobbyId]);
$game = $q->fetch();

if (!$game) {
    echo json_encode(["success" => false]);
    exit;
}

$players = $pdo->prepare("SELECT user_id, team_id, joined_server 
    FROM game_players WHERE game_id = ?");
$players->execute([$game['id']]);
$players = $players->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();
$readyUntil = new DateTime($game['server_ready_until']);
$phase = $game['status'];

// jeśli jesteśmy w "waiting" i czas minął -> sprawdzamy kto dołączył
if ($phase === "waiting" && $now > $readyUntil) {
    $teamCounts = [];
    $teamJoined = [];

    foreach ($players as $p) {
        $teamCounts[$p['team_id']] = ($teamCounts[$p['team_id']] ?? 0) + 1;
        if ($p['joined_server']) {
            $teamJoined[$p['team_id']] = ($teamJoined[$p['team_id']] ?? 0) + 1;
        }
    }

    $team1Id = $game['team1'];
    $team2Id = $game['team2'];

    $team1Ready = ($teamJoined[$team1Id] ?? 0) >= ($teamCounts[$team1Id] ?? 0);
    $team2Ready = ($teamJoined[$team2Id] ?? 0) >= ($teamCounts[$team2Id] ?? 0);

    if ($team1Ready && $team2Ready) {
        // zaczynamy mecz
        $pdo->prepare("UPDATE games SET status = 'playing' WHERE id = ?")
            ->execute([$game['id']]);
        $phase = "playing";
    }
}
$map_name = "";
$map_id = $game['current_map'];
switch ($map_id) {
    case "de_mirage":
        $map_name = "Mirage";
        break;
    case "de_inferno":
        $map_name = "Inferno";
        break;
    case "de_nuke":
        $map_name = "Nuke";
        break;
    case "de_overpass":
        $map_name = "Overpass";
        break;
    case "de_ancient":
        $map_name = "Ancient";
        break;
    case "de_dust2":
        $map_name = "Dust II";
        break;
    case "de_anubis":
        $map_name = "Anubis";
        break;
    default:
        $map_name = "Unknown";
        break;
}

echo json_encode([
    "success" => true,
    "phase" => $phase,
    "ready_until" => $game['server_ready_until'],
    "score" => [
        "team1" => $game['team1_score'],
        "team2" => $game['team2_score']
    ],
    "active_map_id" => $map_id,
    "active_map_name" => $map_name,
    "players" => $players,
    "winner" => $game['winner_id'] ?? null,
    "server_ip" => $game['server_ip']
]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}