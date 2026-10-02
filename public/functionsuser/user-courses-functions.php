<?php

function getCoursesPageData($conn, $user_id)
{
    $edit_id = $_GET['edit'] ?? ($_POST['edit_id'] ?? null);

    return [
        'edit_id' => $edit_id,
        'savedData' => $edit_id ? getCourseById($conn, $user_id, $edit_id) : null,
        'courses' => getAllCourses($conn, $user_id),
        'errors' => $_SESSION['errors'] ?? [],
        'old' => $_SESSION['old'] ?? [],
        'months' => [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ]
    ];
}

function clearCoursesFlash()
{
    unset($_SESSION['errors'], $_SESSION['old']);
}

function handleDeleteCourse($conn, $user_id)
{
    deleteCourse(
        $conn,
        $user_id,
        $_POST['course_id']
    );

    return "courses.php";
}

function handleCourseForm($conn, $user_id)
{
    $edit_id = $_POST['edit_id'] ?? null;

    $data = [
        'course_title' => trim($_POST['course_title'] ?? ''),
        'course_provider' => trim($_POST['course_provider'] ?? ''),
        'course_month' => trim($_POST['course_month'] ?? ''),
        'course_year' => trim($_POST['course_year'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateCourse($data);

    if (empty($errors)) {

        saveCourse(
            $conn,
            $user_id,
            $data,
            $edit_id
        );

        return [
            'success' => true,
            'redirect' =>
                $_POST['action'] === 'add_more'
                ? 'courses.php?new=1'
                : 'skill.php'
        ];
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;

    return [
        'success' => false,
        'redirect' =>
            $edit_id
            ? "courses.php?edit=" . $edit_id
            : "courses.php?new=1"
    ];
}

function getCourseById($conn, $user_id, $id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_courses
        WHERE id = ? AND job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$id, $user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllCourses($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_courses
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validateCourse($data)
{
    $errors = [];

    if ($data['course_title'] === '') {
        $errors['course_title'] = "Course title is required";
    }

    if ($data['course_provider'] === '') {
        $errors['course_provider'] = "Course provider is required";
    }

    if (
        $data['course_month'] === '' ||
        $data['course_year'] === ''
    ) {
        $errors['course_date'] = "Course date is required";
    }

    return $errors;
}

function saveCourse($conn, $user_id, $data, $edit_id = null)
{
    if ($edit_id) {

        $stmt = $conn->prepare("
            UPDATE job_seeker_courses
            SET
                course_title = ?,
                course_provider = ?,
                course_month = ?,
                course_year = ?,
                description = ?
            WHERE id = ? AND job_seeker_id = ?
        ");

        return $stmt->execute([
            $data['course_title'],
            $data['course_provider'],
            $data['course_month'],
            $data['course_year'],
            $data['description'],
            $edit_id,
            $user_id
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_courses
        (
            job_seeker_id,
            course_title,
            course_provider,
            course_month,
            course_year,
            description
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $data['course_title'],
        $data['course_provider'],
        $data['course_month'],
        $data['course_year'],
        $data['description']
    ]);
}

function deleteCourse($conn, $user_id, $course_id)
{
    $stmt = $conn->prepare("
        DELETE FROM job_seeker_courses
        WHERE id = ? AND job_seeker_id = ?
    ");

    return $stmt->execute([
        $course_id,
        $user_id
    ]);
}

function courseDateText($course)
{
    return
        $course['course_month'] .
        ' ' .
        $course['course_year'];
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