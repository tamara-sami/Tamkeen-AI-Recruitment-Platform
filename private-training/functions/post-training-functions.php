<?php

if (!function_exists('training_e')) {
    function training_e($value)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function trainingRequireCompany()
{
    if (!isset($_SESSION['company_id'])) {
        header("Location: ../../public/company/login.php");
        exit;
    }

    return (int)$_SESSION['company_id'];
}

function getTrainingCompanyName($conn, $companyId)
{
    $stmt = $conn->prepare("
        SELECT company_name
        FROM companies
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$companyId]);

    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    return $company['company_name'] ?? 'Company';
}

function getPostTrainingOldAndErrors()
{
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];

    unset($_SESSION['errors'], $_SESSION['old']);

    return [$errors, $old];
}

function validateTrainingPost($post)
{
    $errors = [];

    $title = trim($post['title'] ?? '');
    $description = trim($post['description'] ?? '');
    $trainingType = trim($post['training_type'] ?? '');
    $field = trim($post['field'] ?? '');
    $duration = trim($post['duration'] ?? '');
    $startDate = trim($post['start_date'] ?? '');
    $endDate = trim($post['end_date'] ?? '');
    $requirements = trim($post['requirements'] ?? '');
    $seats = trim($post['seats'] ?? '');

    if ($title === '') {
        $errors['title'] = 'Title is required';
    }

    if ($description === '') {
        $errors['description'] = 'Description is required';
    }

    if ($trainingType === '') {
        $errors['training_type'] = 'Training type is required';
    }

    if ($field === '') {
        $errors['field'] = 'Field is required';
    }

    if ($duration === '' || !is_numeric($duration) || (int)$duration <= 0) {
        $errors['duration'] = 'Duration must be greater than 0';
    }

    if ($startDate === '') {
        $errors['start_date'] = 'Start date is required';
    } elseif ($startDate < date('Y-m-d')) {
        $errors['start_date'] = 'Start date cannot be in the past';
    }

    if ($endDate === '') {
        $errors['end_date'] = 'End date is required';
    } elseif ($endDate < date('Y-m-d')) {
        $errors['end_date'] = 'End date cannot be in the past';
    }

    if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
        $errors['end_date'] = 'End date cannot be before start date';
    }

    if ($requirements === '') {
        $errors['requirements'] = 'Requirements are required';
    }

    if ($seats !== '' && (!is_numeric($seats) || (int)$seats <= 0)) {
        $errors['seats'] = 'Seats must be greater than 0';
    }

    return $errors;
}

function createCompanyTraining($conn, $companyId, $post)
{
    $errors = validateTrainingPost($post);

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors,
        ];
    }

    $status = $post['status'] ?? 'draft';

    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    if ($status === 'published') {
        $status = 'pending_approval';
    }

    $createdBy = $_SESSION['user_id'] ?? null;

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
            status,
            deleted,
            created_at
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
    ");

    $success = $stmt->execute([
        $companyId,
        $createdBy,
        trim($post['title']),
        trim($post['description']),
        trim($post['training_type']),
        trim($post['field']),
        trim($post['location'] ?? ''),
        (int)$post['duration'],
        $post['start_date'],
        $post['end_date'],
        trim($post['required_skills'] ?? ''),
        trim($post['requirements']),
        ($post['seats'] ?? '') !== '' ? (int)$post['seats'] : null,
        $status,
    ]);

    return [
        'success' => $success,
        'errors' => [],
    ];
}

function handlePostTrainingSubmit($conn, $companyId)
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $result = createCompanyTraining($conn, $companyId, $_POST);

    if ($result['success']) {
        header("Location: my-trainings.php?success=1");
        exit;
    }

    $_SESSION['errors'] = $result['errors'];
    $_SESSION['old'] = $_POST;

    header("Location: post-training.php");
    exit;
}

function initPostTrainingPage($conn)
{
    $companyId = trainingRequireCompany();

    handlePostTrainingSubmit($conn, $companyId);

    [$errors, $old] = getPostTrainingOldAndErrors();

    return [
        'company_id' => $companyId,
        'company_name' => getTrainingCompanyName($conn, $companyId),
        'errors' => $errors,
        'old' => $old,
        'fields' => [
            'Programming',
            'Design',
            'Marketing',
            'Data Analysis',
            'Customer Service',
            'HR',
        ],
    ];
}
