<?php

function getSkillsPageData($conn, $user_id)
{
    $edit_id = $_GET['edit'] ?? ($_POST['edit_id'] ?? null);

    return [
        'edit_id' => $edit_id,
        'savedData' => $edit_id ? getSkillById($conn, $user_id, $edit_id) : null,
        'skills' => getAllSkills($conn, $user_id),
        'errors' => $_SESSION['errors'] ?? [],
        'old' => $_SESSION['old'] ?? [],
        'levels' => ["Beginner", "Intermediate", "Advanced", "Expert"],
        'years' => [
            "Less than 1 year",
            "1 Year",
            "2 Years",
            "3 Years",
            "4+ Years"
        ]
    ];
}

function clearSkillsFlash()
{
    unset($_SESSION['errors'], $_SESSION['old']);
}

function handleDeleteSkill($conn, $user_id)
{
    deleteSkill(
        $conn,
        $user_id,
        $_POST['skill_id']
    );

    return "skill.php";
}

function handleSkillForm($conn, $user_id)
{
    $edit_id = $_POST['edit_id'] ?? null;

    $data = [
        'skill_name' => trim($_POST['skill_name'] ?? ''),
        'skill_level' => trim($_POST['skill_level'] ?? ''),
        'years_experience' => trim($_POST['years_experience'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateSkill($data);

    if (empty($errors)) {

        saveSkill(
            $conn,
            $user_id,
            $data,
            $edit_id
        );

        return [
            'success' => true,
            'redirect' =>
                $_POST['action'] === 'add_more'
                ? 'skill.php?new=1'
                : 'languages.php'
        ];
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;

    return [
        'success' => false,
        'redirect' =>
            $edit_id
            ? "skill.php?edit=" . $edit_id
            : "skill.php?new=1"
    ];
}

function getSkillById($conn, $user_id, $id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_skills
        WHERE id = ? AND job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id, $user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllSkills($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_skills
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validateSkill($data)
{
    $errors = [];

    if ($data['skill_name'] === '') {
        $errors['skill_name'] = "Skill name is required";
    }

    if ($data['skill_level'] === '') {
        $errors['skill_level'] = "Skill level is required";
    }

    return $errors;
}

function saveSkill($conn, $user_id, $data, $edit_id = null)
{
    if ($edit_id) {

        $stmt = $conn->prepare("
            UPDATE job_seeker_skills
            SET
                skill_name = ?,
                skill_level = ?,
                years_experience = ?,
                description = ?
            WHERE id = ? AND job_seeker_id = ?
        ");

        return $stmt->execute([
            $data['skill_name'],
            $data['skill_level'],
            $data['years_experience'],
            $data['description'],
            $edit_id,
            $user_id
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_skills
        (
            job_seeker_id,
            skill_name,
            skill_level,
            years_experience,
            description
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $data['skill_name'],
        $data['skill_level'],
        $data['years_experience'],
        $data['description']
    ]);
}

function deleteSkill($conn, $user_id, $skill_id)
{
    $stmt = $conn->prepare("
        DELETE FROM job_seeker_skills
        WHERE id = ? AND job_seeker_id = ?
    ");

    return $stmt->execute([
        $skill_id,
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