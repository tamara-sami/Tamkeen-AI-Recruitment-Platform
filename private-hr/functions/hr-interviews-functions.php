<?php

function getHrInterviewsPageData(PDO $conn, int $company_id, array $filters = []) {
    $company = getHrCompanyInfo($conn, $company_id);
    $companyName = $company['company_name'] ?? 'Company';
    $avatarLetter = strtoupper(substr($companyName, 0, 1));

    return [
        'company' => $company,
        'companyName' => $companyName,
        'avatarLetter' => $avatarLetter,
        'candidateOptions' => getInterviewCandidateOptions($conn, $company_id),
        'interviews' => getHrInterviews($conn, $company_id, $filters),
        'stats' => getHrInterviewStats($conn, $company_id),
        'nextInterview' => getNextHrInterview($conn, $company_id)
    ];
}

function getHrCompanyInfo(PDO $conn, int $company_id) {
    $stmt = $conn->prepare("SELECT company_name, company_logo FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getInterviewCandidateOptions(PDO $conn, int $company_id) {
    $stmt = $conn->prepare("
        SELECT
            hs.job_seeker_id,
            js.full_name,
            js.job_title,
            js.email
        FROM hr_shortlists hs
        JOIN job_seekers js ON js.id = hs.job_seeker_id
        WHERE hs.company_id = ?
        ORDER BY js.full_name ASC
    ");
    $stmt->execute([$company_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHrInterviews(PDO $conn, int $company_id, array $filters = []) {
    $params = [$company_id];

    $query = "
        SELECT
            i.*,
            js.full_name,
            js.email,
            js.job_title,
            js.profile_image,
            jsp.location,
            hr.id AS hiring_request_id,
            hr.status AS hiring_request_status
        FROM interviews i
        JOIN job_seekers js ON js.id = i.job_seeker_id
        LEFT JOIN job_seeker_profiles jsp ON jsp.job_seeker_id = js.id
        LEFT JOIN hiring_requests hr 
            ON hr.job_seeker_id = i.job_seeker_id 
            AND hr.company_id = i.company_id
        WHERE i.company_id = ?
    ";

    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $query .= " AND i.status = ? ";
        $params[] = $filters['status'];
    }

    if (!empty($filters['type']) && $filters['type'] !== 'all') {
        $query .= " AND i.interview_type = ? ";
        $params[] = $filters['type'];
    }

    if (!empty($filters['search'])) {
        $query .= "
            AND (
                js.full_name LIKE ?
                OR js.email LIKE ?
                OR js.job_title LIKE ?
                OR i.interviewer_name LIKE ?
                OR i.interviewer_phone LIKE ?
                OR i.location_or_link LIKE ?
            )
        ";
        $term = '%' . $filters['search'] . '%';
        array_push($params, $term, $term, $term, $term, $term, $term);
    }

    $query .= "
        ORDER BY
            CASE i.status
                WHEN 'scheduled' THEN 1
                WHEN 'completed' THEN 2
                WHEN 'cancelled' THEN 3
                ELSE 4
            END,
            i.interview_date ASC,
            i.interview_time ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHrInterviewStats(PDO $conn, int $company_id) {
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(status = 'scheduled'), 0) AS scheduled,
            COALESCE(SUM(status = 'completed'), 0) AS completed,
            COALESCE(SUM(status = 'cancelled'), 0) AS cancelled
        FROM interviews
        WHERE company_id = ?
    ");
    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'total' => 0,
        'scheduled' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];
}

function getNextHrInterview(PDO $conn, int $company_id) {
    $stmt = $conn->prepare("
        SELECT i.*, js.full_name
        FROM interviews i
        JOIN job_seekers js ON js.id = i.job_seeker_id
        WHERE i.company_id = ?
        AND i.status = 'scheduled'
        AND CONCAT(i.interview_date, ' ', i.interview_time) >= NOW()
        ORDER BY i.interview_date ASC, i.interview_time ASC
        LIMIT 1
    ");
    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createHrInterview(PDO $conn, int $company_id, array $data) {
    $errors = validateHrInterviewData($data);

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $jobSeekerId = (int)$data['job_seeker_id'];

    if (!isCandidateShortlisted($conn, $company_id, $jobSeekerId)) {
        return ['success' => false, 'errors' => ['Selected candidate is not in your shortlist.']];
    }

    $stmt = $conn->prepare("
        INSERT INTO interviews
            (company_id, job_seeker_id, interviewer_name, interviewer_phone, interview_date, interview_time, interview_type, location_or_link, notes, status)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
    ");

    $stmt->execute([
        $company_id,
        $jobSeekerId,
        trim($data['interviewer_name']),
        trim($data['interviewer_phone']),
        $data['interview_date'],
        $data['interview_time'],
        $data['interview_type'],
        trim($data['location_or_link'] ?? ''),
        trim($data['notes'] ?? '')
    ]);

    markShortlistWaitingReply($conn, $company_id, $jobSeekerId);

    return ['success' => true, 'errors' => []];
}

function validateHrInterviewData(array $data) {
    $errors = [];

    $required = [
        'job_seeker_id' => 'Candidate is required.',
        'interviewer_name' => 'Interviewer name is required.',
        'interviewer_phone' => 'Interviewer phone is required.',
        'interview_date' => 'Interview date is required.',
        'interview_time' => 'Interview time is required.',
        'interview_type' => 'Interview type is required.'
    ];

    foreach ($required as $field => $message) {
        if (empty(trim((string)($data[$field] ?? '')))) {
            $errors[] = $message;
        }
    }

    $phone = trim((string)($data['interviewer_phone'] ?? ''));
    if ($phone !== '' && !preg_match('/^07[789][0-9]{7}$/', $phone)) {
        $errors[] = 'Phone number must be Jordanian, 10 digits, and start with 077, 078, or 079.';
    }

    $date = $data['interview_date'] ?? '';
    if ($date !== '') {
        $dateObject = DateTime::createFromFormat('Y-m-d', $date);
        $today = new DateTime('today');

        if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
            $errors[] = 'Interview date is invalid.';
        } elseif ($dateObject < $today) {
            $errors[] = 'Interview date must be today or in the future.';
        }
    }

    $time = $data['interview_time'] ?? '';
    if ($time !== '' && !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        $errors[] = 'Interview time is invalid.';
    }

    $allowedTypes = ['online', 'onsite', 'phone'];
    if (!empty($data['interview_type']) && !in_array($data['interview_type'], $allowedTypes, true)) {
        $errors[] = 'Interview type is invalid.';
    }

    if (strlen(trim((string)($data['interviewer_name'] ?? ''))) < 3) {
        $errors[] = 'Interviewer name must be at least 3 characters.';
    }

    return $errors;
}

function isCandidateShortlisted(PDO $conn, int $company_id, int $jobSeekerId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM hr_shortlists WHERE company_id = ? AND job_seeker_id = ?");
    $stmt->execute([$company_id, $jobSeekerId]);
    return (int)$stmt->fetchColumn() > 0;
}

function markShortlistWaitingReply(PDO $conn, int $company_id, int $jobSeekerId) {
    $stmt = $conn->prepare("
        UPDATE hr_shortlists
        SET status = CASE WHEN status = 'new' THEN 'waiting_reply' ELSE status END
        WHERE company_id = ? AND job_seeker_id = ?
    ");
    $stmt->execute([$company_id, $jobSeekerId]);
}

function updateHrInterviewStatus(PDO $conn, int $company_id, int $interviewId, string $status) {
    $allowedStatuses = ['scheduled', 'completed', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        return false;
    }

    $stmt = $conn->prepare("UPDATE interviews SET status = ? WHERE id = ? AND company_id = ?");
    return $stmt->execute([$status, $interviewId, $company_id]);
}

function interviewStatusClass($status) {
    return [
        'scheduled' => 'scheduled',
        'completed' => 'completed',
        'cancelled' => 'cancelled'
    ][$status] ?? 'scheduled';
}

function interviewTypeLabel($type) {
    return [
        'online' => 'Online Interview',
        'onsite' => 'On-site Interview',
        'phone' => 'Phone Interview'
    ][$type] ?? 'Interview';
}



if (!function_exists('initHrInterviewsPage')) {
    function initHrInterviewsPage(PDO $conn, array $filters = []): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $company_id = (int)($_SESSION['company_id'] ?? 0);

        if (!$company_id) {
            header("Location: /tamkeentest/public/company/login.php");
            exit();
        }

        return getHrInterviewsPageData($conn, $company_id, $filters);
    }
}
