<?php

function renderArchivedTrainingsPage($page)
{
    extract($page);

    $base_url = $base_url ?? '../../';
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
                   class="p-3 d-flex justify-content-between align-items-center text-decoration-none text-dark">

                    <span>
                        Posted trainings
                    </span>

                </a>

                <div class="p-3 d-flex justify-content-between align-items-center border-start border-primary border-4">

                    <span class="fw-bold text-primary">
                        Archived trainings
                    </span>

                    <span>
                        <?= (int)$archived_count ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="bg-white rounded-4 shadow-sm border mb-4 p-4">

                <h4 class="fw-bold mb-2">
                    Archived Trainings
                </h4>

                <p class="text-muted mb-0">
                    Trainings you removed from your active list.
                </p>

            </div>

            <div class="bg-white rounded-4 shadow-sm border">

                <div class="p-4 border-bottom">
                    <h4 class="fw-bold mb-0">
                        Archived Trainings
                    </h4>
                </div>

                <?php if (empty($trainings)): ?>

                    <div class="p-5 text-center">

                        <i class="fa fa-archive fa-3x text-muted mb-3"></i>

                        <h5 class="fw-bold">
                            No archived trainings
                        </h5>

                        <p class="text-muted">
                            Deleted trainings will appear here.
                        </p>

                    </div>

                <?php else: ?>

                    <?php foreach ($trainings as $training): ?>

                        <div class="p-4 border-bottom training-post-item task-post-item"
                             id="training-<?= (int)$training['id'] ?>">

                            <div class="d-flex gap-3">

                                <div>
                                    <div style="width:56px;height:56px;background:#eef3ff;border-radius:14px;"
                                         class="d-flex align-items-center justify-content-center">

                                        <i class="fa fa-archive text-primary"></i>

                                    </div>
                                </div>

                                <div class="flex-grow-1">

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
                                        End Date:
                                        <?= training_e($training['end_date'] ?: '-') ?>
                                    </small>

                                    <div class="mt-3">

                                        <span class="badge bg-dark rounded-pill px-3 py-2">
                                            Archived
                                        </span>

                                        <a href="restore-training.php?id=<?= (int)$training['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-pill ms-2">

                                            Restore

                                        </a>

                                        <a href="javascript:void(0)" 
                                           class="btn btn-sm btn-outline-danger rounded-pill ms-2 delete-training-forever-btn delete-forever-btn"
                                           data-id="<?= (int)$training['id'] ?>">

                                            Delete Forever

                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

        <div class="col-lg-3">

            <div class="bg-white rounded-4 shadow-sm border p-4">

                <a href="my-trainings.php"
                   class="btn btn-outline-primary w-100 rounded-pill fw-bold">

                    Back to My Trainings

                </a>

            </div>

        </div>

        <div class="modal fade" id="deleteForeverModal" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content rounded-4">

                    <div class="modal-body text-center p-4">

                        <h5 class="fw-bold mb-3 text-danger">
                            Delete Forever?
                        </h5>

                        <p class="text-muted">
                            This will permanently remove the training and cannot be undone.
                        </p>

                        <div class="d-flex justify-content-center gap-2 mt-4">

                            <button type="button"
                                    class="btn btn-light px-4"
                                    data-bs-dismiss="modal">

                                Cancel

                            </button>

                            <button type="button"
                                    class="btn btn-danger px-4"
                                    id="confirmDeleteForeverBtn">

                                Delete

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
