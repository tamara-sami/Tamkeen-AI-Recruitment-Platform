<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/admin-tasks-functions.php");

$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    header("Location: admin-tasks.php");
    exit;
}

$task_id = (int)$_GET['id'];

if (isset($_POST['task_action'])) {
    updateTaskApprovalStatus(
        $conn,
        $task_id,
        $company_id,
        $_POST['task_action']
    );

    header("Location: task-details.php?id=" . $task_id);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        t.*,
        u.full_name AS creator_name,
        COUNT(ta.id) AS submissions_count
    FROM tasks t
    LEFT JOIN users u ON u.id = t.created_by
    LEFT JOIN task_attempts ta 
        ON ta.task_id = t.id 
        AND ta.status = 'submitted'
    WHERE t.id = ?
    AND t.company_id = ?
    GROUP BY t.id
");

$stmt->execute([$task_id, $company_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header("Location: admin-tasks.php");
    exit;
}

$base_url = "../../";
$activePage = "tasks";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h4 class="mb-0">Task Details</h4>
                <small class="text-muted">Review task information and approval status</small>
            </div>
        </header>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Task Review</span>
                <h1><?= htmlspecialchars($task['title']); ?></h1>
                <p>
                    Created by <?= htmlspecialchars($task['creator_name'] ?? 'Company Admin'); ?>.
                </p>
            </div>

            <div class="hero-actions">
                <a href="admin-tasks.php#task-approvals" class="btn btn-light fw-bold rounded-pill px-4">
                    Back to Tasks
                </a>
            </div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Task Information</h4>
                        <p>Main task details.</p>
                    </div>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Title:</strong>
                    <p><?= htmlspecialchars($task['title']); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Description:</strong>
                    <p><?= nl2br(htmlspecialchars($task['description'] ?? 'No description')); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Category:</strong>
                    <p><?= htmlspecialchars($task['category'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box">
                    <strong>Required Skills:</strong>
                    <p><?= htmlspecialchars($task['required_skills'] ?? 'Not set'); ?></p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Task Status</h4>
                        <p>Approval and publishing details.</p>
                    </div>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Status:</strong>
                    <p>
                        <span class="status-badge <?= htmlspecialchars($task['status']); ?>">
                            <?= htmlspecialchars(taskStatusLabel($task['status'])); ?>
                        </span>
                    </p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Difficulty:</strong>
                    <p><?= htmlspecialchars($task['difficulty'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Estimated Time:</strong>
                    <p><?= htmlspecialchars($task['estimated_time'] ?? 'Not set'); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Points:</strong>
                    <p><?= htmlspecialchars($task['points'] ?? 0); ?></p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Deadline:</strong>
                    <p><?= htmlspecialchars(taskDate($task['deadline'] ?? null)); ?></p>
                </div>

                <div class="role-help-box">
                    <strong>Submissions:</strong>
                    <p><?= (int)$task['submissions_count']; ?></p>
                </div>
            </section>

        </div>

        <?php if ($task['status'] === 'pending_approval'): ?>
            <section class="admin-panel mt-4">
                <div class="panel-header">
                    <div>
                        <h4>Admin Decision</h4>
                        <p>Approve this task to publish it, or reject it.</p>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST">
                        <button type="submit" name="task_action" value="approved"
                            class="btn btn-success rounded-pill px-4">
                            Approve Task
                        </button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="task_action" value="rejected"
                            class="btn btn-danger rounded-pill px-4">
                            Reject Task
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>