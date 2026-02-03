<?php
session_start();
include_once '../src/connect_db.php';

if (!isset($_POST['mecz_id']) || !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Brak danych']);
    exit;
}

$meczId = (int)$_POST['mecz_id'];
$userId = (int)$_SESSION['id'];

