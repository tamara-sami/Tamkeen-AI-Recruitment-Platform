<?php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('initials')) {
    function initials($name) {
        $parts = preg_split('/\s+/', trim((string)$name));
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part !== '') {
                $letters .= mb_substr($part, 0, 1);
            }
        }

        return strtoupper($letters ?: 'U');
    }
}

if (!function_exists('candidateScore')) {
    function candidateScore($earnedPoints, $submittedTasks) {
        $earnedPoints = (int)$earnedPoints;
        $submittedTasks = (int)$submittedTasks;

        return max(0, min(100, (int)round(($earnedPoints * 0.7) + min($submittedTasks * 6, 30))));
    }
}

if (!function_exists('fitLabel')) {
    function fitLabel($score) {
        if ($score >= 85) return 'Excellent Fit';
        if ($score >= 70) return 'Strong Fit';
        if ($score >= 50) return 'Potential Fit';
        return 'Review';
    }
}

if (!function_exists('fitClass')) {
    function fitClass($score) {
        if ($score >= 85) return 'excellent';
        if ($score >= 70) return 'strong';
        if ($score >= 50) return 'potential';
        return 'review';
    }
}

if (!function_exists('matchBadge')) {
    function matchBadge($score) {
        if ($score >= 70) return 'Full Time';
        return 'Part Time';
    }
}

if (!function_exists('activityMeta')) {
    function activityMeta($activityType) {
        switch ($activityType) {
            case 'task_completed':
                return ['icon' => 'fa-clipboard-check', 'text' => 'completed a task'];
            case 'shortlisted':
                return ['icon' => 'fa-star', 'text' => 'was added to shortlist'];
            case 'approval_request':
                return ['icon' => 'fa-shield-alt', 'text' => 'approval request submitted'];
            default:
                return ['icon' => 'fa-wave-square', 'text' => 'updated activity'];
        }
    }
}

if (!function_exists('getCompanyProfile')) {
    function getCompanyProfile(PDO $conn, $companyId) {
        $stmt = $conn->prepare("
            SELECT company_name, company_logo
            FROM companies
            WHERE id = ?
        ");
        $stmt->execute([$companyId]);

        $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $companyName = $company['company_name'] ?? ($_SESSION['company_name'] ?? 'Tamkeen Company');

        return [
            'companyName' => $companyName,
            'companyLogo' => $company['company_logo'] ?? '',
            'avatarLetter' => strtoupper(substr($companyName, 0, 1)),
        ];
    }
}

if (!function_exists('getCandidateFilters')) {
    function getCandidateFilters() {
        return [
            'search' => trim($_GET['search'] ?? ''),
            'skill' => trim($_GET['skill'] ?? 'All Skills'),
            'qrSearch' => trim($_GET['qr'] ?? '')
        ];
    }
}

if (!function_exists('extractQrTokenValue')) {
    function extractQrTokenValue($qrValue) {
        $qrValue = trim((string)$qrValue);
        $decoded = urldecode($qrValue);
        $token = $decoded;

        $parts = parse_url($decoded);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            if (!empty($queryParams['token'])) {
                $token = trim($queryParams['token']);
            }
        }

        if (strpos($token, 'token=') !== false) {
            parse_str(parse_url('http://x.test/?' . ltrim($token, '?'), PHP_URL_QUERY), $queryParams);
            if (!empty($queryParams['token'])) {
                $token = trim($queryParams['token']);
            }
        }

        return $token;
    }
}

if (!function_exists('redirectQrSearchToPublicProfile')) {
    function redirectQrSearchToPublicProfile() {
        $qrSearch = trim($_GET['qr'] ?? '');

        if ($qrSearch === '') {
            return;
        }

        $token = extractQrTokenValue($qrSearch);

        if ($token === '') {
            return;
        }

header('Location: /tamkeentest/public/user/public-profile.php?token=' . urlencode($token));        exit;
    }
}

if (!function_exists('buildCandidateWhere')) {
    function buildCandidateWhere($search, $skill, $qrSearch) {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(js.full_name LIKE ? OR js.email LIKE ? OR js.job_title LIKE ? OR js.mobile LIKE ? OR jss.skill_name LIKE ?)";
            $term = "%$search%";
            array_push($params, $term, $term, $term, $term, $term);
        }

        if ($qrSearch !== '') {
            $qrValue = trim($qrSearch);
            $token = extractQrTokenValue($qrValue);

            $where[] = "(
                js.qr_token = ?
                OR js.email LIKE ?
                OR js.mobile LIKE ?
                OR js.full_name LIKE ?
            )";

            array_push(
                $params,
                $token,
                "%$qrValue%",
                "%$qrValue%",
                "%$qrValue%"
            );
        }

        if ($skill !== '' && $skill !== 'All Skills') {
            $where[] = "jss.skill_name = ?";
            $params[] = $skill;
        }

        return [
            'sql' => !empty($where) ? "WHERE " . implode(" AND ", $where) : "",
            'params' => $params
        ];
    }
}

