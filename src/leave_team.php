<?php
session_start();
include_once 'connect_db.php'; // Upewnij się, że ścieżka do połączenia z bazą jest poprawna

header('Content-Type: application/json');

// 1. Sprawdzenie, czy użytkownik jest zalogowany i ma drużynę
if (!isset($_SESSION['logged']) || !$_SESSION['logged'] || empty($_SESSION['team_id'])) {
    echo json_encode(['success' => false, 'message' => 'Niezalogowany lub nie masz drużyny.']);
    exit();
}

$teamId = $_SESSION['team_id'];
$currentUserId = $_SESSION['id'];

// 2. Weryfikacja: Brak Lidera i Status Zapisów

try {
    // Sprawdzenie, czy aktualny użytkownik NIE jest Liderem
    $sqlLeader = "SELECT leader_id FROM teams WHERE id = :teamid";
    $stmtLeader = $pdo->prepare($sqlLeader);
    $stmtLeader->execute([':teamid' => $teamId]);
    $team = $stmtLeader->fetch(PDO::FETCH_ASSOC);

    if ($team && $team['leader_id'] == $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Lider nie może opuścić drużyny. Musisz przekazać lidera lub usunąć drużynę.']);
        exit();
    }
    
    // Sprawdzenie, czy zapisy są otwarte
    $sqlEvent = "SELECT ending_at FROM events WHERE `type`='Zapisy' LIMIT 1";
    $stmtEvent = $pdo->prepare($sqlEvent);
    $stmtEvent->execute();
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if (!$event || !isset($event['ending_at']) || (new DateTime() > new DateTime($event['ending_at']))) {
        echo json_encode(['success' => false, 'message' => 'Zapisy zostały już zakończone.']);
        exit();
    }

    // 3. Aktualizacja bazy danych (opuszczenie drużyny)
    $pdo->beginTransaction();

    $sqlLeave = "UPDATE users SET team_id = NULL WHERE id = :userid AND team_id = :teamid";
    $stmtLeave = $pdo->prepare($sqlLeave);
    
    if ($stmtLeave->execute([':userid' => $currentUserId, ':teamid' => $teamId])) {
        // Usuń team_id z sesji po pomyślnym opuszczeniu
        unset($_SESSION['team_id']);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Pomyślnie opuszczono drużynę.']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Błąd opuszczania drużyny.']);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Błąd serwera: ' . $e->getMessage()]);
}

?>
