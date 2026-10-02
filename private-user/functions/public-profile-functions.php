<?php

function pp_e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function initPublicProfilePage($conn)
{
    $viewerJobSeekerId = isset($_SESSION['job_seeker_id']) ? (int)$_SESSION['job_seeker_id'] : 0;
    $token = trim($_GET['token'] ?? '');

    if ($token !== '') {
    $stmt = $conn->prepare("
        SELECT id
        FROM job_seekers
        WHERE qr_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $profileUserId = (int)$stmt->fetchColumn();
    } else {
        $profileUserId = (int)($_GET['id'] ?? $viewerJobSeekerId);
    }

    if ($profileUserId <= 0) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user = getPublicProfileUser($conn, $profileUserId);

    if (!$user) {
        return [
            'allowed' => false,
            'reason' => 'Profile not found',
            'profileUserId' => $profileUserId
        ];
    }

    $isOwner = $viewerJobSeekerId === $profileUserId;

    return [
        'allowed' => true,
        'profileUserId' => $profileUserId,
        'viewerJobSeekerId' => $viewerJobSeekerId,
        'isOwner' => $isOwner,
        'user' => $user,
        'skills' => getPublicProfileSkills($conn, $profileUserId),
        'languages' => getPublicProfileLanguages($conn, $profileUserId),
        'education' => getPublicProfileEducation($conn, $profileUserId),
        'experience' => getPublicProfileExperience($conn, $profileUserId),
        'courses' => getPublicProfileCourses($conn, $profileUserId),
        'completedTasks' => getPublicProfileCompletedTasks($conn, $profileUserId),
        'trainings' => getPublicProfileTrainings($conn, $profileUserId),
        'exchanges' => getPublicProfileExchanges($conn, $profileUserId),
        'stats' => getPublicProfileStats($conn, $profileUserId),
        'error' => $_SESSION['profile_public_error'] ?? ''
    ];
}

function getPublicProfileUser($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT
            js.id,
            js.full_name,
            js.email,
            js.mobile,
            js.job_title,
            js.profile_image,
            js.about_me,
            jsp.location,
            jsp.residence_country,
            jsp.job_status,
            jsp.years_experience,
            jsp.minimum_salary,
            jsp.currency
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp
            ON jsp.job_seeker_id = js.id
        WHERE js.id = ?
        LIMIT 1
    ");

    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPublicProfileSkills($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_skills
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileLanguages($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_languages
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileEducation($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_educations
        WHERE job_seeker_id = ?
        ORDER BY graduation_year DESC, id DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileExperience($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_experiences
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileCourses($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_courses
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileCompletedTasks($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT
            ta.*,
            t.title,
            t.category,
            t.difficulty,
            t.points,
            c.company_name
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        LEFT JOIN companies c ON c.id = t.company_id
        WHERE ta.job_seeker_id = ?
        AND ta.status = 'submitted'
        ORDER BY ta.submitted_at DESC
        LIMIT 8
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileTrainings($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT
            ta.*,
            t.title,
            t.field,
            t.training_type,
            c.company_name
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        JOIN companies c ON c.id = t.company_id
        WHERE ta.job_seeker_id = ?
        AND ta.status IN ('accepted','interview','reviewing')
        ORDER BY ta.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileExchanges($conn, $userId)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM learning_posts
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPublicProfileStats($conn, $userId)
{
    $stats = [
        'completed_tasks' => 0,
        'task_points' => 0,
        'training_count' => 0,
        'exchange_count' => 0
    ];

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total, COALESCE(SUM(t.points),0) AS points
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE ta.job_seeker_id = ?
        AND ta.status = 'submitted'
    ");
    $stmt->execute([$userId]);
    $taskStats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stats['completed_tasks'] = (int)($taskStats['total'] ?? 0);
    $stats['task_points'] = (int)($taskStats['points'] ?? 0);

    $stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM training_applications
    WHERE job_seeker_id = ?
    AND status = 'accepted'
");
    $stmt->execute([$userId]);
    $stats['training_count'] = (int)$stmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM learning_posts
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stats['exchange_count'] = (int)$stmt->fetchColumn();

    return $stats;
}
