<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/ai-review-functions.php");
require_once(__DIR__ . "/../components/ai-review-sections.php");

$activePage = "ai-review";
$dashboard_type = "tasks";
$body_class = "task-dashboard-body";
$base_url = "/tamkeentest/";

handleAiReviewAction($conn);

$page = initAiReviewPage($conn);
$page['base_url'] = $base_url;
$page['conn'] = $conn;

$companyName = $page['companyName'] ?? 'Company';

$extra_css = [
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/task-navbar.php");

renderAiReviewPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");
