<?php

function registerJobSeeker($conn, $data)
{
    $errors = [];

    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $mobile = trim($data['mobile'] ?? '');
    $job_title = trim($data['job_title'] ?? '');
    $password = $data['password'] ?? '';

    if ($full_name === '') {
        $errors['full_name'] = "Full name is required";
    } elseif (!preg_match("/^[A-Za-z\s'-]{2,80}$/", $full_name)) {
        $errors['full_name'] = "Full name must contain letters only.";
    }

    if ($email === '') {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address";
    } else {
        $check = $conn->prepare("SELECT id FROM job_seekers WHERE email = ? LIMIT 1");
        $check->execute([$email]);

        if ($check->fetch()) {
            $errors['email'] = "This email is already registered";
        }
    }

    if ($mobile === '') {
        $errors['mobile'] = "Mobile number is required";
    } elseif (!preg_match("/^07[789][0-9]{7}$/", $mobile)) {
        $errors['mobile'] = "Enter a valid Jordanian number like 0791234567";
    }

    if ($job_title === '') {
        $errors['job_title'] = "Job title is required";
    } elseif (!preg_match("/^[A-Za-z\s'-]{2,80}$/", $job_title)) {
        $errors['job_title'] = "Job title must contain letters only.";
    }

    if ($password === '') {
        $errors['password'] = "Password is required";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/', $password)) {
        $errors['password'] = "Password must be 8+ characters with letter, number, and symbol";
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO job_seekers
        (full_name, email, mobile, job_title, password)
        VALUES (?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $full_name,
        $email,
        $mobile,
        $job_title,
        $hashed_password
    ]);

    return [
        'success' => $success,
        'errors' => [],
        'user_id' => $conn->lastInsertId()
    ];
}