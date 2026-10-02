<?php

function getJobSeekerSavedPreferences($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_profiles
        WHERE job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function saveJobSeekerPreferences($conn, $data, $user_id)
{
    $errors = [];

    $job_status = trim($data['job_status'] ?? '');
    $years_experience = trim($data['years_experience'] ?? '');

    $allowed_status = ['unemployed', 'working_open', 'not_looking'];

    if (!in_array($job_status, $allowed_status, true)) {
        $errors['job_status'] = "Select job status";
    }

    if ($years_experience === '') {
        $errors['years_experience'] = "Years of experience is required";
    } elseif (!is_numeric($years_experience) || $years_experience < 0 || $years_experience > 60) {
        $errors['years_experience'] = "Enter valid years of experience";
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $stmt = $conn->prepare("
        UPDATE job_seeker_profiles
        SET
            job_status = ?,
            years_experience = ?
        WHERE job_seeker_id = ?
    ");

    $success = $stmt->execute([
        $job_status,
        (int)$years_experience,
        $user_id
    ]);

    return [
        'success' => $success,
        'errors' => []
    ];
}
