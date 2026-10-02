<?php

if (!function_exists('training_e')) {
    function training_e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function trainingRequireCompany()
{
    if (!isset($_SESSION['company_id'])) {
        header("Location: ../../public/company/login.php");
        exit;
    }

    return (int)$_SESSION['company_id'];
}

function getTrackerCompanyName($conn, $companyId)
{
    $stmt = $conn->prepare("
        SELECT company_name
        FROM companies
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$companyId]);

    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    return $company['company_name'] ?? 'Company';
}

function getTrackerApplications($conn, $companyId, $status = 'all', $trainingId = '')
{
    $params = [$companyId];

    $query = "
        SELECT 
            ta.*,
            t.title AS training_title,
            t.field,
            t.training_type,
            t.start_date,
            t.end_date,
            js.full_name,
            js.email AS user_email,
            js.mobile AS user_mobile,
            js.profile_image,
            (
                SELECT GROUP_CONCAT(tc.comment SEPARATOR ' || ')
                FROM training_comments tc
                WHERE tc.training_id = ta.training_id
                AND tc.job_seeker_id = ta.job_seeker_id
            ) AS interest_notes
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE t.company_id = ?
    ";

    if ($status !== 'all') {
        $query .= " AND ta.status = ? ";
        $params[] = $status;
    }

    if ($trainingId !== '') {
        $query .= " AND ta.training_id = ? ";
        $params[] = $trainingId;
    }

    $query .= " ORDER BY ta.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrackerStats($conn, $companyId)
{
    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            SUM(ta.status = 'pending') AS pending_count,
            SUM(ta.status = 'reviewing') AS reviewing_count,
            SUM(ta.status = 'interview') AS interview_count,
            SUM(ta.status = 'accepted') AS accepted_count,
            SUM(ta.status = 'rejected') AS rejected_count
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        WHERE t.company_id = ?
    ");

    $statsStmt->execute([$companyId]);

    return $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getTrackerTrainings($conn, $companyId)
{
    $stmt = $conn->prepare("
        SELECT id, title
        FROM trainings
        WHERE company_id = ?
        AND deleted = 0
        ORDER BY created_at DESC
    ");

    $stmt->execute([$companyId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function initTrainingTrackerPage($conn)
{
    $companyId = trainingRequireCompany();

    $status = $_GET['status'] ?? 'all';
    $trainingId = $_GET['training_id'] ?? '';

    return [
        'company_id' => $companyId,
        'company_name' => getTrackerCompanyName($conn, $companyId),
        'status' => $status,
        'training_id' => $trainingId,
        'applications' => getTrackerApplications($conn, $companyId, $status, $trainingId),
        'stats' => getTrackerStats($conn, $companyId),
        'trainings' => getTrackerTrainings($conn, $companyId),
    ];
}
