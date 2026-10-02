<?php

function getJobSeekerProfileImage($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT profile_image
        FROM job_seeker_profiles
        WHERE job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateProfileImage($file, $savedImage = null)
{
    $errors = [];

    if (
        !$savedImage &&
        (
            !isset($file) ||
            $file['error'] === UPLOAD_ERR_NO_FILE
        )
    ) {
        $errors['profile_image'] = "Please select an image";
    }

    if (
        isset($file) &&
        $file['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $ext = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($ext, $allowed)) {
            $errors['profile_image'] = "Only JPG, PNG, GIF, WEBP allowed";
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $errors['profile_image'] = "Max size 5MB";
        }
    }

    return $errors;
}

function removeProfileImage($conn, $user_id, $savedImage)
{
    if ($savedImage) {

        $filePath = "../uploads/profile_images/" . $savedImage;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $stmt = $conn->prepare("
            UPDATE job_seeker_profiles
            SET profile_image = NULL
            WHERE job_seeker_id = ?
        ");

        return $stmt->execute([$user_id]);
    }

    return true;
}

function uploadProfileImage($conn, $user_id, $file, $savedImage = null)
{
    $uploadDir = "../uploads/profile_images/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    $fileName =
        time() . "_" .
        $user_id . "." .
        $ext;

    $target = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return false;
    }

    if (
        $savedImage &&
        file_exists("../uploads/profile_images/" . $savedImage)
    ) {
        unlink("../uploads/profile_images/" . $savedImage);
    }

    $stmt = $conn->prepare("
        UPDATE job_seeker_profiles
        SET profile_image = ?
        WHERE job_seeker_id = ?
    ");

    return $stmt->execute([
        $fileName,
        $user_id
    ]);
}