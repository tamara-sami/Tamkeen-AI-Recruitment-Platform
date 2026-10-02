<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/activity-log-functions.php");

$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$_SESSION['company_id']]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
$pageData = getActivityLogData($conn, $company_id);

$stats = $pageData["stats"];
$activities = $pageData["activities"];

$base_url = "../../";
$activePage = "activity";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">Activity Log</h5>
                <small class="text-muted">Monitor all important actions inside the company portal</small>
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
                <span class="hero-badge">System Timeline</span>
                <h1>Everything important, tracked in one place</h1>
                <p>See HR actions, task updates, AI recommendations, user changes, and hiring decisions.</p>
            </div>
        </section>

        <section class="admin-stats">
            <div class="stat-card"><i class="fa fa-history"></i><h3><?= (int)$stats['totalEvents']; ?></h3><p>Total Events</p></div>
            <div class="stat-card"><i class="fa fa-user-tie"></i><h3><?= (int)$stats['hrActions']; ?></h3><p>HR Actions</p></div>
            <div class="stat-card"><i class="fa fa-tasks"></i><h3><?= (int)$stats['taskActions']; ?></h3><p>Task Actions</p></div>
            <div class="stat-card"><i class="fa fa-robot"></i><h3><?= (int)$stats['aiEvents']; ?></h3><p>AI Events</p></div>
            <div class="stat-card"><i class="fa fa-users-cog"></i><h3><?= (int)$stats['userChanges']; ?></h3><p>User Changes</p></div>
            <div class="stat-card"><i class="fa fa-ticket-alt"></i><h3><?= (int)$stats['tickets']; ?></h3><p>Tickets</p></div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Activity Timeline</h4>
                        <p>Recent important actions across your company portal</p>
                    </div>
                </div>

                <div class="activity-list">
                    <?php if (empty($activities)): ?>
                        <div class="activity-item">
                            <span class="activity-icon blue"><i class="fa fa-info"></i></span>
                            <div>
                                <strong>No activity yet</strong>
                                <p>Company actions will appear here.</p>
                                <small>—</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <span class="activity-icon <?= htmlspecialchars($activity['color']); ?>">
                                    <i class="<?= htmlspecialchars($activity['icon']); ?>"></i>
                                </span>

                                <div>
                                    <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                    <p><?= htmlspecialchars($activity['description']); ?></p>
                                    <small>
                                        <?= htmlspecialchars(activityDate($activity['created_at'])); ?>
                                        ·
                                        <?= htmlspecialchars($activity['type']); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Activity Summary</h4>
                        <p>Quick view by category</p>
                    </div>
                </div>

                <div class="team-summary">
                    <div class="team-summary-item">
                        <div class="summary-icon hr"><i class="fa fa-user-tie"></i></div>
                        <div>
                            <h5>HR</h5>
                            <span>Hiring requests and candidate actions</span>
                        </div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon task"><i class="fa fa-tasks"></i></div>
                        <div>
                            <h5>Tasks</h5>
                            <span>Created tasks and task submissions</span>
                        </div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon review"><i class="fa fa-robot"></i></div>
                        <div>
                            <h5>AI</h5>
                            <span>Ranking and candidate performance signals</span>
                        </div>
                    </div>
                </div>

                <div class="role-note mt-4">
                    <i class="fa fa-info-circle"></i>
                    <p>
                        Activity Log is read-only for Admin. It helps track the system without interrupting daily HR or task work.
                    </p>
                </div>
            </section>

        </div>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>