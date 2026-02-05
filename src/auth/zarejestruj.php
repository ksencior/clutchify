<?php
require_once __DIR__ . '/../core/connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['mc_nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nickname === '' || $email === '' || $password === '' || $password2 === '') {
        redirect_to('register.php?err=no-inputs');
        exit;
    }

    if (strlen($password) < 3) {
        redirect_to('register.php?err=no-inputs');
        exit;
    }

    if (strlen($nickname) < 3) {
        redirect_to('register.php?err=no-inputs');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@zsngasawa.pl')) {
        redirect_to('register.php?err=no-inputs');
        exit;
    }

    if ($password !== $password2) {
        redirect_to('register.php?err=no-inputs');
        exit;
    }

    try {
        // Sprawdź, czy email już istnieje
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :nickname");
        $stmt->execute(['email' => $email, 'nickname' => $nickname]);
        if ($stmt->fetchColumn() > 0) {
            redirect_to('register.php?err=no-inputs');
            exit;
        }

        // Zapisz użytkownika
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, email, password_hash) VALUES (:nickname, :email, :hash)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nickname' => $nickname,
            'email' => $email,
            'hash' => $hash
        ]);

        redirect_to('register.php?err=no-inputs');
        exit;
    } catch (PDOException $e) {
        redirect_to('register.php?err=no-inputs');
        exit;
    }
}
?>








