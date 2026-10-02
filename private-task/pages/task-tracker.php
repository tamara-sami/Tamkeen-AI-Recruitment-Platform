<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/task-tracker-functions.php");
require_once(__DIR__ . "/../components/task-tracker-sections.php");

$activePage = "task-tracker";
$dashboard_type = "tasks";
$body_class = "task-dashboard-body";

$base_url = "/tamkeentest/";

$page = initTaskTrackerPage($conn);
$page['base_url'] = $base_url;

$companyName = $page['companyName'] ?? 'Company';

$extra_css = [
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");

renderTaskTrackerPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");
