<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

$company_id = $_SESSION['company_id'] ?? null;

$application_id = $_POST['application_id'] ?? null;
$status = $_POST['status'] ?? '';

$allowed = [
    'pending',
    'reviewing',
    'interview',
    'accepted',
    'rejected'
];

if (!$company_id || !$application_id || !in_array($status, $allowed, true)) {
    header("Location: ../../public/training/training-tracker.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT ta.id
    FROM training_applications ta
    JOIN trainings t 
        ON t.id = ta.training_id
    WHERE ta.id = ?
    AND t.company_id = ?
    LIMIT 1
");

$stmt->execute([
    $application_id,
    $company_id
]);

$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    header("Location: ../../public/training/training-tracker.php");
    exit;
}

$update = $conn->prepare("
    UPDATE training_applications
    SET status = ?
    WHERE id = ?
");

$update->execute([
    $status,
    $application_id
]);

header("Location: ../../public/training/training-tracker.php");
exit;
