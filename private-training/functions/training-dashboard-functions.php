<?php

if (!function_exists('training_e')) {
    function training_e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function getTrainingCompanyName($conn, $companyId)
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

function getTrainingDashboardStats($conn, $companyId)
{
    $stmt = $conn->prepare("
        SELECT 
            SUM(status = 'published') AS active_count,
            SUM(status = 'draft') AS draft_count,
            SUM(status = 'pending_approval') AS pending_count,
            SUM(status = 'rejected') AS rejected_count,
            COUNT(*) AS total_count
        FROM trainings
        WHERE company_id = ?
        AND deleted = 0
    ");

    $stmt->execute([$companyId]);

    $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'active_count' => (int)($stats['active_count'] ?? 0),
        'draft_count' => (int)($stats['draft_count'] ?? 0),
        'pending_count' => (int)($stats['pending_count'] ?? 0),
        'rejected_count' => (int)($stats['rejected_count'] ?? 0),
        'total_count' => (int)($stats['total_count'] ?? 0),
    ];
}

function getCompanyTrainings($conn, $companyId, $filter = 'all')
{
    $query = "
        SELECT *
        FROM trainings
        WHERE company_id = ?
        AND deleted = 0
    ";

    $params = [$companyId];

    if ($filter === 'active') {
        $query .= " AND status = 'published'";
    } elseif ($filter === 'draft') {
        $query .= " AND status = 'draft'";
    } elseif ($filter === 'pending_approval') {
        $query .= " AND status = 'pending_approval'";
    } elseif ($filter === 'rejected') {
        $query .= " AND status = 'rejected'";
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function initTrainingDashboardPage($conn)
{
    $companyId = $_SESSION['company_id'] ?? null;

    if (!$companyId) {
        header("Location: ../../public/company/login.php");
        exit;
    }

    $filter = $_GET['filter'] ?? 'all';

    if (!in_array($filter, ['all', 'active', 'draft', 'pending_approval', 'rejected'], true)) {
        $filter = 'all';
    }

    $companyName = getTrainingCompanyName($conn, $companyId);

    $stats = getTrainingDashboardStats($conn, $companyId);

    $trainings = getCompanyTrainings($conn, $companyId, $filter);

    $recentTrainings = array_slice($trainings, 0, 3);

    return [
        'company_id' => $companyId,
        'company_name' => $companyName,
        'filter' => $filter,
        'stats' => $stats,
        'trainings' => $trainings,
        'recent_trainings' => $recentTrainings,
    ];
}
