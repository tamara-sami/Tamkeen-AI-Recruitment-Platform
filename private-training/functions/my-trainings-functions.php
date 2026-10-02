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

function getMyTrainingsCounts($conn, $companyId)
{
    $countStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            SUM(status = 'published') AS published_count,
            SUM(status = 'draft') AS draft_count,
            SUM(status = 'pending_approval') AS pending_count,
            SUM(status = 'rejected') AS rejected_count
        FROM trainings
        WHERE company_id = ?
        AND deleted = 0
    ");

    $countStmt->execute([$companyId]);

    $counts = $countStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $archivedStmt = $conn->prepare("
        SELECT COUNT(*)
        FROM trainings 
        WHERE company_id = ?
        AND deleted = 1
    ");

    $archivedStmt->execute([$companyId]);

    return [
        'total_count' => (int)($counts['total_count'] ?? 0),
        'published_count' => (int)($counts['published_count'] ?? 0),
        'draft_count' => (int)($counts['draft_count'] ?? 0),
        'pending_count' => (int)($counts['pending_count'] ?? 0),
        'rejected_count' => (int)($counts['rejected_count'] ?? 0),
        'archived_count' => (int)$archivedStmt->fetchColumn(),
    ];
}

function getMyTrainings($conn, $companyId, $filter = 'all', $search = '')
{
    $query = "
        SELECT *
        FROM trainings
        WHERE company_id = ?
        AND deleted = 0
    ";

    $params = [$companyId];

    if ($filter === 'published') {
        $query .= " AND status = 'published'";
    } elseif ($filter === 'draft') {
        $query .= " AND status = 'draft'";
    } elseif ($filter === 'pending_approval') {
        $query .= " AND status = 'pending_approval'";
    } elseif ($filter === 'rejected') {
        $query .= " AND status = 'rejected'";
    }

    if ($search !== '') {
        $query .= "
            AND (
                title LIKE ?
                OR field LIKE ?
                OR training_type LIKE ?
                OR required_skills LIKE ?
                OR location LIKE ?
            )
        ";

        $searchTerm = "%" . $search . "%";

        array_push(
            $params,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm
        );
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function initMyTrainingsPage($conn)
{
    $companyId = trainingRequireCompany();

    $filter = $_GET['filter'] ?? 'all';

    if (!in_array($filter, ['all', 'published', 'draft', 'pending_approval', 'rejected'], true)) {
        $filter = 'all';
    }

    $search = trim($_GET['search'] ?? '');

    return [
        'company_id' => $companyId,
        'company_name' => getTrainingCompanyName($conn, $companyId),
        'filter' => $filter,
        'search' => $search,
        'counts' => getMyTrainingsCounts($conn, $companyId),
        'trainings' => getMyTrainings($conn, $companyId, $filter, $search),
        'fields' => [
            'Data Analysis',
            'Design',
            'Marketing',
            'Customer Service',
            'Programming',
            'HR',
        ],
    ];
}
