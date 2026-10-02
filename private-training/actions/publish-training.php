<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Training not found'
    ]);
    exit;
}

$training_id = $_POST['id'];
$company_id = $_SESSION['company_id'];

$stmt = $conn->prepare("
    SELECT * FROM trainings 
    WHERE id = ? AND company_id = ? AND deleted = 0
");
$stmt->execute([$training_id, $company_id]);
$training = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$training) {
    echo json_encode([
        'success' => false,
        'message' => 'Training not found'
    ]);
    exit;
}

$errors = [];

if (empty(trim($training['title'] ?? ''))) $errors[] = 'Title is required';
if (empty(trim($training['description'] ?? ''))) $errors[] = 'Description is required';
if (empty(trim($training['training_type'] ?? ''))) $errors[] = 'Training type is required';
if (empty(trim($training['field'] ?? ''))) $errors[] = 'Field is required';

if (empty($training['duration']) || !is_numeric($training['duration']) || $training['duration'] <= 0) {
    $errors[] = 'Duration must be greater than 0';
}

if (empty($training['start_date']) || $training['start_date'] < date('Y-m-d')) {
    $errors[] = 'Start date is required and cannot be in the past';
}

if (empty($training['end_date']) || $training['end_date'] < date('Y-m-d')) {
    $errors[] = 'End date is required and cannot be in the past';
}

if (!empty($training['start_date']) && !empty($training['end_date']) && $training['end_date'] < $training['start_date']) {
    $errors[] = 'End date cannot be before start date';
}

if (empty(trim($training['requirements'] ?? ''))) {
    $errors[] = 'Requirements are required';
}

if (!empty($training['seats']) && (!is_numeric($training['seats']) || $training['seats'] <= 0)) {
    $errors[] = 'Seats must be greater than 0';
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
    UPDATE trainings 
    SET status = 'pending_approval'
    WHERE id = ? AND company_id = ?
");

$success = $update->execute([$training_id, $company_id]);

echo json_encode([
    'success' => $success
]);
