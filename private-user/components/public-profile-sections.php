<?php

function renderPublicProfilePage($page)
{
    extract($page);
    $base_url = $base_url ?? '../../';

    unset($_SESSION['profile_public_success'], $_SESSION['profile_public_error']);

    if (empty($allowed)) {
        ?>
        <main class="public-profile-shell">
            <section class="profile-empty-card">
                <i class="fa fa-lock"></i>
                <h2><?= pp_e($reason ?? 'This profile is not available') ?></h2>
                <p>This profile is not available.</p>
<?php
$backHomeUrl = $base_url . 'public/user/homepage.php';

if (!empty($_SESSION['company_id'])) {
    $backHomeUrl = $base_url . 'public/hr/hr-dashboard.php';
}
?>

<a href="<?= $backHomeUrl ?>" class="pp-btn pp-btn-primary">
    Back Home
</a>            </section>
        </main>
        <?php
        return;
    }

    /*
        Profile images:
        Your DB usually stores: uploads/profile_images/xxx.png
        But the real folder is: public/uploads/profile_images/
        So the browser path must become: ../../public/uploads/profile_images/xxx.png
    */
    $profileImage = $base_url . 'img/default-user.png';

    if (!empty($user['profile_image'])) {
        $imagePath = ltrim($user['profile_image'], '/');

        if (str_starts_with($imagePath, 'public/')) {
            $profileImage = $base_url . $imagePath;
        } else {
            $profileImage = $base_url . 'public/' . $imagePath;
        }
    }

    $locationText = trim(($user['location'] ?? '') . ', ' . ($user['residence_country'] ?? ''));
    $locationText = trim($locationText, ', ');

    $jobStatusLabels = [
        'unemployed' => 'Open to work',
        'working_open' => 'Working, open to offers',
        'not_looking' => 'Not looking'
    ];

    $initial = strtoupper(substr($user['full_name'] ?? 'T', 0, 1));

    $completedTasksCount = (int)($stats['completed_tasks'] ?? 0);
    $trainingCount = (int)($stats['training_count'] ?? 0);
    $exchangeCount = (int)($stats['exchange_count'] ?? 0);
    $points = (int)($stats['task_points'] ?? 0);
    ?>

<main class="public-profile-shell">

    

    <section class="pp-hero-card">

        <div class="pp-cover">
            <div class="pp-cover-pattern"></div>
        </div>

        <div class="pp-hero-body">

            <div class="pp-avatar-block">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= pp_e($profileImage) ?>" class="pp-avatar" alt="Profile image">
                <?php else: ?>
                    <div class="pp-avatar pp-avatar-fallback"><?= pp_e($initial) ?></div>
                <?php endif; ?>
            </div>

            <div class="pp-hero-info">

                <div class="pp-hero-left">
                    <h1><?= pp_e($user['full_name'] ?? 'Tamkeen Candidate') ?></h1>

                    <p class="pp-headline">
                        <?= pp_e($user['job_title'] ?? 'Tamkeen Candidate') ?>
                    </p>

                    <div class="pp-meta-line">
                        <span>
                            <i class="fa fa-map-marker-alt"></i>
                            <?= pp_e($locationText ?: 'Jordan') ?>
                        </span>

                        <span class="pp-dot"></span>

                        <span>
                            <?= pp_e($jobStatusLabels[$user['job_status'] ?? ''] ?? 'Career profile') ?>
                        </span>
                    </div>

                    <div class="pp-hero-badges">

                        <span>
                            <i class="fa fa-star"></i>
                            <?= $points ?> verified points
                        </span>

                        <span>
                            <i class="fa fa-briefcase"></i>
                            <?= pp_e($jobStatusLabels[$user['job_status'] ?? ''] ?? 'Career profile') ?>
                        </span>
                    </div>
                </div>

<div class="pp-hero-actions" style="position:relative; z-index:999;">                    <a href="mailto:<?= pp_e($user['email'] ?? '') ?>" class="pp-btn pp-btn-primary">
                        <i class="fa fa-envelope"></i>
                        Contact
                    </a>

                  <?php if ($isOwner): ?>
    <a href="/tamkeentest/public/user/dashboard.php" class="pp-btn pp-btn-outline">
        <i class="fa fa-pen"></i>
        Edit Profile
    </a>
<?php endif; ?>
                </div>

            </div>

            <div class="pp-stats-row">
                <div>
                    <strong><?= $completedTasksCount ?></strong>
                    <span>Completed tasks</span>
                </div>

                <div>
                    <strong><?= $trainingCount ?></strong>
                    <span>Trainings</span>
                </div>

                <div>
                    <strong><?= $exchangeCount ?></strong>
                    <span>Skill exchanges</span>
                </div>

                <div>
                    <strong><?= $points ?></strong>
                    <span>Verified points</span>
                </div>
            </div>

        </div>
    </section>

    <section class="pp-profile-grid">

        <div class="pp-main-column">

            <article class="pp-card pp-about-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-user-circle"></i></span>
                    <div>
                        <small>INTRODUCTION</small>
                        <h2>About</h2>
                    </div>
                </div>

              <p>
    <?php
        // About should show the Professional Summary saved as job_seekers.about_me.
        $professionalSummary = trim($user['about_me'] ?? '');
    ?>

    <?php if ($professionalSummary !== ''): ?>

        <?= nl2br(pp_e($professionalSummary)) ?>

    <?php else: ?>

        <?= pp_e($user['full_name'] ?? 'This candidate') ?>
        is building a verified professional profile on Tamkeen.

    <?php endif; ?>
</p>
            </article>

            <article class="pp-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-check-circle"></i></span>
                    <div>
                        <small>CORE ABILITIES</small>
                        <h2>Skills</h2>
                    </div>
                </div>

                <?php if (empty($skills)): ?>
                    <p class="pp-muted">No skills added yet.</p>
                <?php else: ?>
                    <div class="pp-skills-grid">
                        <?php foreach ($skills as $skill): ?>
                            <div class="pp-skill-pill">
                                <strong><?= pp_e($skill['skill_name']) ?></strong>
                                <span><?= pp_e($skill['skill_level']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="pp-card pp-journey-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-briefcase"></i></span>
                    <div>
                        <small>PROFESSIONAL JOURNEY</small>
                        <h2>Experience</h2>
                    </div>
                </div>

                <?php if (empty($experience)): ?>
                    <p class="pp-muted">No experience added yet.</p>
                <?php else: ?>
                    <div class="pp-timeline">
                        <?php foreach ($experience as $exp): ?>
                            <div class="pp-timeline-item">
                                <div class="pp-timeline-dot"></div>

                                <div class="pp-timeline-content">
                                    <h3><?= pp_e($exp['job_title']) ?></h3>

                                    <p class="pp-timeline-meta">
                                        <?= pp_e($exp['company_name']) ?>
                                        <?php if (!empty($exp['company_location'])): ?>
                                            · <?= pp_e($exp['company_location']) ?>
                                        <?php endif; ?>
                                    </p>

                                    <?php if (!empty($exp['description'])): ?>
                                        <p class="pp-timeline-desc"><?= pp_e($exp['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="pp-card pp-journey-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-graduation-cap"></i></span>
                    <div>
                        <small>ACADEMIC BACKGROUND</small>
                        <h2>Education</h2>
                    </div>
                </div>

                <?php if (empty($education)): ?>
                    <p class="pp-muted">No education added yet.</p>
                <?php else: ?>
                    <div class="pp-timeline">
                        <?php foreach ($education as $edu): ?>
                            <div class="pp-timeline-item">
                                <div class="pp-timeline-dot"></div>

                                <div class="pp-timeline-content">
                                    <h3><?= pp_e($edu['degree'] ?? 'Education') ?></h3>

                                    <p class="pp-timeline-meta">
                                        <?= pp_e($edu['institution'] ?? '') ?>
                                        <?php if (!empty($edu['field'])): ?>
                                            · <?= pp_e($edu['field']) ?>
                                        <?php endif; ?>
                                    </p>

                                    <?php if (!empty($edu['graduation_year'])): ?>
                                        <p class="pp-timeline-desc"><?= pp_e($edu['graduation_year']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="pp-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-tasks"></i></span>
                    <div>
                        <small>VERIFIED WORK</small>
                        <h2>Completed Tasks</h2>
                    </div>
                </div>

                <?php if (empty($completedTasks)): ?>
                    <p class="pp-muted">No submitted tasks yet.</p>
                <?php else: ?>
                    <div class="pp-achievement-list">
                        <?php foreach ($completedTasks as $task): ?>
                            <div class="pp-achievement">
                                <div>
                                    <h3><?= pp_e($task['title']) ?></h3>
                                    <p>
                                        <?= pp_e($task['company_name'] ?? 'Tamkeen') ?>
                                        · <?= pp_e($task['category']) ?>
                                        · <?= pp_e($task['difficulty']) ?>
                                    </p>
                                </div>

                                <span>+<?= (int)$task['points'] ?> pts</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="pp-card">
                <div class="pp-card-title">
                    <span class="pp-icon-soft"><i class="fa fa-exchange-alt"></i></span>
                    <div>
                        <small>COMMUNITY LEARNING</small>
                        <h2>Skill Exchange Activity</h2>
                    </div>
                </div>

                <?php if (empty($exchanges)): ?>
                    <p class="pp-muted">No exchange posts yet.</p>
                <?php else: ?>
                    <div class="pp-achievement-list">
                        <?php foreach ($exchanges as $exchange): ?>
                            <div class="pp-achievement">
                                <div>
                                    <h3><?= pp_e($exchange['title'] ?: 'Skill Exchange') ?></h3>
                                    <p><?= pp_e($exchange['type']) ?> · <?= pp_e($exchange['category']) ?></p>
                                </div>

                                <span><?= pp_e($exchange['type']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

        </div>

        <aside class="pp-side-column">

            <article class="pp-side-card pp-contact-card">
                <div class="pp-side-title">
                    <h2>Contact</h2>
                    <span>Available</span>
                </div>

                <div class="pp-contact-item">
                    <i class="fa fa-envelope"></i>
                    <div>
                        <small>Email</small>
                        <strong><?= pp_e($user['email'] ?? 'Not added') ?></strong>
                    </div>
                </div>

                <div class="pp-contact-item">
                    <i class="fa fa-phone"></i>
                    <div>
                        <small>Phone</small>
                        <strong><?= pp_e($user['mobile'] ?? 'Not added') ?></strong>
                    </div>
                </div>

                <div class="pp-contact-item">
                    <i class="fa fa-map-marker-alt"></i>
                    <div>
                        <small>Location</small>
                        <strong><?= pp_e($locationText ?: 'Jordan') ?></strong>
                    </div>
                </div>
            </article>

            <article class="pp-side-card">
                <div class="pp-side-title">
                    <h2>Career Preferences</h2>
                </div>

                <div class="pp-info-row">
                    <span>Status</span>
                    <strong><?= pp_e($jobStatusLabels[$user['job_status'] ?? ''] ?? 'Not set') ?></strong>
                </div>

                <div class="pp-info-row">
                    <span>Experience</span>
                    <strong><?= pp_e($user['years_experience'] ?? '0') ?> years</strong>
                </div>

                
            </article>

            <article class="pp-side-card">
                <div class="pp-side-title">
                    <h2>Languages</h2>
                </div>

                <?php if (empty($languages)): ?>
                    <p class="pp-muted">No languages added yet.</p>
                <?php else: ?>
                    <?php foreach ($languages as $lang): ?>
                        <div class="pp-info-row">
                            <span><?= pp_e($lang['language_name']) ?></span>
                            <strong><?= pp_e($lang['proficiency_level']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </article>

            <article class="pp-side-card">
                <div class="pp-side-title">
                    <h2>Courses</h2>
                </div>

                <?php if (empty($courses)): ?>
                    <p class="pp-muted">No courses added yet.</p>
                <?php else: ?>
                    <div class="pp-mini-list">
                        <?php foreach ($courses as $course): ?>
                            <div>
                                <strong><?= pp_e($course['course_title']) ?></strong>
                                <span><?= pp_e($course['course_provider']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="pp-side-card">
                <div class="pp-side-title">
                    <h2>Training</h2>
                </div>

                <?php if (empty($trainings)): ?>
                    <p class="pp-muted">No training activity yet.</p>
                <?php else: ?>
                    <div class="pp-mini-list">
                        <?php foreach ($trainings as $training): ?>
                            <div>
                                <strong><?= pp_e($training['title']) ?></strong>
                                <span><?= pp_e($training['company_name']) ?> · <?= pp_e($training['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

        </aside>

    </section>

</main>

<?php
}
