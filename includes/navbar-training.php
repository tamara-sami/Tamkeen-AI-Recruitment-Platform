<?php
$base_url = $base_url ?? "/tamkeentest/";
$activePage = $activePage ?? "";
$companyName = $_SESSION['company_name'] ?? $companyName ?? 'Company';

if (!str_ends_with($base_url, "/")) {
    $base_url .= "/";
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4 py-3">
    <div class="container">

        <a class="navbar-brand fw-bold d-flex align-items-center"
           href="<?= $base_url ?>public/training/training-dashboard.php">

            <span class="brand-icon me-2 custom-logo">
                <span class="logo-t">T</span>
                <span class="logo-dot dot1"></span>
                <span class="logo-dot dot2"></span>
                <span class="logo-dot dot3"></span>
            </span>

            <span class="fw-bold fs-3">
                <span style="color:#080808;">Tam</span><span style="color:#2563EB;">keen</span>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#trainingNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="trainingNav">
            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'training-dashboard' ? 'active' : '' ?>"
                       href="<?= $base_url ?>public/training/training-dashboard.php">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'post-training' ? 'active' : '' ?>"
                       href="<?= $base_url ?>public/training/post-training.php">
                        Post Training
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'training-tracker' ? 'active' : '' ?>"
                       href="<?= $base_url ?>public/training/training-tracker.php">
                        Training Tracker
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'my-trainings' ? 'active' : '' ?>"
                       href="<?= $base_url ?>public/training/my-trainings.php">
                        My Trainings
                    </a>
                </li>

            </ul>

            <div class="dropdown">
                <button class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold dropdown-toggle"
                        type="button" data-bs-toggle="dropdown">
                    <?= htmlspecialchars($companyName) ?>
                </button>

                <div class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">
                    <a class="dropdown-item text-danger"
                       href="<?= $base_url ?>public/training/logout.php">
                        Logout
                    </a>
                </div>
            </div>
        </div>

    </div>
</nav>