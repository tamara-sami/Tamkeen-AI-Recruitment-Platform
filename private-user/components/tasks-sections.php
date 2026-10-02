<?php

function renderTasksPage($page, $conn){
    extract($page);
    $base_url = $base_url ?? '../../';
    ?>
    <main class="container py-4">
        <div class="row g-4">

            <aside class="col-lg-3 d-none d-lg-block">
                <div class="card side-card overflow-hidden mb-3">
                    <div class="profile-cover"></div>

                    <div class="card-body text-center">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($base_url . 'public/' . $user['profile_image']) ?>" class="profile-img" alt="Profile">
                        <?php else: ?>
                            <div class="profile-img d-flex align-items-center justify-content-center fw-bold fs-3 bg-primary text-white">
                                <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name'] ?? 'Tamkeen User') ?></h5>
                        <p class="text-muted small mb-1"><?= htmlspecialchars($user['job_title'] ?? 'Learner') ?></p>
                        <p class="text-muted small mb-3">
                            <i class="fa fa-map-marker-alt me-1 text-primary"></i>
<?= htmlspecialchars($user['location'] ?? $user['residence_country'] ?? 'Jordan') ?>
                        </p>

                        <a href="public-profile.php" class="btn btn-outline-primary rounded-pill w-100 fw-bold">View Profile</a>
                    </div>
                </div>

                <div class="card side-card mb-3">
                    <div class="card-body">
                        <a href="tasks.php" class="side-link"><i class="fa fa-layer-group"></i> Browse Tasks</a>
                        <a href="tasks.php?difficulty=Beginner" class="side-link"><i class="fa fa-seedling"></i> Beginner Tasks</a>
                        <a href="tasks.php?difficulty=Intermediate" class="side-link"><i class="fa fa-chart-line"></i> Intermediate</a>
                        <a href="public-profile.php" class="side-link mb-0"><i class="fa fa-qrcode"></i> My QR Profile</a>
                    </div>
                </div>
<?php

$totalTasks =
    (int)($stats['total_tasks'] ?? 0);

$completedTasks =
    (int)($stats['completed_tasks'] ?? 0);

$progress =
    $totalTasks > 0
        ? round(($completedTasks / $totalTasks) * 100)
        : 0;

?>

    
            </aside>

            <section class="col-lg-6">
                <div class="card main-card mb-4">
                    <div class="card-body">
                        <div class="task-categories">
                            <a href="tasks.php?search=<?= urlencode($search) ?>&difficulty=<?= urlencode($difficulty) ?>" class="task-category <?= $category === 'All' ? 'active' : '' ?>">
                                <i class="fa fa-layer-group"></i><span>All</span>
                            </a>

                            <?php foreach ($categories as $cat): ?>
                                <a href="tasks.php?category=<?= urlencode($cat['category']) ?>&difficulty=<?= urlencode($difficulty) ?>&search=<?= urlencode($search) ?>" class="task-category <?= $category === $cat['category'] ? 'active' : '' ?>">
                                    <i class="fa fa-folder"></i><span><?= htmlspecialchars($cat['category']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card main-card mb-4">
                    <div class="card-body">
                        <form method="GET" id="taskFilterForm">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">

                            <div class="position-relative mb-3">
                                <div class="search-box">
                                    <i class="fa fa-search text-primary"></i>
                                    <input type="text" id="taskSearch" name="search" placeholder="Search by title, company, skill, category..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                                </div>
                                <div id="searchSuggestions" class="search-suggestions d-none"></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach (['All', 'Beginner', 'Intermediate', 'Advanced'] as $level): ?>
                                    <button name="difficulty" value="<?= $level ?>" class="filter-btn <?= $difficulty === $level ? 'active' : '' ?>">
                                        <?= $level ?>
                                    </button>
                                <?php endforeach; ?>

                                <?php if ($search !== '' || $category !== 'All' || $difficulty !== 'All'): ?>
                                    <a href="tasks.php" class="filter-btn clear-filter">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($tasks)): ?>
                    <div class="card task-post mb-4">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-search fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">No tasks found</h4>
                            <p class="text-muted mb-0">Try another search, category, or difficulty.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <?php $attempt = $user_id ? getTaskAttempt($conn, $task['id'], $user_id) : null; ?>

                        <div class="card task-post mb-4 task-post-item"
                             data-title="<?= strtolower(htmlspecialchars($task['title'])) ?>"
                             data-company="<?= strtolower(htmlspecialchars($task['company_name'] ?? '')) ?>"
                             data-category="<?= strtolower(htmlspecialchars($task['category'])) ?>"
                             data-skills="<?= strtolower(htmlspecialchars($task['required_skills'])) ?>">

                            <div class="card-body">
                                <div class="d-flex gap-3 mb-3">
                                    <?php if (!empty($task['company_logo'])): ?>
                                        <div class="task-company-avatar">
<img src="<?= htmlspecialchars($base_url . 'public/uploads/company/' . $task['company_logo']) ?>" alt="">
                                        </div>
                                    <?php else: ?>
                                        <div class="task-company-avatar fallback">
                                            <?= strtoupper(substr($task['company_name'] ?? 'T', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($task['company_name'] ?? 'Tamkeen Company') ?></h6>
                                        <small class="text-muted">
                                            Company Task · <?= htmlspecialchars($task['category']) ?> · <?= date("M d, Y", strtotime($task['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>

                                <p class="text-muted mb-3">Task details will be visible after you start the task.</p>

                                <?php if (!empty($task['task_image'])): ?>
                                    <img src="<?= htmlspecialchars($base_url . 'uploads/tasks/' . $task['task_image']) ?>" class="task-img mb-3" alt="">
                                <?php endif; ?>

                                <div class="task-info-box mb-3">
                                    <span class="badge bg-primary rounded-pill mb-2">Company Verified</span>
                                    <h4 class="fw-bold mb-2"><?= htmlspecialchars($task['title']) ?></h4>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="tag"><?= htmlspecialchars($task['category']) ?></span>
                                        <span class="tag"><?= htmlspecialchars($task['difficulty']) ?></span>
                                        <span class="tag"><?= htmlspecialchars($task['estimated_time']) ?></span>
                                        <span class="tag">+<?= htmlspecialchars($task['points']) ?> pts</span>
                                    </div>

                                    <?php if (!empty($task['required_skills'])): ?>
                                        <p class="text-muted small mt-3 mb-0">
                                            <strong>Required skills:</strong> <?= htmlspecialchars($task['required_skills']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span><i class="fa fa-clock me-1"></i>Deadline: <?= taskDeadlineText($task['deadline']) ?></span>
                                    <?php if (!empty($task['task_file'])): ?>
                                        <span><i class="fa fa-paperclip me-1"></i>Attachment available</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-around border-top pt-3">
                                    <?php if (!$attempt): ?>
                                        <a href="task-workspace.php?id=<?= (int)$task['id'] ?>" class="btn btn-primary rounded-pill px-4">Start Task</a>
                                    <?php elseif ($attempt['status'] === 'submitted'): ?>
                                        <button class="btn btn-success rounded-pill px-4" disabled>Submitted</button>
                                    <?php elseif ($attempt['status'] === 'disqualified'): ?>
                                        <button class="btn btn-danger rounded-pill px-4" disabled>Disqualified</button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary rounded-pill px-4" disabled>Task Locked</button>
                                    <?php endif; ?>

                                    
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="col-lg-3 d-none d-lg-block">
                <div class="card side-card mb-3">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">AI Suggested Task</h5>
                        <?php if ($suggestedTask): ?>
                            <div class="suggest-box">
                                <span class="badge bg-primary rounded-pill mb-2">Best Match</span>
                                <h6 class="fw-bold"><?= htmlspecialchars($suggestedTask['title']) ?></h6>
                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars($suggestedTask['category']) ?> · <?= htmlspecialchars($suggestedTask['difficulty']) ?> · +<?= (int)$suggestedTask['points'] ?> pts
                                </p>
                                <a href="task-workspace.php?id=<?= (int)$suggestedTask['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">Try Now</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No suggestions available yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

               
                

                <div class="card side-card">
                    <div class="card-body text-center">
                        <i class="fa fa-qrcode fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Task Proof</h5>
                        <p class="text-muted small">Completed tasks will appear on your QR profile.</p>
                        <a href="public-profile.php" class="btn btn-outline-primary rounded-pill px-4">Preview</a>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <?php
}

function renderTasksSearchScript()
{
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("taskSearch");
        const suggestions = document.getElementById("searchSuggestions");
        const form = document.getElementById("taskFilterForm");
        const items = document.querySelectorAll(".task-post-item");

        if (!input || !suggestions || !form) return;

        function buildSuggestions(value) {
            suggestions.innerHTML = "";

            if (value.trim().length < 1) {
                suggestions.classList.add("d-none");
                return;
            }

            let matches = [];

            items.forEach(item => {
                const title = item.dataset.title || "";
                const company = item.dataset.company || "";
                const category = item.dataset.category || "";
                const skills = item.dataset.skills || "";
                const haystack = `${title} ${company} ${category} ${skills}`;

                if (haystack.includes(value.toLowerCase())) {
                    matches.push({title, company, category});
                }
            });

            matches = matches.slice(0, 6);

            if (matches.length === 0) {
                suggestions.innerHTML = `<div class="suggestion-item text-muted">No quick matches. Press Enter to search.</div>`;
                suggestions.classList.remove("d-none");
                return;
            }

            matches.forEach(match => {
                const div = document.createElement("div");
                div.className = "suggestion-item";
                div.innerHTML = `<strong>${match.title}</strong><small>${match.company || "Company"} · ${match.category}</small>`;

                div.addEventListener("click", function () {
                    input.value = match.title;
                    form.submit();
                });

                suggestions.appendChild(div);
            });

            suggestions.classList.remove("d-none");
        }

        input.addEventListener("input", function () { buildSuggestions(this.value); });
        input.addEventListener("keydown", function (e) { if (e.key === "Enter") form.submit(); });

        document.addEventListener("click", function (e) {
            if (!suggestions.contains(e.target) && e.target !== input) suggestions.classList.add("d-none");
        });
    });
    </script>
    <?php
}
