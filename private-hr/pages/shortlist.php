<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/helpers.php");
require_once(__DIR__ . "/../functions/hr-shortlist-functions.php");
require_once(__DIR__ . "/../components/shortlist-sections.php");

$activePage = "shortlist";
$body_class = "hr-body tamkeen-shortlist-body";

$base_url = "/tamkeentest/";

$pageData = getHrShortlistPageData(
    $conn,
    (int)$_SESSION['company_id']
);

$pageData['base_url'] = $base_url;

$extra_css = [
    "css/style.css",
    "css/hr.css"
];

include(__DIR__ . "/../../includes/header.php");

renderHrShortlistPage($pageData);

include(__DIR__ . "/../../includes/scripts.php");
