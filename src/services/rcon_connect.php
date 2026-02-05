<?php
require __DIR__ . '/../../vendor/autoload.php';
$host = '51.83.175.128'; // Server host name or IP
$port = 25471;                      // Port rcon is listening on
$password = 'zsnturniej'; // rcon.password setting set in server.properties
$timeout = 3;                       // How long to timeout.

use Thedudeguy\Rcon;

$rcon = new Rcon($host, $port, $password, $timeout);

if (!($rcon->connect()))
{
    echo 'Nie udało się połączyć z RCON.';
    die();
}

