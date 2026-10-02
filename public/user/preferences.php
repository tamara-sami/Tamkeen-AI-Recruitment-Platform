<?php
include("../../config.php");
include("../functionsuser/user-preferences-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: register.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$errors = [];
$old = getJobSeekerSavedPreferences($conn, $user_id) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveJobSeekerPreferences($conn, $_POST, $user_id);

    if ($result['success']) {
        header("Location: uploadcv.php");
        exit;
    }

    $errors = $result['errors'];
    $old = $_POST;
}

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

    <?php
    $current_sidebar = 2;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="setup-main">

        <form method="POST" class="setup-form">

            <div class="d-flex justify-content-between mb-4">
                <a href="setup-profile.php" class="text-primary text-decoration-none">
                    <i class="fa fa-arrow-left me-2"></i>Previous
                </a>

                <small class="text-muted">Step 2</small>

                <a href="uploadcv.php" class="text-primary text-decoration-none">
                    Skip <i class="fa fa-forward ms-1"></i>
                </a>
            </div>

            <h2>Availability & Preferences</h2>

            <label>Job Status *</label>
            <select name="job_status" class="form-select mb-2" required>
                <option value="">Select job status</option>
                <option value="unemployed" <?= ($old['job_status'] ?? '') === 'unemployed' ? 'selected' : '' ?>>
                    Unemployed. Looking for a job
                </option>
                <option value="working_open" <?= ($old['job_status'] ?? '') === 'working_open' ? 'selected' : '' ?>>
                    Working but looking for new opportunities
                </option>
                <option value="not_looking" <?= ($old['job_status'] ?? '') === 'not_looking' ? 'selected' : '' ?>>
                    Not looking for a job
                </option>
            </select>
            <?php if (isset($errors['job_status'])): ?>
                <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['job_status']); ?></small>
            <?php endif; ?>

            <label>Years of Experience *</label>
            <input type="number"
                   name="years_experience"
                   class="form-control mb-2"
                   min="0"
                   max="60"
                   value="<?= htmlspecialchars($old['years_experience'] ?? ''); ?>"
                   required>
            <?php if (isset($errors['years_experience'])): ?>
                <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['years_experience']); ?></small>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100">
                Next Step <i class="fa fa-arrow-right ms-2"></i>
            </button>

        </form>

        <div class="col-lg-3">
            <?php
            $current_step = 2;
            include("../../includes/setup-steps.php");
            ?>
        </div>

    </main>

</div>

<?php include("../../includes/scripts.php"); ?>
