<?php
session_start();

require_once(__DIR__ . "/../../config.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: ../../public/user/login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];
$training_id = $_POST['training_id'] ?? null;
$comment = trim($_POST['comment'] ?? '');

if (!$training_id) {
    header("Location: ../../public/user/trainings.php");
    exit;
}

if ($comment === '' || strlen($comment) < 3) {
    $_SESSION['training_comment_error'] = "Comment must be at least 3 characters";
    $_SESSION['training_comment_open_id'] = $training_id;

    header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO training_comments
    (training_id, job_seeker_id, comment)
    VALUES (?, ?, ?)
");

$stmt->execute([$training_id, $user_id, $comment]);

header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
exit;