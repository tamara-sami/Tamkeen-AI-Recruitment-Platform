<?php

function getAiCenterData(PDO $conn, int $company_id): array
{
    return [
        "stats" => getAiStats($conn, $company_id),
        "topCandidates" => getAiTopCandidates($conn, $company_id),
        "recommendations" => getAiRecommendations($conn, $company_id),
        "activities" => getAiActivities($conn, $company_id),
        "pendingAi" => countPendingAiEvaluations($conn, $company_id),
    ];
}

function handleAiCenterAction(PDO $conn, int $company_id): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = $_POST['ai_action'] ?? '';

    if ($action !== 'refresh_ai') {
        return;
    }

    $limit = isset($_POST['limit']) ? max(1, min(20, (int)$_POST['limit'])) : 10;
    $result = evaluatePendingAttemptsWithGemini($conn, $company_id, $limit);

    $_SESSION['ai_center_flash'] = $result;

    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . "?ai_refreshed=1");
    exit;
}

function evaluatePendingAttemptsWithGemini(PDO $conn, int $company_id, int $limit = 10): array
{
    $apiKeyFile = dirname(__DIR__, 2) . '/private-config/gemini-key.php';

    if (!defined('GEMINI_API_KEY') && file_exists($apiKeyFile)) {
        require_once $apiKeyFile;
    }

    if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
        return [
            'success' => false,
            'evaluated' => 0,
            'failed' => 0,
            'message' => 'AI API key is missing. Check private-config/gemini-key.php.'
        ];
    }

    $sql = "
        SELECT
            ta.id,
            ta.answer_text,
            ta.answer_file,
            ta.submitted_at,
            t.title,
            t.description,
            t.required_skills,
            t.evaluation_criteria,
            t.points,
            js.full_name,
            js.job_title
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND (ta.ai_score IS NULL OR ta.ai_feedback IS NULL OR ta.ai_feedback = '')
        ORDER BY ta.submitted_at ASC
        LIMIT {$limit}
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($attempts)) {
        return [
            'success' => true,
            'evaluated' => 0,
            'failed' => 0,
            'message' => 'No pending AI evaluations. Everything is already evaluated.'
        ];
    }

    $evaluated = 0;
    $failed = 0;

    foreach ($attempts as $attempt) {
        $aiData = callGeminiForAttempt($attempt, GEMINI_API_KEY);

        if (!$aiData) {
            $failed++;
            continue;
        }

        $maxPoints = max(0, (int)$attempt['points']);
        $score = (int)($aiData['score'] ?? 0);
        $score = max(0, min($maxPoints, $score));

        $feedback = trim((string)($aiData['feedback'] ?? ''));
        $recommendation = trim((string)($aiData['recommendation'] ?? ''));
        $status = normalizeAiStatus((string)($aiData['status'] ?? 'needs_review'));

        $updateSql = "
            UPDATE task_attempts
            SET
                ai_score = ?,
                ai_feedback = ?,
                ai_recommendation = ?,
                ai_status = ?,
                final_points = COALESCE(final_points, ?)
            WHERE id = ?
        ";

        $update = $conn->prepare($updateSql);
        $update->execute([
            $score,
            $feedback,
            $recommendation,
            $status,
            $score,
            (int)$attempt['id']
        ]);

        $evaluated++;
    }

    return [
        'success' => $failed === 0,
        'evaluated' => $evaluated,
        'failed' => $failed,
        'message' => "AI refresh completed. Evaluated {$evaluated} submission(s), failed {$failed}."
    ];
}

function callGeminiForAttempt(array $attempt, string $apiKey): ?array
{
    $prompt = buildGeminiAttemptPrompt($attempt);
    $parts = [["text" => $prompt]];

    $filePart = buildGeminiFilePart($attempt['answer_file'] ?? '');
    if ($filePart !== null) {
        $parts[] = $filePart;
    }

    $payload = [
        "contents" => [
            ["parts" => $parts]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "responseMimeType" => "application/json"
        ]
    ];

    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 45,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('Gemini cURL error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('Gemini HTTP error ' . $httpCode . ': ' . $response);
        return null;
    }

    $result = json_decode($response, true);
    $text = trim((string)($result['candidates'][0]['content']['parts'][0]['text'] ?? ''));
    $text = trim(str_replace(['```json', '```'], '', $text));

    $decoded = json_decode($text, true);

    if (!is_array($decoded)) {
        error_log('Gemini invalid JSON: ' . $text);
        return null;
    }

    return $decoded;
}

