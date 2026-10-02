<?php

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

require_once(__DIR__ . "/../functions/helpers.php");
require_once(__DIR__ . "/../functions/hr-interviews-functions.php");

$company_id = (int)($_SESSION['company_id'] ?? 0);
$hr_user_id = (int)($_SESSION['user_id'] ?? 0);

require_once(__DIR__ . "/../actions/interviews-handler.php");
require_once(__DIR__ . "/../components/interviews-sections.php");
// Only run this file when the page receives a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$action = $_POST['action'] ?? '';

if ($action === 'create_interview') {
    // createHrInterview() is defined in ../functions-hrsystem/hr-interviews-functions.php.
    $result = createHrInterview($conn, $company_id, $_POST);

    if ($result['success']) {
        $_SESSION['interview_success'] = 'Interview scheduled successfully.';
        header("Location: /tamkeentest/public/hr/interviews.php#interviews-list");
        exit;
    }

    // If validation fails, send errors back to the page so it can display them.
    $errors = $result['errors'];
}

if ($action === 'update_status') {
    // Updates interview status: scheduled, completed, or cancelled.
    updateHrInterviewStatus(
        $conn,
        $company_id,
        $_POST['interview_id'] ?? 0,
        $_POST['status'] ?? 'scheduled'
    );

    $_SESSION['interview_success'] = 'Interview status updated.';
    header("Location: /tamkeentest/public/hr/interviews.php#interviews-list");
    exit;
}
?>
