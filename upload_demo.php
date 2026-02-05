<?php
require_once __DIR__ . "/src/core/connect_db.php";
$logDir = __DIR__ . '/src/storage/logs';
$logFile = $logDir . '/demos.log';
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
        // Używamy JSON_UNESCAPED_SLASHES by ścieżki wyglądały czytelniej w logu
        $entry .= " | DATA: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    file_put_contents($logFile, $entry . "\n", FILE_APPEND);
}
// --------------------------------------------------------------------------

// 1. Sprawdzenie klucza dostępu
logEvent('Got a new client connected. Checking access key...');
if ($_GET['key'] !== 'zsnturniej2026') {
    logEvent('Access denied for upload attempt');
    http_response_code(403);
    exit('Access denied');
}
logEvent('Client authorized. Checking method...');
// Sprawdzenie, czy to żądanie POST (Matchzy używa POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    logEvent('Method Not Allowed: ' . $_SERVER['REQUEST_METHOD']);
    exit('Method not allowed');
}
logEvent('Method OK. Checking headers...');
// 2. Odczytanie niestandardowych nagłówków Matchzy z tablicy $_SERVER
// Nagłówki są zazwyczaj konwertowane na wielkie litery, myślniki na podkreślenia i dodawany jest prefiks HTTP_
$fileName = $_SERVER['HTTP_MATCHZY_FILENAME'] ?? null;
$matchId = $_SERVER['HTTP_MATCHZY_MATCHID'] ?? null;

if ($fileName === null || $matchId === null) {
    // Brak kluczowych nagłówków!
    http_response_code(400);
    logEvent('Missing MatchZy headers (FileName or MatchId) in request.', [
        'MatchZy-FileName' => $fileName,
        'MatchZy-MatchId' => $matchId
    ]);
    exit("Missing required MatchZy headers");
}
logEvent('Headers OK: FileName=' . $fileName . ', MatchId=' . $matchId);
logEvent('Preparing to save demo file...');
// 3. Sprawdzenie i utworzenie katalogu docelowego
$uploadBase = __DIR__ . '/uploads/';
if (!file_exists($uploadBase)) {
    logEvent('No folder /uploads/ found, creating it..');
    mkdir($uploadBase, 0777, true);
}

// Ścieżka docelowa na podstawie MatchId z nagłówka
$uploadDir = $uploadBase . $matchId . '/';
if (!file_exists($uploadDir)) {
    logEvent('Tworze folder dla match_id ' . $matchId);
    mkdir($uploadDir, 0777, true);
}

$targetFile = $uploadDir . basename($fileName);

// 4. Odczyt surowej zawartości dema z ciała żądania (Raw Body)
$demoContent = file_get_contents('php://input');
logEvent('Read raw body content, size: ' . strlen($demoContent) . ' bytes', ['match_id' => $matchId]);
if ($demoContent === false || empty($demoContent)) {
    http_response_code(400);
    logEvent('Error reading raw body content or body is empty.', ['match_id' => $matchId]);
    exit("Error reading body or body is empty");
}
logEvent('Raw body content read successfully.', ['match_id' => $matchId]);
// 5. Zapisanie pliku
if (file_put_contents($targetFile, $demoContent) !== false) {
    http_response_code(200);
    logEvent('Uploaded new demo (raw): ' . $fileName, ['match_id' => $matchId, 'size' => strlen($demoContent), 'path' => $targetFile]);
    $stmt = $pdo->prepare("SELECT winner_id FROM mecze WHERE id = :mid");
    $stmt->execute([':mid' => $matchId]);
    $winnerId = $stmt->fetchColumn();
    logEvent('Checking if match' . $matchId . ' has a winner...', ['match_id' => $matchId, 'winner_id' => $winnerId]);
    if ($winnerId && $winnerId != NULL) {
        logEvent('Packing all demos for match_id ' . $matchId);

        $demoDir = __DIR__ . "/uploads/$matchId";
        $outputTar = __DIR__ . "/demos/match_{$matchId}_demos.tar";

        if (is_dir($demoDir)) {
            if (!file_exists(dirname($outputTar))) {
                mkdir(dirname($outputTar), 0777, true);
            }

            // Tworzenie archiwum .tar
            try {
                $phar = new PharData($outputTar);
                $phar->buildFromDirectory($demoDir);
                logEvent("📦 Demos packed into TAR for match $matchId: $outputTar");
            } catch (Exception $e) {
                logEvent("❌ TAR pack error for match $matchId: " . $e->getMessage());
            }
        } else {
            logEvent("⚠️ No demos found for match $matchId");
        }

    } else {
        logEvent('Match ' . $matchId . ' has no winner yet, skipping packing demos.');
    }
    logEvent('File write successful: ' . $targetFile, ['match_id' => $matchId]);
    echo "OK";
} else {
    // POBRANIE BŁĘDU SYSTEMOWEGO (jeśli dostępny) I UPRAWNIEŃ KATALOGU
    $errorMsg = error_get_last()['message'] ?? 'Unknown file system error.';
    $dirPerms = is_dir($uploadDir) ? substr(sprintf('%o', fileperms($uploadDir)), -4) : 'N/A';
    
    http_response_code(500);
    logEvent('Error writing file to disk: ' . $fileName, [
        'match_id' => $matchId, 
        'system_error' => $errorMsg,
        'dir_perms' => $dirPerms
    ]);
    echo "Upload failed";
}
// Koniec





