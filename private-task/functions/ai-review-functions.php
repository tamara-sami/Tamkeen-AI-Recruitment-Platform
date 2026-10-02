<?php

function initAiReviewPage(PDO $conn): array
{
    $company_id = (int)($_SESSION['company_id'] ?? 0);

    $stmt = $conn->prepare("SELECT company_name FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    $filter = $_GET['filter'] ?? 'all';

    $where = [
        "t.company_id = ?",
        "ta.status = 'submitted'"
    ];

    $params = [$company_id];

    if ($filter === 'recommended') {
        $where[] = "ta.ai_status = 'recommended'";
    } elseif ($filter === 'needs_review') {
        $where[] = "ta.ai_status = 'needs_review'";
    } elseif ($filter === 'sent_hr') {
        $where[] = "ta.recommended_to_hr = 1";
    }

    $sql = "
        SELECT
            ta.*,
            t.title AS task_title,
            t.points AS task_points,
            t.category,
            t.difficulty,
            js.full_name,
            js.email,
            js.job_title,
            js.profile_image
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY
            ta.recommended_to_hr DESC,
            CASE
                WHEN ta.ai_status = 'recommended' THEN 1
                WHEN ta.ai_status = 'needs_review' THEN 2
                ELSE 3
            END,
            ta.ai_score DESC,
            ta.submitted_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            SUM(ta.ai_status = 'recommended') AS recommended_count,
            SUM(ta.ai_status = 'needs_review') AS needs_review_count,
            SUM(ta.ai_status = 'not_recommended') AS not_recommended_count,
            SUM(ta.recommended_to_hr = 1) AS sent_hr_count
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
    ");

    $countStmt->execute([$company_id]);
    $counts = $countStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'companyName' => $company['company_name'] ?? 'Company',
        'filter' => $filter,
        'submissions' => $submissions,
        'total' => (int)($counts['total_count'] ?? 0),
        'recommendedCount' => (int)($counts['recommended_count'] ?? 0),
        'needsReviewCount' => (int)($counts['needs_review_count'] ?? 0),
        'notRecommendedCount' => (int)($counts['not_recommended_count'] ?? 0),
        'sentHrCount' => (int)($counts['sent_hr_count'] ?? 0),
    ];
}

function handleAiReviewAction(PDO $conn): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $attempt_id = (int)($_POST['attempt_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $final_points = $_POST['final_points'] !== '' ? (int)$_POST['final_points'] : null;
    $review_note = trim($_POST['review_note'] ?? '');

    if ($attempt_id <= 0) {
        return;
    }

    $review_status = 'pending';
    $recommended_to_hr = 0;
    $recommended_at = null;

    if ($action === 'approve') {
        $review_status = 'approved';
    } elseif ($action === 'reject') {
        $review_status = 'rejected';
    } elseif ($action === 'edit_points') {
        $review_status = 'edited';
    } elseif ($action === 'recommend_hr') {
        $review_status = 'approved';
        $recommended_to_hr = 1;
        $recommended_at = date('Y-m-d H:i:s');
    } else {
        return;
    }

    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET
            final_points = ?,
            review_status = ?,
            review_note = ?,
            recommended_to_hr = ?,
            recommended_at = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $final_points,
        $review_status,
        $review_note,
        $recommended_to_hr,
        $recommended_at,
        $attempt_id
    ]);

    header("Location: /tamkeentest/public/task/ai-review.php?updated=1");
    exit;
}
