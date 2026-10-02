<?php

function renderTrainingsPage($page)
{
    extract($page);
    $base_url = $base_url ?? '../../';
?>
<main class="container py-4">
    <div class="row g-4">

        <!-- Left Sidebar -->
        <aside class="col-lg-3 d-none d-lg-block">

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                <div style="height:75px;background:linear-gradient(90deg,var(--primary),var(--accent));"></div>

                <div class="card-body text-center">
                    <?php if (!empty($user['profile_image'])): ?>
<img
    src="<?= $base_url . 'public/' . htmlspecialchars($user['profile_image']) ?>"
    class="rounded-circle shadow-sm bg-white mb-3"
    style="width:95px;height:95px;object-fit:cover;margin-top:-60px;"
    alt="">                            
                            
                    <?php else: ?>
                        <div class="rounded-circle shadow-sm bg-white mb-3 mx-auto d-flex align-items-center justify-content-center"
                             style="width:95px;height:95px;margin-top:-60px;">
                            <i class="fa fa-user fa-2x text-primary"></i>
                        </div>
                    <?php endif; ?>

                    <h5 class="fw-bold mb-1"><?= e($user['full_name'] ?? 'Job Seeker') ?></h5>
                    <p class="text-muted small mb-1"><?= e($user['job_title'] ?? 'Learner') ?></p>
                    <p class="text-muted small mb-3">
                        <i class="fa fa-map-marker-alt me-1 text-primary"></i>
                        <?= e(($user['location'] ?? 'Amman') . ', ' . ($user['residence_country'] ?? 'Jordan')) ?>
                    </p>

                    <a href="dashboard.php" class="btn btn-outline-primary rounded-pill w-100 fw-bold">
                        View Profile
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <a href="trainings.php" class="d-block text-dark fw-bold mb-3">
                        <i class="fa fa-graduation-cap text-primary me-2"></i> Trainings
                    </a>
                    <a href="my-training-applications.php" class="d-block text-dark fw-bold mb-3">
                        <i class="fa fa-file-alt text-primary me-2"></i> My Training Applications
                    </a>
                    <a href="dashboard.php" class="d-block text-dark fw-bold mb-3">
                        <i class="fa fa-user text-primary me-2"></i> My Profile
                    </a>
                   
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Training Goal</h6>

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Readiness</span>
                        <strong>80%</strong>
                    </div>

                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar" style="width:80%;"></div>
                    </div>

                    <small class="text-muted d-block mb-2">
                        Apply to trainings that match your major and skills.
                    </small>
                </div>
            </div>

        </aside>

        <!-- Center Feed -->
        <section class="col-lg-6">

            <?php if ($success): ?>
                <div class="alert alert-success rounded-4 shadow-sm border-0">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <!-- Stories -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex gap-3 overflow-auto pb-1">

                        <a href="trainings.php?type=all" class="text-decoration-none text-dark text-center">
                            <div class="rounded-circle border border-primary border-3 d-flex align-items-center justify-content-center"
                                 style="width:70px;height:70px;background:#EEF4FF;">
                                <i class="fa fa-layer-group text-primary fa-lg"></i>
                            </div>
                            <small class="d-block mt-2 fw-bold">All</small>
                        </a>

                        <a href="trainings.php?type=university" class="text-decoration-none text-dark text-center">
                            <div class="rounded-circle border border-primary border-3 d-flex align-items-center justify-content-center"
                                 style="width:70px;height:70px;background:#EEF4FF;">
                                <i class="fa fa-graduation-cap text-primary fa-lg"></i>
                            </div>
                            <small class="d-block mt-2 fw-bold">University</small>
                        </a>

                        <a href="trainings.php?type=voluntary" class="text-decoration-none text-dark text-center">
                            <div class="rounded-circle border border-primary border-3 d-flex align-items-center justify-content-center"
                                 style="width:70px;height:70px;background:#EEF4FF;">
                                <i class="fa fa-hands-helping text-primary fa-lg"></i>
                            </div>
                            <small class="d-block mt-2 fw-bold">Voluntary</small>
                        </a>

                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <form method="GET">
                        <div class="d-flex gap-2">
                            <input type="hidden" name="type" value="<?= e($type) ?>">

                            <div class="training-search-wrap flex-grow-1 position-relative">
                                <input 
                                    type="text" 
                                    id="trainingSearch"
                                    name="search"
                                    class="form-control rounded-pill px-4 py-3"
                                    placeholder="Search trainings by title, company, field..."
                                    value="<?= e($search) ?>"
                                    autocomplete="off"
                                >
                                <div id="trainingSearchSuggestions" class="search-suggestions d-none"></div>
                            </div>

                            <button class="btn btn-primary rounded-pill px-4 fw-bold">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($trainings)): ?>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body text-center p-5">
                        <i class="fa fa-search fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">No trainings found</h5>
                        <p class="text-muted mb-0">Try changing the search or filter.</p>
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($trainings as $training): ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-4 training-post-item"
                         id="training-<?= $training['id'] ?>"
                         data-title="<?= strtolower(e($training['title'] ?? '')) ?>"
                         data-company="<?= strtolower(e($training['company_name'] ?? '')) ?>"
                         data-field="<?= strtolower(e($training['field'] ?? '')) ?>"
                         data-location="<?= strtolower(e($training['location'] ?? '')) ?>"
                         data-skills="<?= strtolower(e($training['required_skills'] ?? '')) ?>">
                        <div class="card-body">

                            <div class="d-flex gap-3 mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:55px;height:55px;background:#EEF4FF;">
                                    <i class="fa fa-building text-primary"></i>
                                </div>

                                <div>
                                    <h6 class="fw-bold mb-0"><?= e($training['company_name']) ?></h6>
                                    <small class="text-muted">
                                        Company · Training Provider · <?= date("M d, Y", strtotime($training['created_at'])) ?>
                                    </small>
                                </div>
                            </div>

                            <p>
                                <?= nl2br(e($training['description'])) ?>
                            </p>

                            <div class="bg-light rounded-4 p-4 mb-3">
                                <span class="badge bg-primary rounded-pill mb-2">
                                    <?= $training['training_type'] === 'university' ? 'University Training' : 'Voluntary Training' ?>
                                </span>

                                <h5 class="fw-bold"><?= e($training['title']) ?></h5>

                                <p class="text-muted small mb-3">
                                    <?= e($training['field']) ?> ·
                                    Duration: <?= e($training['duration']) ?> months ·
                                    <?= e($training['location'] ?: 'Location not specified') ?>
                                </p>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 bg-white">
                                            <small class="text-muted d-block">Start Date</small>
                                            <strong><?= e($training['start_date'] ?: '-') ?></strong>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 bg-white">
                                            <small class="text-muted d-block">End Date</small>
                                            <strong><?= e($training['end_date'] ?: '-') ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($training['required_skills'])): ?>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach (explode(',', $training['required_skills']) as $skill): ?>
                                            <span class="badge bg-white text-dark border rounded-pill px-3 py-2">
                                                <?= e(trim($skill)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($training['already_applied']): ?>
                                    <button class="btn btn-outline-primary rounded-pill px-4" disabled>
                                        Applied · <?= e($training['application_status']) ?>
                                    </button>
                                <?php else: ?>
                                    <button 
                                        class="btn btn-primary rounded-pill px-4"
                                        data-bs-toggle="modal"
                                        data-bs-target="#applyModal<?= $training['id'] ?>">
                                        Apply for Training
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span>🎓 Training opportunity</span>
                                <span><?= e($training['seats'] ?: 'Open') ?> seats</span>
                            </div>

           

<div class="collapse mt-3" id="commentBox<?= $training['id'] ?>">
    <?php if (
        isset($_SESSION['training_comment_error']) &&
        ($_SESSION['training_comment_open_id'] ?? '') == $training['id']
    ): ?>
        <small class="text-danger d-block mb-2">
            <?= e($_SESSION['training_comment_error']) ?>
        </small>
    <?php endif; ?>

    <form method="POST" action="comment-training.php" class="d-flex gap-2 training-ajax-action training-comment-form">
        <input type="hidden" name="training_id" value="<?= $training['id'] ?>">

        <input 
            type="text" 
            name="comment"
            class="form-control rounded-pill"
            placeholder="Write a comment..."
        >

        <button class="btn btn-primary rounded-pill px-4" type="submit">
            Send
        </button>
    </form>
</div>
                        </div>
                    </div>

                    <!-- Apply Modal -->
                    <div class="modal fade" id="applyModal<?= $training['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">

                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="modal-title fw-bold">Apply for Training</h5>
                                        <small class="text-muted"><?= e($training['title']) ?></small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <form method="POST" action="apply-training.php" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="training_id" value="<?= e($training['id']) ?>">

                                        <label class="form-label fw-bold">Comment</label>
                                        <textarea 
                                            name="comment" 
                                            rows="4"
                                            class="form-control bg-light rounded-4 mb-1 <?= isset($errors['comment']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
                                            placeholder="Tell the company why you are interested..."
                                        ><?= (string)$openTrainingId === (string)$training['id'] ? e($old['comment'] ?? '') : '' ?></textarea>
                                        <?php if (isset($errors['comment']) && (string)$openTrainingId === (string)$training['id']): ?>
                                            <small class="text-danger d-block mb-2"><?= e($errors['comment']) ?></small>
                                        <?php endif; ?>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">University</label>
                                                <input 
                                                    type="text" 
                                                    name="university"
                                                    class="form-control mb-1 <?= isset($errors['university']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
                                                    value="<?= (string)$openTrainingId === (string)$training['id'] ? e($old['university'] ?? '') : '' ?>"
                                                >
                                                <?php if (isset($errors['university']) && (string)$openTrainingId === (string)$training['id']): ?>
                                                    <small class="text-danger d-block mb-2"><?= e($errors['university']) ?></small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Major</label>
                                                <input 
                                                    type="text" 
                                                    name="major"
                                                    class="form-control mb-1 <?= isset($errors['major']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
                                                    value="<?= (string)$openTrainingId === (string)$training['id'] ? e($old['major'] ?? '') : '' ?>"
                                                >
                                                <?php if (isset($errors['major']) && (string)$openTrainingId === (string)$training['id']): ?>
                                                    <small class="text-danger d-block mb-2"><?= e($errors['major']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
<div class="row mt-3">

    <div class="col-md-6">
        <label class="form-label fw-bold">Email</label>
        <input 
            type="email" 
            name="email"
            class="form-control mb-1 <?= isset($errors['email']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
            value="<?= (string)$openTrainingId === (string)$training['id'] 
                ? e($old['email'] ?? ($user['email'] ?? '')) 
                : e($user['email'] ?? '') ?>"
        >
        <?php if (isset($errors['email']) && (string)$openTrainingId === (string)$training['id']): ?>
            <small class="text-danger d-block mb-2"><?= e($errors['email']) ?></small>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold">Phone</label>
        <input 
            type="text" 
            name="phone"
            class="form-control mb-1 <?= isset($errors['phone']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
            value="<?= (string)$openTrainingId === (string)$training['id'] 
                ? e($old['phone'] ?? ($user['mobile'] ?? '')) 
                : e($user['mobile'] ?? '') ?>"
        >
        <?php if (isset($errors['phone']) && (string)$openTrainingId === (string)$training['id']): ?>
            <small class="text-danger d-block mb-2"><?= e($errors['phone']) ?></small>
        <?php endif; ?>
    </div>

</div>
                                        <label class="form-label fw-bold mt-3">Expected Graduation Year</label>
                                        <select 
                                            name="expected_graduation_year"
                                            class="form-select mb-1 <?= isset($errors['expected_graduation_year']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>">
                                            <option value="">Choose year</option>
                                            <?php for ($year = date('Y'); $year <= date('Y') + 8; $year++): ?>
                                                <option value="<?= $year ?>" 
                                                    <?= ((string)$openTrainingId === (string)$training['id'] && ($old['expected_graduation_year'] ?? '') == $year) ? 'selected' : '' ?>>
                                                    <?= $year ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <?php if (isset($errors['expected_graduation_year']) && (string)$openTrainingId === (string)$training['id']): ?>
                                            <small class="text-danger d-block mb-2"><?= e($errors['expected_graduation_year']) ?></small>
                                        <?php endif; ?>

                                        <label class="form-label fw-bold mt-3">CV File <span class="text-muted">(Optional)</span></label>
                                        <input 
                                            type="file" 
                                            name="cv_file"
                                            accept=".pdf,.doc,.docx"
                                            class="form-control mb-1 <?= isset($errors['cv_file']) && (string)$openTrainingId === (string)$training['id'] ? 'is-invalid' : '' ?>"
                                        >
                                        <?php if (isset($errors['cv_file']) && (string)$openTrainingId === (string)$training['id']): ?>
                                            <small class="text-danger d-block mb-2"><?= e($errors['cv_file']) ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            Submit Application
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

        <!-- Right Sidebar -->
        <aside class="col-lg-3 d-none d-lg-block">

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Suggested Training</h5>

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1">University Training</h6>
                        <small class="text-muted d-block mb-2">For graduation requirements</small>
                        <a href="trainings.php?type=university" class="btn btn-outline-primary btn-sm rounded-pill">View</a>
                    </div>

                    <hr>

                    <div>
                        <h6 class="fw-bold mb-1">Voluntary Training</h6>
                        <small class="text-muted d-block mb-2">For skill building</small>
                        <a href="trainings.php?type=voluntary" class="btn btn-outline-primary btn-sm rounded-pill">View</a>
                    </div>
                </div>
            </div>

            

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center">
                    <i class="fa fa-file-alt fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">My Applications</h5>
                    <p class="text-muted small">
                        Track your training application status.
                    </p>
                    <a href="my-training-applications.php" class="btn btn-primary rounded-pill px-4">
                        Open
                    </a>
                </div>
            </div>

        </aside>

    </div>
</main>


<?php if (!empty($errors) && !empty($openTrainingId)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalEl = document.getElementById("applyModal<?= e($openTrainingId) ?>");
    if (modalEl) {
        new bootstrap.Modal(modalEl).show();
    }
});
</script>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("trainingSearch");
    const suggestions = document.getElementById("trainingSearchSuggestions");
    const form = input ? input.closest("form") : null;
    const items = document.querySelectorAll(".training-post-item");

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
            const field = item.dataset.field || "";
            const location = item.dataset.location || "";
            const skills = item.dataset.skills || "";
            const haystack = `${title} ${company} ${field} ${location} ${skills}`;

            if (haystack.includes(value.toLowerCase())) {
                matches.push({
                    title: title || "Training",
                    company: company || "Company",
                    field: field || "Field"
                });
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
            div.innerHTML = `
                <strong>${match.title}</strong>
                <small>${match.company} · ${match.field}</small>
            `;

            div.addEventListener("click", function () {
                input.value = match.title;
                form.submit();
            });

            suggestions.appendChild(div);
        });

        suggestions.classList.remove("d-none");
    }

    input.addEventListener("input", function () {
        buildSuggestions(this.value);
    });

    input.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            form.submit();
        }
    });

    document.addEventListener("click", function (e) {
        if (!suggestions.contains(e.target) && e.target !== input) {
            suggestions.classList.add("d-none");
        }
    });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".training-ajax-action").forEach(function (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const button = form.querySelector("button[type='submit']");
            const oldText = button ? button.innerHTML : "";

            if (button) {
                button.disabled = true;
                button.innerHTML = "Saving...";
            }

            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    body: new FormData(form),
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                if (!response.ok) {
                    throw new Error("Request failed");
                }

                if (form.classList.contains("training-comment-form")) {
                    const input = form.querySelector("input[name='comment']");
                    if (input) input.value = "";

                    const box = form.closest(".collapse");
                    if (box && window.bootstrap) {
                        const instance = bootstrap.Collapse.getOrCreateInstance(box);
                        instance.hide();
                    }
                }

                if (button) {
                    button.innerHTML = "Done";
                    setTimeout(function () {
                        button.innerHTML = oldText;
                        button.disabled = false;
                    }, 900);
                }
            } catch (error) {
                if (button) {
                    button.innerHTML = "Try again";
                    button.disabled = false;
                }
            }
        });
    });
});
</script>

<?php
unset($_SESSION['training_comment_error'], $_SESSION['training_comment_open_id']);
?>
<?php
}
