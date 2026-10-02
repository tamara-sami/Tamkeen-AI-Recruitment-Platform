<?php

session_start();

require_once(__DIR__ . "/../../config.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: ../../public/user/login.php");
    exit;
}

$user_id = (int)$_SESSION['job_seeker_id'];

$visibility = $_POST['profile_visibility'] ?? 'public';
$allowed = ['public', 'registered', 'hidden'];

if (!in_array($visibility, $allowed, true)) {
    $visibility = 'public';
}

$salaryConfidential = isset($_POST['salary_confidential']) ? 1 : 0;

$stmt = $conn->prepare("
    UPDATE job_seeker_profiles
    SET profile_visibility = ?,
        salary_confidential = ?
    WHERE job_seeker_id = ?
");

$success = $stmt->execute([
    $visibility,
    $salaryConfidential,
    $user_id
]);

$_SESSION[$success ? 'profile_public_success' : 'profile_public_error'] =
    $success ? 'Profile controls updated successfully.' : 'Could not update profile controls.';

header("Location: ../../public/user/public-profile.php");
exit;
