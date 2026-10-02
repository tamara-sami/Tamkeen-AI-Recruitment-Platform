<?php

function initLanguagesPage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['job_seeker_id'];

    $edit_id = $_GET['edit'] ?? ($_POST['edit_id'] ?? null);

    $savedData = $edit_id
        ? getLanguageById($conn, $user_id, $edit_id)
        : null;

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['delete_language'])
    ) {

        deleteLanguage(
            $conn,
            $user_id,
            $_POST['language_id']
        );

        header("Location: languages.php");
        exit;
    }

    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];

    unset($_SESSION['errors'], $_SESSION['old']);

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        !isset($_POST['delete_language'])
    ) {

        $edit_id = $_POST['edit_id'] ?? null;

        $data = [
            'language_name' => trim($_POST['language_name'] ?? ''),
            'proficiency_level' => trim($_POST['proficiency_level'] ?? ''),
            'reading_level' => trim($_POST['reading_level'] ?? ''),
            'writing_level' => trim($_POST['writing_level'] ?? '')
        ];

        $errors = validateLanguage($data);

        if (empty($errors)) {

            saveLanguage(
                $conn,
                $user_id,
                $data,
                $edit_id
            );

            if ($_POST['action'] === 'add_more') {
                header("Location: languages.php?new=1");
                exit;
            }

            header("Location: congratulations.php");
            exit;
        }

        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $data;

        if ($edit_id) {
            header("Location: languages.php?edit=" . $edit_id);
        } else {
            header("Location: languages.php?new=1");
        }

        exit;
    }

    return [
        'user_id' => $user_id,
        'edit_id' => $edit_id,
        'savedData' => $savedData,
        'languages' => getAllLanguages($conn, $user_id),
        'errors' => $errors,
        'old' => $old,
        'levels' => [
            "Basic",
            "Intermediate",
            "Advanced",
            "Fluent",
            "Native"
        ],
        'ratings' => [
            "Good",
            "Very Good",
            "Excellent"
        ]
    ];
}

function getLanguageById($conn, $user_id, $id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_languages
        WHERE id = ? AND job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id, $user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllLanguages($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_languages
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validateLanguage($data)
{
    $errors = [];

    if ($data['language_name'] === '') {
        $errors['language_name'] = "Language name is required";
    }

    if ($data['proficiency_level'] === '') {
        $errors['proficiency_level'] = "Proficiency level is required";
    }

    return $errors;
}

function saveLanguage($conn, $user_id, $data, $edit_id = null)
{
    if ($edit_id) {

        $stmt = $conn->prepare("
            UPDATE job_seeker_languages
            SET
                language_name = ?,
                proficiency_level = ?,
                reading_level = ?,
                writing_level = ?
            WHERE id = ? AND job_seeker_id = ?
        ");

        return $stmt->execute([
            $data['language_name'],
            $data['proficiency_level'],
            $data['reading_level'],
            $data['writing_level'],
            $edit_id,
            $user_id
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_languages
        (
            job_seeker_id,
            language_name,
            proficiency_level,
            reading_level,
            writing_level
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $data['language_name'],
        $data['proficiency_level'],
        $data['reading_level'],
        $data['writing_level']
    ]);
}

function deleteLanguage($conn, $user_id, $language_id)
{
    $stmt = $conn->prepare("
        DELETE FROM job_seeker_languages
        WHERE id = ? AND job_seeker_id = ?
    ");

    return $stmt->execute([
        $language_id,
        $user_id
    ]);
}

function formValue($old, $savedData, $key)
{
    return htmlspecialchars(
        $old[$key] ?? ($savedData[$key] ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function formSelected($old, $savedData, $key, $value)
{
    $current =
        $old[$key] ??
        ($savedData[$key] ?? '');

    return (string)$current === (string)$value
        ? 'selected'
        : '';
}