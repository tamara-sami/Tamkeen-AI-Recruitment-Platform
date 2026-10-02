<?php

function initMyTasksPage(PDO $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $company_id = (int)($_SESSION['company_id'] ?? 0);
    $filter = $_GET['filter'] ?? 'all';
    $search = trim($_GET['search'] ?? '');

    $stmt = $conn->prepare("SELECT company_name FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    $companyName = $company ? $company['company_name'] : "Company";

    $countStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            SUM(status = 'published') AS published_count,
            SUM(status = 'draft') AS draft_count
        FROM tasks
        WHERE company_id = ? AND deleted = 0
    ");
    $countStmt->execute([$company_id]);
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalCount = $counts['total_count'] ?? 0;
    $publishedCount = $counts['published_count'] ?? 0;
    $draftCount = $counts['draft_count'] ?? 0;

    $stmt2 = $conn->prepare("
        SELECT COUNT(*) FROM tasks
        WHERE company_id = ? AND deleted = 1
    ");
    $stmt2->execute([$company_id]);
    $archivedCount = $stmt2->fetchColumn();

    $query = "
        SELECT *
        FROM tasks
        WHERE company_id = ? AND deleted = 0
    ";
    $params = [$company_id];

    if ($filter === 'published') {
        $query .= " AND status = 'published'";
    } elseif ($filter === 'draft') {
        $query .= " AND status = 'draft'";
    }

    if ($search !== '') {
        $query .= " AND (title LIKE ? OR category LIKE ? OR difficulty LIKE ? OR required_skills LIKE ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'companyName' => $companyName,
        'filter' => $filter,
        'search' => $search,
        'tasks' => $tasks,
        'totalCount' => $totalCount,
        'publishedCount' => $publishedCount,
        'draftCount' => $draftCount,
        'archivedCount' => $archivedCount
    ];
}
