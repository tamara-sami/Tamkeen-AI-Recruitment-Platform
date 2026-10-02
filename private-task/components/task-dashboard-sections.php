<?php

if (!function_exists('renderTaskDashboardPage')) {

    function renderTaskDashboardPage(array $page)
    {
        extract($page);
?>

<main class="container py-5">
    <div class="row g-4">

        <div class="col-lg-8">

            <div class="company-banner p-4 p-lg-5 mb-4">
                <div class="position-relative" style="z-index:2;">
                    <h3 class="fw-bold text-white mb-2">Hire by proof, not by claims.</h3>
                    <p class="mb-4" style="color:rgba(255,255,255,.85);">
                        Post real tasks, receive verified submissions, and let AI recommend the strongest candidates.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="/tamkeentest/public/task/post-task.php" class="btn btn-light rounded-pill px-4 fw-bold">
    <?= $totalTasks == 0 ? 'Post Your First Task' : 'Add Task' ?>
    <i class="fa fa-plus ms-2"></i>
</a>
                        <a href="/tamkeentest/public/task/ai-review.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                            View AI Ranking
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="company-card p-4 text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= $activeTasks ?></h3>
                        <small class="text-muted">Active Tasks</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= $draftTasks ?></h3>
                        <small class="text-muted">Drafts</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= $totalTasks ?></h3>
                        <small class="text-muted">Total Tasks</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center">
                        <h3 class="fw-bold text-primary mb-0">AI</h3>
                        <small class="text-muted">Ranking Ready</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <h4 class="fw-bold mb-0">Company Tasks</h4>

               <div class="task-tabs">
    <a href="?filter=all" class="<?= $filter == 'all' ? 'active' : '' ?>">All Tasks</a>
    <a href="?filter=active" class="<?= $filter == 'active' ? 'active' : '' ?>">Active</a>
    <a href="?filter=draft" class="<?= $filter == 'draft' ? 'active' : '' ?>">Drafts</a>
</div>
            </div>

            <?php if (count($tasks) == 0): ?>
                <div class="company-card p-5 text-center mb-4">
                    <i class="fa fa-tasks fa-3x text-primary mb-3"></i>

                    <h5 class="fw-bold">No tasks posted yet</h5>

                    <p class="text-muted mb-4">
                        Start by posting a real-world task. Candidates will solve it, and AI will help you rank the best performers.
                    </p>

                    <a href="/tamkeentest/public/company/post-task.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Add Task <i class="fa fa-plus ms-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-3 mb-4">
                    <?php foreach ($tasks as $task): ?>
                        <div class="col-md-6">
                            <div class="company-card p-4 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">
                                        <?= htmlspecialchars($task['title']) ?>
                                    </h5>

                                    <span class="badge <?= $task['status'] == 'published' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars($task['status']) ?>
                                    </span>
                                </div>

                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars($task['description']) ?>
                                </p>

                                <div class="mb-3 small">
                                    <div><strong>Category:</strong> <?= htmlspecialchars($task['category']) ?></div>
                                    <div><strong>Difficulty:</strong> <?= htmlspecialchars($task['difficulty']) ?></div>
                                    <div><strong>Time:</strong> <?= htmlspecialchars($task['estimated_time']) ?></div>
                                    <div><strong>Points:</strong> <?= htmlspecialchars($task['points']) ?></div>
                                    <div><strong>Deadline:</strong> <?= htmlspecialchars($task['deadline']) ?></div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                  <a href="/tamkeentest/public/task/my-tasks.php" class="btn btn-sm btn-outline-primary rounded-pill">
    Manage
</a>

<?php if ($task['status'] == 'draft'): ?>
    <a href="/tamkeentest/public/company/my-tasks.php" class="btn btn-sm btn-success rounded-pill">
        Publish
    </a>
<?php endif; ?>
                                  
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="company-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                    <span class="activity-badge">Live</span>
                </div>

                <?php if (count($recentTasks) == 0): ?>
                    <p class="text-muted mb-0">No recent task activity yet.</p>
                <?php else: ?>
                    <?php foreach ($recentTasks as $task): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fa fa-tasks"></i>
                            </div>

                            <div class="activity-content">
                                <strong><?= htmlspecialchars($task['title']) ?></strong>
                                <p class="mb-1">
                                    Task status:
                                    <span><?= htmlspecialchars($task['status']) ?></span>
                                </p>
                                <small><?= htmlspecialchars($task['created_at']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="company-card p-4">
                <h4 class="fw-bold mb-4">Task Tracker</h4>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="tracker-box rounded-4 p-3 h-100">
                            <h6 class="fw-bold">Submission Status</h6>
                            <p class="text-muted small mb-0">
                                Track who submitted, who is pending, and who completed tasks on time.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="tracker-box rounded-4 p-3 h-100">
                            <h6 class="fw-bold">AI Summary</h6>
                          <p class="text-muted small mb-1">
    Total submissions:
    <strong><?= (int)$aiSummary['total_attempts'] ?></strong>
</p>

<p class="text-success small mb-1">
    Recommended:
    <strong><?= (int)$aiSummary['recommended_count'] ?></strong>
</p>

<p class="text-warning small mb-1">
    Needs Review:
    <strong><?= (int)$aiSummary['review_count'] ?></strong>
</p>

<p class="text-primary small mb-0">
    Top AI Score:
    <strong><?= (int)$aiSummary['top_score'] ?></strong>
</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="tracker-box rounded-4 p-3 h-100">
                            <h6 class="fw-bold">Shortlist Talent</h6>
                            <p class="text-muted small mb-0">
                                Save top candidates directly from task performance results.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php include(__DIR__ . "/../../includes/task-side.php"); ?>

    </div>
</main>

<?php
    }
}
