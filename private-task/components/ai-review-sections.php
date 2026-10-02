<?php

if (!function_exists('renderAiReviewPage')) {

function tamkeenLabelText($value)
{
    $value = strtolower((string)$value);

    if ($value === 'recommended') return 'Recommended';
    if ($value === 'needs_review') return 'Needs Human Review';
    if ($value === 'not_recommended') return 'Not Recommended';
    if ($value === 'approved') return 'Approved';
    if ($value === 'rejected') return 'Rejected';
    if ($value === 'edited') return 'Score Edited';
    if ($value === 'pending' || $value === '') return 'Pending';

    return ucwords(str_replace('_', ' ', $value));
}

function tamkeenRiskClass($risk)
{
    $risk = strtolower((string)$risk);

    if ($risk === 'high') return 'bg-danger';
    if ($risk === 'medium') return 'bg-warning text-dark';

    return 'bg-success';
}

function tamkeenCameraEventLabel($eventType)
{
    $labels = [
        'no_face_detected' => 'No Face Detected',
        'face_missing' => 'No Face Detected',
        'multiple_faces_detected' => 'Multiple Faces Detected',
        'multiple_faces' => 'Multiple Faces Detected',
        'camera_covered' => 'Camera Covered',
        'camera_blocked' => 'Camera Covered',
        'person_left_frame' => 'Person Left Frame',
        'looking_away' => 'Looking Away',
        'phone_detected' => 'Phone Detected',
        'camera_off' => 'Camera Off',
        'camera_permission_denied' => 'Camera Permission Denied',
    ];

    $eventType = strtolower((string)$eventType);

    return $labels[$eventType] ?? ucwords(str_replace('_', ' ', $eventType));
}

function tamkeenIsCameraFlag($eventType)
{
    $eventType = strtolower((string)$eventType);

    $ignoreEvents = [
        'camera_started',
        'face_detected',
        'tab_switch',
        'leave',
        'copy_paste',
        'right_click',
        'blur',
        'visibility_change'
    ];

    if (in_array($eventType, $ignoreEvents, true)) {
        return false;
    }

    return true;
}

function renderAiReviewPage(array $page)
{
    extract($page);
?>

<main class="container py-5">

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h3 class="fw-bold mb-2">Task Submission Review</h3>
                <p class="text-muted mb-0">
                    Review submitted task answers in three clear steps: AI solution evaluation, integrity review, and final reviewer decision.
                </p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-primary mb-0"><?= (int)$total ?></h3>
                        <small class="text-muted">Submitted Attempts</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-success mb-0"><?= (int)$recommendedCount ?></h3>
                        <small class="text-muted">AI Recommended</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h3 class="fw-bold text-info mb-0"><?= (int)$sentHrCount ?></h3>
                        <small class="text-muted">Sent to HR</small>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm border mb-4">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">Candidate Submissions</h4>
                        <small class="text-muted">Only submitted attempts for your company appear here.</small>
                    </div>

                    <div class="task-tabs">
                        <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
                        <a href="?filter=recommended" class="<?= $filter === 'recommended' ? 'active' : '' ?>">Recommended</a>
                        <a href="?filter=needs_review" class="<?= $filter === 'needs_review' ? 'active' : '' ?>">Needs Review</a>
                        <a href="?filter=sent_hr" class="<?= $filter === 'sent_hr' ? 'active' : '' ?>">Sent HR</a>
                    </div>
                </div>

                <?php if (empty($submissions)): ?>
                    <div class="p-5 text-center">
                        <i class="fa fa-clipboard-check fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold">No submissions yet</h5>
                        <p class="text-muted mb-0">Submitted task attempts will appear here after AI evaluation.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($submissions as $s): ?>
                        <?php
                            $aiStatus = tamkeenLabelText($s['ai_status'] ?? 'not evaluated');
                            $reviewStatus = tamkeenLabelText($s['review_status'] ?? 'pending');
                            $integrityRisk = strtolower($s['integrity_risk'] ?? 'low');
                            $riskClass = tamkeenRiskClass($integrityRisk);

                            $proctoringSummary = [];

                            try {
                                require_once __DIR__ . "/../../private-user/proctoring/proctoring-functions.php";

                                $db = $page['conn'] ?? null;

                                if ($db instanceof PDO && !empty($s['id'])) {
                                    $proctoringSummary = getAttemptProctoringSummary($db, (int)$s['id']);
                                }
                            } catch (Exception $e) {
                                $proctoringSummary = [];
                            }
                        ?>

                        <div class="p-4 border-bottom">

                            <!-- 1. Candidate Submission -->
                            <section class="bg-white border rounded-4 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <span class="badge bg-light text-dark rounded-pill mb-2">1. Candidate Submission</span>
                                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($s['full_name']) ?></h5>
                                        <p class="text-muted mb-1">
                                            <?= htmlspecialchars($s['job_title'] ?? 'Job Seeker') ?> ·
                                            <?= htmlspecialchars($s['email']) ?>
                                        </p>
                                        <small class="text-muted">
                                            Task: <strong><?= htmlspecialchars($s['task_title']) ?></strong>
                                        </small>
                                    </div>

                                    <div class="text-end">
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            AI Screening: <?= htmlspecialchars($aiStatus) ?>
                                        </span>
                                        <br>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2 mt-2">
                                            Human Review: <?= htmlspecialchars($reviewStatus) ?>
                                        </span>
                                        <?php if ((int)($s['recommended_to_hr'] ?? 0) === 1): ?>
                                            <br>
                                            <span class="badge bg-success rounded-pill px-3 py-2 mt-2">Sent to HR</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if (!empty($s['answer_text'])): ?>
                                    <div class="bg-light rounded-4 p-3 mt-3">
                                        <strong>Submitted Answer</strong>
                                        <p class="text-muted mb-0 mt-2">
                                            <?= nl2br(htmlspecialchars($s['answer_text'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($s['answer_file']) || !empty($s['drawing_file'])): ?>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <?php if (!empty($s['answer_file'])): ?>
                                            <a href="/tamkeentest/<?= htmlspecialchars($s['answer_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                                View Answer File
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($s['drawing_file'])): ?>
                                            <a href="/tamkeentest/<?= htmlspecialchars($s['drawing_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                                View Drawing
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </section>

                            <!-- 2. AI Solution Evaluation -->
                            <section class="bg-white border rounded-4 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                    <div>
                                        <span class="badge bg-light text-dark rounded-pill mb-2">2. AI Solution Evaluation</span>
                                        <h6 class="fw-bold mb-0">Answer quality based on model answer and criteria</h6>
                                    </div>

                                    <div class="rounded-4 bg-light px-4 py-3 text-center">
                                        <h3 class="fw-bold text-primary mb-0">
                                            <?= htmlspecialchars($s['ai_score'] ?? '-') ?>
                                        </h3>
                                        <small class="text-muted">AI Score</small>
                                    </div>
                                </div>

                                <div class="bg-light rounded-4 p-3">
                                    <strong>AI Feedback</strong>
                                    <p class="text-muted mb-0 mt-2">
                                        <?= nl2br(htmlspecialchars($s['ai_feedback'] ?? 'No feedback yet')) ?>
                                    </p>
                                </div>
                            </section>

                            <!-- 3. Integrity Review -->
                            <section class="bg-white border rounded-4 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                    <div>
                                        <span class="badge bg-light text-dark rounded-pill mb-2">3. Integrity Review</span>
                                        <h6 class="fw-bold mb-0">Behavior during task completion</h6>
                                        <small class="text-muted">Integrity does not directly change the AI score; it helps decide if human review is needed.</small>
                                    </div>

                                    <span class="badge <?= $riskClass ?> rounded-pill px-3 py-2">
                                        Integrity Risk: <?= htmlspecialchars(ucfirst($integrityRisk)) ?>
                                    </span>
                                </div>

                                <div class="bg-light border rounded-4 p-3 mb-3">
                                    <strong>Task Behavior Events</strong>
                                    <p class="text-muted small mb-3">
                                        These are browser/workspace events, not camera events.
                                    </p>

                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                            Total Violations: <?= (int)($s['violation_count'] ?? 0) ?>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            Tab Switch: <?= (int)($s['tab_switch_count'] ?? 0) ?>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            Leave Page: <?= (int)($s['leave_count'] ?? 0) ?>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            Copy/Paste: <?= (int)($s['copy_paste_count'] ?? 0) ?>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">
                                            Right Click: <?= (int)($s['right_click_count'] ?? 0) ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if (!empty($s['integrity_note'])): ?>
                                    <div class="bg-warning bg-opacity-10 border border-warning rounded-4 p-3 mb-3">
                                        <strong>Integrity Note</strong>
                                        <p class="text-muted mb-0 mt-2">
                                            <?= nl2br(htmlspecialchars($s['integrity_note'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="bg-light border rounded-4 p-3">
                                    <strong>Camera Monitoring Flags</strong>
                                    <p class="text-muted small mb-3">
                                        These come from camera AI, such as no face, multiple faces, or covered camera.
                                    </p>

                                    <div class="d-flex flex-wrap gap-2">
                                        <?php
                                            $hasCameraFlags = false;
                                            foreach ($proctoringSummary as $event):
                                                $eventTypeRaw = strtolower((string)($event['event_type'] ?? ''));

                                                if (!tamkeenIsCameraFlag($eventTypeRaw)) {
                                                    continue;
                                                }

                                                $hasCameraFlags = true;
                                                $eventType = tamkeenCameraEventLabel($eventTypeRaw);
                                                $eventTotal = (int)($event['total'] ?? 0);
                                        ?>
                                            <span class="badge bg-dark rounded-pill px-3 py-2">
                                                <?= htmlspecialchars($eventType) ?> (<?= $eventTotal ?>)
                                            </span>
                                        <?php endforeach; ?>

                                        <?php if (!$hasCameraFlags): ?>
                                            <span class="text-muted small">No important camera flags.</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>

                            <!-- 4. Reviewer Decision -->
                            <section class="bg-white border rounded-4 p-3">
                                <span class="badge bg-light text-dark rounded-pill mb-2">4. Reviewer Decision</span>
                                <h6 class="fw-bold mb-3">Final human decision</h6>

                                <form method="POST">
                                    <input type="hidden" name="attempt_id" value="<?= (int)$s['id'] ?>">

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Final Reviewed Score</label>
                                            <input
                                                type="number"
                                                name="final_points"
                                                class="form-control rounded-pill"
                                                value="<?= htmlspecialchars($s['final_points'] ?? $s['ai_score'] ?? 0) ?>"
                                                min="0"
                                            >
                                            <small class="text-muted">Reviewer can adjust if AI score needs correction.</small>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">Reviewer Note</label>
                                            <textarea
                                                name="review_note"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Write the final reviewer note for this submission..."
                                            ><?= htmlspecialchars($s['review_note'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button name="action" value="approve" class="btn btn-success rounded-pill px-4">
                                            Approve
                                        </button>

                                        <button name="action" value="reject" class="btn btn-outline-danger rounded-pill px-4">
                                            Reject
                                        </button>

                                        <button name="action" value="edit_points" class="btn btn-outline-primary rounded-pill px-4">
                                            Save Score
                                        </button>

                                        <button name="action" value="recommend_hr" class="btn btn-primary rounded-pill px-4 fw-bold">
                                            Send to HR
                                        </button>
                                    </div>
                                </form>
                            </section>

                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

        </div>

        <div class="col-lg-4">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h5 class="fw-bold mb-3">Reviewer Workflow</h5>

                <div class="d-flex gap-3 mb-3">
                    <i class="fa fa-file-alt text-primary mt-1"></i>
                    <div>
                        <strong>Candidate submits task</strong>
                        <p class="text-muted small mb-0">Only submitted attempts appear in this page.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <i class="fa fa-robot text-primary mt-1"></i>
                    <div>
                        <strong>AI evaluates the solution</strong>
                        <p class="text-muted small mb-0">AI compares the answer with the model answer and criteria.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <i class="fa fa-desktop text-primary mt-1"></i>
                    <div>
                        <strong>System tracks behavior</strong>
                        <p class="text-muted small mb-0">Tab switching, leaving the page, copy/paste, and right click are browser events.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <i class="fa fa-video text-primary mt-1"></i>
                    <div>
                        <strong>Camera AI checks visibility</strong>
                        <p class="text-muted small mb-0">Camera flags include no face, multiple faces, or covered camera.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <i class="fa fa-user-check text-primary mt-1"></i>
                    <div>
                        <strong>Reviewer makes final decision</strong>
                        <p class="text-muted small mb-0">Final score, approval, rejection, or sending to HR remain human-controlled.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4">
                <h5 class="fw-bold mb-3">Screening Rules</h5>
                <p class="text-muted small mb-2">
                    <strong>Recommended:</strong> strong solution score with low integrity risk.
                </p>
                <p class="text-muted small mb-2">
                    <strong>Needs Human Review:</strong> suspicious behavior, camera flags, or medium score.
                </p>
                <p class="text-muted small mb-0">
                    <strong>Not Recommended:</strong> low solution score or disqualified attempt.
                </p>
            </div>

        </div>

    </div>

</main>

<?php
}
}
