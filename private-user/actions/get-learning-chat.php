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

$user_id = $_SESSION['job_seeker_id'];

$other_user_id =
    (int)($_GET['user_id'] ?? 0);

if ($other_user_id <= 0) {

    echo json_encode([
        "success" => false
    ]);

    exit;
}

markLearningMessagesAsRead(
    $conn,
    $user_id,
    $other_user_id
);

$messages =
    getLearningChatMessages(
        $conn,
        $user_id,
        $other_user_id
    );

$unread =
    countUnreadLearningMessages(
        $conn,
        $user_id
    );

echo json_encode([

    "success" => true,

    "messages" => $messages,

    "unread_count" => $unread

]);

exit;