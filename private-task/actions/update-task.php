<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$company_id = $_SESSION['company_id'];

$errors = [];

if (empty(trim($_POST['title'] ?? ''))) {
    $errors['title'] = 'Title is required';
}

if (empty(trim($_POST['description'] ?? ''))) {
    $errors['description'] = 'Description is required';
}

if (empty(trim($_POST['category'] ?? ''))) {
    $errors['category'] = 'Category is required';
}

if (empty(trim($_POST['difficulty'] ?? ''))) {
    $errors['difficulty'] = 'Difficulty is required';
}

if (
    empty(trim($_POST['estimated_time'] ?? '')) ||
    !is_numeric($_POST['estimated_time']) ||
    (int)$_POST['estimated_time'] < 1
) {
    $errors['estimated_time'] = 'Estimated time must be numbers only';
}

if (
    empty(trim($_POST['minimum_focus_minutes'] ?? '')) ||
    !is_numeric($_POST['minimum_focus_minutes']) ||
    (int)$_POST['minimum_focus_minutes'] < 1
) {
    $errors['minimum_focus_minutes'] =
        'Minimum focus minutes must be at least 1 minute';
}

if (
    empty($_POST['points']) ||
    !is_numeric($_POST['points']) ||
    $_POST['points'] <= 0
) {
    $errors['points'] = 'Points must be greater than 0';
}

if (
    empty($_POST['deadline']) ||
    $_POST['deadline'] < date('Y-m-d')
) {
    $errors['deadline'] = 'Deadline cannot be in the past';
}

if (empty(trim($_POST['required_skills'] ?? ''))) {
    $errors['required_skills'] = 'Required skills are required';
}

if (!empty($errors)) {

    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);

    exit;
}

$stmt = $conn->prepare("
    UPDATE tasks SET
    title = ?,
    description = ?,
    category = ?,
    difficulty = ?,
    estimated_time = ?,
    minimum_focus_minutes = ?,
    points = ?,
    deadline = ?,
    required_skills = ?
    WHERE id = ? AND company_id = ?
");

$success = $stmt->execute([
    trim($_POST['title']),
    trim($_POST['description']),
    $_POST['category'],
    $_POST['difficulty'],
    (int)$_POST['estimated_time'],
    (int)$_POST['minimum_focus_minutes'],
    $_POST['points'],
    $_POST['deadline'],
    trim($_POST['required_skills']),
    $id,
    $company_id
]);

echo json_encode([
    'success' => $success
]);