<?php

function createCompanyUser($conn, $data, $company_id)
{
    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';

    $allowed_roles = ['hr', 'task_manager', 'training_manager'];

    if ($full_name === '' || $email === '' || $password === '' || $role === '') {
        return "missing_fields";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "invalid_email";
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/', $password)) {
        return "weak_password";
    }

    if (!in_array($role, $allowed_roles)) {
        return "invalid_role";
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        return "email_exists";
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, password, role, company_id, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    return $stmt->execute([
        $full_name,
        $email,
        $hashed_password,
        $role,
        $company_id
    ]) ? "success" : "error";
}

function getCompanyUsersPageData($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE company_id = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$company_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "users" => $users,
        "hr_count" => countUsersByRole($users, "hr"),
        "task_manager_count" => countUsersByRole($users, "task_manager"),
        "training_manager_count" => countUsersByRole($users, "training_manager")
    ];
}

function countUsersByRole($users, $role)
{
    $count = 0;

    foreach ($users as $user) {
        if ($user["role"] === $role) {
            $count++;
        }
    }

    return $count;
}

function companyUserMessage($result, $name = "", $role = "")
{
    if ($result === "success") {
        return $name . " added as " . roleLabel($role) . ".";
    }

    return [
        "email_exists" => "Email already exists.",
        "missing_fields" => "Please fill in all fields.",
        "invalid_email" => "Please enter a valid email address.",
        "weak_password" => "Password must be at least 8 characters and include letters, numbers, and a special symbol.",
        "invalid_role" => "Please select a valid role.",
        "error" => "Something went wrong."
    ][$result] ?? "Something went wrong.";
}

function roleLabel($role)
{
    return [
        "hr" => "HR",
        "task_manager" => "Task Manager",
        "training_manager" => "Training Manager"
    ][$role] ?? ucfirst($role);
}

function renderCompanyLogo($company_name, $company_logo, $class = "profile-avatar")
{
    if (!empty($company_logo)) {

        return '
            <img
               src="../../public/uploads/company/' . htmlspecialchars($company_logo) . '"
                class="' . htmlspecialchars($class) . '"
                style="object-fit:cover;"
                alt="Company Logo">
        ';
    }

    return '
        <div class="' . htmlspecialchars($class) . '">
            ' . htmlspecialchars(strtoupper(substr($company_name, 0, 1))) . '
        </div>
    ';
}