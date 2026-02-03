<?php 
include_once 'src/connect_db.php';
include_once 'src/fetch_steam_data.php';
if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: login.php');
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
    <title><?= Config::get('app_name', 'ZSN Champions III') ?></title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <script src="src/notifications.js?v=<?= time() ?>"></script>
    <script src="src/chat.js"></script>
    <!-- Primary Meta Tags -->
    <meta name="title" content="ZSN CHAMPIONS III" />
    <meta name="description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://zsnturniej.0bg.pl/" />
    <meta property="og:title" content="ZSN CHAMPIONS III" />
    <meta property="og:description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />
    <meta property="og:image" content="https://metatags.io/images/meta-tags.png" />

    <!-- X (Twitter) -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://zsnturniej.0bg.pl/" />
    <meta property="twitter:title" content="ZSN CHAMPIONS III" />
    <meta property="twitter:description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />
    <meta property="twitter:image" content="https://metatags.io/images/meta-tags.png" />

    <!-- Meta Tags Generated with https://metatags.io -->
</head>
<body>
    <div id="root">
        <?php include 'src/navbar.php'; ?>
        <div class="content">
            <img src="img/promo_poster.png" alt="" class="promo-poster">
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
                        channel: "zsn_gasawa",
                        autoplay: true,
                        layout: "video",
                        muted: false
                    });
                </script>
            </div>
            <?php endif; ?>
            <div class="account-info">
                <h1>Twoje konto</h1>
                    <img src="<?php echo isset($_SESSION['avatar_url']) && !empty($_SESSION['avatar_url']) ? $_SESSION['avatar_url'] : 'img/avatar_default.png'; ?>" alt="Avatar uzytkownika" class="avatar">
                <div class="info">
                    <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <?php 
                    if (empty($_SESSION['steam_id']) || $_SESSION['steam_id'] == NULL) {
                        echo '
                        <div class="steam-connect">
                            <p>Nie połączyłeś jeszcze swojego konta steam.</p>
                            <button onclick="location.href=`src/connect_steam.php`"><i class="fa-brands fa-steam"></i>Połącz za pomocą steam</button>
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
        <?php include 'src/sidebar_load.php';?>
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
    <script src="src/mobile-menu.js"></script>
</body>
</html>