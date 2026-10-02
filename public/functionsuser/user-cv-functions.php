<?php

function getJobSeekerCV($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_cvs
        WHERE job_seeker_id = ?
        ORDER BY uploaded_at DESC
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateCVUpload($language, $file, $savedCV = null)
{
    $errors = [];

    if (!in_array($language, ['English', 'Arabic'])) {
        $errors['cv_language'] = "Select CV language";
    }

    if (
        !$savedCV &&
        (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)
    ) {
        $errors['cv_file'] = "Please upload your CV";
    }

    if (
        $file &&
        $file['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $allowed = ['pdf', 'doc', 'docx'];

        $ext = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($ext, $allowed)) {
            $errors['cv_file'] = "Only PDF, DOC, and DOCX files are allowed";
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $errors['cv_file'] = "Maximum file size is 10 MB";
        }
    }

    return $errors;
}

function updateCVLanguageOnly($conn, $language, $cv_id)
{
    $stmt = $conn->prepare("
        UPDATE job_seeker_cvs
        SET cv_language = ?
        WHERE id = ?
    ");

    return $stmt->execute([
        $language,
        $cv_id
    ]);
}

function uploadJobSeekerCV($conn, $user_id, $language, $file)
{
$uploadDir = "../../public/uploads/company/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeName = preg_replace(
        "/[^a-zA-Z0-9._-]/",
        "",
        $file['name']
    );

    $newFileName =
        time() . "_" .
        $user_id . "_" .
        $safeName;

    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_cvs
        (
            job_seeker_id,
            cv_file,
            cv_language
        )
        VALUES (?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $newFileName,
        $language
    ]);
}