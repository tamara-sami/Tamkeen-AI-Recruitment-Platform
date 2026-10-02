<?php
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

$company_id = $_SESSION['company_id'];
$hr_user_id = $_SESSION['user_id'] ?? 0;

$job_seeker_id = $_POST['job_seeker_id'] ?? null;
$hiring_type = $_POST['hiring_type'] ?? 'full_time';

$allowedTypes = ['full_time', 'internship', 'temporary', 'freelance'];

if (!$job_seeker_id || !in_array($hiring_type, $allowedTypes)) {
    header("Location: hr-dashboard.php");
    exit;
}

$check = $conn->prepare("
    SELECT id 
    FROM hr_shortlists
    WHERE company_id = ?
    AND job_seeker_id = ?
");
$check->execute([$company_id, $job_seeker_id]);

if (!$check->fetch(PDO::FETCH_ASSOC)) {
    $stmt = $conn->prepare("
        INSERT INTO hr_shortlists
        (company_id, hr_user_id, job_seeker_id, hiring_type, status)
        VALUES (?, ?, ?, ?, 'new')
    ");

    $stmt->execute([
        $company_id,
        $hr_user_id,
        $job_seeker_id,
        $hiring_type
    ]);
}

header("Location: /tamkeentest/public/hr/candidates.php");
exit;