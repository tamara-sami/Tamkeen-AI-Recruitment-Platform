<?php
session_start();

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: login.php");
    exit;
}

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

    <?php
    $current_sidebar = 3;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="complete-page">

        <section class="complete-card">

            <div class="complete-badge">
                <i class="fa fa-check"></i>
            </div>

            <h1>Congratulations!</h1>

            <p>
                Your profile has been completed successfully.
                You can now search and apply for jobs using your Tamkeen profile.
            </p>

            <div class="complete-actions">

                <a href="dashboard.php"
                   class="btn btn-primary">

                    View My Profile
                    <i class="fa fa-user ms-2"></i>

                </a>

                <a href="../../index.php"
                   class="btn btn-outline-primary">

                    Back Home

                </a>

            </div>

        </section>

        <section class="complete-illustration">

            <img src="../../img/teamwork.png"
                 class="success-img wow fadeInRight"
                 data-wow-delay="0.3s"
                 alt="Success illustration">

        </section>

    </main>

</div>

<?php include("../../includes/scripts.php"); ?>