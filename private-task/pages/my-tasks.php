<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/my-tasks-functions.php");
require_once(__DIR__ . "/../components/my-tasks-sections.php");

$activePage = "my-tasks";
$dashboard_type = "tasks";
$body_class = "task-dashboard-body";

$base_url = "/tamkeentest/";

$page = initMyTasksPage($conn);
$page['base_url'] = $base_url;

$companyName = $page['companyName'];

$extra_css = [
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");

renderMyTasksPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");