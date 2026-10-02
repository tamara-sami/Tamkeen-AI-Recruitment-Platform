<?php

function renderTrainingDashboardPage($page)
{
    extract($page);

    $base_url = $base_url ?? '../../';

    $activeTrainings = (int)($stats['active_count'] ?? 0);
    $draftTrainings = (int)($stats['draft_count'] ?? 0);
    $pendingTrainings = (int)($stats['pending_count'] ?? 0);
    $rejectedTrainings = (int)($stats['rejected_count'] ?? 0);
    $totalTrainings = (int)($stats['total_count'] ?? 0);
?>

<main class="container py-5">

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="company-banner p-4 p-lg-5 mb-4">

                <div class="position-relative" style="z-index:2;">

                    <span class="badge bg-light text-primary rounded-pill px-3 py-2 mb-3">
                        Training Dashboard
                    </span>

                    <h3 class="fw-bold text-white mb-2">
                        Build future talent through real training.
                    </h3>

                    <p class="mb-4" style="color:rgba(255,255,255,.85);">
                        Publish training opportunities, help students gain experience,
                        and manage internships in one place.
                    </p>

                    <div class="d-flex flex-wrap gap-3">

                        <a href="post-training.php"
                           class="btn btn-light rounded-pill px-4 fw-bold">

                            <?= $totalTrainings === 0 ? 'Post Your First Training' : 'Add Training' ?>

                            <i class="fa fa-plus ms-2"></i>
                        </a>

                        <a href="my-trainings.php"
                           class="btn btn-outline-light rounded-pill px-4 fw-bold">

                            Manage Trainings
                        </a>

                    </div>

                </div>

            </div>

            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="company-card p-4 text-center h-100">
                        <h3 class="fw-bold text-primary mb-0"><?= $totalTrainings ?></h3>
                        <small class="text-muted">Total Trainings</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center h-100">
                        <h3 class="fw-bold text-success mb-0"><?= $activeTrainings ?></h3>
                        <small class="text-muted">Active Trainings</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center h-100">
                        <h3 class="fw-bold text-warning mb-0"><?= $pendingTrainings ?></h3>
                        <small class="text-muted">Pending Approval</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="company-card p-4 text-center h-100">
                        <h3 class="fw-bold text-secondary mb-0"><?= $draftTrainings ?></h3>
                        <small class="text-muted">Draft Trainings</small>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

                <div>
                    <h4 class="fw-bold mb-1">
                        Company Trainings
                    </h4>

                    <small class="text-muted">
                        <?= training_e($company_name ?? 'Company') ?>
                    </small>
                </div>

                <div class="task-tabs">

                    <a href="?filter=all"
                       class="<?= $filter === 'all' ? 'active' : '' ?>">
                        All Trainings
                    </a>

                    <a href="?filter=active"
                       class="<?= $filter === 'active' ? 'active' : '' ?>">
                        Active
                    </a>

                    <a href="?filter=draft"
                       class="<?= $filter === 'draft' ? 'active' : '' ?>">
                        Drafts
                    </a>

                    <a href="?filter=pending_approval"
                       class="<?= $filter === 'pending_approval' ? 'active' : '' ?>">
                        Pending
                    </a>

                    <a href="?filter=rejected"
                       class="<?= $filter === 'rejected' ? 'active' : '' ?>">
                        Rejected
                    </a>

                </div>

            </div>

            <?php if (empty($trainings)): ?>

                <div class="company-card p-5 text-center mb-4">

                    <i class="fa fa-graduation-cap fa-3x text-primary mb-3"></i>

                    <h5 class="fw-bold">
                        No trainings posted yet
                    </h5>

                    <p class="text-muted mb-4">
                        Start by creating a training opportunity for students or volunteers.
                    </p>

                    <a href="post-training.php"
                       class="btn btn-primary rounded-pill px-5 py-3 fw-bold">

                        Add Training
                        <i class="fa fa-plus ms-2"></i>

                    </a>

                </div>

            <?php else: ?>

                <div class="row g-3 mb-4">

                    <?php foreach ($trainings as $training): ?>

                        <div class="col-md-6">

                            <div class="company-card p-4 h-100">

                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">

                                    <h5 class="fw-bold mb-0">
                                        <?= training_e($training['title'] ?? '') ?>
                                    </h5>

                                    <?php
                                    $status = $training['status'] ?? '';
                                    $badgeClass = 'bg-secondary';
                                    $statusText = ucfirst(str_replace('_', ' ', $status));

                                    if ($status === 'published') {
                                        $badgeClass = 'bg-success';
                                        $statusText = 'Published';
                                    } elseif ($status === 'pending_approval') {
                                        $badgeClass = 'bg-warning text-dark';
                                        $statusText = 'Pending Approval';
                                    } elseif ($status === 'rejected') {
                                        $badgeClass = 'bg-danger';
                                        $statusText = 'Rejected';
                                    } elseif ($status === 'draft') {
                                        $badgeClass = 'bg-secondary';
                                        $statusText = 'Draft';
                                    }
                                    ?>

                                    <span class="badge <?= $badgeClass ?>">
                                        <?= training_e($statusText) ?>
                                    </span>

                                </div>

                                <p class="text-muted mb-2">

                                    <?= training_e($training['field'] ?? '') ?>

                                    ·

                                    <?= training_e($training['training_type'] ?? '') ?>

                                </p>

                                <small class="text-muted d-block mb-3">

                                    Duration:
                                    <?= training_e($training['duration'] ?? '-') ?> months

                                </small>

                                <div class="d-flex justify-content-between align-items-center gap-2">

                                    <span class="activity-badge">

                                        <?= training_e($training['location'] ?: 'No location') ?>

                                    </span>

                                    <a href="my-trainings.php"
                                       class="btn btn-sm btn-outline-primary rounded-pill">

                                        Manage

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

        <div class="col-lg-4">

            <div class="company-card p-4 mb-4">

                <h5 class="fw-bold mb-4">
                    Recent Activity
                </h5>

                <?php if (empty($recent_trainings)): ?>

                    <p class="text-muted mb-0">
                        No recent activity yet.
                    </p>

                <?php else: ?>

                    <?php foreach ($recent_trainings as $training): ?>

                        <div class="activity-item">

                            <div class="activity-icon">

                                <i class="fa fa-graduation-cap"></i>

                            </div>

                            <div class="activity-content">

                                <strong>
                                    <?= training_e($training['title'] ?? '') ?>
                                </strong>

                                <p class="mb-0">

                                    <?= training_e($training['field'] ?? '') ?>

                                    ·

                                    <span>
                                        <?= training_e($training['status'] ?? '') ?>
                                    </span>

                                </p>

                                <small>
                                    <?= !empty($training['created_at']) ? date('M d, Y', strtotime($training['created_at'])) : '' ?>
                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <div class="tracker-box rounded-4 p-4">

                <h5 class="fw-bold mb-3">
                    Training Tips
                </h5>

                <p class="mb-2">
                    • Add clear requirements
                </p>

                <p class="mb-2">
                    • Mention training duration
                </p>

                <p class="mb-2">
                    • Specify if it's university or voluntary
                </p>

                <p class="mb-0">
                    • Keep training details updated
                </p>

            </div>

        </div>

    </div>

</main>

<?php
}
