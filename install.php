<?php
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<h2>Instalacja jest już zakończona.</h2>";
    echo "<p>Usuń plik .env, jeśli chcesz uruchomić instalator ponownie.</p>";
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';

    $appName = trim($_POST['app_name'] ?? 'Clutchify.gg');
    $baseUrl = trim($_POST['base_url'] ?? '/');

    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';

    $importSample = isset($_POST['import_sample']) && $_POST['import_sample'] === '1';

    if ($dbName === '' || $dbUser === '') {
        $errors[] = "Podaj nazwę bazy i użytkownika.";
    }
    if ($adminEmail === '' || $adminPass === '') {
        $errors[] = "Podaj e-mail i hasło administratora.";
    }

    if (empty($errors)) {
        try {
            $dsnServer = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdoServer = new PDO($dsnServer, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

            $dsnDb = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsnDb, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $sqlPath = __DIR__ . '/clutchify_template.sql';
            if (!file_exists($sqlPath)) {
                throw new RuntimeException("Brak pliku clutchify_template.sql");
            }
            $sql = file_get_contents($sqlPath);

            // Strip comment lines to avoid skipping statements that follow comments
            $lines = preg_split("/\\r\\n|\\n|\\r/", $sql);
            $filtered = [];
            foreach ($lines as $line) {
                $trim = ltrim($line);
                if (str_starts_with($trim, '--') || str_starts_with($trim, '/*') || str_starts_with($trim, '/*!')) {
                    continue;
                }
                $filtered[] = $line;
            }
            $sql = implode("\n", $filtered);

            if (!$importSample) {
                // Remove all INSERT blocks (also multiline) when sample data is disabled
                $sql = preg_replace('/INSERT\\s+INTO\\s+[^;]*;\\s*/is', '', $sql);
            }
            // Remove transaction wrappers to avoid partial execution issues
            $sql = preg_replace('/^\\s*START\\s+TRANSACTION;\\s*/mi', '', $sql);
            $sql = preg_replace('/^\\s*COMMIT;\\s*/mi', '', $sql);

            $statements = preg_split('/;\\s*(\\r?\\n|$)/', $sql);
            $importError = null;
            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) {
                    continue;
                }
                try {
                    $pdo->exec($stmt);
                } catch (Throwable $e) {
                    $preview = mb_substr($stmt, 0, 300);
                    $importError = "Błąd SQL: " . $e->getMessage() . " | Fragment: " . $preview;
                    break;
                }
            }

            if ($importError) {
                throw new RuntimeException($importError);
            }

            // system settings
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS system_settings (
                    setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
                    setting_value TEXT NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $ins = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $ins->execute([':k' => 'app_name', ':v' => $appName]);
            $ins->execute([':k' => 'base_url', ':v' => $baseUrl]);

            // ensure users table exists
            $checkUsersTable = $pdo->prepare("
                SELECT COUNT(*) FROM information_schema.tables 
                WHERE table_schema = :db AND table_name = 'users'
            ");
            $checkUsersTable->execute([':db' => $dbName]);
            if ((int)$checkUsersTable->fetchColumn() === 0) {
                throw new RuntimeException("Import nie utworzył tabeli 'users'. Sprawdź plik clutchify_template.sql.");
            }

            // create admin user if not exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $check->execute([':email' => $adminEmail]);
            if ((int)$check->fetchColumn() === 0) {
                $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, isAdmin) VALUES (:u, :e, :p, 1)");
                $stmt->execute([
                    ':u' => $adminUser,
                    ':e' => $adminEmail,
                    ':p' => $hash
                ]);
            }

            // write .env
            $env = [];
            $env[] = "DB_HOST={$dbHost}";
            $env[] = "DB_PORT={$dbPort}";
            $env[] = "DB_NAME={$dbName}";
            $env[] = "DB_USER={$dbUser}";
            $env[] = "DB_PASS={$dbPass}";
            $env[] = "";
            $env[] = "APP_NAME={$appName}";
            $env[] = "BASE_URL={$baseUrl}";
            $env[] = "STEAM_API_KEY=";
            $env[] = "";
            $env[] = "RCON_HOST=";
            $env[] = "RCON_PORT=25471";
            $env[] = "RCON_PASSWORD=";
            $env[] = "";
            $env[] = "SERVER_IP=127.0.0.1:27015";

            file_put_contents($envPath, implode(PHP_EOL, $env));

            $success = true;
        } catch (Throwable $e) {
            $errors[] = "Błąd instalacji: " . $e->getMessage();
        }
    }
}

$baseDefault = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($baseDefault === '') {
    $baseDefault = '/';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalator | Clutchify.gg</title>
    <link rel="stylesheet" href="assets/css/style.css?v=1">
</head>
<body>
    <div id="root" class="root-login">
        <div class="login-box" style="max-height: unset;">
            <h1>Instalator Clutchify.gg</h1>
            <?php if ($success): ?>
                <p style="color:#66ff99;">Instalacja zakończona. Możesz przejść do logowania.</p>
                <a href="<?php echo htmlspecialchars(($baseDefault === '/' ? '' : $baseDefault) . '/login.php'); ?>">Przejdź do logowania</a>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div style="color:#ff6666;">
                        <?php foreach ($errors as $err): ?>
                            <p><?php echo htmlspecialchars($err); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <label>DB Host</label>
                    <input type="text" name="db_host" value="127.0.0.1">
                    <label>DB Port</label>
                    <input type="text" name="db_port" value="3306">
                    <label>DB Name</label>
                    <input type="text" name="db_name" required>
                    <label>DB User</label>
                    <input type="text" name="db_user" required>
                    <label>DB Password</label>
                    <input type="password" name="db_pass">

                    <label>Nazwa aplikacji</label>
                    <input type="text" name="app_name" value="Clutchify.gg">
                    <label>Base URL</label>
                    <input type="text" name="base_url" value="<?php echo htmlspecialchars($baseDefault); ?>">

                    <label>Admin login</label>
                    <input type="text" name="admin_user" value="admin">
                    <label>Admin email</label>
                    <input type="email" name="admin_email" required>
                    <label>Admin hasło</label>
                    <input type="password" name="admin_pass" required>

                    <input type="submit" value="Zainstaluj">
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
