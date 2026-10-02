<?php

function initArchivedTasksPage(PDO $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $companyId = (int)($_SESSION['company_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT *
        FROM tasks
        WHERE company_id = ?
        AND deleted = 1
        ORDER BY created_at DESC
    ");

    $stmt->execute([$companyId]);

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $companyName = "Company";

    if ($companyId > 0) {
        $stmt = $conn->prepare("
            SELECT company_name
            FROM companies
            WHERE id = ?
        ");

        $stmt->execute([$companyId]);

        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            $companyName = $company['company_name'];
        }
    }

    return [
        'tasks' => $tasks,
        'companyName' => $companyName
    ];
}
