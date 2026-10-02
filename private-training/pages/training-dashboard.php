<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");
require_once(__DIR__ . "/../functions/training-dashboard-functions.php");
require_once(__DIR__ . "/../components/training-dashboard-sections.php");

$activePage = "training-dashboard";

$page = initTrainingDashboardPage($conn);

$companyName = $page['company_name'] ?? ($_SESSION['company_name'] ?? 'Company');
$base_url = "/tamkeentest/";
$page['base_url'] = $base_url;
$extra_css = [
    "css/bootstrap.min.css",
    "css/style.css",
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/navbar-training.php");

renderTrainingDashboardPage($page);

include(__DIR__ . "/../../includes/footer.php");
include(__DIR__ . "/../../includes/scripts.php");