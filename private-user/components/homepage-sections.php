<?php

function renderUserHomepage($page)
{
    extract($page);
    $base_url = $base_url ?? '../../';

    $userName = $user['full_name'] ?? 'Tamkeen User';
    $firstName = explode(' ', trim($userName))[0] ?: 'there';
    $profileImg = homeProfileImage($base_url, $user['profile_image'] ?? null);
    $location = trim(($user['location'] ?? '') . ', ' . ($user['residence_country'] ?? ''));
    $location = trim($location, ', ') ?: 'Jordan';

    $visibility = $user['profile_visibility'] ?? 'public';

    $visibilityLabel = [
        'public' => 'Public profile',
        'registered' => 'Registered only',
        'hidden' => 'Applied companies only'
    ][$visibility] ?? 'Public profile';
?>

<main class="home-hub-shell">

    <section class="hub-hero">
        <div class="hub-hero-left">
            <span class="hub-kicker">
                <i class="fa fa-sparkles"></i>
                Tamkeen Hub
            </span>

            <h1>Welcome back, <?= home_e($firstName) ?> 👋</h1>

            <p>
                Track your tasks, training opportunities, skill exchanges, and profile progress from one smart dashboard.
            </p>

            <div class="hub-quick-actions">
                <a href="tasks.php" class="hub-btn primary">
                    <i class="fa fa-tasks"></i>
                    Start a Task
                </a>

                <a href="trainings.php" class="hub-btn">
                    <i class="fa fa-graduation-cap"></i>
                    Browse Training
                </a>

                <a href="courseshome.php" class="hub-btn">
                    <i class="fa fa-exchange-alt"></i>
                    Skill Exchange
                </a>
            </div>
        </div>

       <?php
$profileImg = homeProfileImage(
    $base_url,
    $user['profile_image'] ?? ''
);
?>

<div class="hub-profile-card">

<?php if (!empty($profileImg)): ?>
    <img src="<?= home_e($profileImg) ?>" alt="Profile">
<?php else: ?>
    <div class="hub-profile-fallback">
        <?= home_e(strtoupper(substr($userName, 0, 1))) ?>
    </div>
<?php endif; ?>

    <h3><?= home_e($userName) ?></h3>

    <p><?= home_e($user['job_title'] ?? 'Learner') ?></p>

    <small>
        <i class="fa fa-map-marker-alt"></i>
        <?= home_e($location) ?>
    </small>



            <div class="hub-progress">
                <div class="d-flex justify-content-between mb-1">
                    <span>Profile strength</span>
                    <strong><?= (int)$profileCompletion ?>%</strong>
                </div>

                <div class="progress">
                    <div class="progress-bar" style="width:<?= (int)$profileCompletion ?>%;"></div>
                </div>
            </div>

            <a href="public-profile.php" class="hub-profile-link">
                Preview public profile
            </a>
        </div>
    </section>

    <section class="hub-stats-grid">
        <div class="hub-stat-card">
            <i class="fa fa-star"></i>
            <strong><?= (int)$stats['points'] ?></strong>
            <span>Verified points</span>
        </div>

        <div class="hub-stat-card">
            <i class="fa fa-check-circle"></i>
            <strong><?= (int)$stats['completed_tasks'] ?></strong>
            <span>Completed tasks</span>
        </div>

        <div class="hub-stat-card">
            <i class="fa fa-file-alt"></i>
            <strong><?= (int)$stats['training_applications'] ?></strong>
            <span>Applications</span>
        </div>

        <div class="hub-stat-card">
            <i class="fa fa-shield-alt"></i>
            <strong><?= home_e($visibilityLabel) ?></strong>
            <span>Visibility</span>
        </div>
    </section>

    <div class="hub-layout">

        <section class="hub-main-feed">

            <article class="hub-panel">
                <div class="hub-panel-header">
                    <div>
                        <span>SMART MATCHES</span>
                        <h2>Recommended for you</h2>
                    </div>

                    <a href="tasks.php">View all</a>
                </div>

                <div class="hub-recommend-grid">
                    <?php if (empty($recommendedTasks) && empty($recommendedTrainings)): ?>
                        <div class="hub-empty">
                            <i class="fa fa-search"></i>
                            <h4>No recommendations yet</h4>
                            <p>Complete your profile to get smarter recommendations.</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($recommendedTasks as $task): ?>
                        <div class="hub-recommend-card">
                            <span class="hub-badge task">Task</span>
                            <h3><?= home_e($task['title']) ?></h3>
                            <p><?= home_e(mb_strimwidth($task['description'] ?? '', 0, 120, '...')) ?></p>

                            <div class="hub-meta">
                                <span><?= home_e($task['category']) ?></span>
                                <span><?= home_e($task['difficulty']) ?></span>
                                <span>+<?= (int)$task['points'] ?> pts</span>
                            </div>

                            <a href="task-workspace.php?id=<?= (int)$task['id'] ?>" class="hub-card-action">
                                Open Task
                            </a>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach (array_slice($recommendedTrainings, 0, 2) as $training): ?>
                        <div class="hub-recommend-card">
                            <span class="hub-badge training">Training</span>
                            <h3><?= home_e($training['title']) ?></h3>
                            <p><?= home_e(mb_strimwidth($training['description'] ?? '', 0, 120, '...')) ?></p>

                            <div class="hub-meta">
                                <span><?= home_e($training['field']) ?></span>
                                <span><?= home_e($training['training_type']) ?></span>
                            </div>

                            <a href="trainings.php#training-<?= (int)$training['id'] ?>" class="hub-card-action">
                                View Training
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="hub-panel">
                <div class="hub-panel-header">
                    <div>
                        <span>COMMUNITY</span>
                        <h2>Latest skill exchanges</h2>
                    </div>

                    <a href="courseshome.php">Open exchange</a>
                </div>

                <?php if (empty($latestExchanges)): ?>
                    <div class="hub-empty">
                        <i class="fa fa-exchange-alt"></i>
                        <h4>No exchange posts yet</h4>
                        <p>Start by offering a skill or requesting help.</p>
                    </div>
                <?php else: ?>
                    <div class="hub-exchange-list">
                        <?php foreach ($latestExchanges as $post): ?>
                            <?php $avatar = homeProfileImage($base_url, $post['profile_image'] ?? null); ?>

                            <div class="hub-exchange-item">
                                <img src="<?= home_e($avatar) ?>" alt="User">

                                <div>
                                    <div class="hub-exchange-top">
                                        <strong><?= home_e($post['full_name']) ?></strong>
                                        <span><?= home_e($post['type']) ?></span>
                                    </div>

                                    <h4><?= home_e($post['title'] ?: 'Skill Exchange') ?></h4>
                                    <p><?= home_e(mb_strimwidth($post['content'] ?? '', 0, 150, '...')) ?></p>

                                    <a href="courseshome.php#post-<?= (int)$post['id'] ?>">
                                        View conversation
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

        </section>

        <aside class="hub-sidebar">

            <article class="hub-side-panel">
                <h3>Recent activity</h3>

                <div class="hub-activity-list">
                    <?php foreach ($activity as $item): ?>
                        <a href="<?= home_e($item['link']) ?>" class="hub-activity-item">
                            <i class="fa <?= home_e($item['icon']) ?>"></i>

                            <div>
                                <strong><?= home_e($item['title']) ?></strong>
                                <span><?= home_e($item['text']) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="hub-side-panel">
                <h3>My applications</h3>

                <?php if (empty($applications)): ?>
                    <p class="hub-muted">You have not applied to trainings yet.</p>
                    <a href="trainings.php" class="hub-mini-link">Browse trainings</a>
                <?php else: ?>
                    <div class="hub-mini-list">
                        <?php foreach ($applications as $app): ?>
                            <a href="my-training-applications.php">
                                <strong><?= home_e($app['title']) ?></strong>
                                <span><?= home_e($app['company_name']) ?> · <?= home_e($app['status']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="hub-side-panel">
                <h3>Trending skills</h3>

                <div class="hub-skill-cloud">
                    <?php foreach ($trendingSkills as $skill): ?>
                        <span>
                            <?= home_e($skill['skill_name']) ?>
                            <small><?= (int)$skill['total'] ?></small>
                        </span>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="hub-side-panel">
                <h3>Companies active now</h3>

                <?php if (empty($companiesHiring)): ?>
                    <p class="hub-muted">No active companies yet.</p>
                <?php else: ?>
                    <div class="hub-company-list">
                        <?php foreach ($companiesHiring as $company): ?>
                            <?php $logo = homeCompanyLogo($base_url, $company['company_logo'] ?? null); ?>

                            <div class="hub-company-item">
                                <?php if ($logo): ?>
                                    <img src="<?= home_e($logo) ?>" alt="">
                                <?php else: ?>
                                    <div class="hub-company-fallback">
                                        <?= home_e(strtoupper(substr($company['company_name'], 0, 1))) ?>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <strong><?= home_e($company['company_name']) ?></strong>
                                    <span><?= (int)$company['openings'] ?> opportunities</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

        </aside>

    </div>

</main>

<?php
}
