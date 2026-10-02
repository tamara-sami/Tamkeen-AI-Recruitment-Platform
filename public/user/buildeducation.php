<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-education-functions.php");

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$savedData = getJobSeekerEducation(
    $conn,
    $user_id
);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = handleEducationForm(
        $conn,
        $user_id,
        $savedData
    );

    if ($result['success']) {

        header("Location: " . $result['redirect']);
        exit;
    }

    $errors = $result['errors'];
}
$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

    <?php
    $current_sidebar = 5;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="setup-main">

        <section class="setup-form">

            <div class="d-flex justify-content-between mb-4">

                <a href="ProfileImage.php"
                   class="text-primary text-decoration-none">

                    <i class="fa fa-arrow-left me-2"></i>
                    Previous
                </a>

                <small class="text-muted">Step 5</small>

                <a href="bulidcv.php"
                   class="text-primary text-decoration-none">

                    Skip
                    <i class="fa fa-forward ms-1"></i>
                </a>

            </div>

            <h2>Education Information</h2>

            <form method="POST">

                <label>Degree *</label>

                <select name="degree"
                        class="form-select mb-2"
                        required>

                    <option value="">Select degree</option>

                    <?php
                    $degrees = [
                        "University (Bachelor)",
                        "Diploma",
                        "Master",
                        "PhD"
                    ];

                    $selectedDegree =
                        $_POST['degree'] ??
                        ($savedData['degree'] ?? '');

                    foreach ($degrees as $degreeOption):
                    ?>

                        <option value="<?= htmlspecialchars($degreeOption); ?>"
                            <?= $selectedDegree === $degreeOption ? 'selected' : ''; ?>>

                            <?= htmlspecialchars($degreeOption); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <?php if (isset($errors['degree'])): ?>

                    <small class="text-danger d-block mb-3">
                        <?= htmlspecialchars($errors['degree']); ?>
                    </small>

                <?php endif; ?>

                <div class="mb-2 input-group">

                    <span class="input-group-text bg-white">
                        <i class="fa fa-university text-primary"></i>
                    </span>

                    <input type="text"
                           name="institution"
                           class="form-control"
                           placeholder="Faculty and University / Institute name *"
                           value="<?= htmlspecialchars($_POST['institution'] ?? ($savedData['institution'] ?? '')); ?>"
                           minlength="2"
                           maxlength="120"
                           pattern="^[A-Za-z0-9\s&.,()'-]+$"
                           required>

                </div>

                <?php if (isset($errors['institution'])): ?>

                    <small class="text-danger d-block mb-3">
                        <?= htmlspecialchars($errors['institution']); ?>
                    </small>

                <?php endif; ?>

                <div class="mb-2 input-group">

                    <span class="input-group-text bg-white">
                        <i class="fa fa-book text-primary"></i>
                    </span>

                    <input type="text"
                           name="field"
                           class="form-control"
                           placeholder="Field *"
                           value="<?= htmlspecialchars($_POST['field'] ?? ($savedData['field'] ?? '')); ?>"
                           minlength="2"
                           maxlength="80"
                           pattern="^[A-Za-z\s&.,()'-]+$"
                           required>

                </div>

                <?php if (isset($errors['field'])): ?>

                    <small class="text-danger d-block mb-3">
                        <?= htmlspecialchars($errors['field']); ?>
                    </small>

                <?php endif; ?>

                <div class="row g-4 mb-4">

                    <div class="col-md-6">

                        <label>Graduation Year *</label>

                        <select name="graduation_year"
                                class="form-select"
                                required>

                            <option value="">Select year</option>

                            <?php
                            $selectedYear =
                                $_POST['graduation_year'] ??
                                ($savedData['graduation_year'] ?? '');

                            for ($year = 2032; $year >= 1980; $year--):
                            ?>

                                <option value="<?= $year; ?>"
                                    <?= (string)$selectedYear === (string)$year ? 'selected' : ''; ?>>

                                    <?= $year; ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                        <?php if (isset($errors['graduation_year'])): ?>

                            <small class="text-danger">
                                <?= htmlspecialchars($errors['graduation_year']); ?>
                            </small>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-6">

                        <label>Grade *</label>

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="fa fa-award text-primary"></i>
                            </span>

                            <input type="text"
                                   name="grade"
                                   class="form-control"
                                   placeholder="Grade *"
                                   value="<?= htmlspecialchars($_POST['grade'] ?? ($savedData['grade'] ?? '')); ?>"
                                   maxlength="50"
                                   required>

                        </div>

                        <?php if (isset($errors['grade'])): ?>

                            <small class="text-danger">
                                <?= htmlspecialchars($errors['grade']); ?>
                            </small>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="alert alert-info rounded-0 small mb-4">

                    <i class="fa fa-info-circle me-2"></i>

                    If you didn't graduate,
                    choose your expected year

                </div>

                <button type="submit"
                        class="btn btn-primary w-100">

                    Proceed
                    <i class="fa fa-arrow-right ms-2"></i>

                </button>

            </form>

        </section>

        <div class="col-lg-3">

            <?php
            $current_cv_step = 1;
            include("../../includes/cv-steps.php");
            ?>

        </div>

    </main>

</div>

<?php include("../../includes/scripts.php"); ?>