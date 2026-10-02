<?php

function renderMyTrainingsPage($page)
{
    extract($page);

    $base_url = $base_url ?? '../../';

    $totalCount = (int)($counts['total_count'] ?? 0);
    $publishedCount = (int)($counts['published_count'] ?? 0);
    $draftCount = (int)($counts['draft_count'] ?? 0);
    $pendingCount = (int)($counts['pending_count'] ?? 0);
    $rejectedCount = (int)($counts['rejected_count'] ?? 0);
    $archivedCount = (int)($counts['archived_count'] ?? 0);
?>

<main class="container py-5">

    <div class="row g-4">

        <div class="col-lg-3">

            <div class="bg-white rounded-4 shadow-sm border overflow-hidden">

                <div class="p-3 border-bottom fw-bold">
                    <i class="fa fa-bookmark me-2 text-muted"></i>
                    My items
                </div>

                <a href="my-trainings.php"
                   class="p-3 d-flex justify-content-between align-items-center text-decoration-none border-start border-primary border-4">

                    <span class="fw-bold text-primary">
                        Posted trainings
                    </span>

                    <span id="trainingsCount">
                        <?= $totalCount ?>
                    </span>

                </a>

                <a href="archived-trainings.php"
                   class="p-3 d-flex justify-content-between align-items-center text-decoration-none text-dark border-top">

                    <span>
                        Archived trainings
                    </span>

                    <span>
                        <?= $archivedCount ?>
                    </span>

                </a>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="bg-white rounded-4 shadow-sm border mb-4 p-4">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                    <div>
                        <h4 class="fw-bold mb-2">
                            Posted Trainings
                        </h4>

                        <p class="text-muted mb-0">
                            Manage, publish, edit, and archive your trainings.
                        </p>
                    </div>

                    <a href="post-training.php"
                       class="btn btn-primary rounded-pill px-4 fw-bold">

                        <i class="fa fa-plus me-2"></i>
                        Add Training

                    </a>

                </div>

            </div>

            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-primary mb-0">
                            <?= $totalCount ?>
                        </h4>
                        <small class="text-muted">
                            All Trainings
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-success mb-0">
                            <?= $publishedCount ?>
                        </h4>
                        <small class="text-muted">
                            Published
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-secondary mb-0">
                            <?= $draftCount ?>
                        </h4>
                        <small class="text-muted">
                            Drafts
                        </small>
                    </div>
                </div>

            </div>

            <div class="bg-white rounded-4 shadow-sm border">

                <div class="p-4 border-bottom">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

                        <h4 class="fw-bold mb-0">
                            Posted Trainings
                        </h4>

                        <div class="task-tabs">

                            <a href="?filter=all&search=<?= urlencode($search) ?>"
                               class="<?= $filter === 'all' ? 'active' : '' ?>">

                                All

                            </a>

                            <a href="?filter=published&search=<?= urlencode($search) ?>"
                               class="<?= $filter === 'published' ? 'active' : '' ?>">

                                Published

                            </a>

                            <a href="?filter=draft&search=<?= urlencode($search) ?>"
                               class="<?= $filter === 'draft' ? 'active' : '' ?>">

                                Drafts

                            </a>

                            <a href="?filter=pending_approval&search=<?= urlencode($search) ?>"
                               class="<?= $filter === 'pending_approval' ? 'active' : '' ?>">

                                Pending

                            </a>

                            <a href="?filter=rejected&search=<?= urlencode($search) ?>"
                               class="<?= $filter === 'rejected' ? 'active' : '' ?>">

                                Rejected

                            </a>

                        </div>

                    </div>

                    <form method="GET" class="position-relative">

                        <input type="hidden" name="filter" value="<?= training_e($filter) ?>">

                        <input 
                            type="text" 
                            id="trainingSearch"
                            name="search"
                            class="form-control rounded-pill"
                            placeholder="Search trainings by title, field, type, skills, location..."
                            value="<?= training_e($search) ?>"
                            autocomplete="off"
                        >

                        <div id="searchSuggestions" 
                             class="bg-white border rounded-4 shadow-sm position-absolute w-100 mt-2 d-none"
                             style="z-index:999;">
                        </div>

                    </form>

                </div>

                <?php if (empty($trainings)): ?>

                    <div class="p-5 text-center">

                        <i class="fa fa-search fa-3x text-muted mb-3"></i>

                        <h5 class="fw-bold">
                            No trainings found
                        </h5>

                        <p class="text-muted">
                            Try changing the filter or search keyword.
                        </p>

                        <a href="post-training.php"
                           class="btn btn-primary rounded-pill px-4">

                            Post New Training

                        </a>

                    </div>

                <?php else: ?>

                    <?php foreach ($trainings as $training): ?>

                        <div class="p-4 border-bottom training-post-item task-post-item" 
                             id="training-<?= (int)$training['id'] ?>"
                             data-title="<?= strtolower(training_e($training['title'] ?? '')) ?>"
                             data-field="<?= strtolower(training_e($training['field'] ?? '')) ?>"
                             data-category="<?= strtolower(training_e($training['training_type'] ?? '')) ?>">

                            <div class="d-flex gap-3">

                                <div>
                                    <div style="width:56px;height:56px;background:#eef3ff;border-radius:14px;"
                                         class="d-flex align-items-center justify-content-center">

                                        <?php if (($training['training_type'] ?? '') === 'university'): ?>

                                            <i class="fa fa-graduation-cap text-primary"></i>

                                        <?php elseif (($training['training_type'] ?? '') === 'voluntary'): ?>

                                            <i class="fa fa-hands-helping text-primary"></i>

                                        <?php else: ?>

                                            <i class="fa fa-briefcase text-primary"></i>

                                        <?php endif; ?>

                                    </div>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-start gap-3">

                                        <div>

                                            <h5 class="fw-bold mb-1">
                                                <?= training_e($training['title'] ?: 'Untitled Training') ?>
                                            </h5>

                                            <p class="mb-1 text-muted">
                                                <?= training_e($training['field'] ?: 'No field') ?>
                                                ·
                                                <?= training_e($training['training_type'] ?: 'No type') ?>
                                            </p>

                                            <small class="text-muted">
                                                Duration:
                                                <?= training_e($training['duration'] ?: '-') ?> months
                                                ·
                                                Start:
                                                <?= training_e($training['start_date'] ?: '-') ?>
                                                ·
                                                End:
                                                <?= training_e($training['end_date'] ?: '-') ?>
                                            </small>

                                        </div>

                                        <div class="dropdown">

                                            <button class="btn btn-sm"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">

                                                <i class="fa fa-ellipsis-h"></i>

                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">

                                                <?php if (($training['status'] ?? '') === 'draft'): ?>

                                                    <li>
                                                        <a href="javascript:void(0)"
                                                           class="dropdown-item text-success publish-training-btn"
                                                           data-id="<?= (int)$training['id'] ?>">

                                                            <i class="fa fa-upload me-2"></i>
                                                            Publish

                                                        </a>
                                                    </li>

                                                <?php endif; ?>

                                                <li>
                                                    <a href="javascript:void(0)"
                                                       class="dropdown-item edit-training-btn"
                                                       data-id="<?= (int)$training['id'] ?>"
                                                       data-title="<?= training_e($training['title']) ?>"
                                                       data-description="<?= training_e($training['description']) ?>"
                                                       data-training-type="<?= training_e($training['training_type']) ?>"
                                                       data-field="<?= training_e($training['field']) ?>"
                                                       data-location="<?= training_e($training['location']) ?>"
                                                       data-duration="<?= training_e($training['duration']) ?>"
                                                       data-start-date="<?= training_e($training['start_date']) ?>"
                                                       data-end-date="<?= training_e($training['end_date']) ?>"
                                                       data-skills="<?= training_e($training['required_skills']) ?>"
                                                       data-requirements="<?= training_e($training['requirements']) ?>"
                                                       data-seats="<?= training_e($training['seats']) ?>">

                                                        <i class="fa fa-edit me-2"></i>
                                                        Edit

                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="javascript:void(0)"
                                                       class="dropdown-item text-danger delete-training-btn"
                                                       data-id="<?= (int)$training['id'] ?>">

                                                        <i class="fa fa-archive me-2"></i>
                                                        Archive

                                                    </a>
                                                </li>

                                            </ul>

                                        </div>

                                    </div>

                                    <div class="mt-3 d-flex flex-wrap align-items-center gap-2">

                                        <?php if (($training['status'] ?? '') === 'published'): ?>

                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                Published
                                            </span>

                                        <?php elseif (($training['status'] ?? '') === 'pending_approval'): ?>

                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                Pending Approval
                                            </span>

                                        <?php elseif (($training['status'] ?? '') === 'rejected'): ?>

                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                Rejected
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                Draft
                                            </span>

                                        <?php endif; ?>

                                        <span class="text-muted">
                                            Created
                                            <?= !empty($training['created_at']) ? date("M d, Y", strtotime($training['created_at'])) : '-' ?>
                                        </span>

                                        <?php if (!empty($training['location'])): ?>

                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                                <?= training_e($training['location']) ?>
                                            </span>

                                        <?php endif; ?>

                                        <?php if (($training['status'] ?? '') === 'draft'): ?>

                                            <button 
                                                class="btn btn-sm btn-success rounded-pill px-3 publish-training-btn"
                                                data-id="<?= (int)$training['id'] ?>">

                                                Publish

                                            </button>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

        <div class="col-lg-3">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">

                <a href="post-training.php"
                   class="btn btn-outline-primary w-100 rounded-pill fw-bold">

                    <i class="fa fa-plus me-2"></i>
                    Post a new training

                </a>

            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">

                <h6 class="fw-bold mb-3">
                    Quick Filters
                </h6>

                <a href="?filter=all"
                   class="btn btn-light w-100 rounded-pill mb-2 text-start">

                    All Trainings
                    <span class="float-end">
                        <?= $totalCount ?>
                    </span>

                </a>

                <a href="?filter=published"
                   class="btn btn-light w-100 rounded-pill mb-2 text-start">

                    Published
                    <span class="float-end">
                        <?= $publishedCount ?>
                    </span>

                </a>

                <a href="?filter=draft"
                   class="btn btn-light w-100 rounded-pill mb-2 text-start">

                    Drafts
                    <span class="float-end">
                        <?= $draftCount ?>
                    </span>

                </a>

                <a href="?filter=pending_approval"
                   class="btn btn-light w-100 rounded-pill mb-2 text-start">

                    Pending Approval
                    <span class="float-end">
                        <?= $pendingCount ?>
                    </span>

                </a>

                <a href="?filter=rejected"
                   class="btn btn-light w-100 rounded-pill text-start">

                    Rejected
                    <span class="float-end">
                        <?= $rejectedCount ?>
                    </span>

                </a>

            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4">

                <h6 class="fw-bold mb-3">
                    Training support
                </h6>

                <p class="text-muted small mb-3">
                    Archive old trainings, publish drafts, and keep your training list organized.
                </p>

                <a href="training-dashboard.php"
                   class="btn btn-primary rounded-pill px-4">

                    Dashboard

                </a>

            </div>

        </div>

        <div class="modal fade" id="deleteModal" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content rounded-4">

                    <div class="modal-body text-center p-4">

                        <h5 class="fw-bold mb-3">
                            Move to Archive?
                        </h5>

                        <p class="text-muted mb-0">
                            This training will move to Archived Trainings. You can restore it later.
                        </p>

                        <div class="d-flex justify-content-center gap-2 mt-4">

                            <button type="button"
                                    class="btn btn-light px-4"
                                    data-bs-dismiss="modal">

                                Cancel

                            </button>

                            <button type="button"
                                    class="btn btn-danger px-4"
                                    id="confirmDeleteBtn">

                                Archive

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="modal fade" id="editModal" tabindex="-1">

            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

                <div class="modal-content rounded-4">

                    <div class="modal-body p-4 p-lg-5">

                        <h3 class="fw-bold mb-4">
                            Edit Training
                        </h3>

                        <form id="editForm" data-type="training">

                            <input type="hidden" id="editId" name="id">

                            <label class="form-label fw-bold">Training Title</label>
                            <input type="text" id="editTitle" name="title" class="form-control mb-1">
                            <small class="text-danger d-none" id="error-title"></small>

                            <label class="form-label fw-bold mt-3">Training Description</label>
                            <textarea id="editDescription" name="description" class="form-control mb-1" rows="5"></textarea>
                            <small class="text-danger d-none" id="error-description"></small>

                            <div class="row mt-3">

                                <div class="col-md-6">

                                    <label class="form-label fw-bold">
                                        Training Type
                                    </label>

                                    <select id="editTrainingType" name="training_type" class="form-select mb-1">

                                        <option value="">
                                            Choose type
                                        </option>

                                        <option value="voluntary">
                                            Voluntary Training
                                        </option>

                                        <option value="university">
                                            University Training
                                        </option>

                                    </select>

                                    <small class="text-danger d-none" id="error-training_type"></small>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label fw-bold">
                                        Field
                                    </label>

                                    <select id="editField" name="field" class="form-select mb-1">

                                        <option value="">
                                            Choose field
                                        </option>

                                        <?php foreach ($fields as $fieldOption): ?>
                                            <option value="<?= training_e($fieldOption) ?>">
                                                <?= training_e($fieldOption) ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>

                                    <small class="text-danger d-none" id="error-field"></small>

                                </div>

                            </div>

                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <label class="form-label fw-bold">
                                        Duration
                                    </label>

                                    <input type="number"
                                           id="editDuration"
                                           name="duration"
                                           class="form-control mb-1">

                                    <small class="text-danger d-none" id="error-duration"></small>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label fw-bold">
                                        Start Date
                                    </label>

                                    <input type="date"
                                           id="editStartDate"
                                           name="start_date"
                                           class="form-control mb-1">

                                    <small class="text-danger d-none" id="error-start_date"></small>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label fw-bold">
                                        End Date
                                    </label>

                                    <input type="date"
                                           id="editEndDate"
                                           name="end_date"
                                           class="form-control mb-1">

                                    <small class="text-danger d-none" id="error-end_date"></small>

                                </div>

                            </div>

                            <label class="form-label fw-bold mt-3">
                                Location
                            </label>

                            <input type="text"
                                   id="editLocation"
                                   name="location"
                                   class="form-control mb-1">

                            <small class="text-danger d-none" id="error-location"></small>

                            <label class="form-label fw-bold mt-3">
                                Required Skills
                            </label>

                            <input type="text"
                                   id="editSkills"
                                   name="required_skills"
                                   class="form-control mb-1">

                            <small class="text-danger d-none" id="error-required_skills"></small>

                            <label class="form-label fw-bold mt-3">
                                Requirements
                            </label>

                            <textarea id="editRequirements"
                                      name="requirements"
                                      class="form-control mb-1"
                                      rows="4"></textarea>

                            <small class="text-danger d-none" id="error-requirements"></small>

                            <label class="form-label fw-bold mt-3">
                                Available Seats
                            </label>

                            <input type="number"
                                   id="editSeats"
                                   name="seats"
                                   class="form-control mb-1">

                            <small class="text-danger d-none" id="error-seats"></small>

                            <div class="d-flex justify-content-end gap-2 mt-4">

                                <button type="button"
                                        class="btn btn-light"
                                        data-bs-dismiss="modal">

                                    Cancel

                                </button>

                                <button type="submit"
                                        class="btn btn-primary">

                                    Save Changes

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

        <div class="modal fade" id="publishWarningModal" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content rounded-4">

                    <div class="modal-body text-center p-4">

                        <h5 class="fw-bold mb-3">
                            Complete training details
                        </h5>

                        <p class="text-muted mb-4">
                            This draft is incomplete. Continue to edit?
                        </p>

                        <div class="d-flex justify-content-center gap-2">

                            <button type="button"
                                    class="btn btn-light px-4"
                                    data-bs-dismiss="modal">

                                Cancel

                            </button>

                            <button type="button"
                                    class="btn btn-primary px-4"
                                    id="continueEditBtn">

                                Continue

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

<?php
}
