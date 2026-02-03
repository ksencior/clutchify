<?php 
session_start();
include_once 'src/connect_db.php';

if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
    header('Location: index.php');
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
    <title>Zaloguj się</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/6fb5402435.js" crossorigin="anonymous"></script>
    <meta name="description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://zsnturniej.0bg.pl/" />
    <meta property="og:title" content="ZSN CHAMPIONS III" />
    <meta property="og:description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />
    <meta property="og:image" content="https://zsnturniej.0bg.pl/img/promo_poster.png" />

    <!-- X (Twitter) -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://zsnturniej.0bg.pl/" />
    <meta property="twitter:title" content="ZSN CHAMPIONS III" />
    <meta property="twitter:description" content="Turniej CS2 dla Zespołu Szkół Niepublicznych w Gąsawie" />
    <meta property="twitter:image" content="https://zsnturniej.0bg.pl/img/promo_poster.png" />
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
            <form action="src/zaloguj.php" method="post">
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