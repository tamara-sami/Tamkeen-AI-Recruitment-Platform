<?php

if (!function_exists('renderTaskTrackerPage')) {

    function renderTaskTrackerPage(array $page)
    {
        extract($page);
?>

<main class="container py-5">

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h3 class="fw-bold mb-2">Task Tracker</h3>
                <p class="text-muted mb-0">
                    Track your task progress, deadlines, drafts, and published tasks in one place.
                </p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= $totalTasks ?></h3>
                        <small class="text-muted">Total</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-success mb-0"><?= $publishedTasks ?></h3>
                        <small class="text-muted">Published</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-secondary mb-0"><?= $draftTasks ?></h3>
                        <small class="text-muted">Drafts</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-danger mb-0"><?= $overdueTasks ?></h3>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0">Publishing Progress</h5>
                    <strong><?= $progress ?>%</strong>
                </div>

                <div class="progress rounded-pill" style="height: 12px;">
                    <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                </div>

                <small class="text-muted">
                    <?= $publishedTasks ?> of <?= $totalTasks ?> tasks are published.
                </small>
            </div>

            <div class="bg-white rounded-4 shadow-sm border">

                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <h4 class="fw-bold mb-0">Task Timeline</h4>

                        <div class="task-tabs">
                            <a href="/tamkeentest/public/task/task-tracker.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
                            <a href="/tamkeentest/public/task/task-tracker.php?filter=published" class="<?= $filter === 'published' ? 'active' : '' ?>">Published</a>
                            <a href="/tamkeentest/public/task/task-tracker.php?filter=draft" class="<?= $filter === 'draft' ? 'active' : '' ?>">Drafts</a>
                            <a href="/tamkeentest/public/task/task-tracker.php?filter=overdue" class="<?= $filter === 'overdue' ? 'active' : '' ?>">Overdue</a>
                        </div>
                    </div>

                    <div class="position-relative">
    <input 
        type="text" 
        id="taskSearch"
        class="form-control rounded-pill"
        placeholder="Search tasks by title..."
        autocomplete="off"
    >

    <div id="searchSuggestions" 
         class="bg-white border rounded-4 shadow-sm position-absolute w-100 mt-2 d-none"
         style="z-index:999;">
    </div>
</div>
                </div>

                <?php if (empty($tasks)): ?>

                    <div class="p-5 text-center">
                        <i class="fa fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold">No tasks to track</h5>
                        <p class="text-muted">Create tasks first to start tracking progress.</p>
                        <a href="/tamkeentest/public/task/post-task.php" class="btn btn-primary rounded-pill px-4">
                            Post New Task
                        </a>
                    </div>

                <?php else: ?>

                    <?php foreach ($tasks as $task): ?>
                        <?php
                            $isOverdue = !empty($task['deadline']) && $task['deadline'] < date('Y-m-d');
                            $daysLeft = !empty($task['deadline'])
                                ? floor((strtotime($task['deadline']) - strtotime(date('Y-m-d'))) / 86400)
                                : null;
                        ?>

<div class="p-4 border-bottom task-post-item"
     data-title="<?= strtolower(htmlspecialchars($task['title'])) ?>">                            <div class="d-flex gap-3">

                                <div>
                                    <div style="width:54px;height:54px;background:#eef3ff;border-radius:16px;"
                                         class="d-flex align-items-center justify-content-center">
                                        <?php if ($isOverdue): ?>
                                            <i class="fa fa-exclamation-triangle text-danger"></i>
                                        <?php elseif ($task['status'] === 'published'): ?>
                                            <i class="fa fa-check text-success"></i>
                                        <?php else: ?>
                                            <i class="fa fa-pencil-alt text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <h5 class="fw-bold mb-1">
                                                <?= htmlspecialchars($task['title'] ?: 'Untitled Task') ?>
                                            </h5>

                                            <p class="text-muted mb-1">
                                                <?= htmlspecialchars($task['category'] ?: 'No category') ?> ·
                                                <?= htmlspecialchars($task['difficulty'] ?: 'No difficulty') ?> ·
                                                <?= htmlspecialchars($task['estimated_time'] ?: 'No time') ?>
                                            </p>

                                            <small class="text-muted">
                                                Points: <?= htmlspecialchars($task['points'] ?: '-') ?>
                                            </small>
                                        </div>

                                        <div class="text-end">
                                            <?php if ($task['status'] === 'published'): ?>
                                                <span class="badge bg-success rounded-pill px-3 py-2">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">Draft</span>
                                            <?php endif; ?>

                                            <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger rounded-pill px-3 py-2">Overdue</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <span class="<?= $isOverdue ? 'text-danger' : 'text-muted' ?>">
                                            <i class="fa fa-calendar me-1"></i>
                                            Deadline: <?= htmlspecialchars($task['deadline'] ?: '-') ?>
                                        </span>

                                        <?php if ($daysLeft !== null): ?>
                                            <?php if ($daysLeft < 0): ?>
                                                <strong class="text-danger"><?= abs($daysLeft) ?> days late</strong>
                                            <?php elseif ($daysLeft == 0): ?>
                                                <strong class="text-warning">Due today</strong>
                                            <?php else: ?>
                                                <strong class="text-success"><?= $daysLeft ?> days left</strong>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <a href="/tamkeentest/public/task/my-tasks.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Manage
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

        <div class="col-lg-4">
            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h5 class="fw-bold mb-3">Tracker Summary</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span>Upcoming</span>
                    <strong><?= $upcomingTasks ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Overdue</span>
                    <strong class="text-danger"><?= $overdueTasks ?></strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Drafts</span>
                    <strong><?= $draftTasks ?></strong>
                </div>

                <div class="d-flex justify-content-between">
                    <span>Published</span>
                    <strong><?= $publishedTasks ?></strong>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h5 class="fw-bold mb-3">Smart Suggestions</h5>

                <?php if ($draftTasks > 0): ?>
                    <p class="text-muted small">
                        You have <?= $draftTasks ?> draft task(s). Review and publish them when ready.
                    </p>
                <?php endif; ?>

                <?php if ($overdueTasks > 0): ?>
                    <p class="text-danger small">
                        <?= $overdueTasks ?> task(s) are overdue. Update deadlines or archive old tasks.
                    </p>
                <?php endif; ?>

                <?php if ($totalTasks == 0): ?>
                    <p class="text-muted small">
                        Start by posting your first task to begin tracking progress.
                    </p>
                <?php endif; ?>

                <?php if ($totalTasks > 0 && $overdueTasks == 0): ?>
                    <p class="text-success small">
                        Great! Your task timeline looks organized.
                    </p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4">
                <a href="/tamkeentest/public/task/post-task.php" class="btn btn-primary w-100 rounded-pill fw-bold mb-2">
                    <i class="fa fa-plus me-2"></i>Add Task
                </a>

                <a href="/tamkeentest/public/task/my-tasks.php" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                    Manage Tasks
                </a>
            </div>
        </div>

    </div>

</main>

<?php
    }
}
