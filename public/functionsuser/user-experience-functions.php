<?php

function getExperiencePageData($conn, $user_id)
{
    $edit_id = $_GET['edit'] ?? ($_POST['edit_id'] ?? null);

    return [
        'edit_id' => $edit_id,
        'savedData' => $edit_id ? getExperienceById($conn, $user_id, $edit_id) : null,
        'errors' => $_SESSION['errors'] ?? [],
        'old' => $_SESSION['old'] ?? [],
        'months' => [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ]
    ];
}

function clearExperienceFlash()
{
    unset($_SESSION['errors'], $_SESSION['old']);
}

function getExperienceById($conn, $user_id, $id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_experiences
        WHERE id = ? AND job_seeker_id = ?
        LIMIT 1
    ");
    $stmt->execute([$id, $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function handleExperienceForm($conn, $user_id)
{
    $edit_id = $_POST['edit_id'] ?? null;

    $data = [
        'job_title' => trim($_POST['job_title'] ?? ''),
        'company_name' => trim($_POST['company_name'] ?? ''),
        'company_location' => trim($_POST['company_location'] ?? ''),
        'from_month' => trim($_POST['from_month'] ?? ''),
        'from_year' => trim($_POST['from_year'] ?? ''),
        'to_month' => trim($_POST['to_month'] ?? ''),
        'to_year' => trim($_POST['to_year'] ?? ''),
        'is_present' => isset($_POST['is_present']) ? 1 : 0,
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateExperience($data);

    if (empty($errors)) {
        saveExperience($conn, $user_id, $data, $edit_id);

        return [
            'success' => true,
            'redirect' => 'workexproencelist.php'
        ];
    }

    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;

    return [
        'success' => false,
        'redirect' => $edit_id ? "bulidcv.php?edit=" . $edit_id : "bulidcv.php?new=1"
    ];
}

function validateExperience($data)
{
    $errors = [];

    if ($data['job_title'] === '') {
        $errors['job_title'] = "Job title is required";
    }

    if ($data['company_name'] === '') {
        $errors['company_name'] = "Company name is required";
    }

    if ($data['from_month'] === '' || $data['from_year'] === '') {
        $errors['from_date'] = "From date is required";
    }

    if (!$data['is_present'] && ($data['to_month'] === '' || $data['to_year'] === '')) {
        $errors['to_date'] = "To date is required or choose To Present";
    }

    $months = [
        "January" => 1, "February" => 2, "March" => 3, "April" => 4,
        "May" => 5, "June" => 6, "July" => 7, "August" => 8,
        "September" => 9, "October" => 10, "November" => 11, "December" => 12
    ];

    if (
        !$data['is_present'] &&
        $data['from_month'] !== '' &&
        $data['from_year'] !== '' &&
        $data['to_month'] !== '' &&
        $data['to_year'] !== ''
    ) {
        if ((int)$data['to_year'] < (int)$data['from_year']) {
            $errors['date'] = "End date must be after start date";
        } elseif ((int)$data['to_year'] === (int)$data['from_year']) {
            if ($months[$data['to_month']] < $months[$data['from_month']]) {
                $errors['date'] = "End month must be after start month";
            }
        }
    }

    return $errors;
}

function saveExperience($conn, $user_id, $data, $edit_id = null)
{
    if ($data['is_present']) {
        $data['to_month'] = null;
        $data['to_year'] = null;
    }

    if ($edit_id) {
        $stmt = $conn->prepare("
            UPDATE job_seeker_experiences
            SET job_title = ?,
                company_name = ?,
                company_location = ?,
                from_month = ?,
                from_year = ?,
                to_month = ?,
                to_year = ?,
                is_present = ?,
                description = ?
            WHERE id = ? AND job_seeker_id = ?
        ");

        return $stmt->execute([
            $data['job_title'],
            $data['company_name'],
            $data['company_location'],
            $data['from_month'],
            $data['from_year'],
            $data['to_month'],
            $data['to_year'],
            $data['is_present'],
            $data['description'],
            $edit_id,
            $user_id
        ]);
    }

    $stmt = $conn->prepare("
        INSERT INTO job_seeker_experiences
        (
            job_seeker_id,
            job_title,
            company_name,
            company_location,
            from_month,
            from_year,
            to_month,
            to_year,
            is_present,
            description
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $user_id,
        $data['job_title'],
        $data['company_name'],
        $data['company_location'],
        $data['from_month'],
        $data['from_year'],
        $data['to_month'],
        $data['to_year'],
        $data['is_present'],
        $data['description']
    ]);
}

function formValue($old, $savedData, $key)
{
    return htmlspecialchars($old[$key] ?? ($savedData[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}

function formSelected($old, $savedData, $key, $value)
{
    $current = $old[$key] ?? ($savedData[$key] ?? '');
    return (string)$current === (string)$value ? 'selected' : '';
}

function formChecked($old, $savedData, $key)
{
    if (isset($old[$key])) {
        return !empty($old[$key]) ? 'checked' : '';
    }

    return !empty($savedData[$key]) ? 'checked' : '';
}
function getAllExperiences($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM job_seeker_experiences
        WHERE job_seeker_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteExperience($conn, $user_id, $id)
{
    $stmt = $conn->prepare("
        DELETE FROM job_seeker_experiences
        WHERE id = ? AND job_seeker_id = ?
    ");

    return $stmt->execute([$id, $user_id]);
}

function experienceDateText($exp)
{
    $from = $exp['from_month'] . " " . $exp['from_year'];

    if (!empty($exp['is_present'])) {
        return $from . " - Present";
    }

    return $from . " - " . $exp['to_month'] . " " . $exp['to_year'];
}