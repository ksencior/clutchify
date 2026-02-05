<?php
session_start();
header('Content-Type: application/json');
include_once 'connect_db.php';

if (!isset($_SESSION['logged']) || !isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Nie jesteś zalogowany."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$notifId = (int)($data['notifId'] ?? 0);
$accept = $data['accept'] ?? false;
$userId = $_SESSION['id'];

try {
    // Pobierz zaproszenie i sprawdź właściciela
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = :id AND user_id = :uid AND type = 'team-request'");
    $stmt->execute([':id' => $notifId, ':uid' => $userId]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notif) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Nie znaleziono zaproszenia."]);
        exit;
    }

    // Wyciągnij nick osoby wysyłającej z treści np. "SyloX5 dodał Cię do znajomych."
    preg_match('/Otrzymałeś zaproszenie do drużyny (.+)/', $notif['content'], $matches);
    $teamName = $matches[1] ?? null;

    if (!$teamName) {
        echo json_encode(["success" => false, "message" => "Błąd serwera: Nie udalo się rozpoznać nazwy drużyny."]);
        throw new Exception("Nie udało się rozpoznać nazwy drużyny.");
    }

    $stmt = $pdo->prepare("SELECT id FROM teams WHERE nazwa = :name");
    $stmt->execute([':name' => $teamName]);
    $teamId = $stmt->fetchColumn();

    if (!$teamId) {
        echo json_encode(["success" => false, "message" => "Błąd serwera: Nie znaleziono drużyny o podanej nazwie."]);
        throw new Exception("Nie znaleziono drużyny o podanej nazwie.");
    }

    if ($accept) {
        $stmt = $pdo->prepare("SELECT team_id FROM users WHERE id = :uid");
        $stmt->execute([':uid' => $userId]);
        $currentTeamId = $stmt->fetchColumn();

        if ($currentTeamId !== null) {

            echo json_encode([
                "success" => false,
                "message" => "Już należysz do drużyny. Najpierw opuść obecną drużynę."
            ]);
            exit;
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE team_id = :tid");
        $stmt->execute([':tid' => $teamId]);
        $teamCount = $stmt->fetchColumn();

        if ($teamCount >= 5) {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id");
            $stmt->execute([':id' => $notifId]);

            echo json_encode([
                "success" => false,
                "message" => "Drużyna jest pełna. Jeśli jesteś rezerwowym, administracja zmieni cię w razie potrzeby."
            ]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET team_id = :tid WHERE id = :uid");
        $stmt->execute([':tid' => $teamId, ':uid' => $userId]);
        $_SESSION['team_id'] = $teamId;
        $stmt = $pdo->prepare("SELECT COUNT(team_id) FROM users WHERE team_id = :tid");
        $stmt->execute([':tid' => $teamId]);
        $memberCount = $stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id");
    $stmt->execute([':id' => $notifId]);

    echo json_encode(["success" => true, "message" => $accept ? "Zaakceptowano zaproszenie." : "Odrzucono zaproszenie."]);


} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Błąd serwera", "details" => $e->getMessage()]);
}

