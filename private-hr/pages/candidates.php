<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");
require_once(__DIR__ . "/../functions/helpers.php");
require_once(__DIR__ . "/../functions/hr-candidates-functions.php");
require_once(__DIR__ . "/../components/candidates-sections.php");

$activePage = "candidates";
$body_class = "hr-body tamkeen-candidates-body";

$base_url = "/tamkeentest/";

$page = initHrCandidatesPage($conn);
$page['base_url'] = $base_url;

$extra_css = [
    "css/hr.css"
];

include(__DIR__ . "/../../includes/header.php");

renderHrCandidatesPage($page);

include(__DIR__ . "/../../includes/scripts.php");
