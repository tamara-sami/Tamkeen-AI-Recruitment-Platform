<?php

function getCompanyUserDetails($conn, $user_id, $company_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE id = ? AND company_id = ?
    ");

    $stmt->execute([$user_id, $company_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function toggleCompanyUserStatus($conn, $user)
{
    $new_status = ($user['status'] == 'inactive') ? 'active' : 'inactive';

    $stmt = $conn->prepare("
        UPDATE users
        SET status = ?
        WHERE id = ? AND company_id = ?
    ");

    $stmt->execute([
        $new_status,
        $user['id'],
        $user['company_id']
    ]);

    return $new_status == 'active'
        ? "User account activated successfully."
        : "User account deactivated successfully.";
}

function updateCompanyUser($conn, $user_id, $company_id, $data)
{
    $name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');

    if ($name === '' || $email === '') {
        return "Name and email are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address.";
    }

    $stmt = $conn->prepare("
        UPDATE users
        SET full_name = ?, email = ?
        WHERE id = ? AND company_id = ?
    ");

    $stmt->execute([$name, $email, $user_id, $company_id]);

    return "User updated successfully.";
}

function updateCompanyUserPassword($conn, $user_id, $company_id, $data)
{
    $pass = trim($data['new_password'] ?? '');
    $confirm = trim($data['confirm_password'] ?? '');

    if ($pass !== $confirm) {

        return "Passwords do not match.";
    }

    if (
        !preg_match(
            '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/',
            $pass
        )
    ) {

        return "Password must contain at least 8 characters, one letter, one number, and one special character.";
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users
        SET password = ?
        WHERE id = ? AND company_id = ?
    ");

    $stmt->execute([
        $hashed,
        $user_id,
        $company_id
    ]);

    return "Password updated successfully.";
}

function userRoleLabel($role)
{
    return [
        "hr" => "HR",
        "task_manager" => "Task Manager",
        "training_manager" => "Training Manager"
    ][$role] ?? ucfirst($role);
}

function userRoleClass($role)
{
    return [
        "task_manager" => "task",
        "training_manager" => "task"
    ][$role] ?? $role;
}
function getUserActivityLog($conn, $user_id)
{
    $activities = [];

    $stmt = $conn->prepare("
        SELECT full_name, role, status, created_at
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return [];
    }

    $activities[] = [
        "icon" => "fa fa-user-plus",
        "title" => "User account created",
        "description" => $user['full_name'] . " was added as " . $user['role'] . ".",
        "date" => $user['created_at'] ?? "Not available"
    ];

    $activities[] = [
        "icon" => $user['status'] == 'active' ? "fa fa-check-circle" : "fa fa-ban",
        "title" => $user['status'] == 'active' ? "Account is active" : "Account is inactive",
        "description" => "Current account status.",
        "date" => $user['created_at'] ?? "Not available"
    ];

    return $activities;
}