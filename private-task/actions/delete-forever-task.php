<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");


header('Content-Type: application/json');

$task_id = (int)($_POST['id'] ?? 0);
$company_id = (int)($_SESSION['company_id'] ?? 0);

if (!$task_id || !$company_id) {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM tasks
    WHERE id = ?
    AND company_id = ?
    AND deleted = 1
");

echo json_encode([
    'success' => $stmt->execute([$task_id, $company_id])
]);
