<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/post-task-functions.php");
require_once(__DIR__ . "/../components/post-task-sections.php");

$activePage = "post-task";
$dashboard_type = "tasks";
$body_class = "task-dashboard-body";

$base_url = "/tamkeentest/";

handlePostTaskSubmit($conn);

$page = initPostTaskPage($conn);
$page['base_url'] = $base_url;

$companyName = $page['companyName'];

$extra_css = [
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");

renderPostTaskPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");