<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "../";

include("../includes/header.php");
include("../config.php");
include_once("../includes/functions/home_sections.php");
include("../includes/spinner.php");
include("../includes/navbar-guest.php");

$hireTalentLink = homeStartHiringLink();
$joinTalentLink = homeFindJobLink();

?>

<!-- HERO -->
<div class="hero-center">

    <div class="floating floating-1"></div>
    <div class="floating floating-2"></div>
    <div class="floating floating-3"></div>

    <div class="wave">
        <svg viewBox="0 0 1440 200">
            <path fill="#ffffff"
                d="M0,120L120,100C240,80,480,40,720,40C960,40,1200,80,1320,100L1440,120V0H0Z">
            </path>
        </svg>
    </div>

    <div class="hero-content text-center wow fadeInUp">

        <h5 class="subtitle">REAL-WORLD CHALLENGES</h5>

        <h1>
            Prove Your <span class="grad glow">Skills</span><br>
            Not Just <span class="orange">List</span><br>
            <span class="grad glow section-title d-inline-block position-relative pb-2">Them</span>
        </h1>

        <p>
            Transform your traditional CV into a verified skill profile.
        </p>

        <div class="smart-search-wrap">
            <form class="search-box" id="homeSmartSearchForm" autocomplete="off">
                <input
                    type="text"
                    id="homeSmartSearchInput"
                    name="q"
                    placeholder="Search jobs, trainings, tasks, skills or companies..."
                    autocomplete="off"
                />
                <button type="submit">Search</button>
            </form>
            <div class="smart-search-dropdown d-none" id="homeSmartSearchResults" aria-live="polite"></div>
        </div>

       
    </div>

    <div class="hero-links row g-4 mt-5 justify-content-center">

        <div class="col-md-5 wow fadeInUp" data-wow-delay="0.2s">
            <a href="<?= safe($hireTalentLink) ?>" class="hero-smart-link d-flex gap-3 p-4 rounded-4 text-decoration-none">
                <i class="fa fa-building text-primary mt-1"></i>
                <div>
                    <h6 class="mb-1">Hire Verified Talent</h6>
                    <p class="mb-2">Find candidates proven by real challenges, not just CVs.</p>
                    <span class="hero-mini-link">
                        Start Hiring <i class="fa fa-arrow-right ms-2"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-md-5 wow fadeInUp" data-wow-delay="0.5s">
            <a href="<?= safe($joinTalentLink) ?>" class="hero-smart-link d-flex gap-3 p-4 rounded-4 text-decoration-none">
                <i class="fa fa-user-check text-primary mt-1"></i>
                <div>
                    <h6 class="mb-1">Build Your Skill Profile</h6>
                    <p class="mb-2">Prove your skills and get matched with real opportunities.</p>
                    <span class="hero-mini-link">
                        Find a Job <i class="fa fa-arrow-right ms-2"></i>
                    </span>
                </div>
            </a>
        </div>

    </div>
</div>

<?php include("../includes/sections/challenges.php"); ?>

<?php include("../includes/sections/community.php"); ?>



<?php include("../includes/sections/jobs.php"); ?>

<?php include("../includes/footer.php"); ?>

<a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top">
    <i class="bi bi-arrow-up"></i>
</a>




<?php include("../includes/scripts.php"); ?>