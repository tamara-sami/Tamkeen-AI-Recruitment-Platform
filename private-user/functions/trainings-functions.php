<?php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function initUserTrainingsPage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user_id = (int)$_SESSION['job_seeker_id'];

    $errors = $_SESSION['training_apply_errors'] ?? [];
    $old = $_SESSION['training_apply_old'] ?? [];
    $success = $_SESSION['training_apply_success'] ?? '';
    $openTrainingId = $_SESSION['training_apply_open_id'] ?? '';

    unset(
        $_SESSION['training_apply_errors'],
        $_SESSION['training_apply_old'],
        $_SESSION['training_apply_success'],
        $_SESSION['training_apply_open_id']
    );

    $search = trim($_GET['search'] ?? '');
    $type = $_GET['type'] ?? 'all';

    $user = getTrainingUserBasic($conn, $user_id);
    $trainings = getPublishedTrainingsForUser($conn, $user_id, $search, $type);

    return [
        'user_id' => $user_id,
        'user' => $user,
        'search' => $search,
        'type' => $type,
        'trainings' => $trainings,
        'errors' => $errors,
        'old' => $old,
        'success' => $success,
        'openTrainingId' => $openTrainingId
    ];
}

function getTrainingUserBasic($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT 
            js.*, 
            jsp.location,
            jsp.residence_country
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp 
            ON jsp.job_seeker_id = js.id
        WHERE js.id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPublishedTrainingsForUser($conn, $user_id, $search = '', $type = 'all')
{
    $query = "
        SELECT 
            t.*, 
            c.company_name,
            CASE 
                WHEN ta.id IS NULL THEN 0
                ELSE 1
            END AS already_applied,
            ta.status AS application_status
        FROM trainings t
        JOIN companies c ON c.id = t.company_id
        LEFT JOIN training_applications ta 
            ON ta.training_id = t.id 
            AND ta.job_seeker_id = ?
        WHERE t.status = 'published'
        AND t.deleted = 0
    ";

    $params = [$user_id];

    if ($type === 'voluntary') {
        $query .= " AND t.training_type = 'voluntary'";
    } elseif ($type === 'university') {
        $query .= " AND t.training_type = 'university'";
    }

    if ($search !== '') {
        $query .= " AND (
            t.title LIKE ?
            OR t.description LIKE ?
            OR t.field LIKE ?
            OR t.location LIKE ?
            OR t.training_type LIKE ?
            OR t.required_skills LIKE ?
            OR c.company_name LIKE ?
        )";

        $term = "%" . $search . "%";
        array_push($params, $term, $term, $term, $term, $term, $term, $term);
    }

    $query .= " ORDER BY t.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
