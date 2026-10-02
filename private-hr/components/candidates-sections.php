<?php

if (!function_exists('renderHrCandidatesPage')) {

    function renderHrCandidatesPage(array $page)
    {
        extract($page);
        extract($stats);
?>
<div class="tamkeen-layout">
    <?php
    // Global sidebar path from /z/hr-dashboard/hr/
    include(__DIR__ . "/../../includes/hr-sidebar.php");
    ?>

    <main class="tamkeen-main">
        <div class="tamkeen-container candidates-page">

            <header class="tamkeen-topbar clean-hr-topbar candidates-topbar">
                <div>
                    <span>Talent Discovery</span>
                    <h1>Candidates</h1>
                </div>

                <div class="topbar-company-profile">
                    <div>
                        <strong><?= htmlspecialchars($companyName); ?></strong>
                        <span>HR Talent Pool</span>
                    </div>
<?php if (!empty($companyLogo)): ?>
    <img
        src="/tamkeentest/public/uploads/company/<?= htmlspecialchars(basename($companyLogo)); ?>"
        alt="Company logo"
    >
<?php else: ?>
    <div class="company-letter"><?= htmlspecialchars($avatarLetter); ?></div>
<?php endif; ?>                </div>
            </header>

            <section class="tamkeen-hero candidates-hero-clean">
                <div class="hero-copy">
                    <span class="hero-badge"><i class="fa fa-users"></i> Candidate Search</span>
<h2 style="color: #fff;">Find the right talent from real task results.</h2>                    <p>Search by name, email, location, submitted company tasks, and hiring status. Keep the workflow simple: shortlist, email, then send approval.</p>

                    <form method="GET" class="tamkeen-search candidates-search-clean">
                        <div class="search-input">
                            <i class="fa fa-search"></i>
                            <input type="text" name="search" placeholder="Search name, skill, email, title..." value="<?= htmlspecialchars($search); ?>">
                        </div>

                        <button type="submit" class="search-btn"><i class="fa fa-bolt"></i> Find Talent</button>
                    </form>
                </div>

                <div class="top-match-card candidates-best-card">
                    <div class="match-header">
                        <h3><i class="fa fa-medal"></i> Best Match</h3>
                        <strong><?= $topCandidate ? htmlspecialchars($topCandidate['match_score']) : 0; ?>%</strong>
                    </div>

                    <?php if ($topCandidate): ?>
                        <div class="match-person">
                            <?php if (!empty($topCandidate['profile_image'])): ?>
                                <img src="/tamkeentest/public/uploads/profile_images/<?= htmlspecialchars(basename($topCandidate['profile_image'])); ?>" alt="">
                            <?php else: ?>
                                <div class="match-avatar"><?= htmlspecialchars(strtoupper(substr($topCandidate['full_name'] ?? 'U', 0, 1))); ?></div>
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($topCandidate['full_name']); ?></strong>
                                <span><?= htmlspecialchars($topCandidate['job_title'] ?: 'Job Seeker'); ?></span>
                            </div>
                        </div>
                        <div class="candidate-score-mini"><span style="width: <?= (int)$topCandidate['match_score']; ?>%"></span></div>
                        <p>Top candidate based on submitted tasks for your company.</p>
                    <?php else: ?>
                        <p>No candidates match your current filters.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="tamkeen-stats candidates-stats-clean">
                <div><span><i class="fa fa-users"></i></span><strong><?= $totalCandidates; ?></strong><b>Total Candidates</b><small>Available talent pool</small></div>
                <div><span><i class="fa fa-layer-group"></i></span><strong><?= count($candidates); ?></strong><b>Current Matches</b><small>Based on filters</small></div>
                <div><span><i class="fa fa-star"></i></span><strong><?= $shortlistedCount; ?></strong><b>Shortlisted</b><small>Saved candidates</small></div>
                <div><span><i class="fa fa-clipboard-check"></i></span><strong><?= $submittedTasksCount; ?></strong><b>Tasks Submitted</b><small>Proof-based results</small></div>
                <div><span class="green"><i class="fa fa-shield-alt"></i></span><strong><?= $requestsCount; ?></strong><b>Approval Requests</b><small>Pending decisions</small></div>
                <div><span><i class="fa fa-chart-line"></i></span><strong><?= $avgScore; ?>%</strong><b>Avg Match</b><small>Visible candidates</small></div>
            </section>

            <form method="GET" class="clean-card candidates-filter-clean">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">

                <select name="location">
                    <option value="all">All Locations</option>
                    <?php foreach ($locations as $place): ?>
                        <option value="<?= htmlspecialchars($place); ?>" <?= $location === $place ? 'selected' : ''; ?>><?= htmlspecialchars($place); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="tasks">
                    <option value="all" <?= $tasksFilter === 'all' ? 'selected' : ''; ?>>All Tasks</option>
                    <option value="submitted" <?= $tasksFilter === 'submitted' ? 'selected' : ''; ?>>Submitted Tasks</option>
                    <option value="multi" <?= $tasksFilter === 'multi' ? 'selected' : ''; ?>>2+ Tasks</option>
                </select>

                <select name="status">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="new" <?= $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="review" <?= $statusFilter === 'review' ? 'selected' : ''; ?>>Needs Review</option>
                    <option value="shortlisted" <?= $statusFilter === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                    <option value="approval" <?= $statusFilter === 'approval' ? 'selected' : ''; ?>>Approval</option>
                </select>

                <select name="experience">
                    <option value="all" <?= $experienceFilter === 'all' ? 'selected' : ''; ?>>All Experience</option>
                    <option value="fresh" <?= $experienceFilter === 'fresh' ? 'selected' : ''; ?>>0 - 1 Year</option>
                    <option value="mid" <?= $experienceFilter === 'mid' ? 'selected' : ''; ?>>2 - 4 Years</option>
                    <option value="senior" <?= $experienceFilter === 'senior' ? 'selected' : ''; ?>>5+ Years</option>
                </select>

                <button type="submit"><i class="fa fa-filter"></i> Apply Filters</button>
                <a href="candidates.php">Reset</a>
            </form>

            <div class="dashboard-grid candidates-grid-clean">
                <section class="clean-card panel candidates-results-clean">
                    <div class="panel-title-row">
                        <div>
                            <h3><i class="fa fa-sparkles"></i> Candidate Results</h3>
                            <p><?= count($candidates); ?> candidates match your current filters.</p>
                        </div>
                        <a href="shortlist.php">Open Shortlist</a>
                    </div>

                    <?php if (empty($candidates)): ?>
                        <div class="empty-card">
                            <i class="fa fa-search"></i>
                            <h4>No candidates found</h4>
                            <p>Try removing one filter or searching by a broader name, email, or title.</p>
                        </div>
                    <?php else: ?>
                        <div class="candidate-list modern-candidate-list candidates-list-clean">
                            <?php foreach ($candidates as $index => $candidate): ?>
                                <?php
                                    $score = (int)$candidate['match_score'];
                                    $skillsList = array_filter(array_map('trim', explode(',', $candidate['skills'] ?: '')));
                                    $visibleSkills = array_slice($skillsList, 0, 3);
                                    $extraSkills = max(0, count($skillsList) - count($visibleSkills));
                                    $avatar = strtoupper(substr($candidate['full_name'] ?? 'U', 0, 1));

                                    $fitLabel = 'Needs Review';
                                    $fitClass = '';
                                    if ($score >= 85) { $fitLabel = 'Excellent Match'; $fitClass = 'excellent'; }
                                    elseif ($score >= 70) { $fitLabel = 'Good Potential'; $fitClass = 'strong'; }

                                    $statusText = 'New';
                                    if ($candidate['candidate_status'] === 'review') $statusText = 'Needs Review';
                                    if ($candidate['candidate_status'] === 'shortlisted') $statusText = 'Shortlisted';
                                    if ($candidate['candidate_status'] === 'approval') $statusText = 'Approval Sent';
                                ?>

                                <article class="candidate-card candidate-premium-card candidate-page-card">
                                    <div class="candidate-rank">#<?= $index + 1; ?></div>

                                    <div class="candidate-main premium-candidate-main">
                                        <?php if (!empty($candidate['profile_image'])): ?>
                                            <img class="candidate-avatar premium-avatar" src="/tamkeentest/public/uploads/profile_images/<?= htmlspecialchars(basename($candidate['profile_image'])); ?>" alt="">
                                        <?php else: ?>
                                            <div class="candidate-avatar premium-avatar"><?= htmlspecialchars($avatar); ?></div>
                                        <?php endif; ?>

                                        <div class="candidate-info-block">
                                            <div class="candidate-name-row">
                                                <h5><?= htmlspecialchars($candidate['full_name']); ?></h5>
                                                <span class="fit-pill <?= $fitClass; ?>"><?= htmlspecialchars($fitLabel); ?></span>
                                                <span class="fit-pill status-pill-clean"><?= htmlspecialchars($statusText); ?></span>
                                            </div>

                                            <p class="candidate-meta">
                                                <?= htmlspecialchars($candidate['job_title'] ?: 'Job Seeker'); ?>
                                                <span>•</span>
                                                <?= htmlspecialchars($candidate['location'] ?: $candidate['residence_country'] ?: 'Location not set'); ?>
                                                <span>•</span>
                                                <?= htmlspecialchars((int)($candidate['years_experience'] ?? 0)); ?> yrs exp.
                                            </p>

                                            <div class="score-bar-wrap">
                                                <div class="score-bar-top"><strong>Match Score</strong><b><?= $score; ?>%</b></div>
                                                <div class="score-bar"><span style="width: <?= $score; ?>%"></span></div>
                                            </div>

                                            <div class="candidate-tags premium-tags">
                                                <span><i class="fa fa-clipboard-check"></i> <?= (int)$candidate['submitted_tasks']; ?> Tasks</span>
                                                <span><i class="fa fa-trophy"></i> <?= (int)$candidate['earned_points']; ?> Points</span>
                                                <?php foreach ($visibleSkills as $tag): ?>
                                                    <span><?= htmlspecialchars($tag); ?></span>
                                                <?php endforeach; ?>
                                                <?php if ($extraSkills > 0): ?><span>+<?= $extraSkills; ?></span><?php endif; ?>
                                            </div>

                                            <div class="mini-note">
                                                <i class="fa fa-graduation-cap"></i>
                                                <?= htmlspecialchars($candidate['institution'] ?: 'Education not added'); ?>
                                                <?= !empty($candidate['education_field']) ? ' · ' . htmlspecialchars($candidate['education_field']) : ''; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="candidate-actions premium-actions">
<?php if (!empty($candidate['can_view_profile'])): ?>

   <a class="btn-soft" href="/tamkeentest/public/user/public-profile.php?id=<?= (int)$candidate['id']; ?>">
    View
</a>

<?php else: ?>

    <button
        type="button"
        class="btn-soft private-profile-btn"
    >
        Private
    </button>

<?php endif; ?>
                                        <?php if (!empty($candidate['shortlisted_id'])): ?>
                                            <button type="button" class="btn-soft disabled" disabled>Shortlisted</button>
                                        <?php else: ?>
                                            <form method="POST" action="/tamkeentest/public/hr/actions/add-to-shortlist.php">
                                                <input type="hidden" name="job_seeker_id" value="<?= (int)$candidate['id']; ?>">
                                                <input type="hidden" name="hiring_type" value="full_time">
                                                <button type="submit" class="btn-primary-modern">Shortlist</button>
                                            </form>
                                        <?php endif; ?>

                                        <a class="btn-email" href="mailto:<?= htmlspecialchars($candidate['email']); ?>?subject=Interview Invitation from <?= rawurlencode($companyName); ?>"><i class="fa fa-envelope"></i> Email</a>
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
<script>
window.addEventListener("beforeunload", function () {
    sessionStorage.setItem("candidatesScroll", window.scrollY);
});

window.addEventListener("load", function () {

    const savedScroll = sessionStorage.getItem("candidatesScroll");

    if (savedScroll !== null) {

        window.scrollTo({
            top: parseInt(savedScroll),
            behavior: "instant"
        });
    }
});
</script>
<script>
document.querySelectorAll(".private-profile-btn").forEach(button => {

    button.addEventListener("click", () => {

        const existing = document.querySelector(".private-profile-warning");

        if (existing) {
            existing.remove();
        }

        const box = document.createElement("div");

        box.className = "private-profile-warning";

        box.innerHTML = `
            <i class="fa fa-lock"></i>
            This candidate profile is private and cannot be viewed.
        `;

        document.body.appendChild(box);

        setTimeout(() => {
            box.remove();
        }, 3500);
    });
});
</script>
<?php
    }
}
