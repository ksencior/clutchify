<?php
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    redirect_to('login.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    redirect_to('login.php');
    exit;
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE `id`=:id");
        $stmt->execute([':id' => $id]);
        redirect_to('login.php');
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}







