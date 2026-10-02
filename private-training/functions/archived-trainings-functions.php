<?php

function training_e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function initArchivedTrainingsPage($conn)
{
    if (!isset($_SESSION['company_id'])) {
        header("Location: ../../public/company/login.php");
        exit;
    }

    $company_id = (int)$_SESSION['company_id'];

    $company = getArchivedTrainingsCompany($conn, $company_id);
    $trainings = getArchivedTrainings($conn, $company_id);

    return [
        'company_id' => $company_id,
        'company_name' => $company['company_name'] ?? 'Company',
        'trainings' => $trainings,
        'archived_count' => count($trainings)
    ];
}

function getArchivedTrainingsCompany($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT id, company_name
        FROM companies
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$company_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getArchivedTrainings($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT
            id,
            title,
            field,
            training_type,
            duration,
            end_date,
            status,
            deleted,
            created_at
        FROM trainings
        WHERE company_id = ?
        AND deleted = 1
        ORDER BY created_at DESC
    ");

    $stmt->execute([$company_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
