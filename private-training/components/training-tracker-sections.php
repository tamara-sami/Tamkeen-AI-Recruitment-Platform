<?php

function renderTrainingTrackerPage($page)
{
    extract($page);
?>

<main class="container py-5">

<div class="row g-4">

<div class="col-lg-8">

<div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
<h3 class="fw-bold mb-2">Training Applications Tracker</h3>
<p class="text-muted mb-0">
Review interested students, read their notes, contact them, and update application status.
</p>
</div>

<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="bg-white rounded-4 shadow-sm border p-3 text-center">
<h3 class="fw-bold text-primary mb-0"><?= $stats['total_count'] ?? 0 ?></h3>
<small class="text-muted">Total Applicants</small>
</div>
</div>

<div class="col-md-4">
<div class="bg-white rounded-4 shadow-sm border p-3 text-center">
<h3 class="fw-bold text-secondary mb-0"><?= $stats['pending_count'] ?? 0 ?></h3>
<small class="text-muted">Pending</small>
</div>
</div>

<div class="col-md-4">
<div class="bg-white rounded-4 shadow-sm border p-3 text-center">
<h3 class="fw-bold text-success mb-0"><?= $stats['accepted_count'] ?? 0 ?></h3>
<small class="text-muted">Accepted</small>
</div>
</div>

</div>

<div class="bg-white rounded-4 shadow-sm border mb-4 p-4">

<form method="GET" class="row g-3 align-items-end">

<div class="col-md-6">

<label class="form-label fw-bold">Filter by Status</label>

<select name="status" class="form-select">

<option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
<option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="reviewing" <?= $status === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
<option value="interview" <?= $status === 'interview' ? 'selected' : '' ?>>Interview</option>
<option value="accepted" <?= $status === 'accepted' ? 'selected' : '' ?>>Accepted</option>
<option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>

</select>

</div>

<div class="col-md-6">

<label class="form-label fw-bold">Filter by Training</label>

<select name="training_id" class="form-select">

<option value="">All Trainings</option>

<?php foreach ($trainings as $training): ?>

<option value="<?= $training['id'] ?>" <?= (string)$training_id === (string)$training['id'] ? 'selected' : '' ?>>
<?= training_e($training['title']) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-12">
<button class="btn btn-primary rounded-pill px-4 fw-bold">
Apply Filters
</button>
</div>

</form>

</div>

<div class="bg-white rounded-4 shadow-sm border">

<div class="p-4 border-bottom">
<h4 class="fw-bold mb-0">Applicants</h4>
</div>

<?php if (empty($applications)): ?>

<div class="p-5 text-center">
<i class="fa fa-user-graduate fa-3x text-muted mb-3"></i>
<h5 class="fw-bold">No applications found</h5>
<p class="text-muted mb-0">Applications will appear here when students apply.</p>
</div>

<?php else: ?>

<?php foreach ($applications as $app): ?>

<?php
$statusClass = "bg-secondary";

if ($app['status'] === 'accepted') $statusClass = "bg-success";
if ($app['status'] === 'rejected') $statusClass = "bg-danger";
if ($app['status'] === 'interview') $statusClass = "bg-primary";
if ($app['status'] === 'reviewing') $statusClass = "bg-warning text-dark";
?>

<div class="p-4 border-bottom task-post-item">

<div class="d-flex gap-3">

<div>

<?php if (!empty($app['profile_image'])): ?>

<img src="/tamkeentest/<?= training_e($app['profile_image']) ?>"
class="rounded-circle"
style="width:58px;height:58px;object-fit:cover;">

<?php else: ?>

<div style="width:58px;height:58px;background:#eef3ff;border-radius:50%;"
class="d-flex align-items-center justify-content-center">
<i class="fa fa-user text-primary"></i>
</div>

<?php endif; ?>

</div>

<div class="flex-grow-1">

<div class="d-flex justify-content-between align-items-start gap-3">

<div>

<h5 class="fw-bold mb-1">
<?= training_e($app['full_name']) ?>
</h5>

<p class="text-muted mb-1">
Applied for:
<strong><?= training_e($app['training_title']) ?></strong>
</p>

</div>

<span class="badge <?= $statusClass ?> rounded-pill px-3 py-2">
<?= training_e($app['status']) ?>
</span>

</div>

<div class="bg-light rounded-4 p-3 my-3">
<strong>Student Note</strong>
<p class="mb-0 small mt-1">
<?= nl2br(training_e($app['comment'])) ?>
</p>
</div>

<form method="POST"
action="update-application-status.php"
class="d-flex flex-wrap gap-2 align-items-center">

<input type="hidden"
name="application_id"
value="<?= $app['id'] ?>">

<select name="status"
class="form-select"
style="max-width:220px;">

<option value="pending" <?= $app['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
<option value="reviewing" <?= $app['status'] === 'reviewing' ? 'selected' : '' ?>>Reviewing</option>
<option value="interview" <?= $app['status'] === 'interview' ? 'selected' : '' ?>>Interview</option>
<option value="accepted" <?= $app['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
<option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>

</select>

<button class="btn btn-primary rounded-pill px-4 fw-bold">
Update Status
</button>

</form>

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
<span>Reviewing</span>
<strong><?= $stats['reviewing_count'] ?? 0 ?></strong>
</div>

<div class="d-flex justify-content-between mb-2">
<span>Interview</span>
<strong><?= $stats['interview_count'] ?? 0 ?></strong>
</div>

<div class="d-flex justify-content-between mb-2">
<span>Accepted</span>
<strong class="text-success"><?= $stats['accepted_count'] ?? 0 ?></strong>
</div>

<div class="d-flex justify-content-between">
<span>Rejected</span>
<strong class="text-danger"><?= $stats['rejected_count'] ?? 0 ?></strong>
</div>

</div>

</div>

</div>

</main>

<?php
}
