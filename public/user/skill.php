<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-skills-functions.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$pageData = getSkillsPageData($conn, $user_id);

$edit_id = $pageData['edit_id'];
$savedData = $pageData['savedData'];
$skills = $pageData['skills'];
$errors = $pageData['errors'];
$old = $pageData['old'];
$levels = $pageData['levels'];
$years = $pageData['years'];

clearSkillsFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_skill'])) {
        header("Location: " . handleDeleteSkill($conn, $user_id));
        exit;
    }

    $result = handleSkillForm($conn, $user_id);

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
        <a href="courses.php" class="text-primary text-decoration-none">
            <i class="fa fa-arrow-left me-2"></i>Previous
        </a>

        <small class="text-muted">Step 4</small>

        <a href="languages.php" class="text-primary text-decoration-none">
            Skip <i class="fa fa-forward ms-1"></i>
        </a>
    </div>

    <h2>Skills</h2>

    <p class="text-muted mb-4">
        Add your professional skills to help employers know your strengths.
    </p>

    <?php foreach ($skills as $skill): ?>

        <div class="experience-box mb-3">

            <div class="d-flex justify-content-between align-items-start">

                <div>

                    <h6 class="fw-bold mb-1">
                        <?= htmlspecialchars($skill['skill_name']); ?>
                    </h6>

                    <p class="text-muted mb-1">
                        <?= htmlspecialchars($skill['skill_level']); ?>
                    </p>

                    <?php if (!empty($skill['years_experience'])): ?>
                        <small class="text-primary">
                            <?= htmlspecialchars($skill['years_experience']); ?>
                        </small>
                    <?php endif; ?>

                </div>

                <div class="text-end">

                    <a href="skill.php?edit=<?= (int)$skill['id']; ?>"
                       class="text-muted small me-3 text-decoration-none">
                        <i class="fa fa-edit me-1"></i>Edit
                    </a>

                    <form method="POST" style="display:inline;">

                        <input type="hidden"
                               name="skill_id"
                               value="<?= (int)$skill['id']; ?>">

                        <button type="submit"
                                name="delete_skill"
                                value="1"
                                class="text-muted small border-0 bg-transparent p-0">

                            <i class="fa fa-times me-1"></i>Delete

                        </button>

                    </form>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

    <form method="POST">

        <?php if ($edit_id): ?>
            <input type="hidden"
                   name="edit_id"
                   value="<?= htmlspecialchars($edit_id); ?>">
        <?php endif; ?>

        <div class="mb-2 input-group">

            <span class="input-group-text bg-white">
                <i class="fa fa-star text-primary"></i>
            </span>

            <input type="text"
                   name="skill_name"
                   class="form-control"
                   placeholder="Skill name *"
                   value="<?= formValue($old, $savedData, 'skill_name'); ?>"
                   required>

        </div>

        <?php if (isset($errors['skill_name'])): ?>
            <small class="text-danger d-block mb-3">
                <?= htmlspecialchars($errors['skill_name']); ?>
            </small>
        <?php endif; ?>

        <div class="mb-2">

            <label>Skill Level *</label>

            <select name="skill_level"
                    class="form-select"
                    required>

                <option value="">Select level</option>

                <?php foreach ($levels as $level): ?>

                    <option value="<?= htmlspecialchars($level); ?>"
                        <?= formSelected($old, $savedData, 'skill_level', $level); ?>>

                        <?= htmlspecialchars($level); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <?php if (isset($errors['skill_level'])): ?>
            <small class="text-danger d-block mb-3">
                <?= htmlspecialchars($errors['skill_level']); ?>
            </small>
        <?php endif; ?>

        <div class="mb-4">

            <label>Years of Experience</label>

            <select name="years_experience" class="form-select">

                <option value="">Select years</option>

                <?php foreach ($years as $year): ?>

                    <option value="<?= htmlspecialchars($year); ?>"
                        <?= formSelected($old, $savedData, 'years_experience', $year); ?>>

                        <?= htmlspecialchars($year); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="mb-4">

            <label>Description</label>

            <textarea name="description"
                      class="form-control"
                      rows="4"
                      placeholder="Example: Data analysis using Excel and Power BI"><?= formValue($old, $savedData, 'description'); ?></textarea>

        </div>

        <button type="submit"
                name="action"
                value="add_more"
                class="btn btn-outline-primary mb-4">

            <i class="fa fa-plus me-2"></i>
            Add Another Skill

        </button>

        <button type="submit"
                name="action"
                value="proceed"
                class="btn btn-primary w-100">

            Save & Proceed
            <i class="fa fa-arrow-right ms-2"></i>

        </button>

    </form>

</section>

<div class="col-lg-3">

    <?php
    $current_cv_step = 4;
    include("../../includes/cv-steps.php");
    ?>

</div>

</main>
</div>

<?php include("../../includes/scripts.php"); ?>