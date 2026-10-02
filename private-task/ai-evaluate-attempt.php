<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../private-config/gemini-key.php';
require_once __DIR__ . '/../private-user/proctoring/proctoring-functions.php';

$attempt_id = (int)($_GET['attempt_id'] ?? 0);

if ($attempt_id <= 0) {
    die("Invalid attempt id");
}

$sql = "
SELECT 
    ta.id,
    ta.answer_text,
    ta.answer_file,
    ta.violation_count,
    ta.tab_switch_count,
    ta.leave_count,
    ta.copy_paste_count,
    ta.right_click_count,
    ta.status AS attempt_status,
    t.title,
    t.description,
    t.required_skills,
    t.evaluation_criteria,
    t.model_answer,
    t.points
FROM task_attempts ta
JOIN tasks t ON ta.task_id = t.id
WHERE ta.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->execute([$attempt_id]);

$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    die("Attempt not found");
}

$prompt = "
You are evaluating a candidate task submission.

IMPORTANT EVALUATION RULES:
1. Evaluate the user's answer by comparing it with the Model Answer / Expected Answer.
2. Do NOT require the user's answer to match the model answer word-for-word.
3. Accept different solution methods if the final result, logic, and requirements are mostly correct.
4. For numeric answers, allow reasonable tolerance when the result is close. Example: if the expected answer is 5.5 and the user answer is 5.1, do not mark it completely wrong; reduce only part of the correctness score depending on the task context.
5. If the answer is conceptually correct but incomplete, give partial credit.
6. Ignore integrity/proctoring violations in the score. Those are handled separately by the system.
7. Score must be from 0 to 100.

Scoring breakdown:
- 70% Correctness compared to the Model Answer / Expected Answer
- 20% Fulfillment of the Evaluation Criteria
- 10% Clarity, completeness, and quality of explanation/work

Task Title:
{$attempt['title']}

Task Description:
{$attempt['description']}

Required Skills:
{$attempt['required_skills']}

Evaluation Criteria:
{$attempt['evaluation_criteria']}

Model Answer / Expected Answer:
{$attempt['model_answer']}

Maximum Points:
{$attempt['points']}

User Answer:
{$attempt['answer_text']}

Return ONLY valid JSON like this:
{
  \"score\": 0,
  \"feedback\": \"Explain the score based on correctness, criteria, and clarity. Mention where the answer matches or differs from the model answer.\",
  \"recommendation\": \"Write a short reviewer recommendation.\",
  \"status\": \"recommended\"
}
";

$parts = [
    ["text" => $prompt]
];

if (!empty($attempt['answer_file'])) {
    $filePath = __DIR__ . "/../" . $attempt['answer_file'];

    if (file_exists($filePath)) {
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

        if (isset($mimeTypes[$ext])) {
            $fileData = base64_encode(file_get_contents($filePath));

            $parts[] = [
                "inlineData" => [
                    "mimeType" => $mimeTypes[$ext],
                    "data" => $fileData
                ]
            ];
        }
    }
}

$data = [
    "contents" => [
        [
            "parts" => $parts
        ]
    ]
];

$models = [
    "gemini-2.5-flash",
    "gemini-2.5-flash-lite",
    "gemini-2.5-pro"
];

$response = null;
$result = null;
$lastErrorMessage = '';

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . GEMINI_API_KEY;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 45
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $lastErrorMessage = curl_error($ch);
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (!$result) {
        $lastErrorMessage = "Gemini raw response is not JSON: " . $response;
        continue;
    }

    if (!isset($result['error'])) {
        break;
    }

    $code = (int)($result['error']['code'] ?? 0);
    $lastErrorMessage = $result['error']['message'] ?? json_encode($result['error']);

    // 503 = high demand / unavailable, 429 = quota or rate limit.
    // Try the next model only for temporary capacity/rate issues.
    if (!in_array($code, [429, 503], true)) {
        break;
    }
}

if (!$result) {
    die("Gemini Error: " . htmlspecialchars($lastErrorMessage));
}

if (isset($result['error'])) {
    die("Gemini Error: " . htmlspecialchars($lastErrorMessage));
}

$text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
$text = trim($text);
$text = str_replace(['```json', '```'], '', $text);
$text = trim($text);

$aiData = json_decode($text, true);

// If Gemini adds any extra words before/after JSON, extract only the JSON object.
if (!$aiData && preg_match('/\{.*\}/s', $text, $matches)) {
    $aiData = json_decode($matches[0], true);
}

