<?php

if (!function_exists('home_e')) {
    function home_e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function homeSafeFetchAll($conn, $sql, $params = [])
{
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function homeSafeFetch($conn, $sql, $params = [])
{
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function homeCurrentUserId()
{
    return $_SESSION['job_seeker_id'] ?? null;
}

function initUserHomepage($conn)
{
    $user_id = homeCurrentUserId();

    if (!$user_id) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user = getHomeUser($conn, $user_id);
    $stats = getHomeStats($conn, $user_id);
    $recommendedTasks = getHomeRecommendedTasks($conn, $user_id);
    $recommendedTrainings = getHomeRecommendedTrainings($conn, $user_id);
    $latestExchanges = getHomeLatestExchanges($conn, $user_id);
    $applications = getHomeApplications($conn, $user_id);
    $activity = buildHomeActivity($recommendedTasks, $recommendedTrainings, $latestExchanges, $applications);
    $trendingSkills = getHomeTrendingSkills($conn);
    $companiesHiring = getHomeCompaniesHiring($conn);
    $profileCompletion = calculateHomeProfileCompletion($user, $stats);

    return [
        'user' => $user,
        'stats' => $stats,
        'recommendedTasks' => $recommendedTasks,
        'recommendedTrainings' => $recommendedTrainings,
        'latestExchanges' => $latestExchanges,
        'applications' => $applications,
        'activity' => $activity,
        'trendingSkills' => $trendingSkills,
        'companiesHiring' => $companiesHiring,
        'profileCompletion' => $profileCompletion,
    ];
}

function getHomeUser($conn, $user_id)
{
    $user = homeSafeFetch($conn, "
        SELECT 
            js.id,
            js.full_name,
            js.email,
            js.mobile,
            js.job_title,
            js.profile_image,
            jsp.location,
            jsp.residence_country,
            jsp.job_status,
            jsp.profile_visibility,
            jsp.years_experience,
            jsp.minimum_salary,
            jsp.currency,
            jsp.salary_confidential
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp
            ON jsp.job_seeker_id = js.id
        WHERE js.id = ?
        LIMIT 1
    ", [$user_id]);

    return $user ?: [
        'id' => $user_id,
        'full_name' => $_SESSION['job_seeker_name'] ?? 'Tamkeen User',
        'job_title' => 'Learner',
        'location' => 'Jordan',
        'residence_country' => 'Jordan',
    ];
}

function getHomeStats($conn, $user_id)
{
    $submitted = homeSafeFetch($conn, "
        SELECT 
            COUNT(*) AS completed_tasks,
            COALESCE(SUM(t.points), 0) AS points
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE ta.job_seeker_id = ?
        AND ta.status = 'submitted'
    ", [$user_id]);

    $apps = homeSafeFetch($conn, "
        SELECT 
            COUNT(*) AS training_applications,
            SUM(status = 'accepted') AS accepted_trainings,
            SUM(status = 'pending') AS pending_trainings
        FROM training_applications
        WHERE job_seeker_id = ?
    ", [$user_id]);

    $exchanges = homeSafeFetch($conn, "
        SELECT COUNT(*) AS exchange_posts
        FROM learning_posts
        WHERE job_seeker_id = ?
    ", [$user_id]);

    $availableTasks = homeSafeFetch($conn, "
        SELECT COUNT(*) AS available_tasks
        FROM tasks
        WHERE status = 'published'
        AND deleted = 0
    ");

    $availableTrainings = homeSafeFetch($conn, "
        SELECT COUNT(*) AS available_trainings
        FROM trainings
        WHERE status = 'published'
        AND deleted = 0
    ");

    return [
        'completed_tasks' => (int)($submitted['completed_tasks'] ?? 0),
        'points' => (int)($submitted['points'] ?? 0),
        'training_applications' => (int)($apps['training_applications'] ?? 0),
        'accepted_trainings' => (int)($apps['accepted_trainings'] ?? 0),
        'pending_trainings' => (int)($apps['pending_trainings'] ?? 0),
        'exchange_posts' => (int)($exchanges['exchange_posts'] ?? 0),
        'available_tasks' => (int)($availableTasks['available_tasks'] ?? 0),
        'available_trainings' => (int)($availableTrainings['available_trainings'] ?? 0),
    ];
}

function getHomeRecommendedTasks($conn, $user_id)
{
    return homeSafeFetchAll($conn, "
        SELECT 
            t.id,
            t.title,
            t.description,
            t.category,
            t.difficulty,
            t.estimated_time,
            t.points,
            t.deadline,
            c.company_name,
            c.company_logo
        FROM tasks t
        LEFT JOIN companies c ON c.id = t.company_id
        LEFT JOIN task_attempts ta
            ON ta.task_id = t.id
            AND ta.job_seeker_id = ?
        WHERE t.status = 'published'
        AND t.deleted = 0
        AND ta.id IS NULL
        ORDER BY 
            t.points DESC,
            t.created_at DESC
        LIMIT 3
    ", [$user_id]);
}

function getHomeRecommendedTrainings($conn, $user_id)
{
    return homeSafeFetchAll($conn, "
        SELECT 
            t.id,
            t.title,
            t.description,
            t.field,
            t.training_type,
            t.duration,
            t.location,
            t.start_date,
            t.end_date,
            c.company_name,
            c.company_logo,
            ta.status AS application_status
        FROM trainings t
        JOIN companies c ON c.id = t.company_id
        LEFT JOIN training_applications ta
            ON ta.training_id = t.id
            AND ta.job_seeker_id = ?
        WHERE t.status = 'published'
        AND t.deleted = 0
        ORDER BY 
            CASE WHEN ta.id IS NULL THEN 0 ELSE 1 END,
            t.created_at DESC
        LIMIT 3
    ", [$user_id]);
}

function getHomeLatestExchanges($conn, $user_id)
{
    return homeSafeFetchAll($conn, "
        SELECT
            lp.id,
            lp.title,
            lp.content,
            lp.category,
            lp.type,
            lp.created_at,
            js.full_name,
            js.profile_image
        FROM learning_posts lp
        JOIN job_seekers js ON js.id = lp.job_seeker_id
        ORDER BY lp.created_at DESC
        LIMIT 4
    ");
}

function getHomeApplications($conn, $user_id)
{
    return homeSafeFetchAll($conn, "
        SELECT
            ta.id,
            ta.status,
            ta.created_at,
            t.title,
            t.field,
            t.training_type,
            c.company_name
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        JOIN companies c ON c.id = t.company_id
        WHERE ta.job_seeker_id = ?
        ORDER BY ta.created_at DESC
        LIMIT 4
    ", [$user_id]);
}

function getHomeTrendingSkills($conn)
{
    $skills = homeSafeFetchAll($conn, "
        SELECT skill_name, COUNT(*) AS total
        FROM job_seeker_skills
        GROUP BY skill_name
        ORDER BY total DESC
        LIMIT 5
    ");

    if (!empty($skills)) {
        return $skills;
    }

    return [
        ['skill_name' => 'Excel', 'total' => 42],
        ['skill_name' => 'Power BI', 'total' => 35],
        ['skill_name' => 'SQL', 'total' => 28],
        ['skill_name' => 'Canva', 'total' => 18],
    ];
}

function getHomeCompaniesHiring($conn)
{
    return homeSafeFetchAll($conn, "
        SELECT 
            c.id,
            c.company_name,
            c.company_logo,
            COUNT(t.id) AS openings
        FROM companies c
        JOIN trainings t ON t.company_id = c.id
        WHERE t.status = 'published'
        AND t.deleted = 0
        GROUP BY c.id, c.company_name, c.company_logo
        ORDER BY openings DESC
        LIMIT 4
    ");
}

function buildHomeActivity($tasks, $trainings, $exchanges, $applications)
{
    $activity = [];

    foreach (array_slice($applications, 0, 2) as $app) {
        $activity[] = [
            'icon' => 'fa-file-alt',
            'title' => 'Training application',
            'text' => ($app['title'] ?? 'Training') . ' · ' . ($app['status'] ?? 'pending'),
            'link' => 'my-training-applications.php'
        ];
    }

    foreach (array_slice($tasks, 0, 1) as $task) {
        $activity[] = [
            'icon' => 'fa-tasks',
            'title' => 'Recommended task',
            'text' => ($task['title'] ?? 'Task') . ' · +' . (int)($task['points'] ?? 0) . ' points',
            'link' => 'tasks.php'
        ];
    }

    foreach (array_slice($exchanges, 0, 1) as $post) {
        $activity[] = [
            'icon' => 'fa-exchange-alt',
            'title' => 'New skill exchange',
            'text' => ($post['title'] ?? 'Skill exchange') . ' · ' . ($post['category'] ?? 'Community'),
            'link' => 'courseshome.php'
        ];
    }

    if (empty($activity)) {
        $activity[] = [
            'icon' => 'fa-star',
            'title' => 'Welcome to Tamkeen',
            'text' => 'Start a task, apply to training, or join a skill exchange.',
            'link' => 'tasks.php'
        ];
    }

    return $activity;
}

function calculateHomeProfileCompletion($user, $stats)
{
    $score = 20;

    if (!empty($user['full_name'])) $score += 10;
    if (!empty($user['job_title'])) $score += 10;
    if (!empty($user['profile_image'])) $score += 15;
    if (!empty($user['location'])) $score += 10;
    if (!empty($user['job_status'])) $score += 10;
    if (($stats['completed_tasks'] ?? 0) > 0) $score += 15;
    if (($stats['training_applications'] ?? 0) > 0) $score += 10;

    return min(100, $score);
}

function homeProfileImage($base_url, $path, $fallback = ''){
    if (empty($path)) {
    return '';

    }

    $path = ltrim($path, '/');

    if (str_starts_with($path, 'public/')) {
        return $base_url . $path;
    }

    if (str_starts_with($path, 'uploads/')) {
        return $base_url . 'public/' . $path;
    }

    return $base_url . 'public/uploads/profile_images/' . $path;
}
function homeCompanyLogo($base_url, $path)
{
    if (empty($path)) {
        return null;
    }

    $path = ltrim($path, '/');

    if (str_starts_with($path, 'public/')) {
        return $base_url . $path;
    }

    if (str_starts_with($path, 'uploads/')) {
        return $base_url . 'public/' . $path;
    }

    return $base_url . 'public/uploads/company/' . $path;
}
