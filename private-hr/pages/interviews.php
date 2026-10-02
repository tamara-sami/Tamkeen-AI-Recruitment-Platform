<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/helpers.php");
require_once(__DIR__ . "/../functions/hr-interviews-functions.php");
require_once(__DIR__ . "/../actions/interviews-handler.php");
require_once(__DIR__ . "/../components/interviews-sections.php");

$activePage = "interviews";
$body_class = "hr-body tamkeen-interviews-body";

$base_url = "/tamkeentest/";

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'status' => $_GET['status'] ?? 'all',
    'type' => $_GET['type'] ?? 'all'
];

$page = getHrInterviewsPageData(
    $conn,
    (int)$_SESSION['company_id'],
    $filters
);

$page['filters'] = $filters;
$page['errors'] = $errors ?? [];
$page['successMessage'] = $successMessage ?? '';
$page['base_url'] = $base_url;

$extra_css = [
    "css/bootstrap.min.css",
    "css/style.css",
    "css/hr.css",
    "css/hr-interviews.css"
];

include(__DIR__ . "/../../includes/header.php");

renderHrInterviewsPage($page);

include(__DIR__ . "/../../includes/scripts.php");
