<?php 
session_start();
include_once 'connect_db.php';

if (!isset($_SESSION['logged']) && $_SESSION['logged'] != true) {
    header('Location: index.php');
}

if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != true) {
    header('Location: index.php');
}


$pass = '';

if ($pass != '') {
    $hashed = password_hash($pass, PASSWORD_BCRYPT);
    echo $hashed;
}