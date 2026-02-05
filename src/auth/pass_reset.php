<?php 
require_once __DIR__ . '/../core/connect_db.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] != true) {
    redirect_to('index.php');
    exit;
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    redirect_to('index.php');
    exit;
}


$pass = '';

if ($pass != '') {
    $hashed = password_hash($pass, PASSWORD_BCRYPT);
    echo $hashed;
}