if (!function_exists('getDashboardCandidates')) {
    function getDashboardCandidates(PDO $conn, $companyId, $whereSql, array $params) {
        $stmt = $conn->prepare("
            SELECT js.id, js.full_name, js.email, js.mobile, js.job_title, js.profile_image,
                   jsp.location, jsp.residence_country, jsp.years_experience,
                   edu.degree, edu.institution, edu.field AS education_field, edu.graduation_year,
                   GROUP_CONCAT(DISTINCT jss.skill_name SEPARATOR ', ') AS skills,
                   COUNT(DISTINCT ta.id) AS submitted_tasks,
                   COALESCE(SUM(COALESCE(ta.final_points, t.points, 0)), 0) AS earned_points,
                   MAX(ta.created_at) AS latest_submission,
                   hs.id AS shortlisted_id,
                   hs.status AS shortlist_status,
                   hs.hiring_type AS shortlist_hiring_type,
                   LEAST(
                       100,
                       ROUND(
                           (COALESCE(SUM(COALESCE(ta.final_points, t.points, 0)), 0) * 0.7)
                           + LEAST(COUNT(DISTINCT ta.id) * 6, 30)
                       )
                   ) AS match_score
            FROM job_seekers js
            INNER JOIN task_attempts ta
                ON ta.job_seeker_id = js.id
               AND ta.status = 'submitted'
            INNER JOIN tasks t
                ON t.id = ta.task_id
               AND t.company_id = ?
               AND COALESCE(t.deleted, 0) = 0
            LEFT JOIN job_seeker_profiles jsp ON jsp.job_seeker_id = js.id
            LEFT JOIN job_seeker_educations edu ON edu.job_seeker_id = js.id
            LEFT JOIN job_seeker_skills jss ON jss.job_seeker_id = js.id
            LEFT JOIN hr_shortlists hs ON hs.job_seeker_id = js.id AND hs.company_id = ?
            $whereSql
            GROUP BY js.id
            HAVING submitted_tasks > 0
               AND match_score >= 50
            ORDER BY match_score DESC, earned_points DESC, submitted_tasks DESC, latest_submission DESC, js.id DESC
            LIMIT 12
        ");

        $stmt->execute(array_merge([$companyId, $companyId], $params));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getSkillOptions')) {
    function getSkillOptions(PDO $conn) {
        $stmt = $conn->prepare("
            SELECT DISTINCT skill_name
            FROM job_seeker_skills
            WHERE skill_name IS NOT NULL
            AND skill_name != ''
            ORDER BY skill_name ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

if (!function_exists('getDashboardStats')) {
    function getDashboardStats(PDO $conn, $companyId, array $candidates) {
        $totalCandidatesStmt = $conn->prepare("
            SELECT COUNT(DISTINCT js.id)
            FROM job_seekers js
            INNER JOIN task_attempts ta
                ON ta.job_seeker_id = js.id
               AND ta.status = 'submitted'
            INNER JOIN tasks t
                ON t.id = ta.task_id
               AND t.company_id = ?
               AND COALESCE(t.deleted, 0) = 0
        ");
        $totalCandidatesStmt->execute([$companyId]);
        $totalCandidates = (int)$totalCandidatesStmt->fetchColumn();

        $shortlistStmt = $conn->prepare("
            SELECT COUNT(*)
            FROM hr_shortlists
            WHERE company_id = ?
        ");
        $shortlistStmt->execute([$companyId]);
        $shortlistedCount = (int)$shortlistStmt->fetchColumn();

        $interviewStmt = $conn->prepare("
            SELECT COUNT(*)
            FROM interviews
            WHERE company_id = ?
        ");
        $interviewStmt->execute([$companyId]);
        $interviewsCount = (int)$interviewStmt->fetchColumn();

        $pendingStmt = $conn->prepare("
            SELECT COUNT(*)
            FROM hiring_requests
            WHERE company_id = ?
            AND status = 'pending'
        ");
        $pendingStmt->execute([$companyId]);
        $pendingRequests = (int)$pendingStmt->fetchColumn();

        $hiredStmt = $conn->prepare("
            SELECT COUNT(*)
            FROM hiring_requests
            WHERE company_id = ?
            AND status = 'approved'
        ");
        $hiredStmt->execute([$companyId]);
        $hiredCount = (int)$hiredStmt->fetchColumn();

        return [
            'totalCandidates' => $totalCandidates,
            'shortlistedCount' => $shortlistedCount,
            'interviewsCount' => $interviewsCount,
            'pendingRequests' => $pendingRequests,
            'hiredCount' => $hiredCount,
            'matchesCount' => count($candidates),
        ];
    }
}

if (!function_exists('getRecentActivities')) {
    function getRecentActivities(PDO $conn, $companyId, $limit = 4) {
        $limit = max(1, min((int)$limit, 20));

        $stmt = $conn->prepare("
            (
                SELECT js.full_name, 'task_completed' AS activity_type, ta.created_at
                FROM task_attempts ta
                INNER JOIN job_seekers js ON js.id = ta.job_seeker_id
                INNER JOIN tasks t ON t.id = ta.task_id
                WHERE ta.status = 'submitted'
                AND t.company_id = ?
                AND COALESCE(t.deleted, 0) = 0
            )
            UNION ALL
            (
                SELECT js.full_name, 'shortlisted' AS activity_type, hs.created_at
                FROM hr_shortlists hs
                INNER JOIN job_seekers js ON js.id = hs.job_seeker_id
                WHERE hs.company_id = ?
            )
            UNION ALL
            (
                SELECT js.full_name, 'approval_request' AS activity_type, hr.created_at
                FROM hiring_requests hr
                INNER JOIN job_seekers js ON js.id = hr.job_seeker_id
                WHERE hr.company_id = ?
            )
            ORDER BY created_at DESC
            LIMIT $limit
        ");

        $stmt->execute([$companyId, $companyId, $companyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('prepareCandidateCard')) {
    function prepareCandidateCard(array $candidate, $index, $companyName) {
        $score = candidateScore($candidate['earned_points'], $candidate['submitted_tasks']);
        $skillsText = $candidate['skills'] ?: 'Skills not added yet';
        $skillsArray = array_filter(array_map('trim', explode(',', $skillsText)));

        return [
            'score' => $score,
            'visibleSkills' => array_slice($skillsArray, 0, 3),
            'candidateInitials' => initials($candidate['full_name']),
            'rank' => $index + 1,
            'fitLabel' => fitLabel($score),
            'fitClass' => fitClass($score),
            'mailtoSubject' => rawurlencode('Interview opportunity from ' . $companyName),
            'mailtoBody' => rawurlencode(
                "Hi " . ($candidate['full_name'] ?? '') . ",\n\n" .
                "We reviewed your task performance on Tamkeen and would like to discuss a potential opportunity with you.\n\n" .
                "Best regards,\n" . $companyName
            ),
        ];
    }
}

if (!function_exists('getHrDashboardData')) {
    function getHrDashboardData(PDO $conn, $companyId) {
        $filters = getCandidateFilters();
        $companyProfile = getCompanyProfile($conn, $companyId);

        $candidateWhere = buildCandidateWhere(
            $filters['search'],
            $filters['skill'],
            $filters['qrSearch']
        );

        $candidates = getDashboardCandidates(
            $conn,
            $companyId,
            $candidateWhere['sql'],
            $candidateWhere['params']
        );

        $stats = getDashboardStats($conn, $companyId, $candidates);
        $topCandidate = $candidates[0] ?? null;

        return array_merge($filters, $companyProfile, $stats, [
            'company_id' => $companyId,
            'candidates' => $candidates,
            'skills' => getSkillOptions($conn),
            'activities' => getRecentActivities($conn, $companyId, 4),
            'topCandidate' => $topCandidate,
            'topScore' => $topCandidate
                ? candidateScore($topCandidate['earned_points'], $topCandidate['submitted_tasks'])
                : 0,
        ]);
    }
}

if (!function_exists('initHrDashboardPage')) {
    function initHrDashboardPage(PDO $conn) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $companyId = $_SESSION['company_id'] ?? 0;

        if (!$companyId) {
            header("Location: /tamkeentest/public/company/login.php");
            exit();
        }

        redirectQrSearchToPublicProfile();

        return getHrDashboardData($conn, $companyId);
    }
}