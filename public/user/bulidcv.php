<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-experience-functions.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$pageData = getExperiencePageData($conn, $user_id);

$edit_id = $pageData['edit_id'];
$savedData = $pageData['savedData'];
$errors = $pageData['errors'];
$old = $pageData['old'];
$months = $pageData['months'];

clearExperienceFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handleExperienceForm($conn, $user_id);

    header("Location: " . $result['redirect']);
    exit;
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

        <section class="setup-form">

            <div class="d-flex justify-content-between mb-4">
                <a href="buildeducation.php" class="text-primary text-decoration-none">
                    <i class="fa fa-arrow-left me-2"></i>Previous
                </a>

                <small class="text-muted">Step 2</small>

                <a href="workexproencelist.php" class="text-primary text-decoration-none">
                    Skip <i class="fa fa-forward ms-1"></i>
                </a>
            </div>

            <h2>Work Experience</h2>

            <p class="text-muted mb-4">
                Enter your career history, companies or places you've worked with.
            </p>

            <form method="POST">

                <?php if ($edit_id): ?>
                    <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_id); ?>">
                <?php endif; ?>

                <label>Job Title *</label>
                <input type="text"
                       name="job_title"
                       class="form-control mb-2"
                       value="<?= formValue($old, $savedData, 'job_title'); ?>"
                       required>

                <?php if (isset($errors['job_title'])): ?>
                    <small class="text-danger d-block mb-3">
                        <?= htmlspecialchars($errors['job_title']); ?>
                    </small>
                <?php endif; ?>

                <label>Company Name *</label>
                <input type="text"
                       name="company_name"
                       class="form-control mb-2"
                       value="<?= formValue($old, $savedData, 'company_name'); ?>"
                       required>

                <?php if (isset($errors['company_name'])): ?>
                    <small class="text-danger d-block mb-3">
                        <?= htmlspecialchars($errors['company_name']); ?>
                    </small>
                <?php endif; ?>

                <label>Company Location</label>
                <input type="text"
                       name="company_location"
                       class="form-control mb-4"
                       value="<?= formValue($old, $savedData, 'company_location'); ?>">

                <div class="row g-4">

                    <div class="col-md-6 mb-4">
                        <label>From Date *</label>

                        <div class="row g-2">
                            <div class="col-6">
                                <select name="from_month" class="form-select" required>
                                    <option value="">Month</option>

                                    <?php foreach ($months as $month): ?>
                                        <option value="<?= htmlspecialchars($month); ?>"
                                            <?= formSelected($old, $savedData, 'from_month', $month); ?>>
                                            <?= htmlspecialchars($month); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <select name="from_year" class="form-select" required>
                                    <option value="">Year</option>

                                    <?php for ($year = 2026; $year >= 1980; $year--): ?>
                                        <option value="<?= $year; ?>"
                                            <?= formSelected($old, $savedData, 'from_year', $year); ?>>
                                            <?= $year; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (isset($errors['from_date'])): ?>
                            <small class="text-danger d-block mt-2">
                                <?= htmlspecialchars($errors['from_date']); ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label>To Date</label>

                        <div class="row g-2">
                            <div class="col-6">
                                <select name="to_month" id="toMonth" class="form-select">
                                    <option value="">Month</option>

                                    <?php foreach ($months as $month): ?>
                                        <option value="<?= htmlspecialchars($month); ?>"
                                            <?= formSelected($old, $savedData, 'to_month', $month); ?>>
                                            <?= htmlspecialchars($month); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6">
                                <select name="to_year" id="toYear" class="form-select">
                                    <option value="">Year</option>

                                    <?php for ($year = 2026; $year >= 1980; $year--): ?>
                                        <option value="<?= $year; ?>"
                                            <?= formSelected($old, $savedData, 'to_year', $year); ?>>
                                            <?= $year; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (isset($errors['to_date'])): ?>
                            <small class="text-danger d-block mt-2">
                                <?= htmlspecialchars($errors['to_date']); ?>
                            </small>
                        <?php endif; ?>

                        <?php if (isset($errors['date'])): ?>
                            <small class="text-danger d-block mt-2">
                                <?= htmlspecialchars($errors['date']); ?>
                            </small>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="form-check mb-4 text-end">
                    <input class="form-check-input"
                           type="checkbox"
                           name="is_present"
                           value="1"
                           id="present"
                           <?= formChecked($old, $savedData, 'is_present'); ?>>

                    <label class="form-check-label" for="present">
                        To Present
                    </label>
                </div>

                <label>Description</label>
                <textarea name="description"
                          class="form-control mb-4"
                          rows="4"><?= formValue($old, $savedData, 'description'); ?></textarea>

                <button type="submit" class="btn btn-primary w-100">
                    Proceed <i class="fa fa-arrow-right ms-2"></i>
                </button>

            </form>

        </section>

        <div class="col-lg-3">
            <?php
            $current_cv_step = 2;
            include("../../includes/cv-steps.php");
            ?>
        </div>

    </main>
</div>

<script>
const present = document.getElementById("present");
const toMonth = document.getElementById("toMonth");
const toYear = document.getElementById("toYear");

function togglePresent() {
    if (present.checked) {
        toMonth.value = "";
        toYear.value = "";
        toMonth.disabled = true;
        toYear.disabled = true;
    } else {
        toMonth.disabled = false;
        toYear.disabled = false;
    }
}

present.addEventListener("change", togglePresent);
togglePresent();
</script>

<?php include("../../includes/scripts.php"); ?>