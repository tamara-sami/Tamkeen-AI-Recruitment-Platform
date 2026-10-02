<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/hr-management-functions.php");

$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    header("Location: hr-management.php");
    exit;
}

$request_id = (int)$_GET['id'];

if (isset($_POST['ticket_action'])) {
    updateHiringRequestStatus(
        $conn,
        $request_id,
        $company_id,
        $_POST['ticket_action'],
        $_POST['admin_note'] ?? ''
    );

    header("Location: hiring-request-details.php?id=" . $request_id);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        hr.*,
        js.full_name,
        js.email,
        js.mobile,
        js.job_title,
        i.interview_date,
        i.interview_time,
        i.interview_type,
        i.interviewer_name,
        i.location_or_link,
        i.notes AS interview_notes
    FROM hiring_requests hr
    JOIN job_seekers js ON js.id = hr.job_seeker_id
    LEFT JOIN interviews i ON i.id = hr.interview_id
    WHERE hr.id = ?
    AND hr.company_id = ?
");

$stmt->execute([$request_id, $company_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header("Location: hr-management.php");
    exit;
}

$base_url = "../../";
$activePage = "hr";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<style>
.ticket-wrap {
    display: grid;
    grid-template-columns: 1.3fr .7fr;
    gap: 24px;
    margin-top: 24px;
}

.ticket-card {
    background: #fff;
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    border: 1px solid #eef2ff;
}

.ticket-hero {
    background: linear-gradient(135deg, #071b3f, #173b8f, #315cff);
    color: white;
    border-radius: 30px;
    padding: 36px;
    margin-top: 24px;
    position: relative;
    overflow: hidden;
}

.ticket-hero h1 {
    font-size: 38px;
    font-weight: 900;
    margin: 10px 0;
}

.ticket-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 999px;
    background: rgba(255,255,255,.16);
    font-weight: 800;
}

.status-big {
    display: inline-block;
    padding: 12px 22px;
    border-radius: 999px;
    font-weight: 900;
    text-transform: capitalize;
}

.status-big.pending { background:#fff7d6; color:#9a6a00; }
.status-big.approved { background:#dcfce7; color:#047857; }
.status-big.rejected { background:#fee2e2; color:#b91c1c; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 16px;
}

.info-box {
    background: #f8fbff;
    border: 1px solid #e8eef8;
    border-radius: 20px;
    padding: 18px;
}

.info-box small {
    color: #64748b;
    font-weight: 800;
    display: block;
    margin-bottom: 8px;
}

.info-box strong, .info-box p {
    color: #071b3f;
    font-weight: 800;
    margin: 0;
}

.note-box {
    background: #f1f7ff;
    border-left: 5px solid #315cff;
    border-radius: 18px;
    padding: 18px;
    color: #334155;
    line-height: 1.7;
}

.decision-card textarea {
    border-radius: 18px;
    min-height: 130px;
    resize: vertical;
}

.decision-actions {
    display: grid;
    gap: 12px;
}

.decision-actions button {
    border: 0;
    border-radius: 999px;
    padding: 14px 20px;
    font-weight: 900;
    color: white;
}

.btn-approve { background: linear-gradient(135deg, #059669, #22c55e); }
.btn-reject { background: linear-gradient(135deg, #dc2626, #fb7185); }
.btn-back { border-radius:999px; font-weight:900; }

@media(max-width: 900px) {
    .ticket-wrap { grid-template-columns: 1fr; }
    .info-grid { grid-template-columns: 1fr; }
}
</style>

<div class="admin-layout">
    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h4 class="mb-0">Hiring Request Details</h4>
                <small class="text-muted">Review HR approval ticket</small>
            </div>
        </header>

        <section class="ticket-hero">
            <span class="ticket-pill">
                <i class="fa fa-ticket-alt"></i>
                Approval Ticket
            </span>

            <h1><?= htmlspecialchars($ticket['full_name']); ?></h1>

            <p class="mb-4">
                <?= htmlspecialchars($ticket['job_title'] ?: 'Candidate'); ?> ·
                <?= htmlspecialchars(hiringTypeText($ticket['hiring_type'])); ?>
            </p>

            <span class="status-big <?= htmlspecialchars($ticket['status']); ?>">
                <?= htmlspecialchars($ticket['status']); ?>
            </span>

            <a href="hr-management.php#approval-tickets" class="btn btn-light btn-back px-4 ms-3">
                Back to HR
            </a>
        </section>

        <div class="ticket-wrap">

            <section class="ticket-card">
                <h4 class="fw-bold mb-4">Request Information</h4>

                <div class="info-grid mb-4">
                    <div class="info-box">
                        <small>Candidate</small>
                        <strong><?= htmlspecialchars($ticket['full_name']); ?></strong>
                    </div>

                    <div class="info-box">
                        <small>Email</small>
                        <strong><?= htmlspecialchars($ticket['email']); ?></strong>
                    </div>

                    <div class="info-box">
                        <small>Mobile</small>
                        <strong><?= htmlspecialchars($ticket['mobile'] ?: 'Not set'); ?></strong>
                    </div>

                    <div class="info-box">
                        <small>Hiring Type</small>
                        <strong><?= htmlspecialchars(hiringTypeText($ticket['hiring_type'])); ?></strong>
                    </div>

                    <div class="info-box">
                        <small>Interview Date</small>
                        <strong>
                            <?= htmlspecialchars($ticket['interview_date'] ?? 'Not scheduled'); ?>
                            <?= !empty($ticket['interview_time']) ? ' · ' . htmlspecialchars($ticket['interview_time']) : ''; ?>
                        </strong>
                    </div>

                    <div class="info-box">
                        <small>Interviewer</small>
                        <strong><?= htmlspecialchars($ticket['interviewer_name'] ?: 'Not set'); ?></strong>
                    </div>
                </div>

                <h5 class="fw-bold mb-2">HR Note</h5>
                <div class="note-box mb-4">
                    <?= !empty($ticket['note'])
                        ? nl2br(htmlspecialchars($ticket['note']))
                        : 'No HR note added.'; ?>
                </div>

                <h5 class="fw-bold mb-2">Admin Note</h5>
                <div class="note-box">
                    <?= !empty($ticket['admin_note'])
                        ? nl2br(htmlspecialchars($ticket['admin_note']))
                        : 'No admin note yet.'; ?>
                </div>
            </section>

            <aside class="ticket-card decision-card">
                <h4 class="fw-bold mb-2">Admin Decision</h4>
                <p class="text-muted mb-4">
                    You can approve or reject this request anytime.
                </p>

                <form method="POST">
                    <label class="form-label fw-bold">Admin Note</label>
                    <textarea
                        name="admin_note"
                        class="form-control mb-3"
                        placeholder="Write your decision note..."><?= htmlspecialchars($ticket['admin_note'] ?? '') ?></textarea>

                    <div class="decision-actions">
                        <button type="submit" name="ticket_action" value="approved" class="btn-approve">
                            <i class="fa fa-check me-2"></i>
                            Approve Request
                        </button>

                        <button type="submit" name="ticket_action" value="rejected" class="btn-reject">
                            <i class="fa fa-times me-2"></i>
                            Reject Request
                        </button>
                    </div>
                </form>
            </aside>

        </div>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>