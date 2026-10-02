<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/helpers.php");
require_once(__DIR__ . "/../functions/hr-hiring-approvals-functions.php");
require_once(__DIR__ . "/../components/hiring-requests-sections.php");

$activePage = "hiring-requests";
$body_class = "hr-body tamkeen-approvals-body";

$base_url = "/tamkeentest/";

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'status' => $_GET['status'] ?? 'all',
    'type'   => $_GET['type'] ?? 'all'
];

$page = getHrHiringApprovalPageData(
    $conn,
    (int)$_SESSION['company_id'],
    $filters
);

$page['filters'] = $filters;
$page['base_url'] = $base_url;

$extra_css = [
    "css/style.css",
    "css/hr.css",
    "css/hr-hiring-approvals.css"
];

include(__DIR__ . "/../../includes/header.php");

renderHrHiringRequestsPage($page);

include(__DIR__ . "/../../includes/scripts.php");
