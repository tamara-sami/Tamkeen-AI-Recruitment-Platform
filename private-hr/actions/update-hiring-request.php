<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

$company_id = (int)($_SESSION['company_id'] ?? 0);

$request_id = $_POST['request_id'] ?? null;
$hiring_type = $_POST['hiring_type'] ?? '';
$note = trim($_POST['note'] ?? '');

$allowedTypes = ['full_time', 'internship', 'temporary', 'freelance'];

if (!$company_id || !$request_id || !in_array($hiring_type, $allowedTypes, true)) {
    header("Location: /tamkeentest/public/hr/hiring-requests.php");
    exit;
}

$stmt = $conn->prepare("
    UPDATE hiring_requests
    SET hiring_type = ?,
        note = ?
    WHERE id = ?
    AND company_id = ?
    AND status = 'pending'
");

$stmt->execute([
    $hiring_type,
    $note,
    $request_id,
    $company_id
]);

header("Location: /tamkeentest/public/hr/hiring-requests.php");
exit;
