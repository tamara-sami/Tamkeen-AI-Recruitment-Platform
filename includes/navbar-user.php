<?php
$nav_base = $base_url ?? "../";
?>

<nav class="navbar navbar-expand-lg navbar-light rounded-4 mx-4 px-4 py-2 shadow-sm">

<a class="navbar-brand fw-bold d-flex align-items-center"
   href="/tamkeentest/public/index.php">        <span class="brand-icon me-2 custom-logo">
            <span class="logo-t">T</span>
            <span class="logo-dot dot1"></span>
            <span class="logo-dot dot2"></span>
            <span class="logo-dot dot3"></span>
        </span>

        <span class="fw-bold fs-3">
            <span style="color:#080808;">Tam</span><span style="color:#2563EB;">keen</span>
        </span>
    </a>

    <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#nav">

        <span class="navbar-toggler-icon"></span>

    </button>

    <div class="collapse navbar-collapse" id="nav">

        <ul class="navbar-nav mx-auto">
            <li class="nav-item">
<a class="nav-link" href="<?= $nav_base ?>public/user/homepage.php">Home</a>
            </li>

            <li class="nav-item">
<a class="nav-link" href="<?= $nav_base ?>public/user/tasks.php">Tasks</a>
            </li>

            <li class="nav-item">
<a class="nav-link" href="<?= $nav_base ?>public/user/courseshome.php">Exchange Courses</a>
            </li>

            <li class="nav-item">
<a class="nav-link" href="<?= $nav_base ?>public/user/trainings.php">Training</a>            </li>
        </ul>

        <div class="dropdown">

            <button class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown">

                <?= htmlspecialchars($_SESSION['job_seeker_name'] ?? 'User'); ?>

            </button>
<div class="dropdown-menu dropdown-menu-end rounded-3 shadow border-0">

    <a class="dropdown-item"
       href="<?= $nav_base ?>public/user/public-profile.php">

        Profile

    </a>

    <a class="dropdown-item"
       href="<?= $nav_base ?>public/user/dashboard.php">

        Settings

    </a>

    <a class="dropdown-item text-danger"
       href="<?= $nav_base ?>public/user/logout-user.php">

        Logout

    </a>

</div>

        </div>

    </div>

</nav>