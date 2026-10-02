<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/admin-tasks-functions.php");
$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$company_id]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_POST['task_action'])) {
    updateTaskApprovalStatus(
        $conn,
        (int)$_POST['task_id'],
        $company_id,
        $_POST['task_action']
    );

    header("Location: admin-tasks.php");
    exit;
}

$pageData = getAdminTasksData($conn, $company_id);

$stats = $pageData["stats"];
$tasks = $pageData["tasks"];
$pendingTasks = $pageData["pendingTasks"];
$activities = $pageData["activities"];

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
                <h5 class="mb-0">Tasks Monitoring</h5>
                <small class="text-muted">Track company tasks, approvals, and submissions</small>
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
                <span class="hero-badge">Task Control</span>
                <h1> Control Create, publish, and evaluate skill-based tasks</h1>
            </div>

            
        </section>

        <section class="admin-stats">
            <div class="stat-card"><i class="fa fa-tasks"></i><h3><?= (int)$stats['totalTasks']; ?></h3><p>Total Tasks</p></div>
            <div class="stat-card"><i class="fa fa-play"></i><h3><?= (int)$stats['activeTasks']; ?></h3><p>Active Tasks</p></div>
            <div class="stat-card"><i class="fa fa-file-alt"></i><h3><?= (int)$stats['draftTasks']; ?></h3><p>Draft Tasks</p></div>
        
            <div class="stat-card"><i class="fa fa-paper-plane"></i><h3><?= (int)$stats['submissions']; ?></h3><p>Submissions</p></div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>All Tasks</h4>
                        <p>Latest company tasks.</p>
                    </div>
                </div>

                <div class="company-users-table">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Created By</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Submissions</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        No tasks available yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($task['title']); ?></strong></td>
                                        <td><?= htmlspecialchars($task['creator_name'] ?? 'Company Admin'); ?></td>
                                        <td><?= htmlspecialchars(taskDate($task['deadline'] ?? null)); ?></td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($task['status']); ?>">
                                                <?= htmlspecialchars(taskStatusLabel($task['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?= (int)$task['submissions_count']; ?></td>
                                        <td class="text-end">
                                            <a href="task-details.php?id=<?= (int)$task['id']; ?>"
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

        
        <section class="admin-panel mt-4">
            <div class="panel-header">
                <div>
                    <h4>Task Manager Activity</h4>
                    <p>Latest 3 task actions.</p>
                </div>
            </div>

            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <div class="activity-item">
                        <span class="activity-icon blue"><i class="fa fa-info"></i></span>
                        <div>
                            <strong>No task activity yet</strong>
                            <p>Created tasks and approvals will appear here.</p>
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
                                <small><?= htmlspecialchars(taskDate($activity['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>