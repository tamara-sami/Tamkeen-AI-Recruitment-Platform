<?php

session_start();

require_once "../../private-config/db.php";

/*
|--------------------------------------------------------------------------
| Security Checks
|--------------------------------------------------------------------------
*/

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]));
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]));
}

/*
|--------------------------------------------------------------------------
| Get Data
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['user_id'];

$task_attempt_id = intval($_POST['task_attempt_id'] ?? 0);

$event_type = trim($_POST['event_type'] ?? '');

$severity = trim($_POST['severity'] ?? 'medium');

$details = trim($_POST['details'] ?? '');

/*
|--------------------------------------------------------------------------
| Allowed Events
|--------------------------------------------------------------------------
*/

$allowed_events = [
    'camera_denied',
    'camera_black',
    'camera_stopped',
    'no_face_detected',
    'multiple_faces',
    'looking_away',
    'tab_switch',
    'copy_detected',
    'paste_detected',
    'fullscreen_exit'
];

if (!in_array($event_type, $allowed_events)) {

    http_response_code(400);

    exit(json_encode([
        'success' => false,
        'message' => 'Invalid event type'
    ]));
}

/*
|--------------------------------------------------------------------------
| Insert Event
|--------------------------------------------------------------------------
*/

$sql = "
INSERT INTO task_proctoring_events
(
    task_attempt_id,
    user_id,
    event_type,
    severity,
    details
)
VALUES
(
    ?, ?, ?, ?, ?
)
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iisss",
    $task_attempt_id,
    $user_id,
    $event_type,
    $severity,
    $details
);

$success = $stmt->execute();

echo json_encode([
    'success' => $success
]);
