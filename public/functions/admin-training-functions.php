<?php

function getAdminTrainingData($conn, $company_id)
{
    return [
        "stats" => getAdminTrainingStats($conn, $company_id),
        "trainings" => getCompanyTrainings($conn, $company_id),
        "pendingTrainings" => getPendingApprovalTrainings($conn, $company_id),
        "activities" => getTrainingActivities($conn, $company_id)
    ];
}

function getAdminTrainingStats($conn, $company_id)
{
    return [
        "trainingManagers" => countTrainingManagers($conn, $company_id),
        "totalPrograms" => countTrainingsByStatus($conn, $company_id),
        "activePrograms" => countTrainingsByStatus($conn, $company_id, "published"),
        "applications" => countTrainingApplications($conn, $company_id),
        "acceptedTrainees" => countAcceptedTrainees($conn, $company_id),
        "pendingTickets" => countTrainingsByStatus($conn, $company_id, "pending_approval")
    ];
}

function countTrainingManagers($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE company_id = ?
        AND role = 'training_manager'
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function countTrainingsByStatus($conn, $company_id, $status = null)
{
    if ($status) {
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM trainings
            WHERE company_id = ?
            AND status = ?
        ");

        $stmt->execute([$company_id, $status]);

        return (int)$stmt->fetchColumn();
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM trainings
        WHERE company_id = ?
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function countTrainingApplications($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        WHERE t.company_id = ?
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function countAcceptedTrainees($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM training_applications ta
        JOIN trainings t ON t.id = ta.training_id
        WHERE t.company_id = ?
        AND ta.status = 'accepted'
    ");

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function getCompanyTrainings($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT
            t.*,
            u.full_name AS creator_name,
            COUNT(ta.id) AS applications_count
        FROM trainings t
        LEFT JOIN users u ON u.id = t.created_by
        LEFT JOIN training_applications ta ON ta.training_id = t.id
        WHERE t.company_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
        LIMIT 10
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPendingApprovalTrainings($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT
            t.*,
            u.full_name AS creator_name
        FROM trainings t
        LEFT JOIN users u ON u.id = t.created_by
        WHERE t.company_id = ?
        AND t.status = 'pending_approval'
        ORDER BY t.created_at DESC
        LIMIT 3
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateTrainingApprovalStatus($conn, $training_id, $company_id, $action)
{
    if ($action === "approved") {
        $newStatus = "published";
    } elseif ($action === "rejected") {
        $newStatus = "rejected";
    } else {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE trainings
        SET status = ?
        WHERE id = ?
        AND company_id = ?
        AND status = 'pending_approval'
    ");

    return $stmt->execute([
        $newStatus,
        $training_id,
        $company_id
    ]);
}

function getTrainingActivities($conn, $company_id)
{
    $stmt = $conn->prepare("
        SELECT
            'fa fa-plus' AS icon,
            CONCAT('Training created: ', title) AS title,
            CONCAT('Status: ', status) AS description,
            created_at
        FROM trainings
        WHERE company_id = ?
        ORDER BY created_at DESC
        LIMIT 3
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function trainingStatusLabel($status)
{
    return [
        "draft" => "Draft",
        "pending_approval" => "Pending Approval",
        "published" => "Published",
        "closed" => "Closed",
        "rejected" => "Rejected"
    ][$status] ?? ucfirst($status);
}

function trainingDate($date)
{
    return empty($date) ? "—" : date("M d, Y", strtotime($date));
}

function createTraining($conn, $data)
{
    $errors = [];

    $status = $data['status'] ?? 'draft';

    if ($status === 'published') {
        $status = 'pending_approval';
    }

    if ($status === 'pending_approval') {
        if (empty(trim($data['title'] ?? ''))) {
            $errors['title'] = 'Training title is required';
        }

        if (empty(trim($data['description'] ?? ''))) {
            $errors['description'] = 'Training description is required';
        }

        if (empty(trim($data['training_type'] ?? ''))) {
            $errors['training_type'] = 'Training type is required';
        }

        if (empty(trim($data['field'] ?? ''))) {
            $errors['field'] = 'Training field is required';
        }

        if (empty($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors['duration'] = 'Duration must be greater than 0';
        }

        if (empty($data['start_date'])) {
            $errors['start_date'] = 'Start date is required';
        } elseif ($data['start_date'] < date('Y-m-d')) {
            $errors['start_date'] = 'Start date cannot be in the past';
        }

        if (empty($data['end_date'])) {
            $errors['end_date'] = 'End date is required';
        } elseif ($data['end_date'] < date('Y-m-d')) {
            $errors['end_date'] = 'End date cannot be in the past';
        }

        if (!empty($data['start_date']) && !empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $errors['end_date'] = 'End date cannot be before start date';
        }

        if (empty(trim($data['requirements'] ?? ''))) {
            $errors['requirements'] = 'Requirements are required';
        }

        if (!empty($data['seats']) && (!is_numeric($data['seats']) || $data['seats'] <= 0)) {
            $errors['seats'] = 'Seats must be greater than 0';
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

    $stmt = $conn->prepare("
        INSERT INTO trainings
        (
            company_id,
            created_by,
            title,
            description,
            training_type,
            field,
            location,
            duration,
            start_date,
            end_date,
            required_skills,
            requirements,
            seats,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $company_id,
        $created_by,
        trim($data['title'] ?? ''),
        trim($data['description'] ?? ''),
        $data['training_type'] ?? '',
        trim($data['field'] ?? ''),
        trim($data['location'] ?? ''),
        trim($data['duration'] ?? ''),
        !empty($data['start_date']) ? $data['start_date'] : null,
        !empty($data['end_date']) ? $data['end_date'] : null,
        trim($data['required_skills'] ?? ''),
        trim($data['requirements'] ?? ''),
        !empty($data['seats']) ? $data['seats'] : null,
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