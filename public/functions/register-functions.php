<?php

function validateCompanyRegister($conn, $data)
{
    $errors = [
        "generalError" => "",
        "emailError" => "",
        "passwordError" => ""
    ];

    $required = [
        "company_name",
        "responsible_person",
        "phone",
        "country",
        "location",
        "company_type",
        "industry",
        "company_size",
        "email",
        "password"
    ];

    foreach ($required as $field) {
        if (empty(trim($data[$field] ?? ""))) {
            $errors["generalError"] = "Please fill in all required fields.";
            return $errors;
        }
    }

    if (!preg_match("/^[A-Za-z0-9\s&.,'-]{2,100}$/", trim($data["company_name"]))) {
        $errors["generalError"] = "Company name contains invalid characters.";
        return $errors;
    }

    if (!preg_match("/^[A-Za-z\s'-]{2,80}$/", trim($data["responsible_person"]))) {
        $errors["generalError"] = "Responsible person name must contain letters only.";
        return $errors;
    }

    if (!preg_match("/^07[789][0-9]{7}$/", trim($data["phone"]))) {
        $errors["generalError"] = "Enter a valid Jordanian phone number, like 0791234567.";
        return $errors;
    }

    $allowedCountries = ["Jordan", "Saudi Arabia", "United Arab Emirates", "Qatar"];
    $allowedLocations = ["Amman", "Irbid", "Zarqa", "Aqaba"];
    $allowedTypes = ["Public Company", "Private Company", "Startup", "NGO"];
    $allowedIndustries = ["Accounting", "IT", "Education", "Healthcare", "Marketing"];
    $allowedSizes = ["Myself only", "2 - 10 Employees", "11 - 50 Employees", "51 - 200 Employees", "200+ Employees"];

    if (
        !in_array($data["country"], $allowedCountries) ||
        !in_array($data["location"], $allowedLocations) ||
        !in_array($data["company_type"], $allowedTypes) ||
        !in_array($data["industry"], $allowedIndustries) ||
        !in_array($data["company_size"], $allowedSizes)
    ) {
        $errors["generalError"] = "Please select valid options.";
        return $errors;
    }

    if (!filter_var(trim($data["email"]), FILTER_VALIDATE_EMAIL)) {
        $errors["emailError"] = "Enter a valid email address.";
        return $errors;
    }

    $check = $conn->prepare("SELECT id FROM companies WHERE email = ? LIMIT 1");
    $check->execute([trim($data["email"])]);

    if ($check->fetch()) {
        $errors["emailError"] = "This email is already registered.";
        return $errors;
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/', $data["password"])) {
        $errors["passwordError"] = "Password must be 8+ characters with a letter, number, and symbol.";
        return $errors;
    }

    return $errors;
}

function registerCompany($conn, $data)
{
    $hashed_password = password_hash($data["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO companies
        (
            company_name,
            responsible_person,
            phone,
            country,
            location,
            company_type,
            industry,
            company_size,
            email,
            password
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        trim($data["company_name"]),
        trim($data["responsible_person"]),
        trim($data["phone"]),
        trim($data["country"]),
        trim($data["location"]),
        trim($data["company_type"]),
        trim($data["industry"]),
        trim($data["company_size"]),
        trim($data["email"]),
        $hashed_password
    ]);
}
function loginCompanyAfterRegister($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM companies
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([trim($email)]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}