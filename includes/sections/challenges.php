<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . '/../functions/home_sections.php');
$tasks = getHomeChallenges($conn, 3);
?>

<div id="challenges" class="container-fluid py-5 wow fadeInUp">
    <div class="container py-5">
        <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width: 600px;">
            <h5 class="fw-bold text-primary text-uppercase">Real-World Challenges</h5>
            <h1 class="mb-0">Prove It. Don't Just Say It</h1>
        </div>

        <div class="row g-0">
            <?php foreach($tasks as $index => $task): ?>
                <?php
                    $taskId = (int)$task['id'];

                    if (!empty($_SESSION['job_seeker_id'])) {
                        $challengeLink = "user/task-workspace.php?id=" . $taskId;
                    } else {
                        $challengeLink = "user/login.php";
                    }
                ?>

                <div class="col-lg-4 wow slideInUp" data-wow-delay="<?= 0.3 * ($index + 1) ?>s">
                    <div class="<?= $index == 1 ? 'bg-white rounded shadow position-relative' : 'bg-light rounded' ?> h-100" <?= $index == 1 ? 'style="z-index: 1;"' : '' ?>>
                        <div class="border-bottom py-4 px-5 mb-4">
                            <h4 class="text-primary mb-1"><?= safe($task['category']) ?></h4>
                            <small class="text-uppercase"><?= safe($task['difficulty']) ?></small>
                        </div>
                        <div class="p-5 pt-0">
                            <h3 class="mb-3"><?= safe($task['title']) ?></h3>

                            <div class="d-flex justify-content-between mb-3">
                                <span><?= safe(firstSkill($task['required_skills'])) ?></span>
                                <i class="fa fa-check text-primary pt-1"></i>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span><?= safe(secondSkill($task['required_skills'])) ?></span>
                                <i class="fa fa-check text-primary pt-1"></i>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span><?= safe($task['estimated_time']) ?></span>
                                <i class="fa fa-clock text-primary pt-1"></i>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>+<?= safe($task['points']) ?> Points</span>
                                <i class="fa fa-star text-warning pt-1"></i>
                            </div>

                            <a href="<?= safe($challengeLink) ?>" class="btn btn-primary py-2 px-4 mt-4">Start Challenge</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
