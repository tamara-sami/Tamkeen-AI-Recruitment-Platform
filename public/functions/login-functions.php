<?php

const MAX_LOGIN_ATTEMPTS = 4;
const LOCK_MINUTES = 5;

function getRemainingLockMinutes($lockedUntil)
{
    if (empty($lockedUntil)) {
        return 0;
    }

    $remainingSeconds = strtotime($lockedUntil) - time();

    if ($remainingSeconds <= 0) {
        return 0;
    }

    return (int)ceil($remainingSeconds / 60);
}

function handleCompanyLogin($conn, $post)
{
    $email = trim($post["email"] ?? "");
    $password = $post["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            "emailError" => "Invalid email",
            "passwordError" => ""
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | COMPANY LOGIN
    |--------------------------------------------------------------------------
    */

    $company = getCompanyByEmail($conn, $email);

    if ($company) {

        $remainingMinutes = getRemainingLockMinutes($company['locked_until']);

        if ($remainingMinutes > 0) {
            return [
                "emailError" => "",
                "passwordError" => "Too many failed attempts. Try again after {$remainingMinutes} minute(s)."
            ];
        }

        if (password_verify($password, $company['password'])) {

            resetLoginAttempts($conn, "companies", $company['id']);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION["company_id"] = $company["id"];
            $_SESSION["company_name"] = $company["company_name"];
            $_SESSION["role"] = "company";

            header("Location: employer-dashboard.php");
            exit();
        }

        handleFailedLogin($conn, "companies", $company['id'], $company['failed_login_attempts']);

        return [
            "emailError" => "",
            "passwordError" => "Wrong email or password"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STAFF LOGIN
    |--------------------------------------------------------------------------
    */

    $user = getStaffByEmail($conn, $email);

    if ($user) {

        $remainingMinutes = getRemainingLockMinutes($user['locked_until']);

        if ($remainingMinutes > 0) {
            return [
                "emailError" => "",
                "passwordError" => "Too many failed attempts. Try again after {$remainingMinutes} minute(s)."
            ];
        }

        if (password_verify($password, $user["password"])) {

            resetLoginAttempts($conn, "users", $user['id']);

            if ($user["status"] == "inactive") {
                return [
                    "emailError" => "",
                    "passwordError" => "Account is inactive"
                ];
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["company_id"] = $user["company_id"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["full_name"] = $user["full_name"];

            if ($user["status"] == "pending") {
                activatePendingStaff($conn, $user["id"]);
            }

            if ($user["role"] == "hr") {
                header("Location: ../hr/hr-dashboard.php");
                exit();
            }

            if ($user["role"] == "task_manager") {
                header("Location: ../task/task-dashboard.php");
                exit();
            }

            if ($user["role"] == "training_manager") {
                header("Location: ../training/training-dashboard.php");
                exit();
            }

            return [
                "emailError" => "",
                "passwordError" => "Invalid role"
            ];
        }

        handleFailedLogin($conn, "users", $user['id'], $user['failed_login_attempts']);

        return [
            "emailError" => "",
            "passwordError" => "Wrong email or password"
        ];
    }

    return [
        "emailError" => "",
        "passwordError" => "Wrong email or password"
    ];
}

function handleFailedLogin($conn, $table, $id, $currentAttempts)
{
    $attempts = ((int)$currentAttempts) + 1;

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {

        $lock = $conn->prepare("
            UPDATE {$table}
            SET failed_login_attempts = ?,
                locked_until = DATE_ADD(NOW(), INTERVAL " . LOCK_MINUTES . " MINUTE)
            WHERE id = ?
        ");

        $lock->execute([$attempts, $id]);

    } else {

        $update = $conn->prepare("
            UPDATE {$table}
            SET failed_login_attempts = ?,
                locked_until = NULL
            WHERE id = ?
        ");

        $update->execute([$attempts, $id]);
    }
}

function resetLoginAttempts($conn, $table, $id)
{
    $reset = $conn->prepare("
        UPDATE {$table}
        SET failed_login_attempts = 0,
            locked_until = NULL
        WHERE id = ?
    ");

    $reset->execute([$id]);
}

function getCompanyByEmail($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM companies
        WHERE email = ?
    ");

    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getStaffByEmail($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE email = ?
    ");

    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function activatePendingStaff($conn, $userId)
{
    $update = $conn->prepare("
        UPDATE users
        SET status = 'active'
        WHERE id = ?
    ");

    return $update->execute([$userId]);
}

