<?php

function loginJobSeeker($conn, $email, $password)
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $stmt = $conn->prepare("
        SELECT *
        FROM job_seekers
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return false;
}