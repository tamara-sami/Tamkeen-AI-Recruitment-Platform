<?php

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCompanySettingsData($conn, $company_id)
{
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id = ? LIMIT 1");
    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateCompanyProfile($conn, $company_id, $data, $files)
{
    $company_name = trim($data['company_name'] ?? '');
    $responsible_person = trim($data['responsible_person'] ?? '');

    if ($company_name === '' || $responsible_person === '') {
        return ["success" => false, "message" => "Company name and responsible person are required."];
    }

    if (!preg_match("/^[A-Za-z0-9\s&.,'-]{2,100}$/", $company_name)) {
        return ["success" => false, "message" => "Company name contains invalid characters."];
    }

    if (!preg_match("/^[A-Za-z\s'-]{2,80}$/", $responsible_person)) {
        return ["success" => false, "message" => "Responsible person name must contain letters only."];
    }

    $logo_sql = "";
    $params = [$company_name, $responsible_person];

    if (!empty($files['company_logo']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($files['company_logo']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return ["success" => false, "message" => "Company logo must be JPG, PNG, JPEG, or WEBP."];
        }

        if ($files['company_logo']['size'] > 2 * 1024 * 1024) {
            return ["success" => false, "message" => "Company logo must be less than 2MB."];
        }

$upload_dir = "../../public/uploads/company/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $logo_name = "company_" . $company_id . "_" . time() . "." . $ext;
        $target = $upload_dir . $logo_name;

        if (!move_uploaded_file($files['company_logo']['tmp_name'], $target)) {
            return ["success" => false, "message" => "Logo upload failed."];
        }

        $logo_sql = ", company_logo = ?";
        $params[] = $logo_name;
    }

    $params[] = $company_id;

    $stmt = $conn->prepare("
        UPDATE companies
        SET company_name = ?,
            responsible_person = ?
            $logo_sql
        WHERE id = ?
    ");

    if ($stmt->execute($params)) {
        $_SESSION['company_name'] = $company_name;
        return ["success" => true, "message" => "Company profile updated successfully."];
    }

    return ["success" => false, "message" => "Something went wrong while saving changes."];
}

function updateCompanyContactInfo($conn, $company_id, $data)
{
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');

    if ($email === '' || $phone === '') {
        return ["success" => false, "message" => "Email and phone number are required."];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Please enter a valid email address."];
    }

    if (!preg_match('/^(07)(7|8|9)[0-9]{7}$/', $phone)) {

    return [
        "success" => false,
        "message" => "Please enter a valid Jordanian phone number."
    ];
}

    $stmt = $conn->prepare("
        UPDATE companies
        SET email = ?, phone = ?
        WHERE id = ?
    ");

    if ($stmt->execute([$email, $phone, $company_id])) {
        return ["success" => true, "message" => "Contact information updated successfully."];
    }

    return ["success" => false, "message" => "Something went wrong while updating contact information."];
}

function changeCompanyPassword($conn, $company_id, $data)
{
    $current_password = $data['current_password'] ?? '';
    $new_password = $data['new_password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM companies WHERE id = ? LIMIT 1");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company || !password_verify($current_password, $company['password'])) {
        return ["success" => false, "message" => "Current password is incorrect."];
    }

    if ($new_password !== $confirm_password) {
        return ["success" => false, "message" => "Passwords do not match."];
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/', $new_password)) {
        return ["success" => false, "message" => "Password must be at least 8 characters and include letters, numbers, and a special symbol."];
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE companies SET password = ? WHERE id = ?");

    if ($stmt->execute([$hashed, $company_id])) {
        return ["success" => true, "message" => "Password changed successfully."];
    }

    return ["success" => false, "message" => "Something went wrong while changing password."];
}