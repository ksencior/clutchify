<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../core/connect_db.php';
date_default_timezone_set("Europe/Warsaw");

require_once __DIR__ . '/rcon_connect.php';
$rcon->sendCommand('map de_anubis');
