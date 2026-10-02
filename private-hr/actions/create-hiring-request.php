<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");
$company_id = (int)($_SESSION['company_id'] ?? 0);
$hr_user_id = (int)($_SESSION['user_id'] ?? 0);

$job_seeker_id = (int)($_POST['job_seeker_id'] ?? 0);
$hiring_type = $_POST['hiring_type'] ?? 'full_time';

$allowedTypes = ['full_time', 'internship', 'temporary', 'freelance'];

if (!$company_id || !$hr_user_id || !$job_seeker_id || !in_array($hiring_type, $allowedTypes, true)) {
    header("Location: /tamkeentest/public/hr/interviews.php");
    exit;
}

$interviewStmt = $conn->prepare("
    SELECT id, notes
    FROM interviews
    WHERE company_id = ?
    AND job_seeker_id = ?
    AND status = 'completed'
    ORDER BY created_at DESC
    LIMIT 1
");

$interviewStmt->execute([
    $company_id,
    $job_seeker_id
]);

$interview = $interviewStmt->fetch(PDO::FETCH_ASSOC);

$interview_id = $interview['id'] ?? null;
$note = trim($interview['notes'] ?? '');

$check = $conn->prepare("
    SELECT id
    FROM hiring_requests
    WHERE company_id = ?
    AND job_seeker_id = ?
    AND hiring_type = ?
");

$check->execute([
    $company_id,
    $job_seeker_id,
    $hiring_type
]);

$existing = $check->fetch(PDO::FETCH_ASSOC);

if ($existing) {

    $updateRequest = $conn->prepare("
        UPDATE hiring_requests
        SET
            interview_id = ?,
            note = ?,
            status = 'pending',
            admin_note = NULL,
            created_at = NOW()
        WHERE id = ?
    ");

    $updateRequest->execute([
        $interview_id,
        $note,
        $existing['id']
    ]);

} else {

    $stmt = $conn->prepare("
        INSERT INTO hiring_requests
        (
            company_id,
            hr_user_id,
            job_seeker_id,
            interview_id,
            hiring_type,
            note,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $company_id,
        $hr_user_id,
        $job_seeker_id,
        $interview_id,
        $hiring_type,
        $note
    ]);
}

$update = $conn->prepare("
    UPDATE hr_shortlists
    SET status = 'hiring_request'
    WHERE company_id = ?
    AND job_seeker_id = ?
");

$update->execute([
    $company_id,
    $job_seeker_id
]);


header("Location: /tamkeentest/public/hr/interviews.php");
exit;
