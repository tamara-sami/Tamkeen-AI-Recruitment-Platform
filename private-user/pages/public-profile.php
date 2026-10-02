<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../functions/public-profile-functions.php");
require_once(__DIR__ . "/../components/public-profile-sections.php");

$page = initPublicProfilePage($conn);

$base_url = "../../";
$page['base_url'] = $base_url;

$body_class = "profile-page";
$body_style = "
background:#F3F6FA;
font-family:'Inter',sans-serif;
color:#0F172A;
";

$extra_css = ["css/profile.css"];

include(__DIR__ . "/../../includes/header.php");
if (isset($_SESSION['job_seeker_id'])) {
    include(__DIR__ . "/../../includes/navbar-user.php");
}
renderPublicProfilePage($page);

include(__DIR__ . "/../../includes/footer.php");
include(__DIR__ . "/../../includes/scripts.php");
