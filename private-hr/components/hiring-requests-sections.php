<?php

if (!function_exists('renderHrHiringRequestsPage')) {

    function renderHrHiringRequestsPage(array $pageData)
    {
        extract($pageData);
?>



<div class="tamkeen-layout approvals-page">

    <?php include(__DIR__ . "/../../includes/hr-sidebar.php"); ?>

    <main class="tamkeen-main">
        <div class="tamkeen-container">

            <!-- =====================================================
                 Page Topbar
                 ===================================================== -->

            <header class="tamkeen-topbar clean-hr-topbar approvals-topbar">

                <div>
                    <span class="page-kicker">
                        Hiring Approval Center
                    </span>

                    <h1>
                        <?= e($_SESSION['full_name'] ?? 'HR Manager'); ?>
                    </h1>

                    <p>
                        Track final hiring tickets submitted
                        for admin review.
                    </p>
                </div>

                <div class="topbar-company-profile">

                    <div>
                        <strong><?= e($companyName); ?></strong>
                        <span>HR Portal</span>
                    </div>

                    <?php if (!empty($company['company_logo'])): ?>

                        <!-- Company logo path after moving page -->
                        <img
                            src="/tamkeentest/public/uploads/company/<?= e(basename($company['company_logo'])); ?>"
                            alt="Company logo"
                        >

                    <?php else: ?>

                        <div class="company-letter">
                            <?= e($avatarLetter); ?>
                        </div>

                    <?php endif; ?>

                </div>

            </header>


            <!-- =====================================================
                 Hero Section
                 ===================================================== -->

            <section class="approvals-hero">

                <div>

                    <span class="hero-badge">
                        <i class="fa fa-shield-alt"></i>
                        Hiring Approval
                    </span>

                   <h2 style="color: #ffffff !important;">
    Follow every final candidate request.
</h2>

                    <p>
                        After an interview is completed,
                        HR sends the candidate to admin approval.
                        This page tracks:
                        pending, approved, or rejected requests.
                    </p>

                    <a
                        href="/tamkeentest/public/hr/interviews.php"
                        class="approval-hero-btn"
                    >
                        <i class="fa fa-calendar-check"></i>
                        Go to Interviews
                    </a>

                </div>


                <!-- Latest Approval Ticket -->

                <aside class="latest-approval-card">

                    <span>Latest Ticket</span>

                    <?php if ($latestRequest): ?>

                        <strong>
                            <?= e($latestRequest['full_name']); ?>
                        </strong>

                        <p>
                            <?= e(
                                approvalStatusLabel(
                                    $latestRequest['status']
                                )
                            ); ?>

                            ·

                            <?= e(
                                formatApprovalDate(
                                    $latestRequest['created_at']
                                )
                            ); ?>
                        </p>

                    <?php else: ?>

                        <strong>No tickets yet</strong>

                        <p>
                            Completed interviews can be submitted
                            for approval.
                        </p>

                    <?php endif; ?>

                </aside>

            </section>


            <!-- =====================================================
                 Stats Row
                 ===================================================== -->

            <section class="approvals-stats-row">

                <div>
                    <i class="fa fa-file-signature"></i>

                    <section>
                        <strong><?= (int)$stats['total']; ?></strong>
                        <span>Total Requests</span>
                    </section>
                </div>

                <div>
                    <i class="fa fa-clock"></i>

                    <section>
                        <strong><?= (int)$stats['pending']; ?></strong>
                        <span>Pending Review</span>
                    </section>
                </div>

                <div>
                    <i class="fa fa-circle-check"></i>

                    <section>
                        <strong><?= (int)$stats['approved']; ?></strong>
                        <span>Approved</span>
                    </section>
                </div>

                <div>
                    <i class="fa fa-circle-xmark"></i>

                    <section>
                        <strong><?= (int)$stats['rejected']; ?></strong>
                        <span>Rejected</span>
                    </section>
                </div>

            </section>


            <!-- =====================================================
                 Filters
                 ===================================================== -->

            <form method="GET" class="approvals-filter-bar">

                <input
                    type="text"
                    name="search"
                    value="<?= e($filters['search']); ?>"
                    placeholder="Search by candidate, job title, email..."
                >

                <select name="status">

                    <option
                        value="all"
                        <?= $filters['status'] === 'all' ? 'selected' : ''; ?>
                    >
                        All Status
                    </option>

                    <option
                        value="pending"
                        <?= $filters['status'] === 'pending' ? 'selected' : ''; ?>
                    >
                        Pending
                    </option>

                    <option
                        value="approved"
                        <?= $filters['status'] === 'approved' ? 'selected' : ''; ?>
                    >
                        Approved
                    </option>

                    <option
                        value="rejected"
                        <?= $filters['status'] === 'rejected' ? 'selected' : ''; ?>
                    >
                        Rejected
                    </option>

                </select>

                <select name="type">

                    <option value="all">
                        All Types
                    </option>

                    <option value="full_time">
                        Full-time
                    </option>

                    <option value="internship">
                        Internship
                    </option>

                    <option value="temporary">
                        Temporary
                    </option>

                    <option value="freelance">
                        Freelance
                    </option>

                </select>

                <button type="submit">
                    <i class="fa fa-filter"></i>
                    Filter
                </button>

                <a href="/tamkeentest/public/hr/hiring-requests.php">
                    Reset
                </a>

            </form>


            <!-- =====================================================
                 Requests List
                 ===================================================== -->

            <section class="clean-card approvals-list-panel">

                <div class="panel-title-row">

                    <div>

                        <span class="page-kicker">
                            Submitted Tickets
                        </span>

                        <h3>Hiring Approval Requests</h3>

                        <p>
                            <?= count($requests); ?>
                            request<?= count($requests) === 1 ? '' : 's'; ?>
                            found.
                        </p>

                    </div>

                    <a
                        href="/tamkeentest/public/hr/interviews.php"
                        class="panel-link-btn"
                    >
                        <i class="fa fa-plus"></i>
                        Submit from Interviews
                    </a>

                </div>
<?php if (empty($requests)): ?>

    <div class="empty-card">
        <h4>No hiring requests yet</h4>
        <p>Completed interviews can be submitted for admin approval.</p>
    </div>

<?php else: ?>

    <div class="approval-request-list">

        <?php foreach ($requests as $index => $request): ?>

            <?php
                $statusClass = approvalStatusClass($request['status']);
                $statusLabel = approvalStatusLabel($request['status']);
                $typeLabel = hiringTypeLabel($request['hiring_type']);
            ?>

            <article class="approval-request-card">

                <div class="candidate-rank">
                    #<?= $index + 1; ?>
                </div>

                <div class="approval-candidate-info">

                    <?php if (!empty($request['profile_image'])): ?>
                        <img
                            src="/tamkeentest/public/uploads/profile_images/<?= e(basename($request['profile_image'])); ?>"
                            alt="Candidate photo"
                        >
                    <?php else: ?>
                        <div class="candidate-avatar">
                            <?= e(initials($request['full_name'] ?? 'U')); ?>
                        </div>
                    <?php endif; ?>

                    <section>
                        <h4><?= e($request['full_name']); ?></h4>

                        <p>
                            <?= e($request['job_title'] ?: 'Job Seeker'); ?>
                            <b>•</b>
                            <?= e($request['location'] ?: 'Location not set'); ?>
                        </p>

                        <small>
                            <i class="fa fa-envelope"></i>
                            <?= e($request['email']); ?>
                        </small>
                    </section>

                </div>

                <div class="approval-details">

                    <span class="approval-status <?= e($statusClass); ?>">
                        <?= e($statusLabel); ?>
                    </span>

                    <p>
                        <strong>Hiring Type:</strong>
                        <?= e($typeLabel); ?>
                    </p>

                    <p>
                        <strong>Submitted:</strong>
                        <?= e(formatApprovalDate($request['created_at'])); ?>
                    </p>

                    <?php if (!empty($request['interview_date'])): ?>
                        <p>
                            <strong>Interview:</strong>
                            <?= e(formatApprovalDate($request['interview_date'] . ' ' . $request['interview_time'])); ?>
                        </p>
                    <?php endif; ?>

                </div>

               <?php if (!empty($request['admin_note'])): ?>
    <div class="admin-note-box w-100">
        <strong>
            <i class="fa fa-note-sticky"></i>
            Admin Note
        </strong>

        <p>
            <?= e($request['admin_note']); ?>
        </p>
    </div>
<?php endif; ?>
                <div class="approval-actions">

                    <?php if ($request['status'] === 'approved'): ?>

                        <span class="approval-result approved">
                            <i class="fa fa-circle-check"></i>
                            Approved by admin
                        </span>

                    <?php elseif ($request['status'] === 'rejected'): ?>

                        

                    <?php else: ?>

                        <span class="approval-result pending">
                            <i class="fa fa-clock"></i>
                            Waiting for admin review
                        </span>

                    <?php endif; ?>

                 
                       

             

            </article>

        <?php endforeach; ?>

    </div>

<?php endif; ?>
            </section>

        </div>
    </main>
</div>




<?php
    }
}
