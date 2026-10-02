<?php

/**
 * AI Proctoring helper functions.
 * These functions are intentionally separated from pages/actions to keep the
 * endpoint small and the task page clean.
 */

function getAllowedProctoringEvents()
{
    return [
        'camera_started',
        'camera_denied',
        'camera_stopped',
        'camera_black',
        'no_face_detected',
        'multiple_faces',
        'looking_away',
        'high_movement',
        'face_detected',
        'proctoring_error'
    ];
}

function getAllowedProctoringSeverities()
{
    return ['low', 'medium', 'high'];
}

function sanitizeProctoringDetails($details)
{
    $details = trim((string)$details);
    $details = strip_tags($details);

    if (strlen($details) > 1000) {
        $details = substr($details, 0, 1000);
    }

    return $details;
}

function ensureAttemptBelongsToJobSeeker(PDO $conn, int $attempt_id, int $job_seeker_id)
{
    $stmt = $conn->prepare("
        SELECT id, status
        FROM task_attempts
        WHERE id = ?
        AND job_seeker_id = ?
        LIMIT 1
    ");

    $stmt->execute([$attempt_id, $job_seeker_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function shouldThrottleProctoringEvent(PDO $conn, int $attempt_id, string $event_type, int $cooldownSeconds = 8)
{
    $stmt = $conn->prepare("
        SELECT id
        FROM task_proctoring_events
        WHERE task_attempt_id = ?
        AND event_type = ?
        AND created_at >= (NOW() - INTERVAL {$cooldownSeconds} SECOND)
        LIMIT 1
    ");

    $stmt->execute([$attempt_id, $event_type]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

function recordProctoringEvent(PDO $conn, int $attempt_id, int $job_seeker_id, string $event_type, string $severity = 'medium', string $details = '')
{
    if (!in_array($event_type, getAllowedProctoringEvents(), true)) {
        return [
            'success' => false,
            'message' => 'Invalid proctoring event type.'
        ];
    }

    if (!in_array($severity, getAllowedProctoringSeverities(), true)) {
        $severity = 'medium';
    }

    $attempt = ensureAttemptBelongsToJobSeeker($conn, $attempt_id, $job_seeker_id);

    if (!$attempt) {
        return [
            'success' => false,
            'message' => 'Attempt not found.'
        ];
    }

    if ($attempt['status'] !== 'in_progress') {
        return [
            'success' => false,
            'message' => 'Attempt is not active.'
        ];
    }

    // Prevent the browser from flooding the database with the same event.
    if (shouldThrottleProctoringEvent($conn, $attempt_id, $event_type)) {
        return [
            'success' => true,
            'throttled' => true
        ];
    }

    $details = sanitizeProctoringDetails($details);

    $stmt = $conn->prepare("
        INSERT INTO task_proctoring_events
        (task_attempt_id, job_seeker_id, event_type, severity, details)
        VALUES (?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $attempt_id,
        $job_seeker_id,
        $event_type,
        $severity,
        $details
    ]);

    return [
        'success' => $success,
        'throttled' => false
    ];
}

function getAttemptProctoringSummary(PDO $conn, int $attempt_id)
{
    $stmt = $conn->prepare("
        SELECT event_type, severity, COUNT(*) AS total
        FROM task_proctoring_events
        WHERE task_attempt_id = ?
        GROUP BY event_type, severity
        ORDER BY total DESC
    ");

    $stmt->execute([$attempt_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateProctoringRiskScore(array $summary)
{
   $weights = [
    'camera_denied' => 35,
    'camera_stopped' => 25,
    'camera_black' => 20,
    'no_face_detected' => 15,
    'multiple_faces' => 25,
    'looking_away' => 8,
    'high_movement' => 10,
    'proctoring_error' => 5,
    'camera_started' => 0,
    'face_detected' => 0
];
    $score = 0;

    foreach ($summary as $row) {
        $event = $row['event_type'];
        $count = (int)$row['total'];
        $score += ($weights[$event] ?? 0) * $count;
    }

    return min(100, $score);
}

function getProctoringRiskLevel(int $score)
{
    if ($score >= 70) return 'high';
    if ($score >= 35) return 'medium';
    return 'low';
}
function generateProctoringReview(PDO $conn, int $attempt_id)
{
    $stmt = $conn->prepare("
        SELECT job_seeker_id
        FROM task_attempts
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$attempt_id]);
    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        return false;
    }

    $summary = getAttemptProctoringSummary($conn, $attempt_id);
    $riskScore = calculateProctoringRiskScore($summary);
    $riskLevel = getProctoringRiskLevel($riskScore);

    $notes = [];

    foreach ($summary as $row) {
        if (in_array($row['event_type'], ['camera_started', 'face_detected'], true)) {
            continue;
        }

        $label = ucwords(str_replace('_', ' ', $row['event_type']));
        $notes[] = $label . " (" . (int)$row['total'] . ")";
    }

    $aiSummary = empty($notes)
        ? "No suspicious camera behavior detected."
        : "Camera proctoring flags detected: " . implode(", ", $notes) . ".";

    $stmt = $conn->prepare("
        INSERT INTO task_proctoring_reviews
        (
            task_attempt_id,
            job_seeker_id,
            risk_score,
            risk_level,
            ai_summary,
            evaluator_decision
        )
        VALUES (?, ?, ?, ?, ?, 'pending')
        ON DUPLICATE KEY UPDATE
            risk_score = VALUES(risk_score),
            risk_level = VALUES(risk_level),
            ai_summary = VALUES(ai_summary),
            evaluator_decision = evaluator_decision
    ");

    return $stmt->execute([
        $attempt_id,
        (int)$attempt['job_seeker_id'],
        $riskScore,
        $riskLevel,
        $aiSummary
    ]);
}
