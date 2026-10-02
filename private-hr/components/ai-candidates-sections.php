<?php

if (!function_exists('renderHrAiCandidatesPage')) {

function renderHrAiCandidatesPage(array $page)
{
    extract($page);
?>

<div class="tamkeen-layout ai-hr-page">

    <?php include(__DIR__ . "/../../includes/hr-sidebar.php"); ?>

    <main class="tamkeen-main">
        <div class="tamkeen-container">

            <header class="tamkeen-topbar clean-hr-topbar">
                <div>
                    <span class="page-kicker">AI Hiring Review</span>
                    <h1><?= e($_SESSION['full_name'] ?? 'HR Manager'); ?></h1>
                    <p>Review candidates recommended by AI and approved by the Task Manager.</p>
                </div>

                <div class="topbar-company-profile">
                    <div>
                        <strong><?= e($companyName); ?></strong>
                        <span>HR Portal</span>
                    </div>

                    <?php if (!empty($companyLogo)): ?>
                        <img src="/tamkeentest/public/uploads/company/<?= e(basename($companyLogo)); ?>" alt="Company logo">
                    <?php else: ?>
                        <div class="company-letter"><?= e($avatarLetter); ?></div>
                    <?php endif; ?>
                </div>
            </header>

            <section class="tamkeen-hero">
                <div class="hero-copy">
                    <span class="hero-badge">
                        <i class="fa fa-robot"></i> AI + Reviewer Recommendations
                    </span>

                    <h2 style="color:#fff;">Review hiring recommendations faster.</h2>

                    <p>
                        This page shows candidates already recommended by AI or sent by the Task Manager,
                        so HR can shortlist the best matches quickly.
                    </p>

                    <form method="GET" class="tamkeen-search">
                        <div class="search-input">
                            <i class="fa fa-search"></i>
                            <input
                                type="text"
                                name="search"
                                value="<?= e($search); ?>"
                                placeholder="Search candidate, email, or task..."
                            >
                        </div>

                        <select name="filter">
                            <option value="all" <?= $filter === 'all' ? 'selected' : ''; ?>>All Recommendations</option>
                            <option value="ai" <?= $filter === 'ai' ? 'selected' : ''; ?>>AI Recommended</option>
                            <option value="reviewer" <?= $filter === 'reviewer' ? 'selected' : ''; ?>>Reviewer Recommended</option>
                            <option value="shortlisted" <?= $filter === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                            <option value="hired" <?= $filter === 'hired' ? 'selected' : ''; ?>>Hired</option>
                            <option value="rejected" <?= $filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>

                        <button type="submit" class="search-btn">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </form>
                </div>

                <aside class="top-match-card">
                    <div class="match-header">
                        <h3><i class="fa fa-medal"></i> Recommendation Center</h3>
                    </div>

                    <div class="big-score">
                        <?= (int)($stats['total'] ?? 0); ?>
                    </div>

                    <p>Total recommended submissions</p>

                    <div class="match-pills">
                        <span>AI <?= (int)($stats['ai_recommended'] ?? 0); ?></span>
                        <span>Reviewer <?= (int)($stats['reviewer_recommended'] ?? 0); ?></span>
                        <span>Hired <?= (int)($stats['hired'] ?? 0); ?></span>
                    </div>
                </aside>
            </section>

            <section class="tamkeen-stats">
                <div>
                    <span><i class="fa fa-users"></i></span>
                    <strong><?= (int)($stats['total'] ?? 0); ?></strong>
                    <b>Total</b>
                    <small>Recommended pool</small>
                </div>

                <div>
                    <span><i class="fa fa-robot"></i></span>
                    <strong><?= (int)($stats['ai_recommended'] ?? 0); ?></strong>
                    <b>AI Recommended</b>
                    <small>AI evaluation</small>
                </div>

                <div>
                    <span><i class="fa fa-user-check"></i></span>
                    <strong><?= (int)($stats['reviewer_recommended'] ?? 0); ?></strong>
                    <b>Reviewer Sent</b>
                    <small>Task manager approved</small>
                </div>

                <div>
                    <span><i class="fa fa-star"></i></span>
                    <strong><?= (int)($stats['shortlisted'] ?? 0); ?></strong>
                    <b>Shortlisted</b>
                    <small>HR selected</small>
                </div>

                <div>
                    <span class="green"><i class="fa fa-briefcase"></i></span>
                    <strong><?= (int)($stats['hired'] ?? 0); ?></strong>
                    <b>Hired</b>
                    <small>Final HR decision</small>
                </div>
            </section>

            <section class="clean-card">
                <div class="panel-title-row">
                    <div>
                        <span class="page-kicker">Recommended Candidates</span>
                        <h3><i class="fa fa-sparkles"></i> AI & Reviewer Candidate Results</h3>
                        <p><?= count($candidates); ?> candidate submission<?= count($candidates) === 1 ? '' : 's'; ?> found.</p>
                    </div>

                    <a href="/tamkeentest/public/hr/candidates.php">
                        Browse All Candidates
                    </a>
                </div>

                <?php if (empty($candidates)): ?>

                    <div class="empty-card">
                        <h4>No recommended candidates yet</h4>
                        <p>AI recommended or reviewer recommended submissions will appear here.</p>
                    </div>

                <?php else: ?>

                    <div class="candidate-list modern-candidate-list">

                        <?php foreach ($candidates as $index => $candidate): ?>

                            <?php
                                $score = (int)($candidate['ai_score'] ?? 0);
                                $fitLabel = 'Needs Review';
                                $fitClass = '';

                                if ($score >= 85) {
                                    $fitLabel = 'Excellent Match';
                                    $fitClass = 'excellent';
                                } elseif ($score >= 70) {
                                    $fitLabel = 'Good Potential';
                                    $fitClass = 'strong';
                                }

                                $sourceLabels = [];

                                if (($candidate['ai_status'] ?? '') === 'recommended') {
                                    $sourceLabels[] = 'AI Recommended';
                                }

                                if ((int)($candidate['recommended_to_hr'] ?? 0) === 1) {
                                    $sourceLabels[] = 'Reviewer Recommended';
                                }

                                $hrStatus = $candidate['hr_status'] ?? 'pending';
                            ?>

                            <article class="candidate-card candidate-premium-card">

                                <div class="candidate-rank">
                                    #<?= $index + 1; ?>
                                </div>

                                <div class="candidate-main premium-candidate-main">

                                    <?php if (!empty($candidate['profile_image'])): ?>
                                        <img
                                            class="candidate-avatar premium-avatar"
                                            src="/tamkeentest/public/uploads/profile_images/<?= e(basename($candidate['profile_image'])); ?>"
                                            alt="Candidate photo"
                                        >
                                    <?php else: ?>
                                        <div class="candidate-avatar premium-avatar">
                                            <?= e(initials($candidate['full_name'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="candidate-info-block">

                                        <div class="candidate-name-row">
                                            <h5><?= e($candidate['full_name']); ?></h5>
                                            <span class="fit-pill <?= e($fitClass); ?>">
                                                <?= e($fitLabel); ?>
                                            </span>
                                        </div>

                                        <p class="candidate-meta">
                                            <?= e($candidate['job_title'] ?: 'Job Seeker'); ?>
                                            <span>•</span>
                                            <?= e($candidate['email']); ?>
                                        </p>

                                        <p class="candidate-email-line">
                                            <i class="fa fa-tasks"></i>
                                            Task:
                                            <strong><?= e($candidate['task_title']); ?></strong>
                                            —
                                            <?= e($candidate['category']); ?>
                                            /
                                            <?= e($candidate['difficulty']); ?>
                                        </p>

                                        <div class="score-bar-wrap">
                                            <div class="score-bar-top">
                                                <span>AI Score</span>
                                                <strong><?= $score; ?>%</strong>
                                            </div>

                                            <div class="score-bar">
                                                <span style="width:<?= min(100, max(0, $score)); ?>%"></span>
                                            </div>
                                        </div>

                                        <div class="candidate-tags premium-tags">
                                            <?php foreach ($sourceLabels as $label): ?>
                                                <span><?= e($label); ?></span>
                                            <?php endforeach; ?>

                                            <span>Final Points: <?= e($candidate['final_points'] ?? '-'); ?></span>
                                            <span>HR: <?= e(ucfirst($hrStatus)); ?></span>
                                        </div>

                                    </div>
                                </div>

                                <div class="candidate-actions premium-actions">
                                    <form method="POST">
                                        <input type="hidden" name="attempt_id" value="<?= (int)$candidate['id']; ?>">
                                        <input type="hidden" name="candidate_id" value="<?= (int)$candidate['candidate_id']; ?>">

                                        <button
                                            type="submit"
                                            name="action"
                                            value="shortlist"
                                            class="btn-primary-modern"
                                        >
                                            Shortlist
                                        </button>
                                    </form>
                                </div>

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
