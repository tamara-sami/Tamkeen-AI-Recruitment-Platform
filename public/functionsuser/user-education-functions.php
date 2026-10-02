<?php

function getJobSeekerEducation($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_educations
        WHERE job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateEducationData($data, $savedData = null)
{
    $errors = [];

    $degree = trim($data['degree'] ?? '');
    $institution = trim($data['institution'] ?? '');
    $field = trim($data['field'] ?? '');
    $graduation_year = trim($data['graduation_year'] ?? '');
    $grade = trim($data['grade'] ?? '');

    if (!$savedData && $degree === '') {
        $errors['degree'] = "Degree is required";
    }

    if (!$savedData && $institution === '') {
        $errors['institution'] = "Institution is required";
    } elseif (
        $institution !== '' &&
        !preg_match("/^[A-Za-z0-9\s&.,()'-]{2,120}$/", $institution)
    ) {
        $errors['institution'] = "Institution contains invalid characters";
    }

    if (!$savedData && $field === '') {
        $errors['field'] = "Field is required";
    } elseif (
        $field !== '' &&
        !preg_match("/^[A-Za-z\s&.,()'-]{2,80}$/", $field)
    ) {
        $errors['field'] = "Field contains invalid characters";
    }

    if (!$savedData && $graduation_year === '') {
        $errors['graduation_year'] = "Graduation year is required";
    } elseif (
        $graduation_year !== '' &&
        (
            !is_numeric($graduation_year) ||
            $graduation_year < 1980 ||
            $graduation_year > 2032
        )
    ) {
        $errors['graduation_year'] = "Invalid graduation year";
    }

    if (!$savedData && $grade === '') {
        $errors['grade'] = "Grade is required";
    } elseif (
        $grade !== '' &&
        strlen($grade) > 50
    ) {
        $errors['grade'] = "Grade is too long";
    }

    return $errors;
}

function saveEducationData($conn, $data, $user_id, $savedData = null)
{
    $degree = trim($data['degree'] ?? '');
    $institution = trim($data['institution'] ?? '');
    $field = trim($data['field'] ?? '');
    $graduation_year = trim($data['graduation_year'] ?? '');
    $grade = trim($data['grade'] ?? '');

    if ($savedData) {

        $stmt = $conn->prepare("
            UPDATE job_seeker_educations
            SET
                degree = ?,
                institution = ?,
                field = ?,
                graduation_year = ?,
                grade = ?
            WHERE job_seeker_id = ?
        ");

        return $stmt->execute([
            $degree,
            $institution,
            $field,
            $graduation_year,
            $grade,
            $user_id
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_educations
        (
            job_seeker_id,
            degree,
            institution,
            field,
            graduation_year,
            grade
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $degree,
        $institution,
        $field,
        $graduation_year,
        $grade
    ]);
}
function handleEducationForm($conn, $user_id, $savedData)
{
    $errors = validateEducationData(
        $_POST,
        $savedData
    );

    $degree = trim($_POST['degree'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $field = trim($_POST['field'] ?? '');
    $graduation_year = trim($_POST['graduation_year'] ?? '');
    $grade = trim($_POST['grade'] ?? '');

    if (
        empty($errors) &&
        $savedData &&
        $degree === '' &&
        $institution === '' &&
        $field === '' &&
        $graduation_year === '' &&
        $grade === ''
    ) {

        return [
            'success' => true,
            'redirect' => 'bulidcv.php',
            'errors' => []
        ];
    }

    if (empty($errors)) {

        saveEducationData(
            $conn,
            $_POST,
            $user_id,
            $savedData
        );

        return [
            'success' => true,
            'redirect' => 'bulidcv.php',
            'errors' => []
        ];
    }

    return [
        'success' => false,
        'errors' => $errors
    ];
}