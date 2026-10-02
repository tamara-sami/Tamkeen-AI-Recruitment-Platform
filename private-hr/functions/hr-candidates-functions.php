<?php

function getHrCandidatesPageData(PDO $conn, int $company_id): array
{
    $company = getHrCandidateCompany($conn, $company_id);

    $filters = [
        'search' => trim($_GET['search'] ?? ''),
        // Skill and score dropdowns were removed from the UI.
        // Candidates are now matched by this company's submitted tasks only.
        'skill' => '',
        'location' => trim($_GET['location'] ?? 'all'),
        'scoreFilter' => 'all',
        'tasksFilter' => trim($_GET['tasks'] ?? 'all'),
        'statusFilter' => trim($_GET['status'] ?? 'all'),
        'experienceFilter' => trim($_GET['experience'] ?? 'all')
    ];

    $rawCandidates = getHrCandidates($conn, $company_id, $filters);
    $candidates = filterHrCandidatesAfterScore($conn, $company_id, $rawCandidates, $filters);

    return array_merge($filters, [
        'company' => $company,
        'companyName' => $company['company_name'] ?? 'Company',
        'companyLogo' => $company['company_logo'] ?? '',
        'avatarLetter' => strtoupper(substr($company['company_name'] ?? 'Company', 0, 1)),
        'candidates' => $candidates,
        'skills' => getHrCandidateSkills($conn),
        'locations' => getHrCandidateLocations($conn),
        'stats' => getHrCandidateStats($conn, $company_id, $candidates),
        'topCandidate' => getTopHrCandidate($candidates),
        'topSkills' => getTopHrCandidateSkills($candidates)
    ]);
}

