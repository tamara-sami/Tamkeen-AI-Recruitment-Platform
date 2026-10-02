<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Footer Start -->
<div class="container-fluid bg-dark text-light mt-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="row gx-5">

            <div class="col-lg-4 col-md-6 footer-about">
                <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 bg-primary p-4">
                    <a href="index.php" class="navbar-brand">
                        <h1 class="m-0 text-white"><i class="fa fa-user-tie me-2"></i>Tamkeen</h1>
                    </a>

                    <p class="mt-3 mb-4">
                        Build your future with verified skills, real challenges, smart CV tools, and better job matching.
                    </p>

                    <form>
                        <div class="input-group">
                            <input type="email" class="form-control border-white p-3" placeholder="Your Email">
                            <button class="btn btn-dark" type="submit">Sign Up</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-8 col-md-6">
                <div class="row gx-5">

                    <div class="col-lg-4 col-md-12 pt-5 mb-5">
                        <h3 class="text-light mb-3">Get In Touch</h3>
                        <p>Amman, Jordan</p>
                        <p>info@tamkeen.com</p>
                        <p>+962 7 0000 0000</p>
                    </div>

                    <div class="col-lg-4 col-md-12 pt-5 mb-5">
                        <h3 class="text-light mb-3">Platform</h3>

                        <a class="text-light d-block mb-2" href="../public/index.php#challenges">Challenges</a>
                        <a class="text-light d-block mb-2" href="../public/index.php#community">Community</a>
                        <a class="text-light" href="../public/index.php#jobs">Jobs</a>
                    </div>

                    
<div class="col-lg-4 col-md-12 pt-5 mb-5">
    <h3 class="text-light mb-3">Start Now</h3>

    <?php $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? ''; ?>

    <?php if (!empty($_SESSION['job_seeker_id'])): ?>
        <a class="text-light d-block mb-2" href="../public/user/dashboard.php">My Dashboard</a>

    <?php elseif (!empty($_SESSION['company_id'])): ?>

        <?php
        if ($role === 'hr') {
            $dashboard = '../public/hr/hr-dashboard.php';
        } elseif ($role === 'training_manager') {
            $dashboard = '../public/training/training-dashboard.php';
        } elseif ($role === 'task_manager') {
            $dashboard = '../public/task/task-dashboard.php';
        } else {
            $dashboard = '../public/company/employer-dashboard.php';
        }
        ?>

        <a class="text-light d-block mb-2" href="<?= $dashboard ?>">Dashboard</a>

    <?php else: ?>
        <a class="text-light d-block mb-2" href="../public/user/login.php">Job Seeker Login</a>
        <a class="text-light d-block mb-2" href="../public/user/register.php">Create Account</a>
        <a class="text-light d-block mb-2" href="../public/company/login.php">Hire Talent</a>
    <?php endif; ?>
</div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container-fluid text-white text-center py-3" style="background: #061429;">
    © Tamkeen. All Rights Reserved.
</div>
<!-- Footer End -->