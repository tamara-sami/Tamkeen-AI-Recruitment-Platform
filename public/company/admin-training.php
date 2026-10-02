<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/admin-training-functions.php");

$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$company_id]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_POST['training_action'])) {
    updateTrainingApprovalStatus(
        $conn,
        (int)$_POST['training_id'],
        $company_id,
        $_POST['training_action']
    );

    header("Location: admin-training.php");
    exit;
}

$pageData = getAdminTrainingData($conn, $company_id);

$stats = $pageData["stats"];
$trainings = $pageData["trainings"];
$pendingTrainings = $pageData["pendingTrainings"];
$activities = $pageData["activities"];

$base_url = "../../";
$activePage = "training";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">Training Monitoring</h5>
                <small class="text-muted">Track training programs, approvals, and applications</small>
            </div>
             <div class="admin-profile">
                <div class="profile-info">
                    <strong><?= htmlspecialchars($_SESSION['company_name']); ?></strong>
                    <span>Company Admin</span>
                </div>
  

<?= renderCompanyLogo(
    $_SESSION['company_name'],
    $company['company_logo'] ?? ''
); ?>
            </div>
        </header>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Training Control</span>
                <h1>Approve training before publishing</h1>
                <p>Training Managers can create programs, but programs only go live after admin approval.</p>
            </div>

            <div class="hero-actions">
                <a href="#training-approvals" class="btn btn-light fw-bold rounded-pill px-4">View Tickets</a>
            </div>
        </section>

        <section class="admin-stats">
            <div class="stat-card"><i class="fa fa-user-graduate"></i><h3><?= (int)$stats['trainingManagers']; ?></h3><p>Training Managers</p></div>
            <div class="stat-card"><i class="fa fa-briefcase"></i><h3><?= (int)$stats['totalPrograms']; ?></h3><p>Total Programs</p></div>
            <div class="stat-card"><i class="fa fa-play-circle"></i><h3><?= (int)$stats['activePrograms']; ?></h3><p>Active Programs</p></div>
            <div class="stat-card"><i class="fa fa-file-signature"></i><h3><?= (int)$stats['applications']; ?></h3><p>Applications</p></div>
            <div class="stat-card"><i class="fa fa-check-circle"></i><h3><?= (int)$stats['acceptedTrainees']; ?></h3><p>Accepted Trainees</p></div>
            <div class="stat-card"><i class="fa fa-ticket-alt"></i><h3><?= (int)$stats['pendingTickets']; ?></h3><p>Pending Tickets</p></div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>All Training Programs</h4>
                        <p>Latest company training programs.</p>
                    </div>
                </div>

                <div class="company-users-table">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Created By</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Applications</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($trainings)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        No training programs available yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($trainings as $training): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($training['title']); ?></strong>
                                            <span class="d-block text-muted small">
                                                <?= htmlspecialchars($training['location'] ?? 'No location'); ?>
                                                ·
                                                <?= htmlspecialchars($training['duration'] ?? 'No duration'); ?>
                                            </span>
                                        </td>

                                        <td><?= htmlspecialchars($training['creator_name'] ?? 'Training Manager'); ?></td>
                                        <td><?= htmlspecialchars($training['training_type'] ?? 'Training'); ?></td>

                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($training['status']); ?>">
                                                <?= htmlspecialchars(trainingStatusLabel($training['status'])); ?>
                                            </span>
                                        </td>

                                        <td><?= (int)$training['applications_count']; ?></td>

                                        <td class="text-end">
                                            <a href="training-details.php?id=<?= (int)$training['id']; ?>"
                                               class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-panel" id="training-approvals">
                <div class="panel-header">
                    <div>
                        <h4>Training Approval Tickets</h4>
                        <p>Latest 3 programs waiting for admin approval.</p>
                    </div>
                </div>

                <div class="quick-actions">
                    <?php if (empty($pendingTrainings)): ?>
                        <a href="#">
                            <i class="fa fa-ticket-alt"></i>
                            <div>
                                <strong>No pending programs</strong>
                                <span>Training programs waiting for approval will appear here.</span>
                            </div>
                        </a>
                    <?php else: ?>
                        <?php foreach ($pendingTrainings as $training): ?>
                            <div class="role-help-box mb-3">
                                <strong><?= htmlspecialchars($training['title']); ?></strong>
                                <p class="mb-2">
                                    Created by <?= htmlspecialchars($training['creator_name'] ?? 'Training Manager'); ?>
                                    · Starts <?= htmlspecialchars(trainingDate($training['start_date'] ?? null)); ?>
                                </p>

                                <div class="d-flex align-items-center gap-2 flex-wrap mt-3">
                                    <a href="training-details.php?id=<?= (int)$training['id']; ?>"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                                        <i class="fa fa-eye me-2"></i> View
                                    </a>

                                    <form method="POST">
                                        <input type="hidden" name="training_id" value="<?= (int)$training['id']; ?>">
                                        <button type="submit" name="training_action" value="approved"
                                            class="btn btn-sm btn-success rounded-pill px-3">
                                            Approve
                                        </button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="training_id" value="<?= (int)$training['id']; ?>">
                                        <button type="submit" name="training_action" value="rejected"
                                            class="btn btn-sm btn-danger rounded-pill px-3">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </div>

        <section class="admin-panel mt-4">
            <div class="panel-header">
                <div>
                    <h4>Training Manager Activity</h4>
                    <p>Latest 3 training actions.</p>
                </div>
            </div>

            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <div class="activity-item">
                        <span class="activity-icon blue"><i class="fa fa-info"></i></span>
                        <div>
                            <strong>No training activity yet</strong>
                            <p>Created programs and approvals will appear here.</p>
                            <small>—</small>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-icon blue"><i class="<?= htmlspecialchars($activity['icon']); ?>"></i></span>
                            <div>
                                <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                <p><?= htmlspecialchars($activity['description']); ?></p>
                                <small><?= htmlspecialchars(trainingDate($activity['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>