function getHrCandidateCompany(PDO $conn, int $company_id): array
{
    $stmt = $conn->prepare("
        SELECT company_name, company_logo
        FROM companies
        WHERE id = ?
    ");
    $stmt->execute([$company_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getHrCandidates(PDO $conn, int $company_id, array $filters): array
{
    $where = [];
    $params = [];

    if ($filters['search'] !== '') {
        $where[] = "(
            js.full_name LIKE ?
            OR js.email LIKE ?
            OR js.job_title LIKE ?
            OR js.mobile LIKE ?
            OR jss.skill_name LIKE ?
            OR edu.institution LIKE ?
            OR edu.field LIKE ?
        )";

        $term = '%' . $filters['search'] . '%';
        array_push($params, $term, $term, $term, $term, $term, $term, $term);
    }

    // Skill filtering is intentionally disabled here because the candidate pool
    // is already scoped to candidates who submitted tasks for this company.

    if ($filters['location'] !== 'all') {
        $where[] = "(jsp.location = ? OR jsp.residence_country = ?)";
        array_push($params, $filters['location'], $filters['location']);
    }

    if ($filters['experienceFilter'] !== 'all') {
        if ($filters['experienceFilter'] === 'fresh') {
            $where[] = "(jsp.years_experience IS NULL OR jsp.years_experience <= 1)";
        } elseif ($filters['experienceFilter'] === 'mid') {
            $where[] = "jsp.years_experience BETWEEN 2 AND 4";
        } elseif ($filters['experienceFilter'] === 'senior') {
            $where[] = "jsp.years_experience >= 5";
        }
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $stmt = $conn->prepare("
        SELECT
            js.id,
            js.full_name,
            js.email,
            js.mobile,
            js.job_title,
            js.profile_image,
            jsp.profile_visibility,
            jsp.location,
            jsp.residence_country,
            jsp.years_experience,
            edu.degree,
            edu.institution,
            edu.field AS education_field,
            edu.graduation_year,
            GROUP_CONCAT(DISTINCT jss.skill_name SEPARATOR ', ') AS skills,
            COUNT(DISTINCT ta.id) AS submitted_tasks,
            COALESCE(SUM(COALESCE(ta.final_points, t.points, 0)), 0) AS earned_points,
            hs.id AS shortlisted_id,
            hs.status AS shortlist_status,
            hs.hiring_type AS shortlist_hiring_type,
            hr.id AS hiring_request_id,
            hr.status AS hiring_request_status
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp ON jsp.job_seeker_id = js.id
        LEFT JOIN job_seeker_educations edu ON edu.job_seeker_id = js.id
        LEFT JOIN job_seeker_skills jss ON jss.job_seeker_id = js.id
        INNER JOIN task_attempts ta ON ta.job_seeker_id = js.id AND ta.status = 'submitted'
        INNER JOIN tasks t ON t.id = ta.task_id AND t.company_id = ?
        LEFT JOIN hr_shortlists hs ON hs.job_seeker_id = js.id AND hs.company_id = ?
        LEFT JOIN hiring_requests hr ON hr.job_seeker_id = js.id AND hr.company_id = ?
        $whereSql
        GROUP BY js.id
        ORDER BY earned_points DESC, submitted_tasks DESC, js.id DESC
        LIMIT 120
    ");

    $stmt->execute(array_merge([$company_id, $company_id, $company_id], $params));

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHrCandidateScore(array $candidate): int
{
    return min(100, (int)$candidate['earned_points']);
}

function getHrCandidateStatus(array $candidate): string
{
    if (!empty($candidate['hiring_request_id'])) {
        return 'approval';
    }

    if (!empty($candidate['shortlisted_id'])) {
        return 'shortlisted';
    }

    if ((int)$candidate['submitted_tasks'] > 0) {
        return 'review';
    }

    return 'new';
}

function filterHrCandidatesAfterScore(PDO $conn, int $company_id, array $rawCandidates, array $filters): array
{
    $candidates = [];

    foreach ($rawCandidates as $candidate) {
        $candidate['match_score'] = getHrCandidateScore($candidate);
        $candidate['candidate_status'] = getHrCandidateStatus($candidate);
        $candidate['can_view_profile'] = canHrViewCandidateProfile($conn, $company_id, $candidate);

        $passes = true;

        // Recommended candidates must have a real company-task result.
        // Hide weak / zero matches from the HR candidates page.
        if ($candidate['match_score'] < 50) {
            $passes = false;
        }

        if ($filters['scoreFilter'] === 'excellent' && $candidate['match_score'] < 85) {
            $passes = false;
        }

        if ($filters['scoreFilter'] === 'good' && ($candidate['match_score'] < 70 || $candidate['match_score'] >= 85)) {
            $passes = false;
        }

        if ($filters['scoreFilter'] === 'review' && $candidate['match_score'] >= 70) {
            $passes = false;
        }

        if ($filters['tasksFilter'] === 'submitted' && (int)$candidate['submitted_tasks'] < 1) {
            $passes = false;
        }

        if ($filters['tasksFilter'] === 'none' && (int)$candidate['submitted_tasks'] > 0) {
            $passes = false;
        }

        if ($filters['tasksFilter'] === 'multi' && (int)$candidate['submitted_tasks'] < 2) {
            $passes = false;
        }

        if ($filters['statusFilter'] !== 'all' && $candidate['candidate_status'] !== $filters['statusFilter']) {
            $passes = false;
        }

        if ($passes) {
            $candidates[] = $candidate;
        }
    }

    return $candidates;
}
function getHrCandidateSkills(PDO $conn): array
{
    $stmt = $conn->prepare("
        SELECT DISTINCT skill_name
        FROM job_seeker_skills
        WHERE skill_name IS NOT NULL AND skill_name != ''
        ORDER BY skill_name ASC
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getHrCandidateLocations(PDO $conn): array
{
    $stmt = $conn->prepare("
        SELECT DISTINCT COALESCE(NULLIF(location, ''), NULLIF(residence_country, '')) AS place
        FROM job_seeker_profiles
        HAVING place IS NOT NULL AND place != ''
        ORDER BY place ASC
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getHrCandidateStats(PDO $conn, int $company_id, array $candidates): array
{
    $totalStmt = $conn->prepare("
        SELECT COUNT(DISTINCT js.id)
        FROM job_seekers js
        INNER JOIN task_attempts ta ON ta.job_seeker_id = js.id AND ta.status = 'submitted'
        INNER JOIN tasks t ON t.id = ta.task_id AND t.company_id = ?
    ");
    $totalStmt->execute([$company_id]);
    $totalCandidates = (int)$totalStmt->fetchColumn();

    $shortlistedStmt = $conn->prepare("SELECT COUNT(*) FROM hr_shortlists WHERE company_id = ?");
    $shortlistedStmt->execute([$company_id]);
    $shortlistedCount = (int)$shortlistedStmt->fetchColumn();

    $requestsStmt = $conn->prepare("SELECT COUNT(*) FROM hiring_requests WHERE company_id = ?");
    $requestsStmt->execute([$company_id]);
    $requestsCount = (int)$requestsStmt->fetchColumn();

    $submittedTasksCount = 0;
    $scoreSum = 0;

    foreach ($candidates as $candidate) {
        $submittedTasksCount += (int)$candidate['submitted_tasks'];
        $scoreSum += (int)$candidate['match_score'];
    }

    return [
        'totalCandidates' => $totalCandidates,
        'currentMatches' => count($candidates),
        'shortlistedCount' => $shortlistedCount,
        'requestsCount' => $requestsCount,
        'submittedTasksCount' => $submittedTasksCount,
        'avgScore' => count($candidates) ? round($scoreSum / count($candidates)) : 0
    ];
}

function getTopHrCandidate(array $candidates): ?array
{
    $topCandidate = null;

    foreach ($candidates as $candidate) {
        if ($topCandidate === null || $candidate['match_score'] > $topCandidate['match_score']) {
            $topCandidate = $candidate;
        }
    }

    return $topCandidate;
}

function getTopHrCandidateSkills(array $candidates): array
{
    $topSkills = [];

    foreach ($candidates as $candidate) {
        foreach (explode(',', $candidate['skills'] ?? '') as $skillName) {
            $skillName = trim($skillName);

            if ($skillName !== '') {
                $topSkills[$skillName] = ($topSkills[$skillName] ?? 0) + 1;
            }
        }
    }

    arsort($topSkills);

    return array_slice($topSkills, 0, 6, true);
}

function candidateFitLabel(int $score): string
{
    if ($score >= 85) {
        return 'Excellent Match';
    }

    if ($score >= 70) {
        return 'Good Potential';
    }

    return 'Needs Review';
}

function candidateFitClass(int $score): string
{
    if ($score >= 85) {
        return 'excellent';
    }

    if ($score >= 70) {
        return 'strong';
    }

    return '';
}

function candidateStatusText(string $status): string
{
    return [
        'new' => 'New',
        'review' => 'Needs Review',
        'shortlisted' => 'Shortlisted',
        'approval' => 'Approval Sent'
    ][$status] ?? 'New';
}

if (!function_exists('initHrCandidatesPage')) {
    function initHrCandidatesPage(PDO $conn): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $company_id = (int)($_SESSION['company_id'] ?? 0);

        if (!$company_id) {
            header("Location: /tamkeentest/public/company/login.php");
            exit();
        }

        return getHrCandidatesPageData($conn, $company_id);
    }
}

function canHrViewCandidateProfile(PDO $conn, int $companyId, array $candidate): bool
{
    $visibility = $candidate['profile_visibility'] ?? 'public';
    $jobSeekerId = (int)($candidate['id'] ?? 0);

    if ($jobSeekerId <= 0) {
        return false;
    }

    if ($visibility === 'public') {
        return true;
    }

    if ($visibility === 'registered') {
        return true;
    }

    if ($visibility === 'hidden') {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM task_attempts ta
            INNER JOIN tasks t ON t.id = ta.task_id
            WHERE ta.job_seeker_id = ?
            AND t.company_id = ?
        ");
        $stmt->execute([$jobSeekerId, $companyId]);

        if ((int)$stmt->fetchColumn() > 0) {
            return true;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM training_applications ta
            INNER JOIN trainings t ON t.id = ta.training_id
            WHERE ta.job_seeker_id = ?
            AND t.company_id = ?
        ");
        $stmt->execute([$jobSeekerId, $companyId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    return false;
}