if (!$aiData) {
    die("AI response is not valid JSON: " . htmlspecialchars($text));
}

/*
|--------------------------------------------------------------------------
| 1) Solution AI Evaluation
|--------------------------------------------------------------------------
*/

$score = max(0, min(100, (int)($aiData['score'] ?? 0)));
$feedback = trim((string)($aiData['feedback'] ?? ''));
$recommendation = trim((string)($aiData['recommendation'] ?? ''));

/*
|--------------------------------------------------------------------------
| 2) Integrity Summary = Normal Violations + Camera AI Events
|--------------------------------------------------------------------------
*/

$normalViolations = (int)($attempt['violation_count'] ?? 0);
$tabSwitches = (int)($attempt['tab_switch_count'] ?? 0);
$leaves = (int)($attempt['leave_count'] ?? 0);
$copyPaste = (int)($attempt['copy_paste_count'] ?? 0);
$rightClick = (int)($attempt['right_click_count'] ?? 0);

$proctoringSummary = getAttemptProctoringSummary($conn, $attempt_id);
$cameraRiskScore = calculateProctoringRiskScore($proctoringSummary);

$normalRiskScore = min(100, $normalViolations * 12);
$integrityRiskScore = min(100, $normalRiskScore + $cameraRiskScore);
$integrityRisk = getProctoringRiskLevel($integrityRiskScore);

$cameraNotes = [];
foreach ($proctoringSummary as $row) {
    $event = $row['event_type'];
    $total = (int)$row['total'];

    // Browser/workspace events are not camera flags.
    // They are already counted separately in normal violations.
    if (in_array($event, [
        'camera_started',
        'face_detected',
        'tab_switch',
        'leave',
        'copy_paste',
        'right_click',
        'blur',
        'visibility_change'
    ], true)) {
        continue;
    }

    $cameraNotes[] = str_replace('_', ' ', $event) . " ({$total})";
}

$integrityNotes = [];

if ($normalViolations > 0) {
    $integrityNotes[] = "Normal violations: {$normalViolations} total; tab switches: {$tabSwitches}, leave attempts: {$leaves}, copy/paste: {$copyPaste}, right click: {$rightClick}.";
}

if (!empty($cameraNotes)) {
    $integrityNotes[] = "Camera AI flags: " . implode(', ', $cameraNotes) . ".";
}

if (empty($integrityNotes)) {
    $integrityNotes[] = "No major integrity issues detected.";
}

$integrityNote = implode("\n", $integrityNotes);

/*
|--------------------------------------------------------------------------
| 3) Auto Decision Layer
|--------------------------------------------------------------------------
| ai_status هنا لا يمثل جودة الحل فقط.
| هو قرار أولي يجمع جودة الحل + نزاهة المحاولة.
*/

$finalStatus = 'needs_review';

if ($attempt['attempt_status'] === 'disqualified' || $score < 50) {
    $finalStatus = 'not_recommended';
} elseif ($score >= 85 && $integrityRisk === 'low' && $normalViolations <= 1) {
    $finalStatus = 'recommended';
} elseif ($integrityRisk === 'high') {
    $finalStatus = 'needs_review';
} elseif ($score >= 70) {
    $finalStatus = 'needs_review';
} else {
    $finalStatus = 'needs_review';
}

$allowedAiStatuses = ['recommended', 'needs_review', 'not_recommended'];

if (!in_array($finalStatus, $allowedAiStatuses, true)) {
    $finalStatus = 'needs_review';
}

$combinedRecommendation = $recommendation;
$combinedRecommendation .= "\n\nIntegrity Decision: " . strtoupper($finalStatus) . ".";
$combinedRecommendation .= "\nIntegrity Risk: " . strtoupper($integrityRisk) . " ({$integrityRiskScore}/100).";

/*
|--------------------------------------------------------------------------
| 4) Save Evaluation + Integrity Summary
|--------------------------------------------------------------------------
*/

$update = $conn->prepare("
    UPDATE task_attempts
    SET 
        ai_score = ?,
        ai_feedback = ?,
        ai_recommendation = ?,
        ai_status = ?,
        final_points = ?,
        integrity_risk = ?,
        integrity_note = ?
    WHERE id = ?
");

$update->execute([
    $score,
    $feedback,
    $combinedRecommendation,
    $finalStatus,
    $score,
    $integrityRisk,
    $integrityNote,
    $attempt_id
]);

echo "AI evaluation + integrity decision saved successfully";
