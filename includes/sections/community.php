<?php
include_once(__DIR__ . '/../functions/home_sections.php');
$cards = getCommunityCardsFromTasks($conn, 3);
$stats = getCommunityStatsFromTasks($conn);
?>

<div id="community" class="community-section container-fluid py-5 wow fadeInUp">
    <div class="container py-5">
        <div class="section-title text-center position-relative pb-3 mb-5 mx-auto" style="max-width:700px;">
            <h5 class="fw-bold text-primary text-uppercase">Skill Exchange Community</h5>
            <h1 class="mb-0">Learn Together. Grow Faster.</h1>
            <p class="mt-3 text-muted">Explore skills from the same training tasks and challenges already inside Tamkeen.</p>
        </div>

        <div class="row g-5">
            <div class="col-lg-4">
                <div class="row g-4">
                    <?php foreach($cards as $index => $card): ?>
                        <div class="col-12 wow zoomIn" data-wow-delay="<?= 0.2 * ($index + 1) ?>s">
                            <div class="d-flex align-items-center justify-content-between p-4 bg-white shadow-sm rounded">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-light shadow-sm">
                                        <i class="fa fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?= safe($card['category']) ?></h6>
                                        <small>Offers: <?= safe(firstSkill($card['required_skills'])) ?> → Wants: <?= safe(secondSkill($card['required_skills'])) ?></small>
                                    </div>
                                </div>
                                <span class="badge bg-primary px-3 py-2"><?= safe($card['difficulty']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4 wow zoomIn" data-wow-delay="0.9s" style="min-height:350px;">
                <div class="position-relative h-100">
                    <img class="position-absolute w-100 h-100 rounded shadow-sm" src="../img/jj.png" style="object-fit:cover;">
                </div>
            </div>

            <div class="col-lg-4">
                <div class="row g-4">
                    <div class="col-6 wow zoomIn" data-wow-delay="0.3s">
                        <div class="bg-white shadow-sm rounded text-center p-4 d-flex flex-column justify-content-center" style="height:140px;">
                            <h2 class="text-primary mb-0"><span data-toggle="counter-up"><?= safe($stats['active_courses']) ?></span>+</h2>
                            <small>Active Trainings</small>
                        </div>
                    </div>

                    <div class="col-6 wow zoomIn" data-wow-delay="0.5s">
                        <div class="bg-white shadow-sm rounded text-center p-4 d-flex flex-column justify-content-center" style="height:140px;">
                            <h2 class="text-primary mb-0"><span data-toggle="counter-up"><?= safe($stats['categories_count']) ?></span></h2>
                            <small>Skill Categories</small>
                        </div>
                    </div>

                    <div class="col-6 wow zoomIn" data-wow-delay="0.7s">
                        <div class="bg-white shadow-sm rounded text-center p-4 d-flex flex-column justify-content-center" style="height:140px;">
                            <h2 class="text-primary mb-0"><span data-toggle="counter-up"><?= safe($stats['total_points']) ?></span>+</h2>
                            <small>Total Points</small>
                        </div>
                    </div>

                    <div class="col-6 wow zoomIn" data-wow-delay="0.9s">
                        <div class="bg-white shadow-sm rounded text-center p-4 d-flex flex-column justify-content-center" style="height:140px;">
                            <h2 class="text-primary mb-0"><span data-toggle="counter-up"><?= safe($stats['avg_points']) ?></span></h2>
                            <small>Avg Points</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
