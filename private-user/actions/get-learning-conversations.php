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

$conversations =
    getLearningConversations(
        $conn,
        $user_id
    );

$unread =
    countUnreadLearningMessages(
        $conn,
        $user_id
    );

echo json_encode([

    "success" => true,

    "conversations" => $conversations,

    "unread_count" => $unread

]);

exit;