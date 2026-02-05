<?php
session_start();
include_once 'connect_db.php'; // Upewnij się, że ścieżka do połączenia z bazą jest poprawna

header('Content-Type: application/json');

// 1. Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['logged']) || !$_SESSION['logged'] || empty($_SESSION['team_id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak uprawnień lub niezalogowany.']);
    exit();
}

// Sprawdzenie, czy otrzymano ID gracza
if (!isset($_POST['player_id']) || !is_numeric($_POST['player_id'])) {
    echo json_encode(['success' => false, 'message' => 'Niepoprawne ID gracza.']);
    exit();
}

$teamId = $_SESSION['team_id'];
$playerIdToKick = $_POST['player_id'];
$currentUserId = $_SESSION['id'];

// 2. Weryfikacja: Lider i Status Zapisów

try {
    // Sprawdzenie, czy aktualny użytkownik jest Liderem tej drużyny
    $sqlLeader = "SELECT leader_id FROM teams WHERE id = :teamid";
    $stmtLeader = $pdo->prepare($sqlLeader);
    $stmtLeader->execute([':teamid' => $teamId]);
    $team = $stmtLeader->fetch(PDO::FETCH_ASSOC);

    if (!$team || $team['leader_id'] != $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Tylko Lider może wyrzucać graczy.']);
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
    
    // Sprawdzenie, czy gracz do wyrzucenia faktycznie jest w tej drużynie
    $sqlMember = "SELECT id FROM users WHERE id = :playerid AND team_id = :teamid";
    $stmtMember = $pdo->prepare($sqlMember);
    $stmtMember->execute([':playerid' => $playerIdToKick, ':teamid' => $teamId]);
    if (!$stmtMember->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Gracz nie należy do tej drużyny.']);
        exit();
    }

    // 3. Sprawdzenie, czy Lider nie próbuje wyrzucić siebie
    if ($playerIdToKick == $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Lider nie może wyrzucić siebie.']);
        exit();
    }

    // 4. Aktualizacja bazy danych (wyrzucenie gracza)
    $sqlKick = "UPDATE users SET team_id = NULL WHERE id = :playerid AND team_id = :teamid";
    $stmtKick = $pdo->prepare($sqlKick);
    if ($stmtKick->execute([':playerid' => $playerIdToKick, ':teamid' => $teamId])) {
        echo json_encode(['success' => true, 'message' => 'Gracz został wyrzucony.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Błąd aktualizacji bazy danych.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Błąd serwera: ' . $e->getMessage()]);
}

?>
