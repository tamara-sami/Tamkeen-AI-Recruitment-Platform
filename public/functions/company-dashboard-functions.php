<?php

function getCompanyDashboardData(PDO $conn, int $companyId): array
{
    $company = getCompanyInfo($conn, $companyId);

    return [
        'companyName' => $company['company_name'] ?? 'Company',

        'avatarLetter' => strtoupper(
            substr($company['company_name'] ?? 'C', 0, 1)
        ),

        'stats' => [
            'activeTasks' => getCompanyActiveTasksCount($conn, $companyId),

            'hrUsers' => getCompanyUsersByRoleCount(
                $conn,
                $companyId,
                'hr'
            ),

            'taskManagers' => getCompanyUsersByRoleCount(
                $conn,
                $companyId,
                'task_manager'
            ),

            'trainings' => getCompanyUsersByRoleCount(
                $conn,
                $companyId,
                'training'
            ),

            'submissions' => getCompanySubmissionsCount(
                $conn,
                $companyId
            ),
        ],

        'recentActivities' => getCompanyRecentActivities(
            $conn,
            $companyId
        )
    ];
}

function getCompanyInfo(PDO $conn, int $companyId): array
{
    $stmt = $conn->prepare("
        SELECT company_name
        FROM companies
        WHERE id = ?
    ");

    $stmt->execute([$companyId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getCompanyActiveTasksCount(PDO $conn, int $companyId): int
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM tasks
        WHERE company_id = ?
    ");

    $stmt->execute([$companyId]);

    return (int)$stmt->fetchColumn();
}

function getCompanyUsersByRoleCount(
    PDO $conn,
    int $companyId,
    string $role
): int {

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE company_id = ?
        AND role = ?
    ");

    $stmt->execute([$companyId, $role]);

    return (int)$stmt->fetchColumn();
}

function getCompanySubmissionsCount(PDO $conn, int $companyId): int
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM task_attempts ta

        JOIN tasks t
        ON t.id = ta.task_id

        WHERE t.company_id = ?
        AND ta.status = 'submitted'
    ");

    $stmt->execute([$companyId]);

    return (int)$stmt->fetchColumn();
}

function getCompanyRecentActivities(
    PDO $conn,
    int $companyId
): array {

    $stmt = $conn->prepare("

        (
            SELECT
                'fa fa-user-tie' AS icon,

                CONCAT(
                    full_name,
                    ' was added as ',
                    role
                ) AS title,

                email AS description,

                created_at

            FROM users

            WHERE company_id = ?
        )

        UNION ALL

        (
            SELECT
                'fa fa-tasks' AS icon,

                CONCAT(
                    'Task created: ',
                    title
                ) AS title,

                'Task Added' AS description,

                created_at

            FROM tasks

            WHERE company_id = ?
        )

        UNION ALL

        (
            SELECT
                'fa fa-paper-plane' AS icon,

                CONCAT(
                    'Submission received for ',
                    t.title
                ) AS title,

                CONCAT(
                    'Status: ',
                    ta.status
                ) AS description,

                ta.created_at AS created_at

            FROM task_attempts ta

            JOIN tasks t
            ON t.id = ta.task_id

            WHERE t.company_id = ?
            AND ta.status = 'submitted'
        )

        UNION ALL

        (
            SELECT
                'fa fa-user-graduate' AS icon,

                CONCAT(
                    'Training created: ',
                    title
                ) AS title,

                'Training Added' AS description,

                created_at

            FROM trainings

            WHERE company_id = ?
        )

        ORDER BY created_at DESC

        LIMIT 5
    ");

    $stmt->execute([
        $companyId,
        $companyId,
        $companyId,
        $companyId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function activityDateText($date): string
{
    if (empty($date)) {
        return "—";
    }

    return date(
        "M d, Y",
        strtotime($date)
    );
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