<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");


$task_id = (int)($_GET['id'] ?? 0);
$company_id = (int)($_SESSION['company_id'] ?? 0);

if ($task_id && $company_id) {
    $stmt = $conn->prepare("
        UPDATE tasks
        SET deleted = 0
        WHERE id = ?
        AND company_id = ?
    ");

    $stmt->execute([$task_id, $company_id]);
}

header("Location: /tamkeentest/public/task/archived-tasks.php");
exit;
