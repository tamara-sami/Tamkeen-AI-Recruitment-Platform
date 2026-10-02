<?php
include_once(__DIR__ . '/../functions/home_sections.php');
$trainings = getHomeTraining($conn, 3);

if (!function_exists('homeTrainingSectionLink')) {
    function homeTrainingSectionLink($training)
    {
        if (function_exists('homeTrainingLink')) {
            return homeTrainingLink($training);
        }

        $isLoggedInUser =
            !empty($_SESSION['job_seeker_id']) ||
            (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') ||
            (!empty($_SESSION['role']) && $_SESSION['role'] === 'user');

        if (!$isLoggedInUser) {
            return "user/login.php";
        }

        $id = (int)($training['id'] ?? 0);
        $title = urlencode((string)($training['title'] ?? ''));

        return "user/trainings.php?search={$title}#training-{$id}";
    }
}
?>

<div id="jobs" class="container-fluid py-5 wow fadeInUp">
    <div class="container py-5">
        <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width:700px;">
            <h5 class="fw-bold text-primary text-uppercase">Training Opportunities</h5>
            <h1 class="mb-0">Training That Builds Your Actual Skills</h1>
            <p class="mt-3 text-muted">Explore real training opportunities, apply, and improve your verified skill profile.</p>
        </div>

        <div class="row g-4">
            <?php foreach($trainings as $index => $training): ?>
                <?php
                    $duration = max(1, (int)($training['duration'] ?? 1));
                    $score = min(100, max(10, $duration * 5));
                    $trainingType = ($training['training_type'] ?? '') === 'university' ? 'University Training' : 'Voluntary Training';
                    $trainingLink = homeTrainingSectionLink($training);
                ?>
                <div class="col-lg-4 wow zoomIn" data-wow-delay="<?= 0.2 * ($index + 1) ?>s">
                    <div class="job-card home-training-card bg-white shadow-sm rounded p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= safe($training['title']) ?></h5>
                                <small class="text-muted">
                                    <?= safe($training['company_name'] ?? 'Training Provider') ?> · <?= safe($training['field']) ?>
                                </small>
                            </div>
                            <span class="badge bg-primary px-3 py-2"><?= safe($trainingType) ?></span>
                        </div>

                        <div class="mb-4">
                            <span class="badge bg-light text-dark me-2 mb-2"><?= safe(firstSkill($training['required_skills'])) ?></span>
                            <span class="badge bg-light text-dark me-2 mb-2"><?= safe(secondSkill($training['required_skills'])) ?></span>
                            <span class="badge bg-light text-primary mb-2"><?= safe($duration) ?> Days</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Training duration</small>
                            <strong class="text-primary"><?= safe($duration) ?> Days</strong>
                        </div>

                        <div class="progress mb-4" style="height:8px;">
                            <div class="progress-bar bg-primary" style="width:<?= $score ?>%;"></div>
                        </div>

                        <a href="<?= safe($trainingLink) ?>" class="btn btn-outline-primary w-100 home-training-btn">Start Training</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<style>
.home-training-card::before {
    pointer-events: none !important;
}
.home-training-card > * {
    position: relative;
    z-index: 2;
}
.home-training-btn {
    position: relative;
    z-index: 10;
    pointer-events: auto;
}
</style>

