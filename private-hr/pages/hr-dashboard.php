<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");
require_once(__DIR__ . "/../functions/hr-dashboard-functions.php");
require_once(__DIR__ . "/../components/hr-dashboard-sections.php");

$activePage = "hr-dashboard";
$body_class = "hr-body tamkeen-dashboard-body";

$base_url = "/tamkeentest/";

$page = initHrDashboardPage($conn);
$page['base_url'] = $base_url;

$extra_css = [
    "css/style.css",
    "css/hr.css"
];

include(__DIR__ . "/../../includes/header.php");

renderHrDashboardPage($page);

include(__DIR__ . "/../../includes/scripts.php");