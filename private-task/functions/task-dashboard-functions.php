<?php

function initTaskDashboardPage(PDO $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $companyId = (int)($_SESSION['company_id'] ?? 0);
    $filter = $_GET['filter'] ?? 'all';

    $stmt = $conn->prepare("
        SELECT company_name, company_logo
        FROM companies
        WHERE id = ?
    ");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $statsStmt = $conn->prepare("
    SELECT 
        SUM(status = 'published') AS active_count,
        SUM(status = 'draft') AS draft_count,
        COUNT(*) AS total_count
    FROM tasks
    WHERE company_id = ?
    AND deleted = 0
");
    $statsStmt->execute([$companyId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $query = "
    SELECT *
    FROM tasks
    WHERE company_id = ?
    AND deleted = 0
";

    $params = [$companyId];

    if ($filter === 'active') {
        $query .= " AND status = 'published'";
    } elseif ($filter === 'draft') {
        $query .= " AND status = 'draft'";
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);


      $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $aiStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_attempts,
            SUM(CASE WHEN ai_status = 'recommended' THEN 1 ELSE 0 END) AS recommended_count,
            SUM(CASE WHEN ai_status = 'needs_review' THEN 1 ELSE 0 END) AS review_count,
            MAX(ai_score) AS top_score
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
    ");

    $aiStmt->execute([$companyId]);
    $aiSummary = $aiStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $topStmt = $conn->prepare("
    SELECT
        js.full_name,
        js.job_title,
        ta.ai_score,
        ta.ai_status
    FROM task_attempts ta
    JOIN job_seekers js ON js.id = ta.job_seeker_id
    JOIN tasks t ON t.id = ta.task_id
    WHERE t.company_id = ?
    AND ta.ai_score IS NOT NULL
    ORDER BY ta.ai_score DESC
    LIMIT 3
");

$topStmt->execute([$companyId]);

$topTalents = $topStmt->fetchAll(PDO::FETCH_ASSOC);
return [
    'companyId' => $companyId,
    'company' => $company,
    'companyName' => $company['company_name'] ?? 'Company',
    'companyLogo' => $company['company_logo'] ?? '',
    'filter' => $filter,
    'activeTasks' => (int)($stats['active_count'] ?? 0),
    'draftTasks' => (int)($stats['draft_count'] ?? 0),
    'totalTasks' => (int)($stats['total_count'] ?? 0),
    'tasks' => $tasks,
    'recentTasks' => array_slice($tasks, 0, 3),
    'aiSummary' => $aiSummary,

    'topTalents' => $topTalents
];
}