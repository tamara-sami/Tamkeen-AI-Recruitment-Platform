<?php
session_start();

require_once(__DIR__ . "/../../config.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: ../../public/user/login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];
$training_id = $_POST['training_id'] ?? null;

if (!$training_id) {
    header("Location: ../../public/user/trainings.php");
    exit;
}

$check = $conn->prepare("
    SELECT id FROM training_reactions
    WHERE training_id = ?
    AND job_seeker_id = ?
");
$check->execute([$training_id, $user_id]);

if ($check->fetch()) {
    $stmt = $conn->prepare("
        DELETE FROM training_reactions
        WHERE training_id = ?
        AND job_seeker_id = ?
    ");
} else {
    $stmt = $conn->prepare("
        INSERT INTO training_reactions
        (training_id, job_seeker_id, reaction_type)
        VALUES (?, ?, 'useful')
    ");
}

$stmt->execute([$training_id, $user_id]);

header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
exit;