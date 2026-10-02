<?php

$base_url = $base_url ?? "/tamkeentest/";

if (!str_ends_with($base_url, "/")) {
    $base_url .= "/";
}

$activePage = $activePage ?? "";
$dashboard_type = $dashboard_type ?? "tasks";

$companyName = $_SESSION['company_name'] ?? $_SESSION['full_name'] ?? 'Company';

if ($dashboard_type === "training") {

    $mainUrl = $base_url . "public/training/training-dashboard.php";
    $postUrl = $base_url . "public/training/post-training.php";
    $manageUrl = $base_url . "public/training/my-trainings.php";
    $trackerUrl = $base_url . "public/training/training-tracker.php";
    $archivedUrl = $base_url . "public/training/archived-trainings.php";
    $logoutUrl = $base_url . "public/training/logout.php";

    $mainLabel = "Training";
    $postLabel = "Post Training";
    $manageLabel = "My Trainings";
    $trackerLabel = "Training Tracker";

} else {

    $mainUrl = $base_url . "public/task/task-dashboard.php";
    $postUrl = $base_url . "public/task/post-task.php";
    $manageUrl = $base_url . "public/task/my-tasks.php";
    $trackerUrl = $base_url . "public/task/task-tracker.php";
    $aiReviewUrl = $base_url . "public/task/ai-review.php";

    $archivedUrl = "#";
    $logoutUrl = $base_url . "logout.php";

    $mainLabel = "Tasks";
    $postLabel = "Post Task";
    $manageLabel = "MY Task";
    $trackerLabel = "Task Tracker";
}

$settingsUrl = $base_url . "public/task/settings.php";
$homeUrl = $base_url . "public/index.php";
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4 py-3">

    <div class="container">

        <a class="navbar-brand fw-bold d-flex align-items-center"
           href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>">

            <span class="brand-icon me-2 custom-logo">
                <span class="logo-t">T</span>
                <span class="logo-dot dot1"></span>
                <span class="logo-dot dot2"></span>
                <span class="logo-dot dot3"></span>
            </span>

            <span class="fw-bold fs-3">
                <span style="color:#080808;">Tam</span>
                <span style="color:#2563EB;">keen</span>
            </span>

        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#taskNav"
                aria-controls="taskNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="taskNav">

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link <?= in_array($activePage, ['tasks-dashboard', 'training-dashboard'], true) ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($mainUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <?= htmlspecialchars($mainLabel, ENT_QUOTES, 'UTF-8') ?>

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($activePage, ['post-task', 'post-training'], true) ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <?= htmlspecialchars($postLabel, ENT_QUOTES, 'UTF-8') ?>

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($activePage, ['submissions', 'my-trainings'], true) ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($manageUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <?= htmlspecialchars($manageLabel, ENT_QUOTES, 'UTF-8') ?>

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= in_array($activePage, ['tracker', 'training-tracker'], true) ? 'active' : '' ?>"
                       href="<?= htmlspecialchars($trackerUrl, ENT_QUOTES, 'UTF-8') ?>">

                        <?= htmlspecialchars($trackerLabel, ENT_QUOTES, 'UTF-8') ?>

                    </a>
                </li>

                <?php if ($dashboard_type !== "training"): ?>

                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'ai-review' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($aiReviewUrl, ENT_QUOTES, 'UTF-8') ?>">

                            AI Review

                        </a>
                    </li>

                <?php endif; ?>

                <?php if ($dashboard_type === "training"): ?>

                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'archived-trainings' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($archivedUrl, ENT_QUOTES, 'UTF-8') ?>">

                            Archived

                        </a>
                    </li>

                <?php endif; ?>

            </ul>

            <div class="dropdown">

                <button class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">

                    <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?>

                </button>

                <div class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">

                    <a class="dropdown-item text-danger"
                       href="<?= htmlspecialchars($logoutUrl, ENT_QUOTES, 'UTF-8') ?>">

                        Logout

                    </a>

                </div>

            </div>

        </div>

    </div>

</nav>