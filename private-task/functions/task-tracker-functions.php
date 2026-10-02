<?php

function initTaskTrackerPage(PDO $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $company_id = (int)($_SESSION['company_id'] ?? 0);
    $filter = $_GET['filter'] ?? 'all';
    $search = trim($_GET['search'] ?? '');

    $query = "
        SELECT *
        FROM tasks
        WHERE company_id = ?
        AND deleted = 0
    ";

    $params = [$company_id];

    if ($filter === 'published') {
        $query .= " AND status = 'published'";
    } elseif ($filter === 'draft') {
        $query .= " AND status = 'draft'";
    } elseif ($filter === 'overdue') {
        $query .= " AND deadline < CURDATE()";
    } elseif ($filter === 'upcoming') {
        $query .= " AND deadline >= CURDATE()";
    }

    if ($search !== '') {
        $query .= " AND (title LIKE ? OR category LIKE ? OR difficulty LIKE ?)";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $query .= " ORDER BY deadline ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $companyStmt = $conn->prepare("
        SELECT company_name
        FROM companies
        WHERE id = ?
    ");

    $companyStmt->execute([$company_id]);
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);

    $companyName = $company ? $company['company_name'] : "Company";

    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_tasks,
            SUM(status = 'published') AS published_tasks,
            SUM(status = 'draft') AS draft_tasks,
            SUM(deadline < CURDATE()) AS overdue_tasks,
            SUM(deadline >= CURDATE()) AS upcoming_tasks
        FROM tasks
        WHERE company_id = ?
        AND deleted = 0
    ");

    $statsStmt->execute([$company_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $totalTasks = (int)($stats['total_tasks'] ?? 0);
    $publishedTasks = (int)($stats['published_tasks'] ?? 0);
    $draftTasks = (int)($stats['draft_tasks'] ?? 0);
    $overdueTasks = (int)($stats['overdue_tasks'] ?? 0);
    $upcomingTasks = (int)($stats['upcoming_tasks'] ?? 0);

    $progress = $totalTasks > 0
        ? round(($publishedTasks / $totalTasks) * 100)
        : 0;

    return [
        'companyName' => $companyName,
        'filter' => $filter,
        'search' => $search,
        'tasks' => $tasks,
        'totalTasks' => $totalTasks,
        'publishedTasks' => $publishedTasks,
        'draftTasks' => $draftTasks,
        'overdueTasks' => $overdueTasks,
        'upcomingTasks' => $upcomingTasks,
        'progress' => $progress
    ];
}
