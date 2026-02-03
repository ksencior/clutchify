<?php
session_start();
include_once '../src/connect_db.php';

if (!isset($_POST['mecz_id']) || !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak danych']);
    exit;
}

$meczId = (int)$_POST['mecz_id'];
$userId = (int)$_SESSION['id'];

// pobierz team_id usera
$stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = :uid");
$stmt->execute([':uid' => $userId]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res || !$res['team_id']) {
    echo json_encode(['success' => false, 'message' => 'Brak drużyny']);
    exit;
}

$teamId = $res['team_id'];

// sprawdz czy user jest juz ready
$stmt = $pdo->prepare("SELECT * FROM ready_players WHERE mecz_id=:mid AND user_id=:uid");
$stmt->execute([':mid' => $meczId, ':uid' => $userId]);
$readyRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($readyRow) {
    // usun gotowosc
    $pdo->prepare("DELETE FROM ready_players WHERE user_id=:id")->execute([':id' => $readyRow['user_id']]);
    $ready = false;
} else {
    // dodaj gotowosc
    $pdo->prepare("INSERT INTO ready_players (mecz_id, user_id, team_id) VALUES (:mid, :uid, :tid)")
        ->execute([':mid' => $meczId, ':uid' => $userId, ':tid' => $teamId]);
    $ready = true;
}

// policz aktualne liczniki
$stmt = $pdo->prepare("SELECT 
    COUNT(*) AS ready_count_all,
    SUM(CASE WHEN r.team_id = :tid THEN 1 ELSE 0 END) AS ready_count_team
FROM ready_players r
WHERE r.mecz_id = :mid
");
$stmt->execute([':mid' => $meczId, ':tid' => $teamId]);
$counts = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'ready'   => $ready,
    'ready_count_all'  => (int)$counts['ready_count_all'],
    'ready_count_team' => (int)$counts['ready_count_team']
]);
