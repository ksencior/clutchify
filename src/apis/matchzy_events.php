<?php
require_once __DIR__ . '/../core/connect_db.php';

// 🪵 Ustawienia logowania
$logDir = __DIR__ . '/../storage/logs';
$logFile = $logDir . '/events.log';

// Upewnij się, że folder logs istnieje
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Auto-clean loga jeśli > 5MB
if (file_exists($logFile) && filesize($logFile) > 5 * 1024 * 1024) {
    file_put_contents($logFile, "=== LOG RESET " . date("Y-m-d H:i:s") . " ===\n");
}

// Funkcja logująca eventy
function logEvent($msg, $data = null)
{
    global $logFile;
    $time = date("[Y-m-d H:i:s]");
    $entry = "$time $msg [ZSNChampions LOG]";
    if ($data !== null) {
        $entry .= " | DATA: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    file_put_contents($logFile, $entry . "\n", FILE_APPEND);
}

// 📥 Odbiór danych JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// 🔒 Walidacja JSON-a
if (!$data || !isset($data['event'])) {
    http_response_code(400);
    logEvent("❌ Invalid JSON", $input);
    exit(json_encode(["error" => "Invalid JSON or missing event field"]));
}

$event   = $data['event'];
$matchId = $data['matchid'] ?? null;

logEvent("📩 EVENT RECEIVED: $event", $data);

if (!$matchId) {
    http_response_code(400);
    logEvent("❌ Missing matchid in event $event");
    exit(json_encode(["error" => "Missing matchid"]));
}

try {

    switch ($event) {
        case "series_start":
            // $pdo->prepare("UPDATE games SET status = 'playing' WHERE match_id = ?")
            //     ->execute([$matchId]);
            logEvent("✅ Series started for match $matchId");
            break;

        case "map_picked":
            logEvent("🗺️ Map picked", $data);
            break;

        case "going_live":
            $pdo->prepare("UPDATE games SET status = 'playing', current_round = 0 WHERE match_id = ? AND status = 'waiting'")
                ->execute([$matchId]);
            logEvent("🚀 Match $matchId going live!");
            break;

        case "round_end":
            $round = ($data['round_number'] ?? 0) + 1;
            $team1Score = $data['team1']['score'] ?? 0;
            $team2Score = $data['team2']['score'] ?? 0;
            $pdo->prepare("
                UPDATE games 
                SET current_round = ?, team1_score = ?, team2_score = ?
                WHERE match_id = ? AND status = 'playing'
            ")->execute([$round, $team1Score, $team2Score, $matchId]);
            $stmt = $pdo->prepare("SELECT id FROM games WHERE match_id = ? AND status = 'playing'");
            $stmt->execute([$matchId]);
            $gameId = $stmt->fetchColumn();
            if (!$gameId) {
                logEvent("⚠️ No game found for match $matchId in round_end");
                break;
            }

            $team1Data = $data['team1'] ?? [];
            $team2Data = $data['team2'] ?? [];

            $players = array_merge($team1Data['players'] ?? [], $team2Data['players'] ?? []);

            foreach ($players as $p) {
                $steamID = $p['steamid'] ?? null;
                if (!$steamID) continue;
                
                $stmt = $pdo->prepare("SELECT id FROM users WHERE steam_id = ?");
                $stmt->execute([$steamID]);
                $userID = $stmt->fetchColumn();
                if (!$userID) {
                    logEvent("⚠️ No user found for steamID $steamID in round_end");
                    continue;
                }
                $playerStats = $p['stats'] ?? [];
                $pdo->prepare("UPDATE game_players SET
                    kills = ?, deaths = ?, assists = ?, headshots = ?, mvps = ?
                    WHERE game_id = ? AND user_id = ?")
                    ->execute([
                        $playerStats['kills'] ?? 0,
                        $playerStats['deaths'] ?? 0,
                        $playerStats['assists'] ?? 0,
                        $playerStats['headshot_kills'] ?? 0,
                        $playerStats['mvp'] ?? 0,
                        $gameId,
                        $userID
                    ]);
            }

            logEvent("🏁 Round $round ended in match $matchId", [
                "team1_score" => $team1Score,
                "team2_score" => $team2Score
            ]);
            break;

        case "map_result":
            $winnerMatchzyId = $data['winner']['team'] ?? null;
            if (!$winnerMatchzyId) {
                logEvent("⚠️ Missing winner info in map_result");
                break;
            }

            $winnerRaw = $data[$winnerMatchzyId] ?? [];
            $winnerTeamName = $winnerRaw['name'] ?? null;
            
            if (!$winnerTeamName) {
                logEvent("⚠️ Missing winner team name in map_result");
                break;
            }

            // Szukamy ID wygranego teamu
            $stmt = $pdo->prepare("SELECT id FROM teams WHERE nazwa = ?");
            $stmt->execute([$winnerTeamName]);
            $winnerID = $stmt->fetchColumn();

            // Pobieramy teamy meczu
            $stmt = $pdo->prepare("SELECT team1, team2 FROM mecze WHERE id = ?");
            $stmt->execute([$matchId]);
            $teamy = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($teamy) {
                if ($teamy['team1'] == $winnerID) {
                    $pdo->prepare("UPDATE mecze SET team1_wins = team1_wins + 1 WHERE id = ?")->execute([$matchId]);
                } else {
                    $pdo->prepare("UPDATE mecze SET team2_wins = team2_wins + 1 WHERE id = ?")->execute([$matchId]);
                }
            }

            // Sprawdzamy czy seria się już zakończyła
            $stmt = $pdo->prepare("SELECT team1_wins, team2_wins FROM mecze WHERE id = ?");
            $stmt->execute([$matchId]);
            $scores = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $seriesFinished = false;
            if ($scores) {
                $maxWins = max($scores['team1_wins'], $scores['team2_wins']);
                if ($maxWins >= 2) { // BO3 - wygrana w 2 mapach
                    $seriesFinished = true;
                }
            }

            // Szukamy lobby_id
            $stmt = $pdo->prepare("SELECT lobby_id FROM games WHERE match_id = ?");
            $stmt->execute([$matchId]);
            $lobbyID = $stmt->fetchColumn();

            $currentMapNum = $data["map_number"] ?? 0; // 0-based index
            $stmt = $pdo->prepare("SELECT map FROM games_maps WHERE lobby_id = ?");
            $stmt->execute([$lobbyID]);
            $maps = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $mapTranslation = [
                "mirage"   => "de_mirage",
                "inferno"  => "de_inferno",
                "nuke"     => "de_nuke",
                "overpass" => "de_overpass",
                "ancient"  => "de_ancient",
                "dust ii"  => "de_dust2",
                "dust2"    => "de_dust2",
                "train"    => "de_train",
                "vertigo"  => "de_vertigo",
            ];

            $maps = array_map(function ($m) use ($mapTranslation) {
                $key = strtolower(trim($m));
                return $mapTranslation[$key] ?? ('de_' . $key);
            }, $maps);

            $currentMap = $maps[$currentMapNum] ?? null;
            if (!$currentMap) {
                logEvent("⚠️ No map found for index $currentMapNum");
                break;
            }

            // Pobierz game_id z aktualnej mapy
            $stmt = $pdo->prepare("SELECT id FROM games WHERE lobby_id = :lid AND current_map = :map");
            $stmt->execute([':lid' => $lobbyID, ':map' => $currentMap]);
            $gameId = $stmt->fetchColumn();

            if (!$gameId) {
                logEvent("⚠️ No game found for map $currentMap");
                break;
            }

            // Oznacz aktualną grę jako finished
            $pdo->prepare("UPDATE games SET status = 'finished' WHERE id = ? AND status = 'playing'")
                ->execute([$gameId]);

            // Tylko jeśli seria NIE jest zakończona - utwórz następną grę
            if (!$seriesFinished) {
                $nextMapNum = $currentMapNum + 1;
                $nextMap = $maps[$nextMapNum] ?? null;
                
                if ($nextMap) {
                    $pdo->prepare("INSERT INTO games (lobby_id, match_id, team1, team2, server_ip, current_map, status, server_ready_until) 
                        VALUES (?, ?, ?, ?, '37.221.94.158:27015', ?, 'waiting', DATE_ADD(NOW(), INTERVAL 5 MINUTE))")
                        ->execute([$lobbyID, $matchId, $teamy['team1'], $teamy['team2'], $nextMap]);

                    $nextGameId = $pdo->lastInsertId();  // Pobierz ID nowej gry
                    $stmt = $pdo->prepare("
                        SELECT DISTINCT user_id, team_id 
                        FROM game_players 
                        WHERE game_id = ?
                    ");
                    $stmt->execute([$gameId]);
                    $plrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    logEvent("🔄 Creating new game players for next map. Game ID: $nextGameId");
    
                    foreach ($plrs as $plr) {
                        try {
                            $pdo->prepare("INSERT INTO game_players (game_id, user_id, team_id, kills, deaths, assists, headshots, mvps) 
                                        VALUES (?, ?, ?, 0, 0, 0, 0, 0)")
                                ->execute([$nextGameId, $plr['user_id'], $plr['team_id']]);
                            $userID = $plr['user_id'];
                            logEvent("✅ Created player record for user_id: $userID in game: $nextGameId");
                        } catch (Exception $e) {
                            $userID = $plr['user_id'];
                            logEvent("⚠️ Failed to create player record for user_id: $userID - " . $e->getMessage());
                        }
                    }
                    
                    logEvent("🔄 Next game prepared for map: $nextMap");
                } else {
                    logEvent("⚠️ No next map available - series might be incomplete");
                }
            } else {
                logEvent("🏆 Series completed - no next game needed");
            }

            // Top 3 graczy i ranking - TEN FRAGMENT JEST OK
            $stmt = $pdo->prepare("
                SELECT user_id, kills, deaths, assists, headshots, mvps,
                    ROUND((kills + assists) / NULLIF(deaths, 0), 2) AS kda
                FROM game_players
                WHERE game_id = :game_id
                ORDER BY kda DESC, kills DESC
                LIMIT 3
            ");
            $stmt->execute([':game_id' => $gameId]);
            $bestPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $insert = $pdo->prepare("
                INSERT INTO ranking (user_id, global_kills, global_deaths, global_assists, global_headshots, global_mvps, global_kd)
                VALUES (:uid, :kills, :deaths, :assists, :hs, :mvps, :kd)
                ON DUPLICATE KEY UPDATE 
                    global_kills = global_kills + VALUES(global_kills),
                    global_deaths = global_deaths + VALUES(global_deaths),
                    global_assists = global_assists + VALUES(global_assists),
                    global_headshots = global_headshots + VALUES(global_headshots),
                    global_mvps = global_mvps + VALUES(global_mvps),
                    global_kd = COALESCE(ROUND(global_kills / NULLIF(global_deaths, 0), 2), 0)
            ");

            foreach ($bestPlayers as $p) {
                $insert->execute([
                    ':uid'    => $p['user_id'],
                    ':kills'  => $p['kills'],
                    ':deaths' => $p['deaths'],
                    ':assists'=> $p['assists'],
                    ':hs'     => $p['headshots'],
                    ':mvps'   => $p['mvps'],
                    ':kd'     => ($p['deaths'] > 0) ? round($p['kills'] / $p['deaths'], 2) : $p['kills']
                ]);
            }

            logEvent("🏆 Map $currentMap finished for match $matchId — winner: $winnerTeamName");
            break;

        case "series_end":
            $winnerMatchzyId = $data['winner']['team'] ?? null;
            if ($winnerMatchzyId) {
                $stmt = $pdo->prepare("SELECT team1, team2 FROM mecze WHERE id = ?");
                $stmt->execute([$matchId]);
                $mecz = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($mecz) {
                    // Określ który team wygrał na podstawie team1/team2 z meczu
                    $winnerID = ($winnerMatchzyId === 'team1') ? $mecz['team1'] : $mecz['team2'];
                    
                    $pdo->prepare("UPDATE games SET status = 'finished' WHERE match_id = ?")->execute([$matchId]);
                    $pdo->prepare("UPDATE mecze SET winner_id = ? WHERE id = ?")->execute([$winnerID, $matchId]);
                }

                $stmt = $pdo->prepare('SELECT * FROM games WHERE match_id = ?');
                $stmt->execute([$matchId]);
                $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($games as $game) {
                    $pdo->prepare("INSERT INTO finished_games (match_id, game_id, map_name, team1_id, team2_id, team1_score, team2_score)
                        VALUES (:mid, :gid, :map, :team1, :team2, :t1s, :t2s)")
                        ->execute([':mid' => $matchId,
                                   ':gid' => $game['id'],
                                   ':team1' => $game['team1'],
                                   ':team2' => $game['team2'],
                                   ':map' => $game['current_map'],
                                   ':t1s' => $game['team1_score'],
                                   ':t2s' => $game['team2_score']]);
                }

                $stmt = $pdo->prepare("SELECT * FROM game_players WHERE game_id IN (SELECT id FROM games WHERE match_id = ?)");
                $stmt->execute([$matchId]);
                $allPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($allPlayers as $player) {
                    $pdo->prepare("INSERT INTO finished_stats (match_id, game_id, user_id, team_id, kills, deaths, assists, headshots, mvps)
                        VALUES (:mid, :gid, :uid, :tid, :kills, :deaths, :assists, :hs, :mvps)")
                        ->execute([
                            ':mid'   => $matchId,
                            ':gid'   => $player['game_id'],
                            ':uid'   => $player['user_id'],
                            ':tid'   => $player['team_id'],
                            ':kills' => $player['kills'],
                            ':deaths'=> $player['deaths'],
                            ':assists'=> $player['assists'],
                            ':hs'    => $player['headshots'],
                            ':mvps'  => $player['mvps']
                        ]);
                }

                $pdo->prepare("TRUNCATE TABLE game_players")->execute();
                $pdo->prepare("TRUNCATE TABLE games")->execute();
                $pdo->prepare("TRUNCATE TABLE games_maps")->execute();
                $pdo->prepare("TRUNCATE TABLE lobbies")->execute();
                $pdo->prepare("TRUNCATE TABLE map_veto")->execute();
                
                logEvent("🏁 Series ended for match $matchId — winner: $winnerTeamName");
            }
            break;

        case "demo_upload_ended":
            logEvent("💾 Demo upload finished for match $matchId");
            break;

        default:
            logEvent("❓ Unknown event type: $event");
            break;
    }

    http_response_code(200);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    logEvent("💥 Exception in event '$event': " . $e->getMessage());
    logEvent("💥 Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}

