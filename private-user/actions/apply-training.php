<?php
session_start();

require_once(__DIR__ . "/../../config.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: ../../public/user/login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];
$training_id = $_POST['training_id'] ?? null;

$errors = [];
$old = $_POST;

function backToTrainings($training_id, $errors, $old) {
    $_SESSION['training_apply_errors'] = $errors;
    $_SESSION['training_apply_old'] = $old;
    $_SESSION['training_apply_open_id'] = $training_id;

    header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
    exit;
}

if (!$training_id) {
    header("Location: ../../public/user/trainings.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT id FROM trainings
    WHERE id = ?
    AND status = 'published'
    AND deleted = 0
");
$stmt->execute([$training_id]);

if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    header("Location: ../../public/user/trainings.php");
    exit;
}

$check = $conn->prepare("
    SELECT id FROM training_applications
    WHERE training_id = ?
    AND job_seeker_id = ?
");
$check->execute([$training_id, $user_id]);

if ($check->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION['training_apply_success'] = "You already applied for this training.";
    header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
    exit;
}

$comment = trim($_POST['comment'] ?? '');
$university = trim($_POST['university'] ?? '');
$major = trim($_POST['major'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$expectedYear = trim($_POST['expected_graduation_year'] ?? '');

if ($comment === '') $errors['comment'] = 'Comment is required';
elseif (strlen($comment) < 10) $errors['comment'] = 'Comment must be at least 10 characters';

if ($university === '') $errors['university'] = 'University is required';
if ($major === '') $errors['major'] = 'Major is required';

if ($email === '') $errors['email'] = 'Email is required';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address';

if ($phone === '') $errors['phone'] = 'Phone is required';
elseif (!preg_match("/^07[789]\d{7}$/", $phone)) $errors['phone'] = 'Enter a valid Jordanian phone number';

$currentYear = (int)date('Y');

if ($expectedYear === '') {
    $errors['expected_graduation_year'] = 'Expected graduation year is required';
} elseif (!is_numeric($expectedYear) || $expectedYear < $currentYear || $expectedYear > $currentYear + 8) {
    $errors['expected_graduation_year'] = 'Choose a valid graduation year';
}

$cvFile = null;

if (!empty($_FILES['cv_file']['name'])) {
    $allowed = ['pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $errors['cv_file'] = 'CV must be PDF, DOC, or DOCX';
    } elseif ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
        $errors['cv_file'] = 'CV file size must be less than 5MB';
    }
}

if (!empty($errors)) {
    backToTrainings($training_id, $errors, $old);
}

if (!empty($_FILES['cv_file']['name'])) {
    $dir = __DIR__ . "/../../uploads/training_cvs/";

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
    $fileName = "training_cv_" . $user_id . "_" . time() . "." . $ext;

    if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $dir . $fileName)) {
        $cvFile = "uploads/training_cvs/" . $fileName;
    }
}

$stmt = $conn->prepare("
    INSERT INTO training_applications
    (training_id, job_seeker_id, comment, university, major, email, phone, expected_graduation_year, cv_file, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->execute([
    $training_id,
    $user_id,
    $comment,
    $university,
    $major,
    $email,
    $phone,
    $expectedYear,
    $cvFile
]);

$_SESSION['training_apply_success'] = "Your application has been sent successfully.";

header("Location: ../../public/user/trainings.php#training-" . urlencode($training_id));
exit;