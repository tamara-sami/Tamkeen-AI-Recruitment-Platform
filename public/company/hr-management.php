<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/hr-management-functions.php");

$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$company_id]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_POST['ticket_action'])) {
    updateHiringRequestStatus(
        $conn,
        (int)$_POST['request_id'],
        $company_id,
        $_POST['ticket_action']
    );

    header("Location: hr-management.php");
    exit;
}

$pageData = getHrManagementData($conn, $company_id);

$stats = $pageData["stats"];
$activities = $pageData["activities"];
$tickets = $pageData["tickets"];

$base_url = "../../";
$activePage = "hr";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">HR Management</h5>
                <small class="text-muted">Monitor HR actions and approval tickets</small>
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
                <span class="hero-badge">HR Control Room</span>
                <h1>Review HR approval requests</h1>
                <p>Approve, reject, or view more details for hiring requests submitted by HR.</p>
            </div>

            <div class="hero-actions">
                <a href="#approval-tickets" class="btn btn-light fw-bold rounded-pill px-4">View Tickets</a>
            </div>
        </section>

        <section class="admin-stats">
            <div class="stat-card"><i class="fa fa-user-tie"></i><h3><?= (int)$stats['hrUsers']; ?></h3><p>HR Users</p></div>
            <div class="stat-card"><i class="fa fa-envelope"></i><h3><?= (int)$stats['emailsSent']; ?></h3><p>Emails Sent</p></div>
            <div class="stat-card"><i class="fa fa-star"></i><h3><?= (int)$stats['shortlisted']; ?></h3><p>Shortlisted</p></div>
            <div class="stat-card"><i class="fa fa-briefcase"></i><h3><?= (int)$stats['hiringOffers']; ?></h3><p>Hiring Offers</p></div>
            <div class="stat-card"><i class="fa fa-ticket-alt"></i><h3><?= (int)$stats['pendingTickets']; ?></h3><p>Pending Tickets</p></div>
            <div class="stat-card"><i class="fa fa-qrcode"></i><h3><?= (int)$stats['qrScans']; ?></h3><p>QR Scans</p></div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>HR Activity Feed</h4>
                        <p>Latest 3 HR actions.</p>
                    </div>
                </div>

                <div class="activity-list">
                    <?php if (empty($activities)): ?>
                        <div class="activity-item">
                            <span class="activity-icon blue"><i class="fa fa-info"></i></span>
                            <div>
                                <strong>No HR activity yet</strong>
                                <p>Shortlists, interviews, and hiring requests will appear here.</p>
                                <small>—</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <span class="activity-icon blue">
                                    <i class="<?= htmlspecialchars($activity['icon']); ?>"></i>
                                </span>
                                <div>
                                    <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                    <p><?= htmlspecialchars($activity['description']); ?></p>
                                    <small><?= htmlspecialchars(hrManagementDate($activity['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="admin-panel" id="approval-tickets">
                <div class="panel-header">
                    <div>
                        <h4>Approval Tickets</h4>
                        <p>Latest 3 requests from HR.</p>
                    </div>
                </div>

                <div class="quick-actions">
                    <?php if (empty($tickets)): ?>
                        <a href="#">
                            <i class="fa fa-ticket-alt"></i>
                            <div>
                                <strong>No tickets yet</strong>
                                <span>Hiring approvals will appear here when HR submits them.</span>
                            </div>
                        </a>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="role-help-box mb-3">
                                <strong><?= htmlspecialchars(hiringTypeText($ticket['hiring_type'])); ?> Request</strong>
                                <p class="mb-2">
                                    <?= htmlspecialchars($ticket['full_name']); ?>
                                    <?= !empty($ticket['job_title']) ? " — " . htmlspecialchars($ticket['job_title']) : ""; ?>
                                </p>

                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="hiring-request-details.php?id=<?= (int)$ticket['id']; ?>"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View
                                    </a>

                                    <?php if ($ticket['status'] === 'pending'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="request_id" value="<?= (int)$ticket['id']; ?>">
                                            <button type="submit" name="ticket_action" value="approved"
                                                class="btn btn-sm btn-success rounded-pill px-3">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST">
                                            <input type="hidden" name="request_id" value="<?= (int)$ticket['id']; ?>">
                                            <button type="submit" name="ticket_action" value="rejected"
                                                class="btn btn-sm btn-danger rounded-pill px-3">
                                                Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="status-badge <?= htmlspecialchars($ticket['status']); ?>">
                                            <?= htmlspecialchars(ucfirst($ticket['status'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </div>

        <section class="admin-panel mt-4" id="latest-approval-tickets">
            <div class="panel-header">
                <div>
                    <h4>Latest Approval Tickets</h4>
                    <p>Review the most recent HR requests in one place.</p>
                </div>
            </div>

            <div class="company-users-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    No approval tickets yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($ticket['full_name']); ?></strong><br>
                                        <small><?= htmlspecialchars($ticket['email'] ?? ''); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars(hiringTypeText($ticket['hiring_type'])); ?></td>
                                    <td>
                                        <span class="status-badge <?= htmlspecialchars($ticket['status']); ?>">
                                            <?= htmlspecialchars(ucfirst($ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(hrManagementDate($ticket['created_at'])); ?></td>
                                    <td class="text-end">
                                        <a href="hiring-request-details.php?id=<?= (int)$ticket['id']; ?>"
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

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>