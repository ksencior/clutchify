<?php
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['id']) || !isset($_GET['mecz_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Brak danych']);
    exit;
}

$userId = $_SESSION['id'];
$meczId = (int)$_GET['mecz_id'];

// znajdz drużynę usera
$stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = :uid");
$stmt->execute([':uid' => $userId]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    echo json_encode(['success' => false, 'message' => 'Brak drużyny']);
    exit;
}
$teamId = $team['team_id'];

// ile ready łącznie i ile w mojej drużynie
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS ready_count_all,
        SUM(CASE WHEN r.team_id = :tid THEN 1 ELSE 0 END) AS ready_count_team
    FROM ready_players r
    WHERE r.mecz_id = :mid
");
$stmt->execute([':mid' => $meczId, ':tid' => $teamId]);
$counts = $stmt->fetch(PDO::FETCH_ASSOC);

// ile graczy w obu teamach razem (np. 10)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_players
    FROM users 
    WHERE team_id IN (SELECT team1 FROM mecze WHERE id = :mid1 
                      UNION 
                      SELECT team2 FROM mecze WHERE id = :mid2)
");
$stmt->execute([':mid1' => $meczId, ':mid2' => $meczId]);
$totalPlayers = $stmt->fetchColumn();
$lobbyId = null;
$isStarted = false;
if ((int)$counts['ready_count_all'] == 10) {
    $stmt = $pdo->prepare("DELETE FROM ready_players WHERE mecz_id = :mid");
    $stmt->execute([':mid' => $meczId]);
    $lobbyId = random_int(100000, 999999);
    $stmt = $pdo->prepare("INSERT INTO lobbies (`id`, `mecz_id`, `team1_id`, `team2_id`) VALUES (:lid, :mid, :t1, :t2)");
    $stmt->execute([
        ':lid' => $lobbyId,
        ':mid' => $meczId,
        ':t1' => (int)$pdo->query("SELECT team1 FROM mecze WHERE id = $meczId")->fetchColumn(),
        ':t2' => (int)$pdo->query("SELECT team2 FROM mecze WHERE id = $meczId")->fetchColumn()
    ]);
} else {
    $stmt = $pdo->prepare("SELECT id FROM lobbies WHERE mecz_id = :mid");
    $stmt->execute([':mid' => $meczId]);
    $lobbyId = $stmt->fetchColumn();
    if ($lobbyId) {
        $isStarted = true;
    }
}

echo json_encode([
    'success' => true,
    'ready_count_all' => (int)$counts['ready_count_all'],
    'ready_count_team' => (int)$counts['ready_count_team'],
    'total_players' => (int)$totalPlayers,
    'lobby_id' => $lobbyId,
    'is_started' => $isStarted
]);






