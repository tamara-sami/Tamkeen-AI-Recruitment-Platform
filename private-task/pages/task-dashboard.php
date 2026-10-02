<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/task-dashboard-functions.php");
require_once(__DIR__ . "/../components/task-dashboard-sections.php");

$activePage = "tasks-dashboard";
$body_class = "task-dashboard-body";

$base_url = "/tamkeentest/";

$page = initTaskDashboardPage($conn);

$page['base_url'] = $base_url;

$extra_css = [
    "css/style.css",
    "css/hr.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");
renderTaskDashboardPage($page);

include(__DIR__ . "/../../includes/scripts.php");