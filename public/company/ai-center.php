<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/ai-center-functions.php");

$company_id = (int)$_SESSION['company_id'];

handleAiCenterAction($conn, $company_id);

$sql = "
    SELECT company_logo
    FROM companies
    WHERE id = ?
";
$stmt = $conn->prepare($sql);
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$pageData = getAiCenterData($conn, $company_id);

$stats = $pageData["stats"];
$topCandidates = $pageData["topCandidates"];
$recommendations = $pageData["recommendations"];
$activities = $pageData["activities"];
$pendingAi = (int)$pageData["pendingAi"];
$flash = $_SESSION['ai_center_flash'] ?? null;
unset($_SESSION['ai_center_flash']);

$base_url = "../../";
$activePage = "ai";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">AI Center</h5>
                <small class="text-muted">AI-powered ranking, insights, and hiring recommendations</small>
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

        <?php if (!empty($flash)): ?>
            <div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-warning'; ?> rounded-4 shadow-sm mt-3">
                <strong>AI Refresh:</strong>
                <?= htmlspecialchars($flash['message'] ?? 'AI action completed.'); ?>
            </div>
        <?php endif; ?>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">AI Hiring Assistant</span>
                <h1>Make smarter hiring decisions with AI</h1>
                <p>
                    Evaluate submitted tasks with AI, rank candidates, and display saved AI feedback directly from the backend.
                </p>
            </div>

            <div class="hero-actions">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="ai_action" value="refresh_ai">
                    <input type="hidden" name="limit" value="10">
                    <button type="submit" class="btn btn-light fw-bold rounded-pill px-4">
                        <i class="fa fa-robot me-1"></i>
                        Refresh AI
                        <?php if ($pendingAi > 0): ?>
                            (<?= $pendingAi; ?>)
                        <?php endif; ?>
                    </button>
                </form>
                <a href="#ai-ranking" class="btn btn-outline-light fw-bold rounded-pill px-4">View Ranking</a>
            </div>
        </section>

        <section class="admin-stats">
            <div class="stat-card">
                <i class="fa fa-star"></i>
                <h3><?= (int)$stats['rankedCandidates']; ?></h3>
                <p>AI Ranked Candidates</p>
            </div>

            <div class="stat-card">
                <i class="fa fa-user-check"></i>
                <h3><?= (int)$stats['recommended']; ?></h3>
                <p>AI Recommended</p>
            </div>

            <div class="stat-card">
                <i class="fa fa-clock"></i>
                <h3><?= (int)$stats['fastSolvers']; ?></h3>
                <p>Fast Solvers</p>
            </div>

            <div class="stat-card">
                <i class="fa fa-bullseye"></i>
                <h3><?= (int)$stats['highAccuracy']; ?></h3>
                <p>High Accuracy</p>
            </div>

            <div class="stat-card">
                <i class="fa fa-briefcase"></i>
                <h3><?= (int)$stats['fullTimeFit']; ?></h3>
                <p>Full-time Fit</p>
            </div>

            <div class="stat-card">
                <i class="fa fa-calendar-day"></i>
                <h3><?= (int)$stats['temporaryFit']; ?></h3>
                <p>Temporary Fit</p>
            </div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel" id="ai-ranking">
                <div class="panel-header">
                    <div>
                        <h4>AI Candidate Ranking</h4>
                        <p>Top candidates based on saved AI task evaluations.</p>
                    </div>
                </div>

                <div class="activity-list">
                    <?php if (empty($topCandidates)): ?>
                        <div class="activity-item">
                            <span class="activity-icon blue"><i class="fa fa-robot"></i></span>
                            <div>
                                <strong>No AI ranking yet</strong>
                                <p>Click Refresh AI after candidates submit tasks.</p>
                                <small><?= $pendingAi > 0 ? $pendingAi . ' pending evaluation(s)' : '—'; ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topCandidates as $candidate): ?>
                            <div class="activity-item">
                                <span class="activity-icon blue"><i class="fa fa-medal"></i></span>
                                <div>
                                    <strong>
                                        <?= htmlspecialchars($candidate['full_name']); ?>
                                        — Avg AI Score <?= (int)$candidate['ai_score']; ?>
                                    </strong>
                                    <p>
                                        <?= htmlspecialchars($candidate['job_title'] ?: 'Candidate'); ?>
                                        · Completed <?= (int)$candidate['total_tasks']; ?> task(s).
                                    </p>
                                    <?php if (!empty($candidate['ai_recommendation'])): ?>
                                        <p><?= htmlspecialchars($candidate['ai_recommendation']); ?></p>
                                    <?php endif; ?>
                                    <small><?= htmlspecialchars(aiStatusLabel($candidate['ai_status'] ?? 'needs_review')); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

           

        </div>

        
        <section class="admin-panel mt-4" id="ai-activity">
            <div class="panel-header">
                <div>
                    <h4>AI Activity Log</h4>
                    <p>Latest AI evaluations and hiring-related actions.</p>
                </div>
            </div>

            <div class="activity-list">
                <?php if (empty($activities)): ?>
                    <div class="activity-item">
                        <span class="activity-icon purple"><i class="fa fa-info"></i></span>
                        <div>
                            <strong>No AI activity yet</strong>
                            <p>Click Refresh AI when task submissions are ready.</p>
                            <small>—</small>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-icon purple">
                                <i class="<?= htmlspecialchars($activity['icon']); ?>"></i>
                            </span>
                            <div>
                                <strong><?= htmlspecialchars($activity['title']); ?></strong>
                                <p><?= htmlspecialchars($activity['description']); ?></p>
                                <small><?= htmlspecialchars(aiDate($activity['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
<section class="admin-panel mt-4">
            <div class="panel-header">
                <div>
                    <h4>AI Evaluation Criteria</h4>
                    <p>AI evaluates submissions using task details, skills, criteria, answer text, and supported uploaded files.</p>
                </div>
            </div>

            <div class="role-grid">
                <div class="role-card">
                    <i class="fa fa-check-circle"></i>
                    <h5>Accuracy</h5>
                    <p>Compares the candidate answer with the task description and evaluation criteria.</p>
                </div>

                <div class="role-card">
                    <i class="fa fa-stopwatch"></i>
                    <h5>Speed</h5>
                    <p>Detects fast solvers using task start and submit timestamps.</p>
                </div>

                <div class="role-card">
                    <i class="fa fa-layer-group"></i>
                    <h5>Skill Match</h5>
                    <p>Uses required skills, answer quality, and score percentage to rank candidates.</p>
                </div>

                <div class="role-card">
                    <i class="fa fa-file-alt"></i>
                    <h5>AI Summary</h5>
                    <p>Stores AI feedback and recommendation in the backend for admin review.</p>
                </div>
            </div>
        </section>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>
