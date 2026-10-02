<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/company-dashboard-functions.php");

$base_url = "../../";
$activePage = "dashboard";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

$companyId = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$_SESSION['company_id']]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
$dashboardData = getCompanyDashboardData($conn, $companyId);

$companyName = $dashboardData['companyName'];
$avatarLetter = $dashboardData['avatarLetter'];
$stats = $dashboardData['stats'];
$recentActivities = $dashboardData['recentActivities'];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">
        <div class="admin-container">

            <header class="admin-topbar">
                <div>
                    <span class="page-kicker">Company Admin</span>
                    <h5>Admin Dashboard</h5>
                    <p>Manage your company hiring workflow.</p>
                </div>

                <div class="admin-profile">
                    <div class="profile-info">
                        <strong><?= htmlspecialchars($companyName); ?></strong>
                        <span>Company Admin Access</span>
                    </div>
                    <?= renderCompanyLogo(
    $_SESSION['company_name'],
    $company['company_logo'] ?? ''
); ?>
                </div>
            </header>

            <section class="admin-hero">
                <div class="admin-hero-content">
                    <span class="hero-badge">
                        <i class="fa fa-building"></i>
                        Company Control Center
                    </span>

                    <h1>Welcome, <?= htmlspecialchars($companyName); ?> 👋</h1>

                    <p>
                        Track tasks, manage HR users, training managers, submissions, and smarter hiring decisions.
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="company-users.php" class="btn btn-primary">Add HR</a>
                    <a href="company-users.php" class="btn btn-outline-primary">Add Tasker</a>
                </div>
            </section>

            <section class="admin-stats">
                <div class="stat-card">
                    <i class="fa fa-tasks"></i>
                    <h3><?= (int)$stats['activeTasks']; ?></h3>
                    <p>Active Tasks</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-user-tie"></i>
                    <h3><?= (int)$stats['hrUsers']; ?></h3>
                    <p>HR Users</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-user-cog"></i>
                    <h3><?= (int)$stats['taskManagers']; ?></h3>
                    <p>Task Managers</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-user-graduate"></i>
                    <h3><?= (int)$stats['trainings']; ?></h3>
                    <p>Training</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-paper-plane"></i>
                    <h3><?= (int)$stats['submissions']; ?></h3>
                    <p>Submissions</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-robot"></i>
                    <h3>AI</h3>
                    <p>Ranking Ready</p>
                </div>
            </section>

            <div class="admin-grid">

                <section class="admin-panel">
                    <div class="panel-header">
                        <div>
                            <h4>Recent Activity</h4>
                            <p>Latest actions inside your company portal.</p>
                        </div>
                       
                    </div>

                    <div class="activity-list">
                        <?php if (empty($recentActivities)): ?>
                            <div class="activity-item">
                                <span class="activity-icon blue"><i class="fa fa-info"></i></span>
                                <div>
                                    <strong>No activity yet</strong>
                                    <p>Activity will appear here when HR, tasks, training, or submissions happen.</p>
                                    <small>—</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="activity-item">
                                    <span class="activity-icon blue">
                                        <i class="<?= htmlspecialchars($activity['icon']); ?>"></i>
                                    </span>
                                    <div>
                                        <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                        <p><?= htmlspecialchars($activity['description'] ?? ''); ?></p>
                                        <small><?= htmlspecialchars(activityDateText($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="admin-panel">
                    <div class="panel-header">
                        <div>
                            <h4>Quick Actions</h4>
                            <p>Start common admin tasks quickly.</p>
                        </div>
                    </div>

                    <div class="quick-actions">
                        <a href="company-users.php">
                            <i class="fa fa-user-plus"></i>
                            <div>
                                <strong>Add HR User</strong>
                                <span>Create login access for HR.</span>
                            </div>
                        </a>

                        <a href="company-users.php">
                            <i class="fa fa-user-cog"></i>
                            <div>
                                <strong>Add Task Manager</strong>
                                <span>Allow someone to create tasks.</span>
                            </div>
                        </a>

                        <a href="company-users.php">
                            <i class="fa fa-user-graduate"></i>
                            <div>
                                <strong>Add Training Manager</strong>
                                <span>Allow someone to manage trainings.</span>
                            </div>
                        </a>

                        <a href="post-task.php">
                            <i class="fa fa-plus-square"></i>
                            <div>
                                <strong>Create New Task</strong>
                                <span>Post a real-world challenge.</span>
                            </div>
                        </a>
                    </div>
                </section>

            </div>

            <section class="admin-panel role-overview-panel">
                <div class="panel-header">
                    <div>
                        <h4>Role Access Overview</h4>
                        <p>Each user sees only the pages related to their role.</p>
                    </div>
                </div>

                <div class="role-grid">
                    <div class="role-card">
                        <i class="fa fa-user-shield"></i>
                        <h5>Admin</h5>
                        <p>Manages company users, roles, tasks, reports, and settings.</p>
                    </div>

                    <div class="role-card">
                        <i class="fa fa-user-tie"></i>
                        <h5>HR</h5>
                        <p>Scans QR profiles, reviews candidates, and sends hiring decisions.</p>
                    </div>

                    <div class="role-card">
                        <i class="fa fa-tasks"></i>
                        <h5>Task Manager</h5>
                        <p>Creates and manages real-world tasks and deadlines.</p>
                    </div>

                    <div class="role-card">
                        <i class="fa fa-user-graduate"></i>
                        <h5>Training Manager</h5>
                        <p>Creates trainings, manages sessions, and follows candidate learning progress.</p>
                    </div>
                </div>
            </section>

        </div>
    </main>
</div>

<?php include("../../includes/scripts.php"); ?>