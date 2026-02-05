<?php
require_once __DIR__ . '/../core/connect_db.php';
session_unset(); // Usunięcie wszystkich zmiennych sesji
session_destroy(); // Zniszczenie sesji
redirect_to('index.php'); // Przekierowanie do strony logowania
exit;
?>







