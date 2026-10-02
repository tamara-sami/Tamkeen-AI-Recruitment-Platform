<?php

function renderPostTrainingPage($page)
{
    extract($page);
    $base_url = $base_url ?? '../../';
?>

<main class="container py-5">

    <div class="alert bg-white border border-primary rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4">
        <i class="fa fa-info-circle text-primary"></i>
        <strong>Create a training opportunity</strong>
        <span class="text-muted">and send it to admin approval before it goes live.</span>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">

            <form method="POST">

                <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                    <h5 class="fw-bold mb-3">Training Details</h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Training Title</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control task-input <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                            placeholder="Example: Frontend Development Internship"
                            value="<?= training_e($old['title'] ?? '') ?>"
                        >
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= training_e($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Training Description</label>
                        <textarea
                            name="description"
                            class="form-control task-input <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                            rows="5"
                            placeholder="Explain what trainees will learn and do..."
                        ><?= training_e($old['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= training_e($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Training Type</label>
                            <select name="training_type" class="form-select task-input <?= isset($errors['training_type']) ? 'is-invalid' : '' ?>">
                                <option value="">Choose type</option>
                                <option value="voluntary" <?= ($old['training_type'] ?? '') === 'voluntary' ? 'selected' : '' ?>>Voluntary Training</option>
                                <option value="university" <?= ($old['training_type'] ?? '') === 'university' ? 'selected' : '' ?>>University Training</option>
                            </select>
                            <?php if (isset($errors['training_type'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['training_type']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Field</label>
                            <select name="field" class="form-select task-input <?= isset($errors['field']) ? 'is-invalid' : '' ?>">
                                <option value="">Choose field</option>

                                <?php foreach ($fields as $fieldOption): ?>
                                    <option value="<?= training_e($fieldOption) ?>" <?= ($old['field'] ?? '') === $fieldOption ? 'selected' : '' ?>>
                                        <?= training_e($fieldOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['field'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['field']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Duration <span class="text-muted">(months)</span></label>
                            <input
                                type="number"
                                min="1"
                                name="duration"
                                class="form-control task-input <?= isset($errors['duration']) ? 'is-invalid' : '' ?>"
                                placeholder="Example: 2"
                                value="<?= training_e($old['duration'] ?? '') ?>"
                            >
                            <?php if (isset($errors['duration'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['duration']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Start Date</label>
                            <input
                                type="date"
                                name="start_date"
                                class="form-control task-input <?= isset($errors['start_date']) ? 'is-invalid' : '' ?>"
                                value="<?= training_e($old['start_date'] ?? '') ?>"
                                min="<?= date('Y-m-d') ?>"
                            >
                            <?php if (isset($errors['start_date'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['start_date']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">End Date</label>
                            <input
                                type="date"
                                name="end_date"
                                class="form-control task-input <?= isset($errors['end_date']) ? 'is-invalid' : '' ?>"
                                value="<?= training_e($old['end_date'] ?? '') ?>"
                                min="<?= date('Y-m-d') ?>"
                            >
                            <?php if (isset($errors['end_date'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['end_date']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Location</label>
                            <input
                                type="text"
                                name="location"
                                class="form-control task-input"
                                placeholder="Example: Amman / Remote / Hybrid"
                                value="<?= training_e($old['location'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Available Seats</label>
                            <input
                                type="number"
                                min="1"
                                name="seats"
                                class="form-control task-input <?= isset($errors['seats']) ? 'is-invalid' : '' ?>"
                                placeholder="Example: 2"
                                value="<?= training_e($old['seats'] ?? '') ?>"
                            >
                            <?php if (isset($errors['seats'])): ?>
                                <div class="invalid-feedback"><?= training_e($errors['seats']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Required Skills</label>
                        <input
                            type="text"
                            name="required_skills"
                            class="form-control task-input"
                            placeholder="Example: HTML, CSS, Communication"
                            value="<?= training_e($old['required_skills'] ?? '') ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Requirements</label>
                        <textarea
                            name="requirements"
                            class="form-control task-input <?= isset($errors['requirements']) ? 'is-invalid' : '' ?>"
                            rows="4"
                            placeholder="Example: Must be a university student, basic knowledge in the field..."
                        ><?= training_e($old['requirements'] ?? '') ?></textarea>
                        <?php if (isset($errors['requirements'])): ?>
                            <div class="invalid-feedback"><?= training_e($errors['requirements']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-4 shadow-sm border p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <a href="my-trainings.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                            Cancel
                        </a>

                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="draft" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                                Save Draft
                            </button>

                            <button type="submit" name="status" value="published" class="btn btn-primary rounded-pill px-4 fw-bold">
                                Request Approval
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>

        <div class="col-lg-4">
            <div class="bg-white rounded-4 shadow-sm border p-4 side-sticky">
                <h5 class="fw-bold mb-3">Training Tips</h5>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <div class="activity-content">
                        <strong>University Training</strong>
                        <p class="mb-0">Use it for students who need training hours for graduation.</p>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fa fa-hands-helping"></i>
                    </div>
                    <div class="activity-content">
                        <strong>Voluntary Training</strong>
                        <p class="mb-0">Use it for learners who want experience without university requirements.</p>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fa fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <strong>Clear requirements</strong>
                        <p class="mb-0">Mention skills, dates, location, and expected commitment.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>

<?php
}
