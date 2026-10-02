<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/archived-tasks-functions.php");
require_once(__DIR__ . "/../components/archived-tasks-sections.php");

$activePage = "archived-tasks";
$dashboard_type = "tasks";
$body_class = "task-dashboard-body";

$base_url = "/tamkeentest/";

$page = initArchivedTasksPage($conn);
$page['base_url'] = $base_url;

$companyName = $page['companyName'] ?? 'Company';

$extra_css = [
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");

renderArchivedTasksPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");