function buildGeminiAttemptPrompt(array $attempt): string
{
    $maxPoints = (int)($attempt['points'] ?? 0);

    return "
You are an AI hiring evaluator inside Tamkeen platform.
Evaluate the candidate task submission fairly using the task criteria.

Candidate Name: " . ($attempt['full_name'] ?? 'Candidate') . "
Candidate Job Title: " . ($attempt['job_title'] ?? 'Not provided') . "

Task Title:
" . ($attempt['title'] ?? '') . "

Task Description:
" . ($attempt['description'] ?? '') . "

Required Skills:
" . ($attempt['required_skills'] ?? '') . "

Evaluation Criteria:
" . ($attempt['evaluation_criteria'] ?? '') . "

Maximum Points: {$maxPoints}

Candidate Answer:
" . ($attempt['answer_text'] ?? '') . "

Return ONLY valid JSON in this exact structure:
{
  \"score\": 0,
  \"feedback\": \"Short, specific feedback about the submitted work.\",
  \"recommendation\": \"Clear hiring recommendation for the company admin.\",
  \"status\": \"recommended\"
}

Rules:
- score must be an integer from 0 to {$maxPoints}.
- status must be one of: recommended, needs_review, not_recommended.
- Do not add markdown.
";
}

function buildGeminiFilePart(string $answerFile): ?array
{
    if (empty($answerFile)) {
        return null;
    }

    $safeRelativePath = ltrim(str_replace(['..', '\\'], ['', '/'], $answerFile), '/');
    $filePath = dirname(__DIR__, 2) . '/' . $safeRelativePath;

    if (!file_exists($filePath) || !is_readable($filePath)) {
        return null;
    }

    $maxBytes = 5 * 1024 * 1024;
    if (filesize($filePath) > $maxBytes) {
        return null;
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'json' => 'application/json',
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'php' => 'text/plain'
    ];

    if (!isset($mimeTypes[$ext])) {
        return null;
    }

    return [
        "inlineData" => [
            "mimeType" => $mimeTypes[$ext],
            "data" => base64_encode(file_get_contents($filePath))
        ]
    ];
}

function normalizeAiStatus(string $status): string
{
    $status = strtolower(trim($status));
    $allowed = ['recommended', 'needs_review', 'not_recommended'];
    return in_array($status, $allowed, true) ? $status : 'needs_review';
}

function getAiStats(PDO $conn, int $company_id): array
{
    return [
        "rankedCandidates" => countAiRankedCandidates($conn, $company_id),
        "recommended" => countAiStatus($conn, $company_id, "recommended"),
        "fastSolvers" => countFastSolvers($conn, $company_id),
        "highAccuracy" => countHighAccuracy($conn, $company_id),
        "fullTimeFit" => countHiringType($conn, $company_id, "full_time"),
        "temporaryFit" => countHiringType($conn, $company_id, "temporary")
    ];
}

function countPendingAiEvaluations(PDO $conn, int $company_id): int
{
    $sql = "
        SELECT COUNT(*)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND (ta.ai_score IS NULL OR ta.ai_feedback IS NULL OR ta.ai_feedback = '')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countAiRankedCandidates(PDO $conn, int $company_id): int
{
    $sql = "
        SELECT COUNT(DISTINCT ta.job_seeker_id)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.ai_score IS NOT NULL
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countAiStatus(PDO $conn, int $company_id, string $status): int
{
    $sql = "
        SELECT COUNT(DISTINCT ta.job_seeker_id)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.ai_status = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id, $status]);
    return (int)$stmt->fetchColumn();
}

