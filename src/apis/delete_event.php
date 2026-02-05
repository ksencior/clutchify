<?php
session_start();
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || !$_SESSION['logged']) {
    header('Location: /clutchify/login.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    header('Location: /clutchify/index.php');
    exit;
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE `id`=:id");
        $stmt->execute([':id' => $id]);
        header('Location: /clutchify/admin.php');
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

