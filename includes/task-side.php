<aside class="col-lg-4">
    <div class="side-sticky">

        <div class="company-card p-4 mb-4 text-center">
            <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/post-task.php', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5">
                Post New Task <i class="fa fa-plus ms-2"></i>
            </a>
        </div>

        <div class="company-card p-4 mb-4">
          <div class="company-menu">
    <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/my-tasks.php', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-tasks"></i> My Tasks</a>
    <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/post-task.php', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-plus-square"></i> Add Task</a>
    <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/ai-review.php', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-robot"></i> AI Candidate Ranking</a>
    <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/task-tracker.php', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-chart-line"></i> Task Tracker</a>
    <a href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'logout.php', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
        </div>

        <div class="company-card p-4 mb-4">

    <h5 class="fw-bold mb-3">
        AI Suggested Top Talent
    </h5>

    <?php if (empty($topTalents)): ?>

        <p class="text-muted small mb-0">
            No AI rankings available yet.
        </p>

    <?php else: ?>

        <?php foreach ($topTalents as $talent): ?>

            <div class="d-flex align-items-center gap-3 mb-3">

                <div class="ai-score">
                    <?= (int)$talent['ai_score'] ?>
                </div>

                <div>
                    <h6 class="fw-bold mb-0">
                        <?= htmlspecialchars($talent['full_name']) ?>
                    </h6>

                    <small class="text-muted">
                        <?= htmlspecialchars($talent['job_title'] ?? 'Candidate') ?>
                        ·
                        <?= htmlspecialchars($talent['ai_status']) ?>
                    </small>
                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <a 
        href="<?= htmlspecialchars(($base_url ?? '/tamkeentest/') . 'public/task/ai-review.php', ENT_QUOTES, 'UTF-8') ?>" 
        class="btn btn-outline-primary w-100 rounded-pill mt-4 fw-bold"
    >
        View Full Ranking
    </a>

</div>
    </div>
</aside>