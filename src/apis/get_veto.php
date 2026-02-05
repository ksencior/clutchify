<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../core/connect_db.php';
date_default_timezone_set("Europe/Warsaw");

if (!isset($_GET['lobby_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Brak lobby_id']);
    exit;
}
$lobbyId = (int)$_GET['lobby_id'];

try {
    // pobierz aktualne veto
    $stmt = $pdo->prepare("SELECT * FROM map_veto WHERE lobby_id = :lid ORDER BY stage ASC");
    $stmt->execute([':lid' => $lobbyId]);
    $veto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // pobierz stage i last_action_time (nie lockujemy jeszcze)
    $stmt = $pdo->prepare("SELECT current_stage, last_action_time FROM lobbies WHERE id = :lid");
    $stmt->execute([':lid' => $lobbyId]);
    $lobby = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lobby) {
        echo json_encode(['success' => false, 'message' => 'Lobby nie istnieje']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT current_map AS active_map FROM games WHERE lobby_id = :lid");
    $stmt->execute([':lid' => $lobbyId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $activeMap = $row ? $row['active_map'] : null;

    $currentStage = (int)$lobby['current_stage'];
    $lastActionTime = $lobby['last_action_time'];
    $elapsed = $lastActionTime ? (time() - strtotime($lastActionTime)) : 0;

    // jeśli nie ustawione last_action_time (NULL) -> zainicjuj na teraz (pierwsze odpytanie)
    if ($lastActionTime === null) {
        $stmt = $pdo->prepare("UPDATE lobbies SET last_action_time = :czas WHERE id = :lid");
        $stmt->execute([':lid' => $lobbyId, ':czas' => date('Y-m-d H:i:s')]);
        $elapsed = 0;
        // odśwież veto/tablice (nie konieczne, ale ok)
        $stmt = $pdo->prepare("SELECT * FROM map_veto WHERE lobby_id = :lid ORDER BY stage ASC");
        $stmt->execute([':lid' => $lobbyId]);
        $veto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // jeśli minęło >= 30s -> wykonaj auto-action atomowo (transakcja)
    if ($elapsed >= 30) {
        $pdo->beginTransaction();

        // pobierz ponownie stage i last_action_time z blokadą
        $stmt = $pdo->prepare("SELECT current_stage, last_action_time FROM lobbies WHERE id = :lid FOR UPDATE");
        $stmt->execute([':lid' => $lobbyId]);
        $lobbyLocked = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentStageLocked = (int)$lobbyLocked['current_stage'];
        $lastActionTimeLocked = $lobbyLocked['last_action_time'];
        $elapsedLocked = $lastActionTimeLocked ? (time() - strtotime($lastActionTimeLocked)) : 0;

        // jeśli ktoś już wykonał akcję podczas czekania -> tylko odśwież dane
        if ($elapsedLocked < 30) {
            // commit nic nie rób
            $pdo->commit();
        } else {
            // zbierz dostępną pulę map
            $allMaps = ["Mirage", "Dust II", "Inferno", "Overpass", "Anubis", "Nuke", "Ancient"];
            $stmt = $pdo->prepare("SELECT map_name FROM map_veto WHERE lobby_id = :lid");
            $stmt->execute([':lid' => $lobbyId]);
            $used = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $remaining = array_values(array_diff($allMaps, $used));

            if (!empty($remaining)) {
                // stages hardcoded tu (możesz przenieść do config)
                $stages = [
                    ["action" => "ban", "team" => "Team1"],
                    ["action" => "ban", "team" => "Team2"],
                    ["action" => "pick", "team" => "Team1"],
                    ["action" => "pick", "team" => "Team2"],
                    ["action" => "ban", "team" => "Team1"],
                    ["action" => "ban", "team" => "Team2"],
                    ["action" => "decider", "team" => "System"]
                ];
                $stageDef = $stages[$currentStageLocked] ?? null;

                if ($stageDef && $stageDef['action'] !== 'decider') {
                    $randomMap = $remaining[array_rand($remaining)];

                    $stmt = $pdo->prepare("INSERT INTO map_veto (lobby_id, stage, action, team, map_name) VALUES (:lid, :stage, :action, :team, :map)");
                    $stmt->execute([
                        ':lid' => $lobbyId,
                        ':stage' => $currentStageLocked,
                        ':action' => $stageDef['action'],
                        ':team' => $stageDef['team'],
                        ':map' => $randomMap
                    ]);
                    $currentDate = date('Y-m-d H:i:s');
                    $stmt = $pdo->prepare("UPDATE lobbies SET last_action_time = :czas, current_stage = :next WHERE id = :lid");
                    $stmt->execute([':next' => $currentStageLocked + 1, ':lid' => $lobbyId, ':czas' => $currentDate]);
                }
            }
            $pdo->commit();
        }

        // odśwież dane po możliwej auto-akcji
        $stmt = $pdo->prepare("SELECT * FROM map_veto WHERE lobby_id = :lid ORDER BY stage ASC");
        $stmt->execute([':lid' => $lobbyId]);
        $veto = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT current_stage, last_action_time FROM lobbies WHERE id = :lid");
        $stmt->execute([':lid' => $lobbyId]);
        $lobby = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentStage = (int)$lobby['current_stage'];
        $lastActionTime = $lobby['last_action_time'];
        $elapsed = $lastActionTime ? (time() - strtotime($lastActionTime)) : 0;
    }

    switch($activeMap){
        case "de_mirage":
            $activeMap = "Mirage";
            break;
        case "de_dust2":
            $activeMap = "Dust II";
            break;
        case "de_inferno":
            $activeMap = "Inferno";
            break;
        case "de_overpass":
            $activeMap = "Overpass";
            break;
        case "de_anubis":
            $activeMap = "Anubis";
            break;
        case "de_nuke":
            $activeMap = "Nuke";
            break;
        case "de_ancient":
            $activeMap = "Ancient";
            break;
        default:
            $activeMap = null;
    }

    echo json_encode([
        'success' => true,
        'veto' => $veto,
        'elapsed' => $elapsed,
        'current_stage' => $currentStage,
        'active_map' => $activeMap
    ]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}





