<?php

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function initials($name)
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $letters .= strtoupper(substr($part, 0, 1));
        }
    }

    return substr($letters ?: 'U', 0, 2);
}

function initHrAiCandidatesPage(PDO $conn): array
{
    $companyId = (int)($_SESSION['company_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT company_name, company_logo
        FROM companies
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $filter = $_GET['filter'] ?? 'all';
    $search = trim($_GET['search'] ?? '');

    $where = [
        "t.company_id = ?",
        "ta.status = 'submitted'",
        "(ta.ai_status = 'recommended' OR ta.recommended_to_hr = 1)"
    ];

    $params = [$companyId];

    if ($filter === 'ai') {
        $where[] = "ta.ai_status = 'recommended'";
    } elseif ($filter === 'reviewer') {
        $where[] = "ta.recommended_to_hr = 1";
    } elseif ($filter === 'shortlisted') {
        $where[] = "ta.hr_status = 'shortlisted'";
    } elseif ($filter === 'hired') {
        $where[] = "ta.hr_status = 'hired'";
    } elseif ($filter === 'rejected') {
        $where[] = "ta.hr_status = 'rejected'";
    }

    if ($search !== '') {
        $where[] = "(
            js.full_name LIKE ?
            OR js.email LIKE ?
            OR js.job_title LIKE ?
            OR t.title LIKE ?
            OR t.category LIKE ?
        )";

        $term = "%$search%";
        array_push($params, $term, $term, $term, $term, $term);
    }

    $stmt = $conn->prepare("
        SELECT
            ta.*,
            t.title AS task_title,
            t.category,
            t.difficulty,
            t.points AS task_points,
            js.id AS candidate_id,
            js.full_name,
            js.email,
            js.mobile,
            js.job_title,
            js.profile_image
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY
            ta.recommended_to_hr DESC,
            ta.ai_score DESC,
            ta.submitted_at DESC
    ");

    $stmt->execute($params);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statsStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN ta.ai_status = 'recommended' THEN 1 ELSE 0 END) AS ai_recommended,
            SUM(CASE WHEN ta.recommended_to_hr = 1 THEN 1 ELSE 0 END) AS reviewer_recommended,
            SUM(CASE WHEN ta.hr_status = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted,
            SUM(CASE WHEN ta.hr_status = 'hired' THEN 1 ELSE 0 END) AS hired
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND (ta.ai_status = 'recommended' OR ta.recommended_to_hr = 1)
    ");

    $statsStmt->execute([$companyId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'companyName' => $company['company_name'] ?? 'Company',
        'companyLogo' => $company['company_logo'] ?? '',
        'avatarLetter' => strtoupper(substr($company['company_name'] ?? 'C', 0, 1)),
        'filter' => $filter,
        'search' => $search,
        'candidates' => $candidates,
        'stats' => $stats
    ];
}

function handleHrAiCandidateAction(PDO $conn): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $attemptId = (int)($_POST['attempt_id'] ?? 0);
    $candidateId = (int)($_POST['candidate_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $companyId = (int)($_SESSION['company_id'] ?? 0);
    $hrUserId = (int)($_SESSION['user_id'] ?? 0);

    if ($attemptId <= 0 || $candidateId <= 0 || $companyId <= 0) {
        return;
    }

    if ($action === 'shortlist' || $action === 'interview') {

        $check = $conn->prepare("
            SELECT id 
            FROM hr_shortlists
            WHERE company_id = ?
            AND job_seeker_id = ?
            LIMIT 1
        ");

        $check->execute([$companyId, $candidateId]);

        if (!$check->fetch(PDO::FETCH_ASSOC)) {
            $stmt = $conn->prepare("
                INSERT INTO hr_shortlists
                (company_id, hr_user_id, job_seeker_id, hiring_type, status)
                VALUES (?, ?, ?, 'full_time', 'new')
            ");

            $stmt->execute([
                $companyId,
                $hrUserId,
                $candidateId
            ]);
        }

        $stmt = $conn->prepare("
            UPDATE task_attempts
            SET hr_status = 'shortlisted',
                hr_reviewed_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$attemptId]);

        header("Location: /tamkeentest/public/hr/ai-candidates.php?added=1");
        exit;
    }

    if ($action === 'reject') {
        $stmt = $conn->prepare("
            UPDATE task_attempts
            SET hr_status = 'rejected',
                hr_reviewed_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$attemptId]);

        header("Location: /tamkeentest/public/hr/ai-candidates.php?rejected=1");
        exit;
    }
}