<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../functions/tasks-functions.php");

header("Content-Type: application/json");

if (!isset($_SESSION['job_seeker_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

$attempt_id = (int)($_POST['attempt_id'] ?? 0);
$answer_text = $_POST['answer_text'] ?? '';

if ($attempt_id <= 0) {
    echo json_encode(["success" => false]);
    exit;
}

$success = autosaveTaskAttempt(
    $conn,
    $attempt_id,
    $_SESSION['job_seeker_id'],
    $answer_text
);

echo json_encode(["success" => $success]);
exit;