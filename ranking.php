<?php 
include_once 'src/core/connect_db.php';
$teamID = 0;
$isLeader = false;
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
}

$stmt = $pdo->prepare('SELECT r.*, u.username, u.avatar_url, t.nazwa AS team_name FROM ranking r
    JOIN users u ON r.user_id = u.id 
    JOIN teams t ON u.team_id = t.id
 ORDER BY global_kills DESC, global_kd DESC LIMIT 10');
$stmt->execute();
$best = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?=time()?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js?v=<?= time() ?>"></script>
    <script src="assets/js/chat.js"></script>
    <script>
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

    </script>
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content ranking">
            <?php if (!$best): ?>
            <h1>No patrz, pusto tu..</h1>
            <h3>Ranking będzie widoczny po pierwszych rozgrywkach.</h3>
            <?php else: ?>
            <table>
                <tr>
                    <th><i class="fa-solid fa-user"></i></th>
                    <th><i class="fa-solid fa-people-group"></i></th>
                    <th><i class="fa-solid fa-crosshairs"></i></th>
                    <th><i class="fa-solid fa-skull-crossbones"></th>
                    <th><i class="fa-solid fa-handshake"></i></th>
                    <th>K/D</th>
                    <th><i class="fa-solid fa-bullseye"></th>
                    <th>HS%</th>
                    <th><i class="fa-solid fa-star"></i></th>
                </tr>
                <?php 
                    foreach($best as $p) {
                        $hsPerc = $p['global_kills'] > 0 ? round($p['global_headshots'] / $p['global_kills'] * 100) : 0;
                        echo '
                        <tr>
                            <td class="stats-player"><img src="'.htmlspecialchars($p['avatar_url']).'"><a href="profile.php?id='.$p['user_id'].'">'.htmlspecialchars($p['username']).'</a></td>
                            <td>'.htmlspecialchars($p['team_name']).'</td>
                            <td>'.$p['global_kills'].'</td>
                            <td>'.$p['global_deaths'].'</td>
                            <td>'.$p['global_assists'].'</td>
                            <td>'.$p['global_kd'].'</td>
                            <td>'.$p['global_headshots'].'</td>
                            <td>'.$hsPerc.'%</td>
                            <td>'.$p['global_mvps'].'</td>
                        </tr>';
                    }
                ?>
            </table>
            <?php endif; ?>
        </div>
        <?php include 'src/views/partials/sidebar_load.php';?>
        <div class="notifications-menu"></div>
        <div class="team-chat-window">
            <div class="chat-header">
                <h3>Czat drużynowy</h3>
            </div>
            <div class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" placeholder="Napisz wiadomość...">
                <button class="send-chat"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <script src="assets/js/mobile-menu.js"></script>
</body>
</html>









