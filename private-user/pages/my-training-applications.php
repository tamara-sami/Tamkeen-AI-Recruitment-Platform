<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../includes/user-auth.php");
require_once(__DIR__ . "/../functions/training-applications-functions.php");
require_once(__DIR__ . "/../components/training-applications-sections.php");

$page = initMyTrainingApplicationsPage($conn);

$base_url = "../../";
$page['base_url'] = $base_url;

$body_style = "
background:
radial-gradient(circle at top left, rgba(37,99,235,.08), transparent 30%),
radial-gradient(circle at bottom right, rgba(59,130,246,.06), transparent 30%),
#F8FAFC;
font-family:'Inter',sans-serif;
color:#0F172A;
";

$extra_css = ["css/home-dashboard.css"];

include(__DIR__ . "/../../includes/header.php");
include(__DIR__ . "/../../includes/navbar-user.php");

renderMyTrainingApplicationsPage($page);

include(__DIR__ . "/../../includes/footer.php");
include(__DIR__ . "/../../includes/scripts.php");
