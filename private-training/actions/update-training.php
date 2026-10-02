<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$company_id = $_SESSION['company_id'];

$errors = [];

if (empty(trim($_POST['title'] ?? ''))) $errors['title'] = 'Title is required';
if (empty(trim($_POST['description'] ?? ''))) $errors['description'] = 'Description is required';
if (empty(trim($_POST['training_type'] ?? ''))) $errors['training_type'] = 'Training type is required';
if (empty(trim($_POST['field'] ?? ''))) $errors['field'] = 'Field is required';

if (empty($_POST['duration']) || !is_numeric($_POST['duration']) || $_POST['duration'] <= 0) {
    $errors['duration'] = 'Duration must be greater than 0';
}

if (empty($_POST['start_date']) || $_POST['start_date'] < date('Y-m-d')) {
    $errors['start_date'] = 'Start date cannot be in the past';
}

if (empty($_POST['end_date']) || $_POST['end_date'] < date('Y-m-d')) {
    $errors['end_date'] = 'End date cannot be in the past';
}

if (!empty($_POST['start_date']) && !empty($_POST['end_date']) && $_POST['end_date'] < $_POST['start_date']) {
    $errors['end_date'] = 'End date cannot be before start date';
}

if (empty(trim($_POST['requirements'] ?? ''))) {
    $errors['requirements'] = 'Requirements are required';
}

if (!empty($_POST['seats']) && (!is_numeric($_POST['seats']) || $_POST['seats'] <= 0)) {
    $errors['seats'] = 'Seats must be greater than 0';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE trainings SET
    title = ?,
    description = ?,
    training_type = ?,
    field = ?,
    location = ?,
    duration = ?,
    start_date = ?,
    end_date = ?,
    required_skills = ?,
    requirements = ?,
    seats = ?
    WHERE id = ? AND company_id = ?
");

$success = $stmt->execute([
    trim($_POST['title']),
    trim($_POST['description']),
    $_POST['training_type'],
    trim($_POST['field']),
    trim($_POST['location'] ?? ''),
    $_POST['duration'],
    $_POST['start_date'],
    $_POST['end_date'],
    trim($_POST['required_skills'] ?? ''),
    trim($_POST['requirements']),
    !empty($_POST['seats']) ? $_POST['seats'] : null,
    $id,
    $company_id
]);

echo json_encode(['success' => $success]);
