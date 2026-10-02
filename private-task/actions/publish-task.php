<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

header('Content-Type: application/json');

if (empty($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Task not found'
    ]);
    exit;
}

$task_id = (int)$_POST['id'];
$company_id = (int)($_SESSION['company_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT *
    FROM tasks
    WHERE id = ?
    AND company_id = ?
    AND deleted = 0
");
$stmt->execute([$task_id, $company_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo json_encode([
        'success' => false,
        'message' => 'Task not found'
    ]);
    exit;
}

$errors = [];

if (empty(trim($task['title'] ?? ''))) {
    $errors[] = 'Title is required';
}

if (empty(trim($task['description'] ?? ''))) {
    $errors[] = 'Description is required';
}

if (empty(trim($task['category'] ?? ''))) {
    $errors[] = 'Category is required';
}

if (empty(trim($task['difficulty'] ?? ''))) {
    $errors[] = 'Difficulty is required';
}

if (empty(trim($task['estimated_time'] ?? ''))) {
    $errors[] = 'Estimated time is required';
}

if (
    empty($task['minimum_focus_minutes']) ||
    !is_numeric($task['minimum_focus_minutes']) ||
    (int)$task['minimum_focus_minutes'] < 1
) {
    $errors[] = 'Minimum focus minutes must be at least 1 minute';
}

if (empty($task['points']) || !is_numeric($task['points']) || $task['points'] <= 0) {
    $errors[] = 'Points must be greater than 0';
}

if (empty($task['deadline']) || $task['deadline'] < date('Y-m-d')) {
    $errors[] = 'Deadline is required and cannot be in the past';
}

if (empty(trim($task['required_skills'] ?? ''))) {
    $errors[] = 'Required skills are required';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Complete all fields before publishing',
        'errors' => $errors
    ]);
    exit;
}

$update = $conn->prepare("
    UPDATE tasks
SET status = 'pending_approval'
    WHERE id = ?
    AND company_id = ?
");

$success = $update->execute([$task_id, $company_id]);

echo json_encode([
    'success' => $success
]);