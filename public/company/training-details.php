<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/admin-training-functions.php");

$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    header("Location: admin-training.php");
    exit;
}

$training_id = (int)$_GET['id'];

if (isset($_POST['training_action'])) {
    updateTrainingApprovalStatus(
        $conn,
        $training_id,
        $company_id,
        $_POST['training_action']
    );

    header("Location: training-details.php?id=" . $training_id);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        t.*,
        u.full_name AS creator_name,
        COUNT(ta.id) AS applications_count
    FROM trainings t
    LEFT JOIN users u ON u.id = t.created_by
    LEFT JOIN training_applications ta ON ta.training_id = t.id
    WHERE t.id = ?
    AND t.company_id = ?
    GROUP BY t.id
");

$stmt->execute([$training_id, $company_id]);
$training = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$training) {
    header("Location: admin-training.php");
    exit;
}

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
                <h4 class="mb-0">Training Details</h4>
                <small class="text-muted">Review training information and approval status</small>
            </div>
        </header>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Training Review</span>
                <h1><?= htmlspecialchars($training['title']); ?></h1>
                <p>
                    Created by <?= htmlspecialchars($training['creator_name'] ?? 'Training Manager'); ?>.
                </p>
            </div>

            <div class="hero-actions">
                <a href="admin-training.php#training-approvals" class="btn btn-light fw-bold rounded-pill px-4">
                    Back to Training
                </a>
            </div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Program Information</h4>
                        <p>Main training details.</p>
                    </div>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Title:</strong>
                    <p><?= htmlspecialchars($training['title']); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Description:</strong>
                    <p><?= nl2br(htmlspecialchars($training['description'] ?? 'No description')); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Field:</strong>
                    <p><?= htmlspecialchars($training['field'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Required Skills:</strong>
                    <p><?= htmlspecialchars($training['required_skills'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box">
                    <strong>Requirements:</strong>
                    <p><?= nl2br(htmlspecialchars($training['requirements'] ?? 'Not set')); ?></p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Program Status</h4>
                        <p>Approval and schedule details.</p>
                    </div>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Status:</strong>
                    <p>
                        <span class="status-badge <?= htmlspecialchars($training['status']); ?>">
                            <?= htmlspecialchars(trainingStatusLabel($training['status'])); ?>
                        </span>
                    </p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Training Type:</strong>
                    <p><?= htmlspecialchars($training['training_type'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Location:</strong>
                    <p><?= htmlspecialchars($training['location'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Duration:</strong>
                    <p><?= htmlspecialchars($training['duration'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Start Date:</strong>
                    <p><?= htmlspecialchars(trainingDate($training['start_date'] ?? null)); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>End Date:</strong>
                    <p><?= htmlspecialchars(trainingDate($training['end_date'] ?? null)); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Seats:</strong>
                    <p><?= htmlspecialchars($training['seats'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box">
                    <strong>Applications:</strong>
                    <p><?= (int)$training['applications_count']; ?></p>
                </div>
            </section>

        </div>

        <?php if ($training['status'] === 'pending_approval'): ?>
            <section class="admin-panel mt-4">
                <div class="panel-header">
                    <div>
                        <h4>Admin Decision</h4>
                        <p>Approve this program to publish it, or reject it.</p>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST">
                        <button type="submit" name="training_action" value="approved"
                            class="btn btn-success rounded-pill px-4">
                            Approve Training
                        </button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="training_action" value="rejected"
                            class="btn btn-danger rounded-pill px-4">
                            Reject Training
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>