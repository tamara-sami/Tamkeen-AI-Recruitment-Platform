<?php

function initUserTasksPage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user_id = $_SESSION['job_seeker_id'];

    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? 'All');
    $difficulty = trim($_GET['difficulty'] ?? 'All');

    $user = getJobSeekerBasic($conn, $user_id);

    $tasks = getPublishedTasksSmart($conn, [
        'search' => $search,
        'category' => $category,
        'difficulty' => $difficulty
    ]);

    return [
        'user_id' => $user_id,
        'search' => $search,
        'category' => $category,
        'difficulty' => $difficulty,

        'user' => $user,

        'tasks' => $tasks,

        'categories' => getTaskCategoriesSmart($conn),

        'stats' => getUserTasksStats($conn, $user_id),
        'suggestedTask' => getSuggestedTask($conn)
    ];
}

function getJobSeekerBasic($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT 
            js.full_name,
            js.job_title,
            js.profile_image,
            jsp.location,
            jsp.residence_country
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp 
            ON jsp.job_seeker_id = js.id
        WHERE js.id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPublishedTasksSmart($conn, $filters = [])
{
    $where = [
    "t.status = 'published'",
    "t.deleted = 0",
        "(t.deadline IS NULL OR t.deadline >= CURDATE())"
    ];

    $params = [];

    if (
        !empty($filters['category']) &&
        $filters['category'] !== 'All'
    ) {
        $where[] = "t.category = ?";
        $params[] = $filters['category'];
    }

    if (
        !empty($filters['difficulty']) &&
        $filters['difficulty'] !== 'All'
    ) {
        $where[] = "t.difficulty = ?";
        $params[] = $filters['difficulty'];
    }

    if (!empty($filters['search'])) {

        $term = "%" . trim($filters['search']) . "%";

        $where[] = "(
            t.title LIKE ?
            OR t.description LIKE ?
            OR t.category LIKE ?
            OR t.difficulty LIKE ?
            OR t.required_skills LIKE ?
            OR t.estimated_time LIKE ?
            OR c.company_name LIKE ?
        )";

        array_push(
            $params,
            $term,
            $term,
            $term,
            $term,
            $term,
            $term,
            $term
        );
    }

    $sql = "
        SELECT 
            t.*,
            c.company_name,
            c.company_logo
        FROM tasks t
        LEFT JOIN companies c 
            ON c.id = t.company_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY 
            CASE WHEN t.deadline IS NULL THEN 1 ELSE 0 END,
            t.deadline ASC,
            t.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTaskCategoriesSmart($conn)
{
    $stmt = $conn->prepare("
        SELECT 
            category,
            COUNT(*) AS total
        FROM tasks
        WHERE status = 'published'
        AND deleted = 0
        GROUP BY category
        ORDER BY total DESC
    ");

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserTasksStats($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT t.id) AS total_tasks,

            COUNT(DISTINCT CASE
                WHEN ta.status = 'submitted'
                THEN t.id
            END) AS completed_tasks,

            COALESCE(SUM(CASE
                WHEN ta.status = 'submitted'
                THEN t.points
                ELSE 0
            END), 0) AS earned_points,

            COALESCE(SUM(t.points), 0) AS total_points,

            SUM(t.difficulty = 'Beginner') AS beginner_count,
            SUM(t.difficulty = 'Intermediate') AS intermediate_count,
            SUM(t.difficulty = 'Advanced') AS advanced_count

        FROM tasks t

        LEFT JOIN task_attempts ta
            ON ta.task_id = t.id
            AND ta.job_seeker_id = ?

        WHERE t.status = 'published'
        AND t.deleted = 0
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getSuggestedTask($conn)
{
    $stmt = $conn->prepare("
        SELECT 
            t.*,
            c.company_name,
            c.company_logo

        FROM tasks t

        LEFT JOIN companies c
            ON c.id = t.company_id

        WHERE t.status = 'published'
        AND t.deleted = 0

        ORDER BY 
            t.points DESC,
            t.created_at DESC

        LIMIT 1
    ");

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTaskAttempt($conn, $task_id, $user_id)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM task_attempts
        WHERE task_id = ?
        AND job_seeker_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([
        $task_id,
        $user_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function taskDeadlineText($deadline)
{
    if (empty($deadline)) {
        return "No deadline";
    }

    $today = date('Y-m-d');

    if ($deadline < $today) {
        return "Expired";
    }

    if ($deadline === $today) {
        return "Today";
    }

    $todayObj = new DateTime($today);
    $endObj = new DateTime($deadline);

    $diff = $todayObj->diff($endObj);

    return $diff->days . " days left";
}

/* =========================================================
   TASK WORKSPACE FUNCTIONS
   Merged from task-workspace-functions.php
   ========================================================= */

function initTaskWorkspacePage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user_id = (int)$_SESSION['job_seeker_id'];
    $task_id = (int)($_GET['id'] ?? 0);

    $task = getTaskByIdForUser($conn, $task_id);

    if (!$task) {
        header("Location: tasks.php?not_found=1");
        exit;
    }

    $existingAttempt = getTaskAttempt($conn, $task_id, $user_id);

if ($existingAttempt) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $existingAttempt['status'] === 'in_progress') {
        $attempt = $existingAttempt;
    } else {

        if ($existingAttempt['status'] === 'in_progress') {
            lockReopenedTaskAttempt($conn, (int)$existingAttempt['id'], $user_id);
            header("Location: tasks.php?attempt_locked=1");
            exit;
        }

        if ($existingAttempt['status'] === 'submitted') {
            header("Location: tasks.php?already_submitted=1");
            exit;
        }

        if ($existingAttempt['status'] === 'disqualified') {
            header("Location: tasks.php?blocked=1");
            exit;
        }

        header("Location: tasks.php?unavailable=1");
        exit;
    }

} else {

    $attempt = createTaskAttempt($conn, $task, $user_id);

    if (!$attempt) {
        header("Location: tasks.php?error=1");
        exit;
    }
}
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answer_text = $_POST['answer_text'] ?? '';

        $result = submitTaskAttempt(
            $conn,
            (int)$attempt['id'],
            $user_id,
            $answer_text,
            $_FILES['answer_file'] ?? null
        );

        if ($result['success']) {
            header("Location: tasks.php?submitted=1");
            exit;
        }

        $error = $result['message'];
        $attempt = getTaskAttempt($conn, $task_id, $user_id);
    }

    $remainingSeconds = getAttemptRemainingSeconds($conn, (int)$attempt['id']);
    $totalRemainingSeconds = getTaskTotalRemainingSeconds($conn, (int)$attempt['id']);

    if ($totalRemainingSeconds <= 0 && $attempt['status'] === 'in_progress') {
        autoSubmitTaskAttempt($conn, (int)$attempt['id'], $user_id, '');
        header("Location: tasks.php?auto_submitted=1");
        exit;
    }

    return [
        'user_id' => $user_id,
        'task_id' => $task_id,
        'task' => $task,
        'attempt' => $attempt,
        'error' => $error,
        'remainingSeconds' => $remainingSeconds,
        'totalRemainingSeconds' => $totalRemainingSeconds
    ];
}

function getTaskByIdForUser($conn, $task_id)
{
    $stmt = $conn->prepare("
        SELECT t.*, c.company_name, c.company_logo
        FROM tasks t
        LEFT JOIN companies c ON c.id = t.company_id
        WHERE t.id = ?
        AND t.status = 'published'
        AND t.deleted = 0
        LIMIT 1
    ");

    $stmt->execute([$task_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function createTaskAttempt($conn, $task, $user_id)
{
    if (!$task || empty($task['id'])) {
        return false;
    }

    $task_id = (int)$task['id'];

    if (getTaskAttempt($conn, $task_id, $user_id)) {
        return false;
    }

    $minimum = !empty($task['minimum_focus_minutes'])
        ? (int)$task['minimum_focus_minutes']
        : 2;

    if ($minimum < 2) {
        $minimum = 2;
    }

    $stmt = $conn->prepare("
        INSERT INTO task_attempts
        (task_id, job_seeker_id, started_at, minimum_minutes, status)
        VALUES (?, ?, NOW(), ?, 'in_progress')
    ");

    $stmt->execute([$task_id, $user_id, $minimum]);

    return getTaskAttempt($conn, $task_id, $user_id);
}

/*
 * Kept as a safe wrapper in case another file still calls startTaskAttempt().
 * It no longer returns old in-progress attempts.
 */
function startTaskAttempt($conn, $task_id, $user_id)
{
    $task = getTaskByIdForUser($conn, $task_id);
    return createTaskAttempt($conn, $task, $user_id);
}

function lockReopenedTaskAttempt($conn, $attempt_id, $user_id)
{
    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET status = 'disqualified',
            disqualified_at = NOW(),
            ended_at = NOW(),
            integrity_risk = 'high',
            integrity_note = 'User left the task workspace and attempted to reopen it.'
        WHERE id = ?
        AND job_seeker_id = ?
        AND status = 'in_progress'
    ");

    return $stmt->execute([$attempt_id, $user_id]);
}

function getAttemptRemainingSeconds($conn, $attempt_id)
{
    $stmt = $conn->prepare("
        SELECT 
            GREATEST(
                0,
                TIMESTAMPDIFF(
                    SECOND,
                    NOW(),
                    DATE_ADD(started_at, INTERVAL minimum_minutes MINUTE)
                )
            ) AS remaining_seconds
        FROM task_attempts
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id]);
    return (int)$stmt->fetchColumn();
}

function canSubmitTaskAttempt($conn, $attempt)
{
    return getAttemptRemainingSeconds($conn, $attempt['id']) <= 0
        && $attempt['status'] === 'in_progress';
}

function saveDrawingImageFromPost($attempt_id)
{
    if (empty($_POST['drawing_image'])) {
        return null;
    }

    $drawingData = $_POST['drawing_image'];

    if (!str_starts_with($drawingData, 'data:image/png;base64,')) {
        return null;
    }

    $drawingData = str_replace('data:image/png;base64,', '', $drawingData);
    $drawingData = str_replace(' ', '+', $drawingData);

    $drawingBinary = base64_decode($drawingData, true);

    if ($drawingBinary === false) {
        return null;
    }

    $dir = __DIR__ . "/../../uploads/task_submissions/";

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $drawingName = "drawing_" . (int)$attempt_id . "_" . time() . "_" . rand(1000, 9999) . ".png";
    $drawingPath = $dir . $drawingName;

    if (file_put_contents($drawingPath, $drawingBinary) === false) {
        return null;
    }

    return "uploads/task_submissions/" . $drawingName;
}

function submitTaskAttempt($conn, $attempt_id, $user_id, $answer_text, $file = null)
{
    $stmt = $conn->prepare("
        SELECT *
        FROM task_attempts
        WHERE id = ?
        AND job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        return ['success' => false, 'message' => 'Attempt not found'];
    }

    if ($attempt['status'] === 'submitted') {
        return ['success' => false, 'message' => 'Task already submitted'];
    }

    if ($attempt['status'] === 'disqualified') {
        return ['success' => false, 'message' => 'You are disqualified from this task'];
    }

    if (!canSubmitTaskAttempt($conn, $attempt)) {
        return ['success' => false, 'message' => 'Minimum focus time is not finished yet'];
    }

    $answer_file = null;

    if ($file && !empty($file['name'])) {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls', 'zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        $dir = __DIR__ . "/../../uploads/task_submissions/";

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = "submission_" . $attempt_id . "_" . time() . "." . $ext;
        $target = $dir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $answer_file = "uploads/task_submissions/" . $fileName;
        }
    }

    $drawing_file = saveDrawingImageFromPost($attempt_id);

    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET answer_text = ?,
            answer_file = ?,
            drawing_file = ?,
            submitted_at = NOW(),
            ended_at = NOW(),
            status = 'submitted'
        WHERE id = ?
        AND job_seeker_id = ?
    ");

    $success = $stmt->execute([
        trim($answer_text),
        $answer_file,
        $drawing_file,
        $attempt_id,
        $user_id
    ]);
   if ($success) {

    $aiUrl = "http://localhost/tamkeentest/private-task/ai-evaluate-attempt.php?attempt_id=" . $attempt_id;

    if (function_exists('curl_init')) {
        $ch = curl_init($aiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        curl_exec($ch);
        curl_close($ch);
    } else {
        @file_get_contents($aiUrl);
    }

    calculateAttemptRisk($conn, $attempt_id);

    require_once __DIR__ . "/../proctoring/proctoring-functions.php";
    generateProctoringReview($conn, $attempt_id);
}

    return [
        'success' => $success,
        'message' => $success ? 'Submitted successfully' : 'Submit failed'
    ];
}

function taskEstimatedSeconds($estimated_time)
{
    $text = strtolower(trim($estimated_time ?? ''));

    if (preg_match('/(\d+)\s*hour/', $text, $m)) {
        return (int)$m[1] * 3600;
    }

    if (preg_match('/(\d+)\s*minute/', $text, $m)) {
        return (int)$m[1] * 60;
    }

    return 7200;
}

function getTaskTotalRemainingSeconds($conn, $attempt_id)
{
    $stmt = $conn->prepare("
        SELECT 
            ta.started_at,
            t.estimated_time
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE ta.id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return 0;
    }

    $totalSeconds = taskEstimatedSeconds($row['estimated_time']);

    $stmt = $conn->prepare("
        SELECT GREATEST(
            0,
            TIMESTAMPDIFF(
                SECOND,
                NOW(),
                DATE_ADD(started_at, INTERVAL ? SECOND)
            )
        )
        FROM task_attempts
        WHERE id = ?
    ");

    $stmt->execute([$totalSeconds, $attempt_id]);
    return (int)$stmt->fetchColumn();
}

function autosaveTaskAttempt($conn, $attempt_id, $user_id, $text)
{
    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET autosave_text = ?,
            autosave_updated_at = NOW()
        WHERE id = ?
        AND job_seeker_id = ?
        AND status = 'in_progress'
    ");

    return $stmt->execute([
        trim($text),
        $attempt_id,
        $user_id
    ]);
}

function autoSubmitTaskAttempt($conn, $attempt_id, $user_id, $answer_text = '')
{
    $stmt = $conn->prepare("
        SELECT *
        FROM task_attempts
        WHERE id = ?
        AND job_seeker_id = ?
        AND status = 'in_progress'
        LIMIT 1
    ");

    $stmt->execute([$attempt_id, $user_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        return false;
    }

    $finalText = trim($answer_text);

    if ($finalText === '' && !empty($attempt['autosave_text'])) {
        $finalText = $attempt['autosave_text'];
    }

    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET answer_text = ?,
            submitted_at = NOW(),
            ended_at = NOW(),
            auto_submitted = 1,
            status = 'submitted'
        WHERE id = ?
        AND job_seeker_id = ?
    ");

    $success = $stmt->execute([
        $finalText,
        $attempt_id,
        $user_id
    ]);

    if ($success) {
        $aiUrl = "http://localhost/tamkeentest/private-task/ai-evaluate-attempt.php?attempt_id=" . $attempt_id;

        if (function_exists('curl_init')) {
            $ch = curl_init($aiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);
            curl_exec($ch);
            curl_close($ch);
        } else {
            @file_get_contents($aiUrl);
        }

        calculateAttemptRisk($conn, $attempt_id);

        require_once __DIR__ . "/../proctoring/proctoring-functions.php";
        generateProctoringReview($conn, $attempt_id);
    }

    return $success;
}

function recordTaskViolation($conn, $attempt_id, $type)
{
    $allowed = ['tab_switch', 'leave', 'copy_paste', 'right_click'];

    if (!in_array($type, $allowed)) {
        return ['success' => false];
    }

    $columnMap = [
        'tab_switch' => 'tab_switch_count',
        'leave' => 'leave_count',
        'copy_paste' => 'copy_paste_count',
        'right_click' => 'right_click_count'
    ];

    $column = $columnMap[$type];

    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET 
            $column = $column + 1,
            violation_count = violation_count + 1
        WHERE id = ?
        AND status = 'in_progress'
    ");

    $stmt->execute([$attempt_id]);

    $stmt = $conn->prepare("
        SELECT *
        FROM task_attempts
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attempt && (int)$attempt['violation_count'] >= 5) {
        $stmt = $conn->prepare("
            UPDATE task_attempts
            SET status = 'disqualified',
                disqualified_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([$attempt_id]);
        $attempt['status'] = 'disqualified';
    }

    return [
        'success' => true,
        'status' => $attempt['status'] ?? null,
        'violations' => (int)($attempt['violation_count'] ?? 0)
    ];
}
function calculateAttemptRisk($conn, $attempt_id)
{
    $stmt = $conn->prepare("
        SELECT 
            ta.*,
            t.estimated_time
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE ta.id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id]);

    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        return false;
    }

    $started = strtotime($attempt['started_at']);
    $submitted = strtotime($attempt['submitted_at']);

    $durationMinutes = max(
        1,
        round(($submitted - $started) / 60)
    );

    $expectedMinutes = (int)$attempt['estimated_time'];

    if ($expectedMinutes <= 0) {
        $expectedMinutes = 60;
    }

    $speedRisk = 'low';
    $integrityRisk = 'low';

    $notes = [];

    if ($durationMinutes <= ($expectedMinutes * 0.25)) {

        $speedRisk = 'high';

        $notes[] = "Submission completed unusually fast.";

    } elseif ($durationMinutes <= ($expectedMinutes * 0.5)) {

        $speedRisk = 'medium';

        $notes[] = "Submission completed faster than expected.";
    }

    $violations = (int)($attempt['violation_count'] ?? 0);

    if ($violations >= 4) {

        $integrityRisk = 'high';

        $notes[] = "High number of violations detected.";

    } elseif ($violations >= 2) {

        $integrityRisk = 'medium';

        $notes[] = "Some violations detected.";
    }

    if (
        (int)$attempt['ai_score'] >= 85 &&
        $speedRisk === 'high'
    ) {

        $integrityRisk = 'high';

        $notes[] = "High AI score with unusually fast completion.";
    }

    $stmt = $conn->prepare("
        UPDATE task_attempts
        SET
            speed_risk = ?,
            integrity_risk = ?,
            integrity_note = ?
        WHERE id = ?
    ");

    return $stmt->execute([
        $speedRisk,
        $integrityRisk,
        implode(' ', $notes),
        $attempt_id
    ]);
}
