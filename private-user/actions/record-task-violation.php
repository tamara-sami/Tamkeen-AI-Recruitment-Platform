<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../functions/tasks-functions.php");

header("Content-Type: application/json");

if (!isset($_SESSION['job_seeker_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

$attempt_id = (int)($_POST['attempt_id'] ?? 0);
$type = $_POST['type'] ?? '';

if ($attempt_id <= 0 || $type === '') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id
    FROM task_attempts
    WHERE id = ?
    AND job_seeker_id = ?
    LIMIT 1
");

$stmt->execute([
    $attempt_id,
    $_SESSION['job_seeker_id']
]);

$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    echo json_encode([
        "success" => false,
        "message" => "Attempt not found"
    ]);
    exit;
}

$result = recordTaskViolation(
    $conn,
    $attempt_id,
    $type
);

echo json_encode($result);

exit;