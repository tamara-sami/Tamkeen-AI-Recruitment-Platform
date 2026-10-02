<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");
require_once(__DIR__ . "/../functions/my-trainings-functions.php");
require_once(__DIR__ . "/../components/my-trainings-sections.php");

$activePage = "my-trainings";
$body_class = "task-dashboard-body";

$page = initMyTrainingsPage($conn);

$base_url = "/tamkeentest/";
$page['base_url'] = $base_url;

$companyName = $page['company_name'];

$extra_css = [
    "css/bootstrap.min.css",
    "css/style.css",
    "css/task-dashboardd.css"
];

include(__DIR__ . "/../../includes/task-header.php");
include(__DIR__ . "/../../includes/navbar-training.php");

renderMyTrainingsPage($page);

include(__DIR__ . "/../../includes/task-scripts.php");
include(__DIR__ . "/../../includes/scripts.php");