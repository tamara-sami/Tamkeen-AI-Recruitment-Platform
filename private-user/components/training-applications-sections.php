<?php

function renderMyTrainingApplicationsPage($page)
{
    extract($page);
    $base_url = $base_url ?? '../../';
?>
<main class="container py-4">
    <div class="row g-4">

        <aside class="col-lg-3 d-none d-lg-block">

          

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <a href="trainings.php" class="btn btn-primary w-100 rounded-pill fw-bold mb-2">
                        Browse Trainings
                    </a>

                    <a href="dashboard.php" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                        Back to Profile
                    </a>
                </div>
            </div>

        </aside>

        <section class="col-lg-6">

            <?php if ($success): ?>
                <div class="alert alert-success rounded-4 shadow-sm border-0">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-1">
                        <i class="fa fa-file-alt text-primary me-2"></i>
                        My Training Applications
                    </h3>
                    <p class="text-muted mb-0">
                        Track your applications, edit your details, and follow your status.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        $tabs = [
                            'all' => 'All',
                            'pending' => 'Pending',
                            'reviewing' => 'Reviewing',
                            'interview' => 'Interview',
                            'accepted' => 'Accepted',
                            'rejected' => 'Rejected'
                        ];
                        ?>

                        <?php foreach ($tabs as $key => $label): ?>
                            <a href="?status=<?= $key ?>" class="btn <?= $filter === $key ? 'btn-primary' : 'btn-light' ?> rounded-pill px-4 fw-bold">
                                <?= $label ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (empty($applications)): ?>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center p-5">
                        <i class="fa fa-folder-open fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">No applications found</h5>
                        <p class="text-muted">Apply for training opportunities first.</p>
                        <a href="trainings.php" class="btn btn-primary rounded-pill px-4">
                            Browse Trainings
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($applications as $app): ?>

                    <?php
                    $statusClass = "bg-secondary";
                    if ($app['status'] === 'accepted') $statusClass = "bg-success";
                    if ($app['status'] === 'rejected') $statusClass = "bg-danger";
                    if ($app['status'] === 'interview') $statusClass = "bg-primary";
                    if ($app['status'] === 'reviewing') $statusClass = "bg-warning text-dark";
                    ?>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><?= e($app['title']) ?></h5>
                                    <p class="text-muted mb-1"><?= e($app['company_name']) ?></p>
                                    <small class="text-muted">
                                        <?= e($app['field']) ?> ·
                                        <?= e($app['training_type']) ?> ·
                                        <?= e($app['start_date']) ?> to <?= e($app['end_date']) ?>
                                    </small>
                                </div>

                                <span class="badge <?= $statusClass ?> rounded-pill px-3 py-2">
                                    <?= e($app['status']) ?>
                                </span>
                            </div>

                            <div class="bg-light rounded-4 p-3 mb-3">
                                <strong>Your Comment</strong>
                                <p class="small mb-0 mt-1"><?= nl2br(e($app['comment'])) ?></p>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3">
                                        <small class="text-muted d-block">University</small>
                                        <strong><?= e($app['university']) ?></strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3">
                                        <small class="text-muted d-block">Major</small>
                                        <strong><?= e($app['major']) ?></strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3">
                                        <small class="text-muted d-block">Email</small>
                                        <strong><?= e($app['email'] ?? 'Not added') ?></strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3">
                                        <small class="text-muted d-block">Phone</small>
                                        <strong><?= e($app['phone'] ?? 'Not added') ?></strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3">
                                        <small class="text-muted d-block">Expected Graduation</small>
                                        <strong><?= e($app['expected_graduation_year'] ?? 'Not added') ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <small class="text-muted">
                                    Applied on <?= date("M d, Y", strtotime($app['created_at'])) ?>
                                </small>

                                <button 
                                    class="btn btn-outline-primary rounded-pill px-4 edit-application-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editApplicationModal"
                                    data-id="<?= e($app['id']) ?>"
                                    data-comment="<?= e($app['comment']) ?>"
                                    data-university="<?= e($app['university']) ?>"
                                    data-major="<?= e($app['major']) ?>"
                                    data-email="<?= e($app['email'] ?? '') ?>"
                                    data-phone="<?= e($app['phone'] ?? '') ?>"
                                    data-year="<?= e($app['expected_graduation_year'] ?? '') ?>">
                                    Edit Application
                                </button>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

        <aside class="col-lg-3 d-none d-lg-block">

            

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="fa fa-info-circle text-primary me-2"></i>
                        What happens next?
                    </h5>

                    <p class="text-muted small mb-2">
                        The training manager reviews your application first.
                    </p>

                    <p class="text-muted small mb-2">
                        If accepted, HR may contact you by email or phone.
                    </p>

                    <p class="text-muted small mb-0">
                        Keep your contact information updated.
                    </p>
                </div>
            </div>

        </aside>

    </div>
</main>

<div class="modal fade" id="editApplicationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold">Edit Application</h5>
                    <small class="text-muted">Update your application details.</small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="update-training-application.php" enctype="multipart/form-data">
                <div class="modal-body">

                    <input type="hidden" name="application_id" id="editApplicationId">

                    <label class="form-label fw-bold">Comment</label>
                    <textarea 
                        name="comment" 
                        id="editComment" 
                        rows="4" 
                        class="form-control bg-light rounded-4 mb-1 <?= isset($errors['comment']) ? 'is-invalid' : '' ?>"></textarea>
                    <?php if (isset($errors['comment'])): ?>
                        <small class="text-danger d-block mb-2"><?= e($errors['comment']) ?></small>
                    <?php endif; ?>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">University</label>
                            <input 
                                type="text" 
                                name="university" 
                                id="editUniversity" 
                                class="form-control mb-1 <?= isset($errors['university']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['university'])): ?>
                                <small class="text-danger d-block mb-2"><?= e($errors['university']) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Major</label>
                            <input 
                                type="text" 
                                name="major" 
                                id="editMajor" 
                                class="form-control mb-1 <?= isset($errors['major']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['major'])): ?>
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
                                id="editEmail" 
                                class="form-control mb-1 <?= isset($errors['email']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['email'])): ?>
                                <small class="text-danger d-block mb-2"><?= e($errors['email']) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone</label>
                            <input 
                                type="text" 
                                name="phone" 
                                id="editPhone" 
                                class="form-control mb-1 <?= isset($errors['phone']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <small class="text-danger d-block mb-2"><?= e($errors['phone']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <label class="form-label fw-bold mt-3">Expected Graduation Year</label>
                    <select 
                        name="expected_graduation_year" 
                        id="editYear" 
                        class="form-select bg-white mb-1 <?= isset($errors['expected_graduation_year']) ? 'is-invalid' : '' ?>"
                        style="height:48px;color:#0F172A;">
                        <option value="">Choose year</option>
                        <?php
                        $currentYear = (int)date('Y');
                        for ($year = $currentYear; $year <= $currentYear + 8; $year++):
                        ?>
                            <option value="<?= $year ?>"><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (isset($errors['expected_graduation_year'])): ?>
                        <small class="text-danger d-block mb-2"><?= e($errors['expected_graduation_year']) ?></small>
                    <?php endif; ?>

                    <label class="form-label fw-bold mt-3">Replace CV <span class="text-muted">(Optional)</span></label>
                    <input 
                        type="file" 
                        name="cv_file" 
                        class="form-control mb-1 <?= isset($errors['cv_file']) ? 'is-invalid' : '' ?>" 
                        accept=".pdf,.doc,.docx">
                    <?php if (isset($errors['cv_file'])): ?>
                        <small class="text-danger d-block mb-2"><?= e($errors['cv_file']) ?></small>
                    <?php endif; ?>

                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.edit-application-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editApplicationId').value = this.dataset.id || '';
        document.getElementById('editComment').value = this.dataset.comment || '';
        document.getElementById('editUniversity').value = this.dataset.university || '';
        document.getElementById('editMajor').value = this.dataset.major || '';
        document.getElementById('editEmail').value = this.dataset.email || '';
        document.getElementById('editPhone').value = this.dataset.phone || '';
        document.getElementById('editYear').value = this.dataset.year || '';
    });
});
</script>

<?php if (!empty($errors)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('editApplicationId').value = "<?= e($old['application_id'] ?? '') ?>";
    document.getElementById('editComment').value = "<?= e($old['comment'] ?? '') ?>";
    document.getElementById('editUniversity').value = "<?= e($old['university'] ?? '') ?>";
    document.getElementById('editMajor').value = "<?= e($old['major'] ?? '') ?>";
    document.getElementById('editEmail').value = "<?= e($old['email'] ?? '') ?>";
    document.getElementById('editPhone').value = "<?= e($old['phone'] ?? '') ?>";
    document.getElementById('editYear').value = "<?= e($old['expected_graduation_year'] ?? '') ?>";

    new bootstrap.Modal(document.getElementById('editApplicationModal')).show();
});
</script>
<?php endif; ?>
<?php
}
