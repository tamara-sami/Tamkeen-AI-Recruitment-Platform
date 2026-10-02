<?php

function getActivityLogData($conn, $company_id)
{
    return [
        "stats" => getActivityStats($conn, $company_id),
        "activities" => getAllActivities($conn, $company_id)
    ];
}

function getActivityStats($conn, $company_id)
{
    return [
        "totalEvents" => countTotalEvents($conn, $company_id),
        "hrActions" => countHrActions($conn, $company_id),
        "taskActions" => countTaskActions($conn, $company_id),
        "aiEvents" => countAiEvents($conn, $company_id),
        "userChanges" => countUserChanges($conn, $company_id),
        "tickets" => countTickets($conn, $company_id)
    ];
}

function countHrActions($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM hiring_requests
        WHERE company_id = ?
    ");
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countTaskActions($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM tasks
        WHERE company_id = ?
    ");
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countAiEvents($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.ai_score IS NOT NULL
        AND ta.ai_status IS NOT NULL
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function countUserChanges($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE company_id = ?
    ");
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countTickets($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM hiring_requests
        WHERE company_id = ?
        AND status = 'pending'
    ");
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countTotalEvents($conn, $company_id)
{
    return
        countHrActions($conn, $company_id)
        + countTaskActions($conn, $company_id)
        + countAiEvents($conn, $company_id)
        + countUserChanges($conn, $company_id);
}

function getAllActivities($conn, $company_id)
{
    $stmt = $conn->prepare("
        (
            SELECT
                'blue' AS color,
                'fa fa-user-plus' AS icon,
                'Company user added' AS title,
                CONCAT(u.full_name, ' joined as ', u.role) AS description,
                u.created_at,
                'Users' AS type
            FROM users u
            WHERE u.company_id = ?
        )

        UNION ALL

        (
            SELECT
                'green' AS color,
                'fa fa-star' AS icon,
                'Hiring request created' AS title,
                CONCAT(js.full_name, ' hiring request was submitted.') AS description,
                hr.created_at,
                'HR Action' AS type
            FROM hiring_requests hr
            JOIN job_seekers js ON js.id = hr.job_seeker_id
            WHERE hr.company_id = ?
        )

        UNION ALL

        (
            SELECT
                'orange' AS color,
                'fa fa-tasks' AS icon,
                'Task created' AS title,
                CONCAT(t.title, ' was created.') AS description,
                t.created_at,
                'Task Action' AS type
            FROM tasks t
            WHERE t.company_id = ?
        )

        UNION ALL

        (
            SELECT
                'purple' AS color,
                'fa fa-robot' AS icon,
                'AI ranking updated' AS title,
                CONCAT(js.full_name, ' completed a task submission.') AS description,
                ta.submitted_at AS created_at,
                'AI Action' AS type
            FROM task_attempts ta
            JOIN tasks t ON t.id = ta.task_id
            JOIN job_seekers js ON js.id = ta.job_seeker_id
            WHERE t.company_id = ?
            AND ta.status = 'submitted'
        )

        ORDER BY created_at DESC
        LIMIT 15
    ");

    $stmt->execute([
        $company_id,
        $company_id,
        $company_id,
        $company_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function activityDate($date)
{
    return empty($date)
        ? "—"
        : date("M d, Y h:i A", strtotime($date));
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