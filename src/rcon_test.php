<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once 'connect_db.php';
date_default_timezone_set("Europe/Warsaw");

include_once './rcon_connect.php';
$rcon->sendCommand('map de_anubis');