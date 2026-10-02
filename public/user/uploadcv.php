<?php
include("../../config.php");
include("../functionsuser/user-cv-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: register.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$savedCV = getJobSeekerCV($conn, $user_id);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $language = $_POST['cv_language'] ?? '';
    $file = $_FILES['cv_file'] ?? null;

    $errors = validateCVUpload(
        $language,
        $file,
        $savedCV
    );

    if (empty($errors)) {

        if (
            $savedCV &&
            (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)
        ) {

            updateCVLanguageOnly(
                $conn,
                $language,
                $savedCV['id']
            );

            header("Location: ProfileImage.php");
            exit;
        }

        $uploaded = uploadJobSeekerCV(
            $conn,
            $user_id,
            $language,
            $file
        );

        if ($uploaded) {

            header("Location: ProfileImage.php");
            exit;

        } else {

            $errors['cv_file'] = "Failed to upload file";
        }
    }
}

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

    <?php
    $current_sidebar = 3;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="setup-main">

        <form method="POST"
              enctype="multipart/form-data"
              class="setup-form">

            <div class="d-flex justify-content-between mb-4">

                <a href="preferences.php"
                   class="text-primary text-decoration-none">

                    <i class="fa fa-arrow-left me-2"></i>
                    Previous
                </a>

                <small class="text-muted">Step 3</small>

                <a href="ProfileImage.php"
                   class="text-primary text-decoration-none">

                    Skip
                    <i class="fa fa-forward ms-1"></i>
                </a>

            </div>

            <h2>Upload your CV File</h2>

            <label>Written In *</label>

            <select name="cv_language"
                    class="form-select mb-2"
                    required>

                <option value="">Select language</option>

                <option value="English"
                    <?= ($savedCV['cv_language'] ?? '') === 'English' ? 'selected' : '' ?>>
                    English
                </option>

                <option value="Arabic"
                    <?= ($savedCV['cv_language'] ?? '') === 'Arabic' ? 'selected' : '' ?>>
                    Arabic
                </option>

            </select>

            <?php if (isset($errors['cv_language'])): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['cv_language']); ?>
                </small>
            <?php endif; ?>

            <div class="upload-box text-center mb-2">

                <i class="far fa-file-alt upload-icon"></i>

                <h6 class="mt-3">
                    Choose your CV file
                </h6>

                <p>
                    Allowed file extensions:
                    pdf, doc, docx
                    <br>
                    Maximum file size is 10 MB
                </p>

                <input type="file"
                       name="cv_file"
                       class="form-control mt-3"
                       accept=".pdf,.doc,.docx">

            </div>

            <?php if ($savedCV): ?>

                <div class="alert alert-success mt-3 d-flex justify-content-between align-items-center">

                    <div>

                        <strong>Current CV:</strong>

                        <a href="../uploads/cvs/<?= htmlspecialchars($savedCV['cv_file']); ?>"
                           target="_blank">

                            <?= htmlspecialchars($savedCV['cv_file']); ?>

                        </a>

                        <br>

                        <small>
                            Language:
                            <?= htmlspecialchars($savedCV['cv_language']); ?>
                        </small>

                    </div>

                    <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            onclick="document.querySelector('[name=cv_file]').click()">

                        Replace

                    </button>

                </div>

            <?php endif; ?>

            <?php if (isset($errors['cv_file'])): ?>

                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['cv_file']); ?>
                </small>

            <?php endif; ?>

            <button type="submit"
                    class="btn btn-primary w-100 mt-4">

                Save & Next
                <i class="fa fa-arrow-right ms-2"></i>

            </button>

        </form>

        <div class="col-lg-3">

            <?php
            $current_step = 3;
            include("../../includes/setup-steps.php");
            ?>

        </div>

    </main>

</div>

<?php include("../../includes/scripts.php"); ?>