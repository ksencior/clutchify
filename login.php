<?php 
include_once 'src/core/connect_db.php';

if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
    redirect_to('index.php');
}
$error = NULL;
if (isset($_GET['err']) && $_GET['err'] != '') {
    $error = $_GET['err'];
}
$errorMsg = '';
switch($error) {
    case 'email':
        $errorMsg = "Nieprawidłowy adres e-mail.";
        break;
    case 'password':
        $errorMsg = "Nieprawidłowe hasło.";
        break;
    case 'server':
        $errorMsg = "Wystąpił błąd serwera.";
        break;
    default:
        $errorMsg = '';
        break;
}
$registered = NULL;
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $registered = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaloguj się | <?= htmlspecialchars(Config::get('app_name', 'Clutchify.gg')) ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="assets/img/clutchify-w-o-text.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
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
<?php include 'src/views/partials/head.php'; ?>
</head>
<body>
    <div id="root" class="root-login">
        <div class="lines">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>

        <div class="login-box">
            <h1>Zaloguj się</h1>
            <form action="src/auth/zaloguj.php" method="post">
                <label for="email">Adres e-mail</label>
                <input type="email" name="email" id="email">
                <label for="password">Hasło:</label>
                <input type="password" name="password" id="password">
                <input type="submit" value="Zaloguj się">
                <span style="<?= $registered? "color: green;" : "color: red;" ?>">
                    <?php 
                        if (isset($error)) {
                            echo $errorMsg;
                        }
                        if ($registered) {
                            echo "Rejestracja zakończona sukcesem. Możesz się teraz zalogować.";
                        }
                    ?>
                </span>
                <a href="register.php">Nie masz jeszczce konta?</a>
            </form>
        </div>
    </div>
</body>
</html>










