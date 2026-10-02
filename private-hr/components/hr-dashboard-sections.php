<?php

if (!function_exists('renderHrDashboardPage')) {

    function renderHrDashboardPage($page)
    {
        extract($page);
?>
<div class="hr-layout tamkeen-layout">
    <?php include(__DIR__ . "/../../includes/hr-sidebar.php"); ?>

    <main class="hr-main tamkeen-main">
        <div class="tamkeen-container">

            <header class="tamkeen-topbar clean-hr-topbar">
                <div>
                    <span class="page-kicker">HR Dashboard</span>
                    <h1><?= e($_SESSION['full_name'] ?? 'HR Manager'); ?></h1>
                    <p>Search candidates, track interviews, and manage hiring approvals.</p>
                </div>

                <div class="topbar-company-profile">
                    <div>
                        <strong><?= e($companyName); ?></strong>
                        <span>HR Portal</span>
                    </div>

                    <?php if (!empty($companyLogo)): ?>
                        <?php // Company logo path is saved from the main project root, so we go back to /tamkeentest/. ?>
<img src="/tamkeentest/public/uploads/company/<?= e(basename($companyLogo)); ?>" alt="Company logo">   
                 <?php else: ?>
                        <div class="company-letter"><?= e($avatarLetter); ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <section class="tamkeen-hero">
                <div class="hero-copy">
                    <span class="hero-badge">
                        <i class="fa fa-search"></i> Candidate Search
                    </span>

<h2 style="color: #fff;">Find verified candidates faster.</h2>                    <p>
                        Search by skills, score, email, or QR profile.
                        Review task performance and move top talent through the hiring workflow.
                    </p>

                    <form method="GET" class="tamkeen-search" id="candidateSearchForm">
                        <div class="search-input">
                            <i class="fa fa-search"></i>
                            <input
                                type="text"
                                name="search"
                                value="<?= e($search); ?>"
                                placeholder="Search by name, skill, email, title..."
                            >
                        </div>

                        <select name="skill">
                            <option value="">All Skills</option>
                            <?php foreach ($skills as $skillName): ?>
                                <option
                                    value="<?= e($skillName); ?>"
                                    <?= $skill === $skillName ? 'selected' : ''; ?>
                                >
                                    <?= e($skillName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="hidden" name="qr" id="qrSearchInput" value="<?= e($qrSearch); ?>">

                        <button type="button" class="qr-btn" id="openQrScanner">
                            <i class="fa fa-qrcode"></i> QR
                        </button>

                        <button type="submit" class="search-btn">
                            <i class="fa fa-search"></i> Search
                        </button>
                    </form>

                    <?php if ($qrSearch !== ''): ?>
                        <small class="qr-result">
                            <i class="fa fa-qrcode"></i>
                            QR search active: <?= e($qrSearch); ?>
                            <a href="hr-dashboard.php">clear</a>
                        </small>
                    <?php endif; ?>
                </div>

                <aside class="top-match-card">
                    <div class="match-header">
                        <h3><i class="fa fa-chart-line"></i> Top Match</h3>
                        <i class="fa fa-chevron-down"></i>
                    </div>

                    <?php if ($topCandidate): ?>
                        <div class="match-person">
                            <?php if (!empty($topCandidate['profile_image'])): ?>
                                <?php // Candidate image path is saved from the main project root. ?>
<img src="/tamkeentest/public/uploads/profile_images/<?= e(basename($topCandidate['profile_image'])); ?>" alt="Candidate photo">                            <?php else: ?>
                                <div class="match-avatar">
                                    <?= e(initials($topCandidate['full_name'])); ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <strong><?= e($topCandidate['full_name']); ?></strong>
                                <span><?= e($topCandidate['job_title'] ?: 'Job Seeker'); ?></span>
                            </div>
                        </div>

                        <div class="big-score"><?= (int)$topScore; ?>%</div>
                        <p>Match Score</p>

                        <div class="match-pills">
                            <span>Score <?= (int)$topCandidate['earned_points']; ?></span>
                            <span><?= (int)$topCandidate['submitted_tasks']; ?> Tasks</span>
                            <span><?= e(matchBadge($topScore)); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="match-empty">No matches yet</div>
                    <?php endif; ?>
                </aside>
            </section>

            <section class="tamkeen-stats">
                <div>
                    <span><i class="fa fa-users"></i></span>
                    <strong><?= (int)$totalCandidates; ?></strong>
                    <b>Total Candidates</b>
                    <small>All available talent</small>
                </div>

                <div>
                    <span><i class="fa fa-layer-group"></i></span>
                    <strong><?= (int)$matchesCount; ?></strong>
                    <b>Matches</b>
                    <small>Based on filters</small>
                </div>

                <div>
                    <span><i class="fa fa-star"></i></span>
                    <strong><?= (int)$shortlistedCount; ?></strong>
                    <b>Shortlisted</b>
                    <small>Saved candidates</small>
                </div>

                <div>
                    <span><i class="fa fa-calendar-check"></i></span>
                    <strong><?= (int)$interviewsCount; ?></strong>
                    <b>Interviews</b>
                    <small>Scheduled and completed</small>
                </div>

                <div>
                    <span><i class="fa fa-shield-alt"></i></span>
                    <strong><?= (int)$pendingRequests; ?></strong>
                    <b>Approval Requests</b>
                    <small>Pending decision</small>
                </div>

                <div>
                    <span class="green"><i class="fa fa-briefcase"></i></span>
                    <strong><?= (int)$hiredCount; ?></strong>
                    <b>Hired</b>
                    <small>Approved candidates</small>
                </div>
            </section>

            <div class="dashboard-grid">
                <section class="panel pipeline-panel clean-card">
                    <h3><i class="fa fa-calendar-check"></i> Hiring Pipeline</h3>
                    <p>Track candidates through the full hiring process.</p>

                    <div class="line-pipeline">
                        <div>
                            <span><i class="fa fa-users"></i></span>
                            <b>Discover</b>
                            <strong><?= (int)$totalCandidates; ?></strong>
                        </div>

                        <div>
                            <span><i class="fa fa-star"></i></span>
                            <b>Shortlist</b>
                            <strong><?= (int)$shortlistedCount; ?></strong>
                        </div>

                        <div>
                            <span><i class="fa fa-calendar"></i></span>
                            <b>Interview</b>
                            <strong><?= (int)$interviewsCount; ?></strong>
                        </div>

                        <div>
                            <span><i class="fa fa-shield-alt"></i></span>
                            <b>Approval</b>
                            <strong><?= (int)$pendingRequests; ?></strong>
                        </div>

                        <div>
                            <span class="green"><i class="fa fa-briefcase"></i></span>
                            <b>Hired</b>
                            <strong><?= (int)$hiredCount; ?></strong>
                        </div>
                    </div>

                    <a href="interviews.php" class="view-pipeline">
                        View Interviews <i class="fa fa-arrow-right"></i>
                    </a>
                </section>

                <section class="panel activity-panel clean-card">
                    <div class="panel-title-row">
                        <h3><i class="fa fa-wave-square"></i> Recent Activity</h3>
                    </div>

                    <div class="activity-list">
                        <?php if (empty($activities)): ?>
                            <div>
                                <span><i class="fa fa-wave-square"></i></span>
                                <p><b>No activity yet</b></p>
                                <small>—</small>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($activities as $activity): ?>
                            <?php $activityInfo = activityMeta($activity['activity_type']); ?>
                            <div>
                                <span>
                                    <i class="fa <?= e($activityInfo['icon']); ?>"></i>
                                </span>

                                <p>
                                    <b><?= e($activity['full_name']); ?></b>
                                    <?= e($activityInfo['text']); ?>
                                </p>

                                <small>
                                    <?= date('M d, h:i A', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <section class="recommended clean-card">
                <div class="panel-title-row">
                    <div>
                        <h3><i class="fa fa-sparkles"></i> Recommended Candidates</h3>
                        <p>Candidates matched from this company tasks with 50%+ match score.</p>
                    </div>

                    <a href="candidates.php">View All</a>
                </div>

                <div class="dashboard-rec-grid">
                    <?php if (empty($candidates)): ?>
                        <div class="empty-card">
                            No recommended candidates found for this company yet.
                        </div>
                    <?php endif; ?>

                    <?php foreach (array_slice($candidates, 0, 6) as $index => $candidate): ?>
                        <?php $card = prepareCandidateCard($candidate, $index, $companyName); ?>

                        <article class="dash-rec-card">
                            <div class="dash-rec-photo-wrap">
                                <?php if (!empty($candidate['profile_image'])): ?>
                                    <?php // Candidate image path is saved from the main project root. ?>
                                    <img
                                    src="/tamkeentest/public/uploads/profile_images/<?= e(basename($candidate['profile_image'])); ?>"
                                        class="dash-rec-photo"
                                        alt="Candidate photo"
                                    >
                                <?php else: ?>
                                    <div class="dash-rec-photo dash-rec-initials">
                                        <?= e($card['candidateInitials']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="dash-rec-info">
                                <h4><?= e($candidate['full_name']); ?></h4>
                                <p><?= e($candidate['job_title'] ?: 'Job Seeker'); ?></p>

                                <div class="dash-rec-tags">
                                    <span>Score <?= (int)$candidate['earned_points']; ?></span>
                                    <span><?= (int)$candidate['submitted_tasks']; ?> Tasks</span>
                                </div>
                            </div>

                            <div class="dash-rec-score">
                                <strong><?= (int)$card['score']; ?>%</strong>
                                <span>Match</span>
                            </div>

                            <div class="dash-rec-actions">
                                <a
                            href="/tamkeentest/public/user/public-profile.php?id=<?= (int)$candidate['id']; ?>"
                                    class="dash-rec-btn dash-rec-view"
                                >
                                    View
                                </a>

                                <?php if (!empty($candidate['shortlisted_id'])): ?>
                                    <button
                                        type="button"
                                        class="dash-rec-btn dash-rec-shortlisted"
                                        disabled
                                    >
                                        Shortlisted
                                    </button>
                                <?php else: ?>
                                    <?php // Action file is inside z/hr-dashboard/actions/. ?>
                                    <form method="POST" action="../actions/add-to-shortlist.php">
                                        <input type="hidden" name="job_seeker_id" value="<?= (int)$candidate['id']; ?>">
                                        <input type="hidden" name="hiring_type" value="full_time">

                                        <button type="submit" class="dash-rec-btn dash-rec-shortlist">
                                            Shortlist
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a
                                    class="dash-rec-btn dash-rec-email"
                                    href="mailto:<?= e($candidate['email']); ?>?subject=<?= $card['mailtoSubject']; ?>&body=<?= $card['mailtoBody']; ?>"
                                >
                                    Email
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>
</div>

<div class="qr-modal" id="qrModal" aria-hidden="true">
    <div class="qr-modal-card">
        <button type="button" class="qr-close" id="closeQrScanner">×</button>

        <h3><i class="fa fa-qrcode"></i> Search by QR</h3>
        <p>Scan a candidate QR code. The dashboard will search the candidate automatically.</p>

        <video id="qrVideo" autoplay playsinline></video>

        <div class="qr-manual">
            <input type="text" id="manualQrValue" placeholder="Or paste QR value / candidate ID">
            <button type="button" id="applyManualQr">Search QR</button>
        </div>

        <small id="qrStatus">
            Camera scanner uses BarcodeDetector when supported by your browser.
        </small>
    </div>
</div>

<?php // QR scanner JavaScript is inside z/hr-dashboard/js/. ?>
<script src="/tamkeentest/public/hr/jshr/hr-qr-scanner.js"></script>

<?php
    }
}
