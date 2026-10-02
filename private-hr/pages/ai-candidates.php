<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/ai-candidates-functions.php");
require_once(__DIR__ . "/../components/ai-candidates-sections.php");

$activePage = "ai-candidates";
$body_class = "hr-body";
$base_url = "/tamkeentest/";

handleHrAiCandidateAction($conn);

$page = initHrAiCandidatesPage($conn);
$page['base_url'] = $base_url;

$extra_css = [
    "css/hr.css"
];

include(__DIR__ . "/../../includes/header.php");
renderHrAiCandidatesPage($page);

include(__DIR__ . "/../../includes/scripts.php");