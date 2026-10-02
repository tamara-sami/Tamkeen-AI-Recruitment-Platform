<?php

function initPostTaskPage(PDO $conn): array
{
    $errors = $_SESSION['errors'] ?? [];
    $old = $_SESSION['old'] ?? [];

    unset($_SESSION['errors'], $_SESSION['old']);

    $companyName = "Company";

    if (isset($_SESSION['company_id'])) {
        $stmt = $conn->prepare("SELECT company_name FROM companies WHERE id = ?");
        $stmt->execute([$_SESSION['company_id']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            $companyName = $company['company_name'];
        }
    }
$companyId = (int)($_SESSION['company_id'] ?? 0);

$topStmt = $conn->prepare("
    SELECT
        js.full_name,
        js.job_title,
        ta.ai_score,
        ta.ai_status
    FROM task_attempts ta
    JOIN job_seekers js ON js.id = ta.job_seeker_id
    JOIN tasks t ON t.id = ta.task_id
    WHERE t.company_id = ?
    AND ta.ai_score IS NOT NULL
    ORDER BY ta.ai_score DESC
    LIMIT 3
");

$topStmt->execute([$companyId]);

$topTalents = $topStmt->fetchAll(PDO::FETCH_ASSOC);
   return [
    'errors' => $errors,
    'old' => $old,
    'companyName' => $companyName,
    'topTalents' => $topTalents
];
}

function handlePostTaskSubmit(PDO $conn): void
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $result = createTask($conn, $_POST, $_FILES);

    if ($result['success']) {
        header("Location: /tamkeentest/public/task/my-tasks.php?success=1");
        exit;
    }

    $_SESSION['errors'] = $result['errors'];
    $_SESSION['old'] = $_POST;

    header("Location: /tamkeentest/public/task/post-task.php");
    exit;
}
function createTask($conn, $data, $files)
{
  $errors = [];
$status = $data['status'] ?? 'draft';

if ($status === 'published') {

    $required_fields = [
        'title' => 'Task title is required',
        'description' => 'Task description is required',
        'category' => 'Task category is required',
        'difficulty' => 'Difficulty level is required',
        'estimated_time' => 'Estimated time is required',
        'points' => 'Points are required',
        'required_skills' => 'Required skills are required',
        'minimum_focus_minutes' => 'Minimum focus minutes is required',
        'model_answer' => 'Model answer is required'
    ];

    foreach ($required_fields as $field => $message) {
        if (empty(trim($data[$field] ?? ''))) {
            $errors[$field] = $message;
        }
    }

    if (!empty($data['points']) && (!is_numeric($data['points']) || $data['points'] <= 0)) {
        $errors['points'] = 'Points must be greater than 0';
    }

    if (
        !empty($data['minimum_focus_minutes']) &&
        (
            !is_numeric($data['minimum_focus_minutes']) ||
            (int)$data['minimum_focus_minutes'] < 1
        )
    ) {
        $errors['minimum_focus_minutes'] = 'Minimum focus minutes must be at least 1 minute';
    }

    if (empty($data['deadline'])) {
        $errors['deadline'] = "Deadline is required";
    } else {
        $today = date('Y-m-d');

        if ($data['deadline'] < $today) {
            $errors['deadline'] = "Deadline cannot be in the past";
        }
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

    $uploadDir = __DIR__ . "/../../public/uploads/tasks/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    move_uploaded_file(
        $files['task_file']['tmp_name'],
        $uploadDir . $task_file
    );
}
    if (!empty($files['task_image']['name'])) {
        $task_image = time() . "_" . basename($files['task_image']['name']);
        $uploadDir = __DIR__ . "/../../public/uploads/tasks/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($files['task_image']['tmp_name'], $uploadDir . $task_image);
    }

    $stmt = $conn->prepare("
        INSERT INTO tasks 
        (company_id, created_by, title, description, category, difficulty, estimated_time, minimum_focus_minutes, points, deadline, required_skills, evaluation_criteria, model_answer, task_file, task_image, status)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)    ");

   $success = $stmt->execute([
    $company_id,
    $created_by,
    trim($data['title']),
    trim($data['description']),
    $data['category'],
    $data['difficulty'],
    $data['estimated_time'],
    (int)($data['minimum_focus_minutes'] ?? 10),
    $data['points'],
    !empty($data['deadline']) ? $data['deadline'] : null,
    trim($data['required_skills']),
trim($data['evaluation_criteria'] ?? ''),
    trim($data['model_answer'] ?? ''),
    $task_file,
    $task_image,
    $status
]);

    return [
        'success' => $success,
        'errors' => []
    ];
}
