<?php

if (!function_exists('renderHrShortlistPage')) {

    function renderHrShortlistPage(array $pageData)
    {
        extract($pageData);
?>

<div class="tamkeen-layout">
    <?php include(__DIR__ . "/../../includes/hr-sidebar.php"); ?>

    <main class="tamkeen-main">
        <div class="tamkeen-container">

            <header class="tamkeen-topbar clean-hr-topbar shortlist-topbar">
                <div>
                    <span class="page-kicker">HR Shortlist</span>
                    <h1><?= htmlspecialchars($_SESSION['full_name'] ?? 'HR Manager'); ?></h1>
                    <p>Review saved candidates and contact talent.</p>
                </div>

                <div class="topbar-company-profile">
                    <div>
                        <strong><?= htmlspecialchars($companyName); ?></strong>
                        <span>HR Portal</span>
                    </div>

                    <?php if (!empty($companyLogo)): ?>
                        <img src="/tamkeentest/public/uploads/company/<?= htmlspecialchars(basename($companyLogo)); ?>" alt="Company logo">
                    <?php else: ?>
                        <div class="company-letter"><?= htmlspecialchars($avatarLetter); ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <section class="shortlist-hero shortlist-hero-polished">
                <div>
                    <span class="hero-badge"><i class="fa fa-star"></i> Shortlisted Talent</span>
<h1 style="color: #fff;">Review your saved candidates.</h1>                    <p>Check candidate scores, view profiles, send interview emails, or remove candidates from your shortlist.</p>

                    <div class="shortlist-hero-mini-stats">
                        <span><b><?= (int)($stats['total_count'] ?? 0); ?></b> saved</span>
                        <span><b><?= (int)$readyForInterview; ?></b> ready</span>
                        <span><b><?= (int)($stats['waiting_count'] ?? 0); ?></b> waiting</span>
                    </div>
                </div>

                <div class="shortlist-best-card shortlist-best-polished">
                    <span>Best shortlisted candidate</span>

                    <?php if ($bestCandidate): ?>
                        <div class="shortlist-best-person">
                            <?php if (!empty($bestCandidate['profile_image'])): ?>
                                <img src="/tamkeentest/public/uploads/profile_images/<?= htmlspecialchars(basename($bestCandidate['profile_image'])); ?>" alt="<?= htmlspecialchars($bestCandidate['full_name']); ?> profile photo">
                            <?php else: ?>
                                <div><?= htmlspecialchars(shortlist_initials($bestCandidate['full_name'])); ?></div>
                            <?php endif; ?>

                            <section>
                                <strong><?= htmlspecialchars($bestCandidate['full_name']); ?></strong>
                                <p><?= htmlspecialchars($bestCandidate['job_title'] ?: 'Job Seeker'); ?></p>
                            </section>
                        </div>

                        <div class="shortlist-best-score">
                            <b><?= shortlist_score($bestCandidate); ?>%</b>
                            <small><?= (int)$bestCandidate['submitted_tasks']; ?> submitted task<?= (int)$bestCandidate['submitted_tasks'] === 1 ? '' : 's'; ?></small>
                        </div>
                    <?php else: ?>
                        <strong>No candidates yet</strong>
                        <p>Add candidates from the Candidates page.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="shortlist-stats-row">
                <div>
                    <i class="fa fa-star"></i>
                    <section>
                        <strong><?= (int)($stats['total_count'] ?? 0); ?></strong>
                        <span>Total Shortlisted</span>
                    </section>
                </div>

                <div>
                    <i class="fa fa-bolt"></i>
                    <section>
                        <strong><?= (int)$readyForInterview; ?></strong>
                        <span>Ready for Interview</span>
                    </section>
                </div>

                <div>
                    <i class="fa fa-envelope"></i>
                    <section>
                        <strong><?= (int)($stats['waiting_count'] ?? 0); ?></strong>
                        <span>Email / Waiting</span>
                    </section>
                </div>
            </section>

            <form method="GET" class="shortlist-filter-bar">
                <input type="text" name="search" placeholder="Search shortlist by name, skill, email..." value="<?= htmlspecialchars($search); ?>">

                <select name="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="new" <?= $status === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="waiting_reply" <?= $status === 'waiting_reply' ? 'selected' : ''; ?>>Waiting Reply</option>
                    <option value="replied" <?= $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>

                <select name="type">
                    <option value="all" <?= $type === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <option value="full_time" <?= $type === 'full_time' ? 'selected' : ''; ?>>Full-time</option>
                    <option value="internship" <?= $type === 'internship' ? 'selected' : ''; ?>>Internship</option>
                    <option value="temporary" <?= $type === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                    <option value="freelance" <?= $type === 'freelance' ? 'selected' : ''; ?>>Freelance</option>
                </select>

                <button type="submit"><i class="fa fa-filter"></i> Filter</button>
                <a href="shortlist.php">Reset</a>
            </form>

            <div class="shortlist-workspace">
                <section class="clean-card shortlist-list-panel">
                    <div class="panel-title-row">
                        <div>
                            <span class="page-kicker">Saved Candidates</span>
                            <h3>Candidate Shortlist</h3>
                            <p><?= count($shortlists); ?> candidate<?= count($shortlists) === 1 ? '' : 's'; ?> ready for review.</p>
                        </div>

                        <a href="candidates.php">Browse Candidates <i class="fa fa-arrow-right"></i></a>
                    </div>

                    <?php if (empty($shortlists)): ?>
                        <div class="empty-card">
                            <h4>No shortlisted candidates</h4>
                            <p>Add strong candidates from the Candidates page.</p>
                        </div>
                    <?php else: ?>
                        <div class="shortlist-cards">
                            <?php foreach ($shortlists as $index => $candidate): ?>
                                <?php
                                    $score = shortlist_score($candidate);
                                    $avatar = shortlist_initials($candidate['full_name'] ?? 'User');

                                    $skillsList = array_filter(array_map('trim', explode(',', $candidate['skills'] ?: '')));
                                    $visibleSkills = array_slice($skillsList, 0, 2);
                                    $hiddenSkillsCount = max(count($skillsList) - count($visibleSkills), 0);

                                    $fitLabel = $score >= 85 ? 'Excellent Match' : ($score >= 70 ? 'Good Potential' : 'Needs Review');
                                    $fitClass = $score >= 85 ? 'excellent' : ($score >= 70 ? 'strong' : '');
                                    $typeLabel = ucwords(str_replace('_', ' ', $candidate['hiring_type'] ?? 'full_time'));

                                    $workflow = 'New';
                                    $workflowClass = 'new';

                                    if (($candidate['status'] ?? '') === 'replied') {
                                        $workflow = 'Candidate Replied';
                                        $workflowClass = 'replied';
                                    } elseif (($candidate['status'] ?? '') === 'waiting_reply') {
                                        $workflow = 'Email Sent';
                                        $workflowClass = 'waiting';
                                    }
                                ?>

                                <article class="shortlist-card">
                                    <div class="candidate-rank">#<?= $index + 1; ?></div>

                                    <div class="shortlist-person">
                                        <?php if (!empty($candidate['profile_image'])): ?>
                                            <img src="/tamkeentest/public/uploads/profile_images/<?= htmlspecialchars(basename($candidate['profile_image'])); ?>" alt="<?= htmlspecialchars($candidate['full_name']); ?> profile photo">
                                        <?php else: ?>
                                            <div><?= htmlspecialchars($avatar); ?></div>
                                        <?php endif; ?>

                                        <section>
                                            <div class="candidate-name-row">
                                                <h4><?= htmlspecialchars($candidate['full_name']); ?></h4>
                                                <span class="fit-pill <?= $fitClass; ?>"><?= htmlspecialchars($fitLabel); ?></span>
                                            </div>

                                            <p>
                                                <?= htmlspecialchars($candidate['job_title'] ?: 'Job Seeker'); ?>
                                                <b>•</b>
                                                <?= htmlspecialchars($candidate['location'] ?: 'Location not set'); ?>
                                                <b>•</b>
                                                <?= (int)($candidate['years_experience'] ?? 0); ?> yrs exp.
                                            </p>

                                            <small><i class="fa fa-envelope"></i> <?= htmlspecialchars($candidate['email']); ?></small>
                                        </section>
                                    </div>

                                    <div class="shortlist-score">
                                        <div class="shortlist-score-top">
                                            <span>Match Score</span>
                                            <strong><?= $score; ?>%</strong>
                                        </div>

                                        <div class="score-bar">
                                            <span style="width:<?= $score; ?>%"></span>
                                        </div>

                                        <div class="candidate-tags premium-tags">
                                            <span><i class="fa fa-clipboard-check"></i> <?= (int)$candidate['submitted_tasks']; ?> Tasks</span>
                                            <span><i class="fa fa-trophy"></i> <?= (int)$candidate['earned_points']; ?> Points</span>
                                            <span><?= htmlspecialchars($typeLabel); ?></span>

                                            <?php foreach ($visibleSkills as $skillName): ?>
                                                <span><?= htmlspecialchars($skillName); ?></span>
                                            <?php endforeach; ?>

                                            <?php if ($hiddenSkillsCount > 0): ?>
                                                <span>+<?= $hiddenSkillsCount; ?> more</span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="mini-note">
                                            <i class="fa fa-graduation-cap"></i>
                                            <?= htmlspecialchars($candidate['institution'] ?: 'Education not added'); ?>
                                            <?= !empty($candidate['education_field']) ? ' · ' . htmlspecialchars($candidate['education_field']) : ''; ?>
                                        </p>
                                    </div>

                                    <div class="shortlist-actions">
                                        <span class="workflow-pill <?= $workflowClass; ?>"><?= htmlspecialchars($workflow); ?></span>

                                        <a class="btn-soft" href="/tamkeentest/public/user/public-profile.php?id=<?= (int)$candidate['job_seeker_id']; ?>">View</a>

                                        <a class="btn-email" href="mailto:<?= htmlspecialchars($candidate['email']); ?>?subject=Interview Invitation from <?= rawurlencode($companyName); ?>">
                                            <i class="fa fa-envelope"></i> Email
                                        </a>

                                        <form method="POST" action="/tamkeentest/public/hr/actions/remove-shortlist.php" class="remove-shortlist-form">
                                            <input type="hidden" name="shortlist_id" value="<?= (int)$candidate['id']; ?>">
                                            <button type="button" class="btn-remove js-open-remove-modal" data-candidate-name="<?= htmlspecialchars($candidate['full_name']); ?>">
                                                <i class="fa fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="shortlist-side">
                    <section class="candidate-side-card">
                        <span class="page-kicker">Next Steps</span>
                        <h3>Quick Actions</h3>
                        <p>Use this page to review and contact shortlisted candidates.</p>

                        <a href="candidates.php" class="side-action-item">
                            <i class="fa fa-users"></i>
                            <div>
                                <strong>Find Candidates</strong>
                                <span>Add more talent to your shortlist.</span>
                            </div>
                        </a>

                        <a href="#interviewTemplateText" class="side-action-item">
                            <i class="fa fa-paper-plane"></i>
                            <div>
                                <strong>Email Template</strong>
                                <span>Copy a professional interview message.</span>
                            </div>
                        </a>
                    </section>

                    <section class="shortlist-email-template">
                        <span class="page-kicker">Template</span>
                        <h3>Interview Email</h3>
                        <p>Use this message when contacting candidates.</p>

                        <div class="shortlist-template-box" id="interviewTemplateText">
                            Hello [Candidate Name],<br><br>
                            We reviewed your task performance and would like to invite you for the next hiring step with <?= htmlspecialchars($companyName); ?>.
                        </div>

                        <button type="button" class="copy-template-btn" id="copyInterviewTemplate">
                            <i class="fa fa-copy"></i> Copy Template
                        </button>
                    </section>
                </aside>
            </div>

        </div>
    </main>
</div>

<div class="remove-modal" id="removeShortlistModal" aria-hidden="true">
    <div class="remove-modal-card" role="dialog" aria-modal="true" aria-labelledby="removeModalTitle">
        <button type="button" class="remove-modal-close" id="closeRemoveModal" aria-label="Close">×</button>

        <div class="remove-modal-icon"><i class="fa fa-trash"></i></div>

        <h3 id="removeModalTitle">Remove candidate?</h3>
        <p id="removeModalText">Are you sure you want to remove this candidate from your shortlist?</p>

        <div class="remove-modal-actions">
            <button type="button" class="remove-modal-cancel" id="cancelRemoveModal">Cancel</button>
            <button type="button" class="remove-modal-confirm" id="confirmRemoveModal">Yes, remove</button>
        </div>
    </div>
</div>
<script src="/tamkeentest/public/hr/jshr/shortlist.js"></script>

<?php
    }
}
