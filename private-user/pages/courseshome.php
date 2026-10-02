<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../includes/user-auth.php");
require_once(__DIR__ . "/../functions/courses-home-functions.php");
require_once(__DIR__ . "/../components/courses-home-sections.php");

$page = initCoursesHomePage($conn);

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

$extra_css = ["css/courses.css"];

include(__DIR__ . "/../../includes/header.php");
include(__DIR__ . "/../../includes/navbar-user.php");

renderCoursesHomePage($page, $conn);
include(__DIR__ . "/../../includes/footer.php");
include(__DIR__ . "/../../includes/scripts.php");
