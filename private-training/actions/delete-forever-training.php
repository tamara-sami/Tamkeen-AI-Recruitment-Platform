<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$training_id = $_POST['id'];
$company_id = $_SESSION['company_id'];

$stmt = $conn->prepare("
    SELECT id 
    FROM trainings 
    WHERE id = ? AND company_id = ?
");
$stmt->execute([$training_id, $company_id]);
$training = $stmt->fetch(PDO::FETCH_ASSOC);

if ($training) {
    $delete = $conn->prepare("
        DELETE FROM trainings 
        WHERE id = ? AND company_id = ?
    ");

    $delete->execute([$training_id, $company_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
