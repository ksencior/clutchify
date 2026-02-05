<?php 
include_once 'src/core/connect_db.php';
//include_once 'src/auth/fetch_steam_data.php';
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit;
}

$matchLive = false;
try {
    $stmt = $pdo->prepare("
        SELECT m.*
        FROM mecze m
        WHERE m.termin <= NOW() AND (m.winner_id IS NULL)
        ORDER BY m.termin DESC
        LIMIT 1");
    $stmt->execute();
    $liveMatch = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($liveMatch) {
        $matchLive = true;
    }
} catch (PDOException $e) {
    // echo "$e"; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strona Główna | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="assets/js/notifications.js?v=<?= time() ?>"></script>
    <script src="assets/js/chat.js"></script>
    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?>" />
    <meta name="description" content="Clutchify.gg - platforma do organizacji turniejow CS2, zarzadzania meczami i statystykami." />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= htmlspecialchars(Config::get('base_url', '/clutchify')) ?>/" />
    <meta property="og:title" content="<?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?>" />
    <meta property="og:description" content="Clutchify.gg - platforma do organizacji turniejow CS2, zarzadzania meczami i statystykami." />
    <meta property="og:image" content="assets/img/promo_poster.png" />

    <!-- X (Twitter) -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="<?= htmlspecialchars(Config::get('base_url', '/clutchify')) ?>/" />
    <meta property="twitter:title" content="<?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?>" />
    <meta property="twitter:description" content="Clutchify.gg - platforma do organizacji turniejow CS2, zarzadzania meczami i statystykami." />
    <meta property="twitter:image" content="assets/img/promo_poster.png" />

    <!-- Meta Tags Generated with https://metatags.io -->
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root">
        <?php include 'src/views/partials/navbar.php'; ?>
        <div class="content">
            <img src="assets/img/promo_poster.png" alt="" class="promo-poster">
            <?php if ($matchLive): ?>
            <div class="livestream">
                <div class="live-title">
                    <h1>Na żywo</h1>
                    <div class="ringring"></div> <div class="circle"></div>
                </div>
                <div id="twitch-embed" style="width: 100%; height: 80%;"></div>
                <script src="https://embed.twitch.tv/embed/v1.js"></script>
                <script type="text/javascript" defer>
                    new Twitch.Embed("twitch-embed", {
                        width: "100%",
                        height: "100%",
                        channel: "<?= htmlspecialchars(Config::get('twitch_channel', 'zsn_gasawa')) ?>",
                        autoplay: true,
                        layout: "video",
                        muted: false
                    });
                </script>
            </div>
            <?php endif; ?>
            <div class="account-info">
                <h1>Twoje konto</h1>
                    <img src="<?php echo isset($_SESSION['avatar_url']) && !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'assets/img/avatar_default.png'; ?>" alt="Avatar uzytkownika" class="avatar">
                <div class="info">
                    <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <?php 
                    if (empty($_SESSION['steam_id']) || $_SESSION['steam_id'] == NULL) {
                        echo '
                        <div class="steam-connect">
                            <p>Nie połączyłeś jeszcze swojego konta steam.</p>
                            <button onclick="location.href=`src/auth/connect_steam.php`"><i class="fa-brands fa-steam"></i>Połącz za pomocą steam</button>
                        </div>
                        ';
                    } else {
                        echo '
                            <p>Połączono konto Steam</p>
                        ';
                    }
                    ?>
                    <a href="profile.php?id=<?php echo $_SESSION['id']; ?>">Przejdź do profilu</a>
                </div>
            </div>
            <div class="upcoming-matches">
                <h1>Nadchodzące mecze</h1>
                <div class="matches-list">
                <?php 
                if (empty($_SESSION['team_id']) || $_SESSION['team_id'] == NULL) {
                    echo '<p>Nie jesteś w żadnej drużynie.</p>';
                } else {
                    try{
                        $sql = $pdo->prepare("
                            SELECT 
                                m.*, 
                                t1.nazwa AS team1_name, 
                                t2.nazwa AS team2_name 
                            FROM mecze m
                            JOIN teams t1 ON m.team1 = t1.id
                            JOIN teams t2 ON m.team2 = t2.id
                            WHERE (m.team1 = :teamId1 OR m.team2 = :teamId2)
                            AND m.termin > NOW()
                            ORDER BY m.termin ASC
                            LIMIT 3
                        ");
                        $sql->execute([':teamId1' => $_SESSION['team_id'], ':teamId2' => $_SESSION['team_id']]);
                        $anyMatches=false;
                        while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
                            echo '
                            <div class="match">
                                <div class="teams-versus">
                                    <h3>'.htmlspecialchars($row['team1_name']).'</h3>
                                    <h1> VS </h1>
                                    <h3>'.htmlspecialchars($row['team2_name']).'</h3>
                                </div>
                                <h3>'.date('d.m.Y H:i', strtotime($row['termin'])).'</h3>
                            </div>
                            ';
                            $anyMatches=true;
                        }
                        if (!$anyMatches) {
                            echo '<p>Brak nadchodzących meczy.</p>';
                        }
                    } catch (PDOException $e) {
                        echo "$e"; exit;
                    }
                }
                ?>
                </div>
            </div>
            <div class="news">
                <h1>Aktualności <?php echo ($_SESSION['isAdmin'] == true)? ' | <a href="add_post.php">Dodaj post</a>' : '' ?></h1>
                <div class="news-container">
                    <?php 
                        $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY posted_at DESC LIMIT 5");
                        $stmt->execute();
                        while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '
                            <div class="news-item">
                                <h2>'.htmlspecialchars($post['title']).'</h2>
                                <span class="time">'.date('d.m.Y H:i', strtotime($post['posted_at'])).'</span>
                                <p>'.nl2br($post['content']).'</p>
                            </div>
                            ';
                        }
                    ?>
                </div>
            </div>
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










