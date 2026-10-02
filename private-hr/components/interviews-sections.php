<?php

if (!function_exists('renderHrInterviewsPage')) {

    function renderHrInterviewsPage(array $page)
    {
        extract($page);
?>

<div class="tamkeen-layout interviews-page">
    <?php include(__DIR__ . "/../../includes/hr-sidebar.php"); ?>

    <main class="tamkeen-main">
        <div class="tamkeen-container">

            <header class="tamkeen-topbar clean-hr-topbar interviews-topbar">
                <div>
                    <span class="page-kicker">Interview Center</span>
                    <h1><?= e($_SESSION['full_name'] ?? 'HR Manager'); ?></h1>
                    <p>Schedule interviews, track progress, and move strong candidates toward approval.</p>
                </div>

                <div class="topbar-company-profile">
                    <div>
                        <strong><?= e($companyName); ?></strong>
                        <span>HR Portal</span>
                    </div>

                    <?php if (!empty($company['company_logo'])): ?>
                        <img src="/tamkeentest/public/uploads/company/<?= e(basename($company['company_logo'])); ?>" alt="Company logo">
                    <?php else: ?>
                        <div class="company-letter"><?= e($avatarLetter); ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (!empty($successMessage)): ?>
                <div class="interview-alert success">
                    <i class="fa fa-check-circle"></i><?= e($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="interview-alert error">
                    <i class="fa fa-triangle-exclamation"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?= e($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <section class="interviews-hero">
                <div>
                    <span class="hero-badge"><i class="fa fa-calendar-check"></i> Interviews</span>
<h2 style="color: #fff;">Plan candidate interviews without losing the hiring trail.</h2>
                    <p>Pick a shortlisted candidate, add the interviewer details, choose the date and time, then track the interview until it is completed.</p>
                    <button type="button" class="schedule-interview-btn" id="openInterviewModal">
                        <i class="fa fa-plus"></i> Schedule Interview
                    </button>
                </div>

                <aside class="next-interview-card">
                    <span>Next Interview</span>
                    <?php if ($nextInterview): ?>
                        <strong><?= e($nextInterview['full_name']); ?></strong>
                        <p><?= e(formatInterviewDateTime($nextInterview['interview_date'], $nextInterview['interview_time'])); ?></p>
                        <small><i class="fa fa-user-tie"></i><?= e($nextInterview['interviewer_name']); ?></small>
                    <?php else: ?>
                        <strong>No interviews scheduled</strong>
                        <p>Schedule your first interview from this page.</p>
                    <?php endif; ?>
                </aside>
            </section>

            <section class="interviews-stats-row">
                <div><i class="fa fa-calendar-days"></i><section><strong><?= (int)$stats['total']; ?></strong><span>Total Interviews</span></section></div>
                <div><i class="fa fa-clock"></i><section><strong><?= (int)$stats['scheduled']; ?></strong><span>Scheduled</span></section></div>
                <div><i class="fa fa-circle-check"></i><section><strong><?= (int)$stats['completed']; ?></strong><span>Completed</span></section></div>
                <div><i class="fa fa-ban"></i><section><strong><?= (int)$stats['cancelled']; ?></strong><span>Cancelled</span></section></div>
            </section>

            <form method="GET" class="interviews-filter-bar">
                <input type="text" name="search" value="<?= e($filters['search']); ?>" placeholder="Search by candidate, interviewer, email...">

                <select name="status">
                    <option value="all" <?= $filters['status'] === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <select name="type">
                    <option value="all" <?= $filters['type'] === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <option value="online" <?= $filters['type'] === 'online' ? 'selected' : ''; ?>>Online</option>
                    <option value="onsite" <?= $filters['type'] === 'onsite' ? 'selected' : ''; ?>>On-site</option>
                    <option value="phone" <?= $filters['type'] === 'phone' ? 'selected' : ''; ?>>Phone</option>
                </select>

                <button type="submit"><i class="fa fa-filter"></i> Filter</button>
                <a href="interviews.php">Reset</a>
            </form>

            <div class="interviews-workspace">
                <section class="clean-card interviews-list-panel" id="interviews-list">
                    <div class="panel-title-row">
                        <div>
                            <span class="page-kicker">Interview Schedule</span>
                            <h3>Candidate Interviews</h3>
                            <p><?= count($interviews); ?> interview<?= count($interviews) === 1 ? '' : 's'; ?> found.</p>
                        </div>

                        <button type="button" class="panel-add-btn" id="openInterviewModalTop">
                            <i class="fa fa-plus"></i> Add Interview
                        </button>
                    </div>

                    <?php if (empty($interviews)): ?>
                        <div class="empty-card interviews-empty">
                            <h4>No interviews yet</h4>
                            <p>Schedule interviews for candidates who replied or are ready for the next hiring step.</p>
                        </div>
                    <?php else: ?>
                        <div class="interview-cards">
                            <?php foreach ($interviews as $index => $interview): ?>
                                <?php
                                    $avatar = initials($interview['full_name'] ?? 'Candidate');
                                    $statusClass = interviewStatusClass($interview['status']);
                                    $typeLabel = interviewTypeLabel($interview['interview_type']);
                                ?>

                                <article class="interview-card">
                                    <div class="interview-rank">#<?= $index + 1; ?></div>

                                    <div class="interview-person">
                                        <?php if (!empty($interview['profile_image'])): ?>
                                            <img src="/tamkeentest/public/uploads/profile_images/<?= e(basename($interview['profile_image'])); ?>" alt="Candidate photo">
                                        <?php else: ?>
                                            <div><?= e($avatar); ?></div>
                                        <?php endif; ?>

                                        <section>
                                            <div class="interview-name-row">
                                                <h4><?= e($interview['full_name']); ?></h4>
                                                <span class="interview-status <?= e($statusClass); ?>">
                                                    <?= e(ucfirst($interview['status'])); ?>
                                                </span>
                                            </div>

                                            <p>
                                                <?= e($interview['job_title'] ?: 'Job Seeker'); ?>
                                                <b>•</b>
                                                <?= e($interview['location'] ?: 'Location not set'); ?>
                                            </p>

                                            <small><i class="fa fa-envelope"></i><?= e($interview['email']); ?></small>
                                        </section>
                                    </div>

                                    <div class="interview-details">
                                        <div>
                                            <i class="fa fa-calendar"></i>
                                            <strong><?= e(formatInterviewDateTime($interview['interview_date'], $interview['interview_time'])); ?></strong>
                                            <span>Date & Time</span>
                                        </div>

                                        <div>
                                            <i class="fa fa-user-tie"></i>
                                            <strong><?= e($interview['interviewer_name']); ?></strong>
                                            <span><?= e($interview['interviewer_phone']); ?></span>
                                        </div>

                                        <div>
                                            <i class="fa fa-video"></i>
                                            <strong><?= e($typeLabel); ?></strong>
                                            <span><?= e($interview['location_or_link'] ?: 'No link/location'); ?></span>
                                        </div>
                                    </div>

                                    <div class="interview-note">
                                        <span>Notes</span>
                                        <p><?= e($interview['notes'] ?: 'No notes added.'); ?></p>
                                    </div>

                                    <div class="interview-actions">
                                        <?php if ($interview['status'] === 'scheduled'): ?>

                                            <form method="POST">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="interview_id" value="<?= (int)$interview['id']; ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="iv-btn iv-btn-complete">
                                                    <i class="fa fa-check"></i> Mark Completed
                                                </button>
                                            </form>

                                            <form method="POST">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="interview_id" value="<?= (int)$interview['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="iv-btn iv-btn-cancel">
                                                    <i class="fa fa-times"></i> Cancel
                                                </button>
                                            </form>

                                        <?php elseif ($interview['status'] === 'completed'): ?>

                                            <?php if (!empty($interview['hiring_request_id'])): ?>
                                                <span class="iv-approval-badge">
                                                    <i class="fa fa-shield-alt"></i> Hiring Approval Submitted
                                                </span>
                                            <?php else: ?>
                                                <form method="POST" action="/tamkeentest/public/hr/actions/create-hiring-request.php">
                                                    <input type="hidden" name="job_seeker_id" value="<?= (int)$interview['job_seeker_id']; ?>">
                                                    <input type="hidden" name="hiring_type" value="full_time">
                                                    <button type="submit" class="iv-btn iv-btn-approval">
                                                        <i class="fa fa-paper-plane"></i> Send Hiring Approval
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <span class="iv-cancelled-badge">
                                                <i class="fa fa-ban"></i> Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

        </div>
    </main>
</div>

<div class="interview-modal" id="interviewModal" aria-hidden="true">
    <div class="interview-modal-card">
        <button type="button" class="interview-modal-close" id="closeInterviewModal">×</button>

        <h3>Schedule Interview</h3>
        <p>Add candidate interview details and save it to the hiring trail.</p>

        <form method="POST" class="interview-form" id="scheduleInterviewForm">
            <input type="hidden" name="action" value="create_interview">

            <label class="full-field">
                Candidate
                <select name="job_seeker_id" required>
                    <option value="">Select candidate</option>
                    <?php foreach ($candidateOptions as $candidate): ?>
                        <option value="<?= (int)$candidate['job_seeker_id']; ?>">
                            <?= e($candidate['full_name']); ?> — <?= e($candidate['job_title'] ?: 'Job Seeker'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Interviewer Name
                <input type="text" name="interviewer_name" required minlength="3" placeholder="Example: Sara Ahmad">
            </label>

            <label>
                Interviewer Phone
                <input type="text" name="interviewer_phone" required pattern="07[789][0-9]{7}" placeholder="0771234567">
                <small>Jordanian number: 077 / 078 / 079 + 7 digits</small>
            </label>

            <label>
                Interview Date
                <input type="date" name="interview_date" required min="<?= date('Y-m-d'); ?>">
            </label>

            <label>
                Interview Time
                <input type="time" name="interview_time" required>
            </label>

            <label>
                Interview Type
                <select name="interview_type" required>
                    <option value="">Select type</option>
                    <option value="online">Online</option>
                    <option value="onsite">On-site</option>
                    <option value="phone">Phone</option>
                </select>
            </label>

            <label>
                Link / Location
                <input type="text" name="location_or_link" placeholder="Google Meet link or office location">
            </label>

            <label class="full-field">
                Notes
                <textarea name="notes" rows="4" placeholder="Write interview notes..."></textarea>
            </label>

            <div class="form-actions full-field">
                <button type="button" class="interview-btn soft" id="cancelInterviewModal">Cancel</button>
                <button type="submit" class="interview-btn primary">Save Interview</button>
            </div>
        </form>
    </div>
</div>

<script src="/tamkeentest/public/hr/jshr/interviews.js"></script>

<?php
    }
}
