
<?php

function getHrShortlistPageData(PDO $conn, int $company_id): array
{
    $company = getShortlistCompany($conn, $company_id);

    $filters = [
        'status' => $_GET['status'] ?? 'all',
        'search' => trim($_GET['search'] ?? ''),
        'type' => $_GET['type'] ?? 'all'
    ];

    $shortlists = getHrShortlists($conn, $company_id, $filters);
    $stats = getHrShortlistStats($conn, $company_id);
    $approvalCount = getHrApprovalCount($conn, $company_id);

    return array_merge($filters, [
        'company' => $company,
        'companyName' => $company['company_name'] ?? 'Company',
        'companyLogo' => $company['company_logo'] ?? '',
        'avatarLetter' => strtoupper(substr($company['company_name'] ?? 'Company', 0, 1)),
        'shortlists' => $shortlists,
        'stats' => $stats,
        'approvalCount' => $approvalCount,
        'bestCandidate' => getBestShortlistCandidate($shortlists),
        'readyForInterview' => countReadyForInterview($shortlists)
    ]);
}

function getShortlistCompany(PDO $conn, int $company_id): array
{
    $stmt = $conn->prepare("SELECT company_name, company_logo FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getHrShortlists(PDO $conn, int $company_id, array $filters): array
{
    $params = [$company_id];

    $query = "
        SELECT
            hs.*,
            js.full_name,
            js.email,
            js.mobile,
            js.job_title,
            js.profile_image,
            jsp.location,
            jsp.years_experience,
            edu.institution,
            edu.field AS education_field,
            GROUP_CONCAT(DISTINCT jss.skill_name SEPARATOR ', ') AS skills,
            COUNT(DISTINCT ta.id) AS submitted_tasks,
            COALESCE(SUM(DISTINCT t.points), 0) AS earned_points,
            hr.id AS hiring_request_id,
            hr.status AS hiring_request_status
        FROM hr_shortlists hs
        JOIN job_seekers js ON js.id = hs.job_seeker_id
        LEFT JOIN job_seeker_profiles jsp ON jsp.job_seeker_id = js.id
        LEFT JOIN job_seeker_educations edu ON edu.job_seeker_id = js.id
        LEFT JOIN job_seeker_skills jss ON jss.job_seeker_id = js.id
        LEFT JOIN task_attempts ta ON ta.job_seeker_id = js.id AND ta.status = 'submitted'
        LEFT JOIN tasks t ON t.id = ta.task_id
        LEFT JOIN hiring_requests hr ON hr.job_seeker_id = js.id AND hr.company_id = hs.company_id
        WHERE hs.company_id = ?
    ";

    if ($filters['status'] !== 'all') {
        $query .= " AND hs.status = ? ";
        $params[] = $filters['status'];
    }

    if ($filters['type'] !== 'all') {
        $query .= " AND hs.hiring_type = ? ";
        $params[] = $filters['type'];
    }

    if ($filters['search'] !== '') {
        $query .= "
            AND (
                js.full_name LIKE ?
                OR js.email LIKE ?
                OR js.job_title LIKE ?
                OR jss.skill_name LIKE ?
                OR edu.institution LIKE ?
                OR edu.field LIKE ?
            )
        ";

        $term = '%' . $filters['search'] . '%';
        array_push($params, $term, $term, $term, $term, $term, $term);
    }

    $query .= "
        GROUP BY hs.id
        ORDER BY
            CASE
                WHEN hr.id IS NOT NULL THEN 1
                WHEN hs.status = 'replied' THEN 2
                WHEN hs.status = 'waiting_reply' THEN 3
                ELSE 4
            END,
            hs.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHrShortlistStats(PDO $conn, int $company_id): array
{
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total_count,
            COALESCE(SUM(status = 'new'), 0) AS new_count,
            COALESCE(SUM(status = 'waiting_reply'), 0) AS waiting_count,
            COALESCE(SUM(status = 'replied'), 0) AS replied_count,
            COALESCE(SUM(status = 'hiring_request'), 0) AS hiring_count
        FROM hr_shortlists
        WHERE company_id = ?
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getHrApprovalCount(PDO $conn, int $company_id): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM hiring_requests WHERE company_id = ?");
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function shortlistScore(array $candidate): int
{
    $score = ((int)$candidate['earned_points']) + (((int)$candidate['submitted_tasks']) * 10);

    if ($score <= 0) {
        $score = 50;
    }

    return min($score, 100);
}

function getBestShortlistCandidate(array $shortlists): ?array
{
    $bestCandidate = null;

    foreach ($shortlists as $candidate) {
        if ($bestCandidate === null || shortlistScore($candidate) > shortlistScore($bestCandidate)) {
            $bestCandidate = $candidate;
        }
    }

    return $bestCandidate;
}

function countReadyForInterview(array $shortlists): int
{
    $count = 0;

    foreach ($shortlists as $candidate) {

        if (($candidate['status'] ?? '') === 'replied') {
            $count++;
        }
    }

    return $count;
}

function shortlistWorkflow(array $candidate): array
{
    if (!empty($candidate['hiring_request_id']) || $candidate['status'] === 'hiring_request') {
        return ['label' => 'Approval Sent', 'class' => 'approval'];
    }

    if ($candidate['status'] === 'replied') {
        return ['label' => 'Candidate Replied', 'class' => 'replied'];
    }

    if ($candidate['status'] === 'waiting_reply') {
        return ['label' => 'Email Sent', 'class' => 'waiting'];
    }

    return ['label' => 'New', 'class' => 'new'];
}

/*
|--------------------------------------------------------------------------
|--------------------------------------------------------------------------
*/

function shortlist_initials($name): string
{
    $name = trim((string)$name);

    if ($name === '') {
        return 'U';
    }

    $parts = preg_split('/\s+/', $name);

    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $second = strtoupper(substr($parts[1] ?? '', 0, 1));

    return $first . $second;
}

function shortlist_score(array $candidate): int
{
    return shortlistScore($candidate);
}



if (!function_exists('initHrShortlistPage')) {
    function initHrShortlistPage(PDO $conn): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $company_id = (int)($_SESSION['company_id'] ?? 0);

        if (!$company_id) {
            header("Location: /tamkeentest/public/company/login.php");
            exit();
        }

        return getHrShortlistPageData($conn, $company_id);
    }
}
