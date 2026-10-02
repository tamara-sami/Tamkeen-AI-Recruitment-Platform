<?php
session_start();

require_once(__DIR__ . "/../../config.php");

if (!isset($_SESSION['job_seeker_id'])) {

    header("Location: ../../public/user/login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$application_id = $_POST['application_id'] ?? null;

$errors = [];

if (!$application_id) {

    header("Location: ../../public/user/my-training-applications.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM training_applications
    WHERE id = ?
    AND job_seeker_id = ?
");

$stmt->execute([
    $application_id,
    $user_id
]);

$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {

    header("Location: ../../public/user/my-training-applications.php");
    exit;
}

$comment = trim($_POST['comment'] ?? '');

$university = trim($_POST['university'] ?? '');

$major = trim($_POST['major'] ?? '');

$email = trim($_POST['email'] ?? '');

$phone = trim($_POST['phone'] ?? '');

$expectedYear = trim(
    $_POST['expected_graduation_year'] ?? ''
);

if ($comment === '') {

    $errors['comment'] = 'Comment is required';

} elseif (strlen($comment) < 10) {

    $errors['comment'] =
        'Comment must be at least 10 characters';
}

if ($university === '') {

    $errors['university'] =
        'University is required';
}

if ($major === '') {

    $errors['major'] =
        'Major is required';
}

if ($email === '') {

    $errors['email'] =
        'Email is required';

} elseif (
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {

    $errors['email'] =
        'Enter a valid email address';
}

if ($phone === '') {

    $errors['phone'] =
        'Phone is required';

} elseif (
    !preg_match("/^07[789]\d{7}$/", $phone)
) {

    $errors['phone'] =
        'Enter a valid Jordanian phone number';
}

$currentYear = (int)date('Y');

if ($expectedYear === '') {

    $errors['expected_graduation_year'] =
        'Expected graduation year is required';

} elseif (
    !is_numeric($expectedYear)
    || $expectedYear < $currentYear
    || $expectedYear > $currentYear + 8
) {

    $errors['expected_graduation_year'] =
        'Choose a valid graduation year';
}

$cvFileSql = "";

$cvFile = $application['cv_file'];

if (!empty($_FILES['cv_file']['name'])) {

    $allowed = ['pdf', 'doc', 'docx'];

    $ext = strtolower(
        pathinfo(
            $_FILES['cv_file']['name'],
            PATHINFO_EXTENSION
        )
    );

    if (!in_array($ext, $allowed)) {

        $errors['cv_file'] =
            'CV must be PDF, DOC, or DOCX';

    } elseif (
        $_FILES['cv_file']['size']
        > 5 * 1024 * 1024
    ) {

        $errors['cv_file'] =
            'CV file size must be less than 5MB';

    } else {

        $dir =
            __DIR__ .
            "/../../uploads/training_cvs/";

        if (!is_dir($dir)) {

            mkdir($dir, 0777, true);
        }

        $fileName =
            "training_cv_" .
            $user_id .
            "_" .
            time() .
            "." .
            $ext;

        if (
            move_uploaded_file(
                $_FILES['cv_file']['tmp_name'],
                $dir . $fileName
            )
        ) {

            $cvFile =
                "uploads/training_cvs/" .
                $fileName;

            $cvFileSql = ", cv_file = ?";
        }
    }
}

if (!empty($errors)) {

    $_SESSION['application_update_errors']
        = $errors;

    $_SESSION['application_update_old']
        = $_POST;

    header(
        "Location: ../../public/user/my-training-applications.php"
    );

    exit;
}

$sql = "
    UPDATE training_applications
    SET
        comment = ?,
        university = ?,
        major = ?,
        email = ?,
        phone = ?,
        expected_graduation_year = ?
        $cvFileSql
    WHERE id = ?
    AND job_seeker_id = ?
";

$params = [
    $comment,
    $university,
    $major,
    $email,
    $phone,
    $expectedYear
];

if (!empty($cvFileSql)) {

    $params[] = $cvFile;
}

$params[] = $application_id;

$params[] = $user_id;

$stmt = $conn->prepare($sql);

$stmt->execute($params);

$_SESSION['application_update_success']
    = "Your application has been updated successfully.";

header(
    "Location: ../../public/user/my-training-applications.php"
);

exit;