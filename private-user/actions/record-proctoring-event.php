<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../proctoring/proctoring-functions.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

if (!isset($_SESSION['job_seeker_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);
    exit;
}

$attempt_id = (int)($_POST['attempt_id'] ?? 0);
$event_type = trim((string)($_POST['event_type'] ?? ''));
$severity = trim((string)($_POST['severity'] ?? 'medium'));
$details = (string)($_POST['details'] ?? '');

if ($attempt_id <= 0 || $event_type === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}

$result = recordProctoringEvent(
    $conn,
    $attempt_id,
    (int)$_SESSION['job_seeker_id'],
    $event_type,
    $severity,
    $details
);

if (!$result['success']) {
    http_response_code(400);
}

echo json_encode($result);
exit;
