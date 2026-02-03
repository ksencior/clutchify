<?php
require_once "connect_db.php";

$lobbyId = (int)($_GET['lobby_id'] ?? 0);

$q = $pdo->prepare("SELECT g.id FROM games g 
    JOIN lobbies l ON l.id = g.lobby_id 
    WHERE l.id = ? AND g.status IN ('waiting', 'playing') ORDER BY g.id DESC LIMIT 1");
$q->execute([$lobbyId]);
$game = $q->fetch();

if (!$game) {
    echo json_encode(["success" => false, "error" => "no game"]);
    exit;
}

$stmt = $pdo->prepare("SELECT gp.*, u.username, u.avatar_url, t.id AS team_id, t.nazwa AS team_name
    FROM game_players gp
    JOIN users u ON gp.user_id = u.id
    JOIN teams t ON gp.team_id = t.id
    WHERE gp.game_id = ? 
    ORDER BY gp.kills DESC, gp.deaths ASC, gp.assists DESC, gp.user_id ASC");
$stmt->execute([$game['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "players" => $rows
]);