function countHighAccuracy(PDO $conn, int $company_id): int
{
    $sql = "
        SELECT COUNT(*)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.ai_score IS NOT NULL
        AND t.points > 0
        AND (ta.ai_score / t.points) >= 0.80
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countFastSolvers(PDO $conn, int $company_id): int
{
    $sql = "
        SELECT COUNT(*)
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.started_at IS NOT NULL
        AND ta.submitted_at IS NOT NULL
        AND TIMESTAMPDIFF(MINUTE, ta.started_at, ta.submitted_at) <= 30
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return (int)$stmt->fetchColumn();
}

function countHiringType(PDO $conn, int $company_id, string $type): int
{
    $sql = "
        SELECT COUNT(*)
        FROM hiring_requests
        WHERE company_id = ?
        AND hiring_type = ?
        AND status = 'approved'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id, $type]);
    return (int)$stmt->fetchColumn();
}

function getAiTopCandidates(PDO $conn, int $company_id): array
{
    $sql = "
        SELECT
            js.id AS job_seeker_id,
            js.full_name,
            js.job_title,
            COUNT(ta.id) AS total_tasks,
            ROUND(AVG(ta.ai_score), 0) AS ai_score,
            SUM(ta.ai_score) AS total_ai_score,
            MAX(ta.ai_status) AS ai_status,
            SUBSTRING_INDEX(GROUP_CONCAT(ta.ai_feedback ORDER BY ta.submitted_at DESC SEPARATOR ' || '), ' || ', 1) AS ai_feedback,
            SUBSTRING_INDEX(GROUP_CONCAT(ta.ai_recommendation ORDER BY ta.submitted_at DESC SEPARATOR ' || '), ' || ', 1) AS ai_recommendation,
            MAX(ta.submitted_at) AS latest_submission
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.ai_score IS NOT NULL
        GROUP BY js.id, js.full_name, js.job_title
        ORDER BY total_ai_score DESC, ai_score DESC, latest_submission DESC
        LIMIT 5
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAiRecommendations(PDO $conn, int $company_id): array
{
    $sql = "
        SELECT
            ta.ai_score,
            ta.ai_status,
            ta.ai_feedback,
            ta.ai_recommendation,
            ta.submitted_at AS created_at,
            t.title AS task_title,
            t.points,
            js.full_name,
            js.job_title
        FROM task_attempts ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN job_seekers js ON js.id = ta.job_seeker_id
        WHERE t.company_id = ?
        AND ta.status = 'submitted'
        AND ta.ai_score IS NOT NULL
        ORDER BY
            CASE ta.ai_status
                WHEN 'recommended' THEN 1
                WHEN 'needs_review' THEN 2
                ELSE 3
            END,
            ta.ai_score DESC,
            ta.submitted_at DESC
        LIMIT 4
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAiActivities(PDO $conn, int $company_id): array
{
    $sql = "
        (
            SELECT
                'fa fa-robot' AS icon,
                CASE
                    WHEN ta.ai_status = 'recommended' THEN 'AI recommended a candidate'
                    WHEN ta.ai_status = 'not_recommended' THEN 'AI marked a submission as not recommended'
                    ELSE 'AI marked a submission for review'
                END AS title,
                CONCAT(js.full_name, ' - ', t.title, ' - Score: ', COALESCE(ta.ai_score, 0), '/', t.points) AS description,
                COALESCE(ta.submitted_at, NOW()) AS created_at
            FROM task_attempts ta
            JOIN tasks t ON t.id = ta.task_id
            JOIN job_seekers js ON js.id = ta.job_seeker_id
            WHERE t.company_id = ?
            AND ta.status = 'submitted'
            AND ta.ai_score IS NOT NULL
        )

        UNION ALL

        (
            SELECT
                'fa fa-chart-line' AS icon,
                'Hiring recommendation updated' AS title,
                CONCAT(js.full_name, ' has ', hr.status, ' hiring request.') AS description,
                hr.created_at
            FROM hiring_requests hr
            JOIN job_seekers js ON js.id = hr.job_seeker_id
            WHERE hr.company_id = ?
        )

        ORDER BY created_at DESC
        LIMIT 5
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_id, $company_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function aiDate($date): string
{
    return empty($date) ? "—" : date("M d, Y", strtotime($date));
}

function aiHiringType($type): string
{
    return ucwords(str_replace("_", " ", (string)$type));
}

function aiStatusLabel($status): string
{
    return ucwords(str_replace("_", " ", (string)$status));
}

function renderCompanyLogo($company_name, $company_logo, $class = "profile-avatar"): string
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
            ' . htmlspecialchars(strtoupper(substr((string)$company_name, 0, 1))) . '
        </div>
    ';
}
