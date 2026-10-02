<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../config.php");
include_once("../includes/functions/home_sections.php");

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    exit;
}

echo smartHomeSearchDropdownHtml($conn, $query, 6);
?>
