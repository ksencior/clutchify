<?php
session_start();
require_once __DIR__ . '/../core/connect_db.php';
if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {
    header('Location: ../index.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM users WHERE email=:email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['logged'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['steam_id'] = $user['steam_id'];
            $_SESSION['avatar_url'] = $user['avatar_url'];
            $_SESSION['team_id'] = $user['team_id'];
            $_SESSION['isAdmin'] = $user['isAdmin'] == 1 ? true : false;
            $_SESSION['isSpectator'] = $user['isSpectator'] == 1 ? true : false;


            if (isset($user['steam_id']) && $user['steam_id'] != null) {
                include __DIR__ . '/fetch_steam_data.php';
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $err = "Niepoprawny e-mail lub/i haslo!";
            header('Location: ../login.php?err=' . ( !$user ? 'email' : 'password' ) );
            exit();
        }
    } catch (PDOException $e) {
        $err = "Wystąpił błąd: " . $e->getMessage();
        header('Location: ../login.php?err=server');
        exit();
    }
}
?>

