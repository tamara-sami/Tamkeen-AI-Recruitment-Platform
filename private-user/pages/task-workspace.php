<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../includes/user-auth.php");
require_once(__DIR__ . "/../functions/tasks-functions.php");
require_once(__DIR__ . "/../components/task-workspace-sections.php");

$page = initTaskWorkspacePage($conn);

$base_url = "../../";
$body_style = "
background:#F3F6FA;
font-family:'Inter',sans-serif;
color:#0F172A;
";
$extra_css = ["css/tasks.css"];

include(__DIR__ . "/../../includes/header.php");

renderTaskWorkspacePage($page);

include(__DIR__ . "/../../includes/scripts.php");
