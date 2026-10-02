<?php

function getJobSeekerSavedProfile($conn, $user_id)
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

function saveJobSeekerProfile($conn, $data, $user_id)
{
    $errors = [];

    $birth_date = $data['birth_date'] ?? '';
    $gender = $data['gender'] ?? '';
    $nationality = trim($data['nationality'] ?? '');
    $residence_country = trim($data['residence_country'] ?? '');

    $location_select = trim($data['location_select'] ?? '');
    $location_input = trim($data['location_input'] ?? '');

    if ($residence_country === 'Jordan') {
        $location = $location_select;
    } else {
        $location = $location_input;
    }

    if (empty($birth_date)) {

        $errors['birth_date'] = "Birth date is required";

    } else {

        $age = date_diff(
            date_create($birth_date),
            date_create('today')
        )->y;

        if ($age < 16) {
            $errors['birth_date'] = "You must be at least 16 years old";
        }
    }

    if (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = "Select gender";
    }

    if ($nationality === '') {
        $errors['nationality'] = "Nationality is required";
    }

    if ($residence_country === '') {
        $errors['residence_country'] = "Residence country is required";
    }

    if ($location === '') {

        $errors['location'] = "Location is required";

    } elseif (!preg_match("/^[A-Za-z\s\-'.,]+$/", $location)) {

        $errors['location'] = "Location contains invalid characters";
    }

    if (!empty($errors)) {

        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $check = $conn->prepare("
        SELECT id
        FROM job_seeker_profiles
        WHERE job_seeker_id = ?
        LIMIT 1
    ");

    $check->execute([$user_id]);

    $exists = $check->fetch(PDO::FETCH_ASSOC);

    if ($exists) {

        $stmt = $conn->prepare("
            UPDATE job_seeker_profiles
            SET
                birth_date = ?,
                gender = ?,
                nationality = ?,
                residence_country = ?,
                location = ?
            WHERE job_seeker_id = ?
        ");

        $success = $stmt->execute([
            $birth_date,
            $gender,
            $nationality,
            $residence_country,
            $location,
            $user_id
        ]);

    } else {

        $stmt = $conn->prepare("
            INSERT INTO job_seeker_profiles
            (
                job_seeker_id,
                birth_date,
                gender,
                nationality,
                residence_country,
                location
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $user_id,
            $birth_date,
            $gender,
            $nationality,
            $residence_country,
            $location
        ]);
    }

    return [
        'success' => $success,
        'errors' => []
    ];
}