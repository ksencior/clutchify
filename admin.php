<?php 
include_once 'src/core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged'] || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    redirect_to('index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <style>
        :root {
            --primary-color: <?= Config::get('primary_color', '#a268ff') ?>;
            --primary-glow: <?= Config::get('primary_color', '#a268ff') ?>80; 
        }

        .content {
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .admin-card { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 20px; border: 1px solid rgba(255,255,255,0.1); }
        .admin-card h2 { margin-bottom: 15px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; color: var(--primary-color); }
        
        .settings-form { display: flex; flex-direction: column; gap: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.9rem; color: #ccc; }
        .form-group input, .form-group select { 
            padding: 10px; background: #111; border: 1px solid #333; color: white; border-radius: 8px; 
        }
        
        .tab-btn { padding: 10px 20px; background: #111; border: none; color: white; cursor: pointer; border-radius: 8px 8px 0 0; }
        .tab-btn.active { background: var(--primary-color); }
        .tab-content { display: none; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 0 15px 15px 15px; }
        .tab-content.active { display: block; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #333; color: var(--primary-color); }
        td { padding: 12px; border-bottom: 1px solid #222; }
        tr:hover { background: rgba(255,255,255,0.02); }
        
        .btn-save { background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-save:hover { filter: brightness(1.2); box-shadow: 0 0 15px var(--primary-glow); }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        
        <div class="content">
            <h1 class="f-upper ls-2" style="margin-bottom: 20px;">Panel Sterowania</h1>

            <div class="admin-grid">
                <div class="admin-card">
                    <h2><i class="fa-solid fa-users"></i> Użytkownicy</h2>
                    <p style="font-size: 2rem; font-weight: bold;">
                        <?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?>
                    </p>
                </div>
                <div class="admin-card">
                    <h2><i class="fa-solid fa-trophy"></i> Aktywne Mecze</h2>
                    <p style="font-size: 2rem; font-weight: bold;">
                        <?php echo $pdo->query("SELECT COUNT(*) FROM mecze WHERE termin <= NOW()")->fetchColumn(); ?>
                    </p>
                </div>
            </div>

            <div class="tabs">
                <button class="tab-btn active" onclick="openTab(event, 'settings')">Ustawienia Systemu</button>
                <button class="tab-btn" onclick="openTab(event, 'matches')">Zarządzaj Meczami</button>
                <button class="tab-btn" onclick="openTab(event, 'players')">Lista Graczy</button>
            </div>

            <div id="settings" class="tab-content active">
                <?php if (isset($_GET['settings']) && $_GET['settings'] === 'ok'): ?>
                    <p style="color: #66ff99; margin-bottom: 10px;">Zapisano ustawienia.</p>
                <?php elseif (isset($_GET['settings']) && $_GET['settings'] === 'error'): ?>
                    <p style="color: #ff6666; margin-bottom: 10px;">Nie udaĹ‚o siÄ™ zapisaÄ‡ ustawieĹ„.</p>
                <?php endif; ?>
                <form class="settings-form" action="src/apis/update_system_settings.php" method="POST">
                    <div class="admin-grid" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group">
                            <label>Nazwa Platformy (Branding)</label>
                            <input type="text" name="app_name" value="<?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Kolor Przewodni</label>
                            <input type="color" name="primary_color" value="<?= htmlspecialchars(Config::get('primary_color', '#a268ff')) ?>" style="height: 45px;">
                        </div>
                        <div class="form-group">
                            <label>Base URL aplikacji</label>
                            <input type="text" name="base_url" value="<?= htmlspecialchars(Config::get('base_url', '/clutchify')) ?>" placeholder="/clutchify">
                        </div>
                        <div class="form-group">
                            <label>Kanał Twitch</label>
                            <input type="text" name="twitch_channel" value="<?= htmlspecialchars(Config::get('twitch_channel', 'zsn_gasawa')) ?>" placeholder="np. zsn_gasawa">
                        </div>
                        <div class="form-group">
                            <label>Domyślny serwer gry (IP:PORT)</label>
                            <input type="text" name="server_ip" value="<?= htmlspecialchars(Config::get('server_ip', '51.83.175.128:25471')) ?>" placeholder="np. 127.0.0.1:27015">
                        </div>
                        <div class="form-group">
                            <label>Serwer RCON (Host)</label>
                            <input type="text" name="rcon_host" value="<?= htmlspecialchars(Config::get('rcon_host', '')) ?>" placeholder="np. 127.0.0.1">
                        </div>
                        <div class="form-group">
                            <label>RCON Port</label>
                            <input type="number" name="rcon_port" value="<?= htmlspecialchars(Config::get('rcon_port', '25471')) ?>" placeholder="np. 25471">
                        </div>
                        <div class="form-group">
                            <label>RCON Hasło</label>
                            <input type="password" name="rcon_password" value="<?= htmlspecialchars(Config::get('rcon_password', '')) ?>" placeholder="***">
                        </div>
                        <div class="form-group">
                            <label>Steam API Key</label>
                            <input type="password" name="steam_api_key" value="<?= htmlspecialchars(Config::get('steam_api_key', '')) ?>" placeholder="key">
                        </div>
                        <div class="form-group">
                            <label>System Veto</label>
                            <select name="enable_veto">
                                <option value="1" <?= Config::get('enable_veto') == '1' ? 'selected' : '' ?>>Włączony</option>
                                <option value="0" <?= Config::get('enable_veto') == '0' ? 'selected' : '' ?>>Wyłączony</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">Zastosuj zmiany</button>
                </form>
            </div>

            <div id="matches" class="tab-content">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Drużyny</th>
                                <th>Status</th>
                                <th>Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("
                                SELECT m.*, t1.nazwa as t1n, t2.nazwa as t2n 
                                FROM mecze m 
                                LEFT JOIN teams t1 ON m.team1 = t1.id 
                                LEFT JOIN teams t2 ON m.team2 = t2.id 
                                ORDER BY m.id DESC LIMIT 10
                            ");

                            while($m = $stmt->fetch()) {
                                // Logika wyświetlania statusu na podstawie Twojej kolumny 'finished'
                                $statusLabel = '';
                                $badgeClass = '';

                                echo "<tr>
                                    <td>#{$m['id']}</td>
                                    <td>" . htmlspecialchars($m['t1n'] ?? 'Brak') . " vs " . htmlspecialchars($m['t2n'] ?? 'Brak') . "</td>
                                    <td><span {$badgeClass}>{$statusLabel}</span></td>
                                    <td><a href='lobby.php?id={$m['id']}' class='btn-s' style='color: var(--primary-color)'>Lobby</a></td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="players" class="tab-content">
                <p>Lista wszystkich zarejestrowanych użytkowników...</p>
            </div>

        </div>

        <?php include 'src/views/partials/sidebar_load.php';?>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>









