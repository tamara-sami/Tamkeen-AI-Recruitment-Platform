<?php

session_start();

require_once(__DIR__ . "/../../config.php");

require_once(__DIR__ . "/../functions/courses-home-functions.php");

header("Content-Type: application/json");

if (!isset($_SESSION['job_seeker_id'])) {

    echo json_encode([
        "success" => false
    ]);

    exit;
}

$sender_id =
    $_SESSION['job_seeker_id'];

$receiver_id =
    (int)($_POST['receiver_id'] ?? 0);

$post_id =
    (int)($_POST['post_id'] ?? 0);

$message =
    trim($_POST['message'] ?? '');

if ($receiver_id <= 0 || $message === '') {

    echo json_encode([
        "success" => false
    ]);

    exit;
}

$success =
    sendLearningMessage(
        $conn,
        $post_id,
        $sender_id,
        $receiver_id,
        $message
    );

echo json_encode([
    "success" => $success
]);

exit;