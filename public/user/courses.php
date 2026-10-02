<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-courses-functions.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$pageData = getCoursesPageData($conn, $user_id);

$edit_id = $pageData['edit_id'];
$savedData = $pageData['savedData'];
$courses = $pageData['courses'];
$errors = $pageData['errors'];
$old = $pageData['old'];
$months = $pageData['months'];

clearCoursesFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_course'])) {
        header("Location: " . handleDeleteCourse($conn, $user_id));
        exit;
    }

    $result = handleCourseForm($conn, $user_id);

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
        <a href="workexproencelist.php" class="text-primary text-decoration-none">
            <i class="fa fa-arrow-left me-2"></i>Previous
        </a>

        <small class="text-muted">Step 3</small>

        <a href="skill.php" class="text-primary text-decoration-none">
            Skip <i class="fa fa-forward ms-1"></i>
        </a>
    </div>

    <h2>Courses</h2>

    <p class="text-muted mb-4">
        Add training courses, workshops, or online learning you have completed.
    </p>

    <?php foreach ($courses as $course): ?>

        <div class="experience-box mb-3">
            <div class="d-flex justify-content-between align-items-start">

                <div>
                    <h6 class="fw-bold mb-1">
                        <?= htmlspecialchars($course['course_title']); ?>
                    </h6>

                    <p class="text-muted mb-1">
                        <?= htmlspecialchars($course['course_provider']); ?>
                    </p>

                    <small class="text-primary">
                        <?= htmlspecialchars(courseDateText($course)); ?>
                    </small>
                </div>

                <div class="text-end">

                    <a href="courses.php?edit=<?= (int)$course['id']; ?>"
                       class="text-muted small me-3 text-decoration-none">
                        <i class="fa fa-edit me-1"></i>Edit
                    </a>

                    <form method="POST" style="display:inline;">
                        <input type="hidden"
                               name="course_id"
                               value="<?= (int)$course['id']; ?>">

                        <button type="submit"
                                name="delete_course"
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
                <i class="fa fa-book text-primary"></i>
            </span>

            <input type="text"
                   name="course_title"
                   class="form-control"
                   placeholder="Course title *"
                   value="<?= formValue($old, $savedData, 'course_title'); ?>"
                   required>
        </div>

        <?php if (isset($errors['course_title'])): ?>
            <small class="text-danger d-block mb-3">
                <?= htmlspecialchars($errors['course_title']); ?>
            </small>
        <?php endif; ?>

        <div class="mb-2 input-group">
            <span class="input-group-text bg-white">
                <i class="fa fa-building text-primary"></i>
            </span>

            <input type="text"
                   name="course_provider"
                   class="form-control"
                   placeholder="Course provider *"
                   value="<?= formValue($old, $savedData, 'course_provider'); ?>"
                   required>
        </div>

        <?php if (isset($errors['course_provider'])): ?>
            <small class="text-danger d-block mb-3">
                <?= htmlspecialchars($errors['course_provider']); ?>
            </small>
        <?php endif; ?>

        <label>Accomplish Date *</label>

        <div class="row g-3 mb-2">

            <div class="col-md-6">
                <select name="course_month" class="form-select" required>
                    <option value="">Month</option>

                    <?php foreach ($months as $month): ?>
                        <option value="<?= htmlspecialchars($month); ?>"
                            <?= formSelected($old, $savedData, 'course_month', $month); ?>>
                            <?= htmlspecialchars($month); ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="col-md-6">
                <select name="course_year" class="form-select" required>
                    <option value="">Year</option>

                    <?php for ($year = 2026; $year >= 1980; $year--): ?>
                        <option value="<?= $year; ?>"
                            <?= formSelected($old, $savedData, 'course_year', $year); ?>>
                            <?= $year; ?>
                        </option>
                    <?php endfor; ?>

                </select>
            </div>

        </div>

        <?php if (isset($errors['course_date'])): ?>
            <small class="text-danger d-block mb-3">
                <?= htmlspecialchars($errors['course_date']); ?>
            </small>
        <?php endif; ?>

        <div class="mb-4 input-group">
            <span class="input-group-text bg-white align-items-start pt-3">
                <i class="fa fa-quote-left text-primary"></i>
            </span>

            <textarea name="description"
                      class="form-control"
                      rows="4"
                      placeholder="Description"><?= formValue($old, $savedData, 'description'); ?></textarea>
        </div>

        <button type="submit"
                name="action"
                value="add_more"
                class="btn btn-outline-primary mb-4">
            <i class="fa fa-plus me-2"></i>Add Another Course
        </button>

        <button type="submit"
                name="action"
                value="proceed"
                class="btn btn-primary w-100">
            Save & Proceed <i class="fa fa-arrow-right ms-2"></i>
        </button>

    </form>

</section>

<div class="col-lg-3">
    <?php
    $current_cv_step = 3;
    include("../../includes/cv-steps.php");
    ?>
</div>

</main>
</div>

<?php include("../../includes/scripts.php"); ?>