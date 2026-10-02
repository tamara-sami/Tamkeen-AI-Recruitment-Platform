<?php

function getAdminTasksData($conn, $company_id)
{
    return [
        "stats" => getAdminTasksStats($conn, $company_id),
        "tasks" => getCompanyTasks($conn, $company_id),
        "pendingTasks" => getPendingApprovalTasks($conn, $company_id),
        "activities" => getTaskActivities($conn, $company_id)
    ];
}

function getAdminTasksStats($conn, $company_id)
{
    return [
        "totalTasks" => countTasksByStatus($conn, $company_id),
        "activeTasks" => countTasksByStatus($conn, $company_id, "published"),
        "draftTasks" => countTasksByStatus($conn, $company_id, "draft"),
        "closedTasks" => countTasksByStatus($conn, $company_id, "closed"),
        "pendingTickets" => countTasksByStatus($conn, $company_id, "pending_approval"),
        "submissions" => countTaskSubmissions($conn, $company_id)
    ];
}

function countTasksByStatus($conn, $company_id, $status = null)
{
    if ($status) {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM tasks
            WHERE company_id = ?
            AND status = ?
        ");
        $stmt->execute([$company_id, $status]);
    } else {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM tasks
            WHERE company_id = ?
        ");
        $stmt->execute([$company_id]);
    }

    return (int)$stmt->fetchColumn();
}

function countTaskSubmissions($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function getCompanyTasks($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            u.full_name AS creator_name,
            COUNT(ta.id) AS submissions_count
        FROM tasks t
        LEFT JOIN users u ON u.id = t.created_by
        LEFT JOIN task_attempts ta 
            ON ta.task_id = t.id 
            AND ta.status = 'submitted'
        WHERE t.company_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
        LIMIT 10
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPendingApprovalTasks($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            u.full_name AS creator_name
        FROM tasks t
        LEFT JOIN users u ON u.id = t.created_by
        WHERE t.company_id = ?
        AND t.status = 'pending_approval'
        ORDER BY t.created_at DESC
        LIMIT 3
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateTaskApprovalStatus($conn, $task_id, $company_id, $action)
{
    if ($action === "approved") {
        $newStatus = "published";
    } elseif ($action === "rejected") {
        $newStatus = "rejected";
    } else {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE tasks
        SET status = ?
        WHERE id = ?
        AND company_id = ?
        AND status = 'pending_approval'
    ");

    return $stmt->execute([
        $newStatus,
        $task_id,
        $company_id
    ]);
}

function getTaskActivities($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT
            'fa fa-plus' AS icon,
            CONCAT('Task created: ', title) AS title,
            CONCAT('Status: ', status) AS description,
            created_at
        FROM tasks
        WHERE company_id = ?
        ORDER BY created_at DESC
        LIMIT 3
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function taskStatusLabel($status)
{
    return [
        "draft" => "Draft",
        "pending_approval" => "Pending Approval",
        "published" => "Published",
        "closed" => "Closed",
        "rejected" => "Rejected"
    ][$status] ?? ucfirst($status);
}

function taskDate($date)
{
    return empty($date) ? "—" : date("M d, Y", strtotime($date));
}

function createTask($conn, $data, $files)
{
    $errors = [];

    $status = $data['status'] ?? 'draft';

    if ($status === 'published') {
        $status = 'pending_approval';
    }

    if ($status === 'pending_approval') {
        $required_fields = [
            'title' => 'Task title is required',
            'description' => 'Task description is required',
            'category' => 'Task category is required',
            'difficulty' => 'Difficulty level is required',
            'estimated_time' => 'Estimated time is required',
            'points' => 'Points are required',
            'required_skills' => 'Required skills are required'
        ];

        foreach ($required_fields as $field => $message) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[$field] = $message;
            }
        }

        if (!empty($data['points']) && (!is_numeric($data['points']) || $data['points'] <= 0)) {
            $errors['points'] = 'Points must be greater than 0';
        }

        if (empty($data['deadline'])) {
            $errors['deadline'] = "Deadline is required";
        } elseif ($data['deadline'] < date('Y-m-d')) {
            $errors['deadline'] = "Deadline cannot be in the past";
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $company_id = $_SESSION['company_id'];
    $created_by = $_SESSION['user_id'] ?? null;

    $task_file = null;
    $task_image = null;

    if (!empty($files['task_file']['name'])) {
        $allowed_files = [
            'pdf', 'doc', 'docx',
            'xls', 'xlsx',
            'ppt', 'pptx',
            'zip', 'rar',
            'txt', 'csv'
        ];

        $ext = strtolower(pathinfo($files['task_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_files)) {
            return [
                'success' => false,
                'errors' => [
                    'task_file' => 'Invalid file type'
                ]
            ];
        }

        $task_file = time() . "_" . basename($files['task_file']['name']);

        move_uploaded_file(
            $files['task_file']['tmp_name'],
            "../../uploads/tasks/" . $task_file
        );
    }

    if (!empty($files['task_image']['name'])) {
        $task_image = time() . "_" . basename($files['task_image']['name']);

        move_uploaded_file(
            $files['task_image']['tmp_name'],
            "../../uploads/tasks/" . $task_image
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO tasks
        (
            company_id,
            created_by,
            title,
            description,
            category,
            difficulty,
            estimated_time,
            points,
            deadline,
            required_skills,
            task_file,
            task_image,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $company_id,
        $created_by,
        trim($data['title'] ?? ''),
        trim($data['description'] ?? ''),
        $data['category'] ?? '',
        $data['difficulty'] ?? '',
        $data['estimated_time'] ?? '',
        $data['points'] ?? 0,
        !empty($data['deadline']) ? $data['deadline'] : null,
        trim($data['required_skills'] ?? ''),
        $task_file,
        $task_image,
        $status
    ]);

    return [
        'success' => $success,
        'errors' => []
    ];
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