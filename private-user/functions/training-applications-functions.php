<?php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function initMyTrainingApplicationsPage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user_id = (int)$_SESSION['job_seeker_id'];

    $filter = $_GET['status'] ?? 'all';

    $errors = $_SESSION['application_update_errors'] ?? [];
    $old = $_SESSION['application_update_old'] ?? [];
    $success = $_SESSION['application_update_success'] ?? '';

    unset(
        $_SESSION['application_update_errors'],
        $_SESSION['application_update_old'],
        $_SESSION['application_update_success']
    );

    $applications = getUserTrainingApplications($conn, $user_id, $filter);
    $stats = getUserTrainingApplicationStats($conn, $user_id);
    $savedTrainings = getUserSavedTrainings($conn, $user_id);
    $notes = getUserTrainingNotes($conn, $user_id);

    return [
        'user_id' => $user_id,
        'filter' => $filter,
        'errors' => $errors,
        'old' => $old,
        'success' => $success,
        'applications' => $applications,
        'stats' => $stats,
        'savedTrainings' => $savedTrainings,
        'notes' => $notes,

        'totalCount' => $stats['total_count'] ?? 0,
        'pendingCount' => $stats['pending_count'] ?? 0,
        'reviewingCount' => $stats['reviewing_count'] ?? 0,
        'interviewCount' => $stats['interview_count'] ?? 0,
        'acceptedCount' => $stats['accepted_count'] ?? 0,
        'rejectedCount' => $stats['rejected_count'] ?? 0
    ];
}

function getUserTrainingApplications($conn, $user_id, $filter = 'all')
{
    $whereStatus = "";
    $params = [$user_id];

    if (in_array($filter, ['pending', 'reviewing', 'interview', 'accepted', 'rejected'])) {
        $whereStatus = " AND ta.status = ? ";
        $params[] = $filter;
    }

    $stmt = $conn->prepare("
        SELECT 
            ta.*,
            t.title,
            t.field,
            t.training_type,
            t.start_date,
            t.end_date,
            t.location,
            c.company_name
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        JOIN companies c ON c.id = t.company_id
        WHERE ta.job_seeker_id = ?
        $whereStatus
        ORDER BY ta.created_at DESC
    ");

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserTrainingApplicationStats($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            SUM(status = 'pending') AS pending_count,
            SUM(status = 'reviewing') AS reviewing_count,
            SUM(status = 'interview') AS interview_count,
            SUM(status = 'accepted') AS accepted_count,
            SUM(status = 'rejected') AS rejected_count
        FROM training_applications
        WHERE job_seeker_id = ?
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserSavedTrainings($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT 
            ts.*,
            t.title,
            t.field,
            t.training_type,
            t.duration,
            t.location,
            c.company_name
        FROM training_saved ts
        JOIN trainings t ON t.id = ts.training_id
        JOIN companies c ON c.id = t.company_id
        WHERE ts.job_seeker_id = ?
        ORDER BY ts.created_at DESC
        LIMIT 5
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserTrainingNotes($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT 
            tc.*,
            t.title,
            c.company_name
        FROM training_comments tc
        JOIN trainings t ON t.id = tc.training_id
        JOIN companies c ON c.id = t.company_id
        WHERE tc.job_seeker_id = ?
        ORDER BY tc.created_at DESC
        LIMIT 5
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
