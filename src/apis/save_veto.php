<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../core/connect_db.php';
date_default_timezone_set("Europe/Warsaw");

if (!isset($_POST['lobby_id'], $_POST['stage'], $_POST['action'], $_POST['team'], $_POST['map'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Brak danych']);
    exit;
}

$lobbyId = (int)$_POST['lobby_id'];
$stage = (int)$_POST['stage'];
$action = $_POST['action'];
$team = $_POST['team'];
$map = $_POST['map'];

function prepareMatch($map) {
require_once __DIR__ . '/../services/rcon_connect.php';
    $rcon->sendCommand('map '. $map);

    register_shutdown_function(function() {
        sleep(10); // poczekaj aż serwer zmieni mapę
require_once __DIR__ . '/../services/rcon_connect.php';
        $rcon->sendCommand('matchzy_loadmatch_url "https://zsnturniej.0bg.pl/src/storage/tmp/matchzy_config.json"');
        $rcon->sendCommand('matchzy_remote_log_url "https://zsnturniej.0bg.pl/src/apis/matchzy_events.php"');
    });
}

try {
    // transakcja by uniknąć race-condition
    $pdo->beginTransaction();

    // pobierz aktualny stage i zablokuj wiersz
    $stmt = $pdo->prepare("SELECT current_stage FROM lobbies WHERE id = :lid FOR UPDATE");
    $stmt->execute([':lid' => $lobbyId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Lobby nie istnieje']);
        exit;
    }

    $currentStage = (int)$row['current_stage'];

    // jeśli ktoś już przesunął stage — odrzuć
    if ($currentStage !== $stage) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Stage nieaktualny', 'current_stage' => $currentStage]);
        exit;
    }

    // zapisz veto
    $stmt = $pdo->prepare("INSERT INTO map_veto (lobby_id, stage, action, team, map_name) VALUES (:lid, :stage, :action, :team, :map)");
    $stmt->execute([
        ':lid' => $lobbyId,
        ':stage' => $stage,
        ':action' => $action,
        ':team' => $team,
        ':map' => $map
    ]);

    // zwiększ stage i ustaw last_action_time
    $currentDate = date('Y-m-d H:i:s'); // Generujemy czas w PHP
    $stmt = $pdo->prepare("UPDATE lobbies SET last_action_time = :czas, current_stage = :next WHERE id = :lid");
    $stmt->execute([':next' => $currentStage + 1, ':lid' => $lobbyId, ':czas' => $currentDate]);

    if ($currentStage + 1 >= 7) {
        $stmt = $pdo->prepare("SELECT mecz_id, team1_id, team2_id FROM lobbies WHERE id = :lid");
        $stmt->execute([':lid' => $lobbyId]);
        $matchInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $matchID = $matchInfo['mecz_id'];
        $team1 = $matchInfo['team1_id'];
        $team2 = $matchInfo['team2_id'];
        $stmt = $pdo->prepare("SELECT stage, map_name FROM map_veto WHERE lobby_id = :lid AND (action='pick' OR action='decider') ORDER BY stage ASC;");
        $stmt->execute([':lid' => $lobbyId]);
        $veto = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($veto as $v) {
            if ($v['stage'] === 2) {
                $stmt = $pdo->prepare("INSERT INTO games_maps(lobby_id, order_num, map) VALUES (:lid, 1, :map)");
                $stmt->execute([':lid' => $lobbyId, ':map' => $v['map_name']]);
            } else if ($v['stage'] === 3) {
                $stmt = $pdo->prepare("INSERT INTO games_maps(lobby_id, order_num, map) VALUES (:lid, 2, :map)");
                $stmt->execute([':lid' => $lobbyId, ':map' => $v['map_name']]);
            } else if ($v['stage'] === 6) {
                $stmt = $pdo->prepare("INSERT INTO games_maps(lobby_id, order_num, map) VALUES (:lid, 3, :map)");
                $stmt->execute([':lid' => $lobbyId, ':map' => $v['map_name']]);
            }
        }
        $stmt = $pdo->prepare("SELECT map FROM games_maps WHERE lobby_id = :lid ORDER BY order_num ASC LIMIT 1");
        $stmt->execute([':lid' => $lobbyId]);
        $map = $stmt->fetchColumn();
        switch ($map) {
            case "Mirage":
                $map = "de_mirage";
                break;
            case "Inferno":
                $map = "de_inferno";
                break;
            case "Nuke":
                $map = "de_nuke";
                break;
            case "Overpass":
                $map = "de_overpass";
                break;
            case "Ancient":
                $map = "de_ancient";
                break;
            case "Dust II":
                $map = "de_dust2";
                break;
            case "Anubis":
                $map = "de_anubis";
                break;
            default:
                $map = "de_mirage";
                break;
        }
        if (isset($matchID, $team1, $team2)) {
            $stmt = $pdo->prepare("INSERT INTO games (lobby_id, match_id, team1, team2, server_ip, current_map, current_round, server_ready_until) 
            VALUES (:lid, :mid, :t1, :t2, '51.83.175.128:25471', :map, 0, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
            $stmt->execute([':lid' => $lobbyId, ':mid' => $matchID, ':t1' => $team1, ':t2' => $team2, ':map' => $map]);
            $stmt = $pdo->prepare("SELECT u.id, u.team_id, u.steam_id FROM users u JOIN teams t1 ON u.team_id = t1.id WHERE t1.id = :t1 
            UNION SELECT u.id, u.team_id, u.steam_id FROM users u JOIN teams t2 ON u.team_id = t2.id WHERE t2.id = :t2");
            $stmt->execute([':t1' => $team1, ':t2' => $team2]);
            $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare("SELECT id FROM games WHERE lobby_id = :lid ORDER BY id DESC LIMIT 1");
            $stmt->execute([':lid' => $lobbyId]);
            $gameId = $stmt->fetchColumn();
            foreach ($players as $p) {
                $stmt = $pdo->prepare("INSERT INTO game_players (game_id, user_id, team_id) VALUES (:gid, :uid, :tid)");
                $stmt->execute([':gid' => $gameId, ':uid' => $p['id'], ':tid' => $p['team_id']]);
            }
            $stmt = $pdo->prepare("SELECT t1.nazwa AS team1_name, t2.nazwa AS team2_name FROM games g JOIN teams t1 ON g.team1 = t1.id JOIN teams t2 ON g.team2 = t2.id WHERE g.lobby_id = :lid");
            $stmt->execute([':lid'=> $lobbyId]);
            $druzyny = $stmt->fetch();
            $team1name = $druzyny['team1_name'];
            $team2name = $druzyny['team2_name'];

            // pobierz mapy
            $stmt = $pdo->prepare("SELECT map FROM games_maps WHERE lobby_id = :lid ORDER BY order_num ASC");
            $stmt->execute([':lid' => $lobbyId]);
            $maps = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // mapowanie nazw na cs'owe
            $mapTranslation = [
                "mirage"   => "de_mirage",
                "inferno"  => "de_inferno",
                "nuke"     => "de_nuke",
                "overpass" => "de_overpass",
                "ancient"  => "de_ancient",
                "dust ii"  => "de_dust2",
                "dust2"    => "de_dust2",
                "anubis"   => "de_anubis",
                "vertigo"  => "de_vertigo",
            ];

            // zamień mapy na cs'owe ID
            $maps = array_map(function($m) use ($mapTranslation) {
                $key = strtolower(trim($m));
                return $mapTranslation[$key] ?? ('de_' . $key);
            }, $maps);

            // pobierz graczy z team1 i team2
            $stmt = $pdo->prepare("SELECT u.id, u.team_id, u.steam_id, u.username 
                FROM users u 
                WHERE u.team_id IN (:t1, :t2)");
            $stmt->execute([':t1' => $team1, ':t2' => $team2]);
            $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // rozdziel na teamy
            $team1players = [];
            $team2players = [];
            foreach ($players as $p) {
            if ($p['team_id'] == $team1) {
                $team1players[$p['steam_id']] = $p['username'];
            } else {
                $team2players[$p['steam_id']] = $p['username'];
            }
        }

            $spectators = [];
            $stmt = $pdo->prepare("SELECT u.id, u.steam_id, u.username 
                FROM users u 
                WHERE u.isSpectator = 1");
            $stmt->execute();
            $specs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($specs as $s) {
                $spectators[$s['steam_id']] = $s['username'];
            }

            // zbuduj strukturę dla MatchZy
            $MatchZyMatch = [
                "matchid"   => $matchID,
                "team1"     => [
                    "name"    => $team1name,
                    "players" => $team1players
                ],
                "team2"     => [
                    "name"    => $team2name,
                    "players" => $team2players
                ],
                "spectators" => [
                    "players" => $spectators
                ],
                "num_maps"  => count($maps),
                "maplist"   => $maps,
                "map_sides" => [ "knife", "knife", "knife" ] // to możesz ustawiać dynamicznie
            ];

            // zapakuj do JSON
            $json = json_encode($MatchZyMatch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__ . "/../storage/tmp/matchzy_config.json", $json);
            prepareMatch($team1name, $team2name, $map);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}



