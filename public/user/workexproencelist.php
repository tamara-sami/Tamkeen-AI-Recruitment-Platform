<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-experience-functions.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_experience'])) {
    deleteExperience($conn, $user_id, $_POST['experience_id']);
    header("Location: workexproencelist.php");
    exit;
}

$experiences = getAllExperiences($conn, $user_id);

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
                <a href="bulidcv.php?new=1" class="text-primary text-decoration-none">
                    <i class="fa fa-arrow-left me-2"></i>Previous
                </a>

                <small class="text-muted">Step 2</small>
            </div>

            <h2>Work Experience</h2>

            <?php if (empty($experiences)): ?>

                <div class="alert alert-info rounded-0 small mb-4">
                    <i class="fa fa-info-circle me-2"></i>
                    No work experience added yet.
                </div>

            <?php else: ?>

                <?php foreach ($experiences as $exp): ?>
                    <div class="experience-box mb-4">
                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <h6 class="fw-bold mb-1">
                                    <?= htmlspecialchars($exp['job_title']); ?>
                                </h6>

                                <p class="text-muted mb-1">
                                    <?= htmlspecialchars($exp['company_name']); ?>
                                </p>

                                <?php if (!empty($exp['company_location'])): ?>
                                    <span class="text-primary small">
                                        <?= htmlspecialchars($exp['company_location']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-end">
                                <small class="fw-bold d-block mb-2">
                                    <?= htmlspecialchars(experienceDateText($exp)); ?>
                                </small>

                                <a href="bulidcv.php?edit=<?= (int)$exp['id']; ?>"
                                   class="text-muted small me-3 text-decoration-none">
                                    <i class="fa fa-edit me-1"></i>Edit
                                </a>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden"
                                           name="experience_id"
                                           value="<?= (int)$exp['id']; ?>">

                                    <button type="submit"
                                            name="delete_experience"
                                            value="1"
                                            class="text-muted small text-decoration-none border-0 bg-transparent p-0">
                                        <i class="fa fa-times me-1"></i>Delete
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

            <hr class="my-4" style="border-top:2px dashed #999;">

            <a href="bulidcv.php?new=1" class="btn btn-outline-primary mb-4">
                <i class="fa fa-plus me-2"></i>Add More Experiences
            </a>

            <a href="courses.php" class="btn btn-primary w-100">
                Proceed <i class="fa fa-arrow-right ms-2"></i>
            </a>

        </section>

        <div class="col-lg-3">
            <?php
            $current_cv_step = 2;
            include("../../includes/cv-steps.php");
            ?>
        </div>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>