<?php

session_start();

require_once(__DIR__ . "/../../config.php");

require_once(__DIR__ . "/../../includes/user-auth.php");

require_once(__DIR__ . "/../functions/dashboard-functions.php");

$page = initUserDashboard($conn);
extract($page);

$base_url = "../../";
$body_class = "user-dashboard-page";
$extra_css = ["css/profile.css"];

include(__DIR__ . "/../../includes/header.php");
include(__DIR__ . "/../../includes/navbar-user.php");
?>
<main class="dashboard-wrap" id="top">

<section class="dashboard-hero-card">
    <div class="hero-cover"></div>

    <div class="hero-content-dashboard">
        <div class="avatar-box">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="../<?= e($user['profile_image']) ?>" alt="Profile Image">
            <?php else: ?>
                <i class="fa fa-user"></i>
            <?php endif; ?>
        </div>

        <div class="hero-user-info">
            <?php if ($edit === 'hero'): ?>
                <?php $heroData = array_merge($user ?: [], $old); ?>
                <form method="POST" enctype="multipart/form-data" class="dash-edit-form">
                    <input type="hidden" name="action" value="update_hero">

                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control mb-1" value="<?= e($heroData['full_name'] ?? '') ?>">
                    <?php if (isset($errors['full_name'])): ?><small class="text-danger d-block mb-2"><?= e($errors['full_name']) ?></small><?php endif; ?>

                    <label>Job Title</label>
                    <input type="text" name="job_title" class="form-control mb-1" value="<?= e($heroData['job_title'] ?? '') ?>">
                    <?php if (isset($errors['job_title'])): ?><small class="text-danger d-block mb-2"><?= e($errors['job_title']) ?></small><?php endif; ?>

                    <label>Mobile</label>
                    <input type="text" name="mobile" class="form-control mb-1" value="<?= e($heroData['mobile'] ?? '') ?>">
                    <?php if (isset($errors['mobile'])): ?><small class="text-danger d-block mb-2"><?= e($errors['mobile']) ?></small><?php endif; ?>

                    <label>Profile Image</label>
                    <input type="file" name="profile_image" class="form-control mb-1" accept="image/*">
                    <?php if (isset($errors['profile_image'])): ?><small class="text-danger d-block mb-2"><?= e($errors['profile_image']) ?></small><?php endif; ?>

                    <button class="btn btn-primary mt-2">Save</button>
                    <a href="dashboard.php" class="btn btn-light mt-2">Cancel</a>
                </form>
            <?php else: ?>
                <h1><?= dashValue($user['full_name'] ?? '') ?></h1>
                <p class="hero-title"><?= dashValue($user['job_title'] ?? 'Job Seeker') ?></p>
                <div class="hero-meta">
                    <span><i class="fa fa-map-marker-alt"></i><?= dashValue(($profile['residence_country'] ?? '') . ', ' . ($profile['location'] ?? ''), 'Location not set') ?></span>
                    <span><i class="fa fa-envelope"></i><?= dashValue($user['email'] ?? '') ?></span>
                    <span><i class="fa fa-mobile-alt"></i><?= dashValue($user['mobile'] ?? '') ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="hero-actions">
            <a href="dashboard.php?edit=hero" class="btn btn-light"><i class="fa fa-pen me-1"></i>Edit Profile</a>
        </div>
    </div>
</section>

<div class="dashboard-grid">

<section class="dashboard-main">

<div class="dash-section" id="personal">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-id-card"></i> Personal Information</h3>
            <p>Basic profile details</p>
        </div>
        <a href="dashboard.php?edit=profile#personal">Edit</a>
    </div>

    <?php if ($edit === 'profile'): ?>
        <?php $profileData = array_merge($profile ?: [], $old); ?>
        <form method="POST" class="dash-edit-form">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-grid">
                <div>
                    <label>Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= e($profileData['birth_date'] ?? '') ?>">
                    <?php if (isset($errors['birth_date'])): ?><small class="text-danger"><?= e($errors['birth_date']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select gender</option>
                        <option value="male" <?= selectedValue($old, $profile, 'gender', 'male') ?>>Male</option>
                        <option value="female" <?= selectedValue($old, $profile, 'gender', 'female') ?>>Female</option>
                    </select>
                    <?php if (isset($errors['gender'])): ?><small class="text-danger"><?= e($errors['gender']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Nationality</label>
                    <select name="nationality" class="form-select">
                        <option value="">Select nationality</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= e($country) ?>" <?= selectedValue($old, $profile, 'nationality', $country) ?>><?= e($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['nationality'])): ?><small class="text-danger"><?= e($errors['nationality']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Residence Country</label>
                    <select name="residence_country" id="residence_country" class="form-select">
                        <option value="">Select residence country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= e($country) ?>" <?= selectedValue($old, $profile, 'residence_country', $country) ?>><?= e($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['residence_country'])): ?><small class="text-danger"><?= e($errors['residence_country']) ?></small><?php endif; ?>
                </div>

                <div>
                    <div>
    <label>Location</label>

    <select id="location_select" class="form-select jordan-field">
        <option value="">Select location</option>
        <?php foreach ($jordanLocations as $loc): ?>
            <option value="<?= e($loc) ?>" <?= selectedValue($old, $profile, 'location', $loc) ?>>
                <?= e($loc) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input
        type="text"
        id="location_input"
        class="form-control non-jordan-field mt-0"
        value="<?= e($profileData['location'] ?? '') ?>"
        placeholder="Enter location"
    >

    <input type="hidden" name="location" id="location_final" value="<?= e($profileData['location'] ?? '') ?>">

    <?php if (isset($errors['location'])): ?>
        <small class="text-danger"><?= e($errors['location']) ?></small>
    <?php endif; ?>
</div>


                </div>

                <div>
                    <label>Job Status</label>
                    <select name="job_status" class="form-select">
                        <option value="">Select status</option>
                        <option value="unemployed" <?= selectedValue($old, $profile, 'job_status', 'unemployed') ?>>Unemployed</option>
                        <option value="working_open" <?= selectedValue($old, $profile, 'job_status', 'working_open') ?>>Working but open</option>
                        <option value="not_looking" <?= selectedValue($old, $profile, 'job_status', 'not_looking') ?>>Not looking for a job </option>
                    </select>
                    <?php if (isset($errors['job_status'])): ?><small class="text-danger"><?= e($errors['job_status']) ?></small><?php endif; ?>
                </div>

                

                <div>
                    <label>Years Experience</label>
                    <input type="number" name="years_experience" class="form-control" value="<?= e($profileData['years_experience'] ?? '') ?>">
                    <?php if (isset($errors['years_experience'])): ?><small class="text-danger"><?= e($errors['years_experience']) ?></small><?php endif; ?>
                </div>

                
               <div class="mt-3">
    <label>Professional Summary</label>

    <textarea
        name="about_me"
        class="form-control"
        rows="5"
        placeholder="Tell companies about yourself, your skills, interests, and career goals..."
    ><?= e(oldOrDb($old, $profile, 'about_me')) ?></textarea>
</div>
            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#personal" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php else: ?>
        <div class="info-grid">
            <div><span>Birth Date</span><strong><?= dashValue($profile['birth_date'] ?? '') ?></strong></div>
            <div><span>Gender</span><strong><?= dashValue($profile['gender'] ?? '') ?></strong></div>
            <div><span>Nationality</span><strong><?= dashValue($profile['nationality'] ?? '') ?></strong></div>
            <div><span>Residence</span><strong><?= dashValue($profile['residence_country'] ?? '') ?></strong></div>
            <div><span>Location</span><strong><?= dashValue($profile['location'] ?? '') ?></strong></div>
            <div><span>Job Status</span><strong><?= dashValue($profile['job_status'] ?? '') ?></strong></div>
            <div><span>Experience</span><strong><?= dashValue($profile['years_experience'] ?? '0') ?> Years</strong></div>
        </div>
    <?php endif; ?>
</div>

<div class="dash-section" id="education">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-graduation-cap"></i> Education</h3>
            <p>Your academic background</p>
        </div>
        <a href="dashboard.php?edit=education#education">Edit</a>
    </div>

    <?php if ($edit === 'education'): ?>
        <?php $eduData = array_merge($education ?: [], $old); ?>
        <form method="POST" class="dash-edit-form">
            <input type="hidden" name="action" value="update_education">

            <div class="form-grid">
                <div>
                    <label>Degree</label>
                    <select name="degree" class="form-select">
                        <option value="">Select degree</option>
                        <?php foreach ($degrees as $degree): ?>
                            <option value="<?= e($degree) ?>" <?= selectedValue($old, $education, 'degree', $degree) ?>><?= e($degree) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['degree'])): ?><small class="text-danger"><?= e($errors['degree']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>University</label>
                    <input type="text" name="institution" class="form-control" value="<?= e($eduData['institution'] ?? '') ?>">
                    <?php if (isset($errors['institution'])): ?><small class="text-danger"><?= e($errors['institution']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Field</label>
                    <input type="text" name="field" class="form-control" value="<?= e($eduData['field'] ?? '') ?>">
                    <?php if (isset($errors['field'])): ?><small class="text-danger"><?= e($errors['field']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Graduation Year</label>
                    <select name="graduation_year" class="form-select">
                        <option value="">Year</option>
                        <?php for ($year = 2032; $year >= 1980; $year--): ?>
                            <option value="<?= $year ?>" <?= selectedValue($old, $education, 'graduation_year', $year) ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (isset($errors['graduation_year'])): ?><small class="text-danger"><?= e($errors['graduation_year']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Grade</label>
                    <input type="text" name="grade" class="form-control" value="<?= e($eduData['grade'] ?? '') ?>">
                    <?php if (isset($errors['grade'])): ?><small class="text-danger"><?= e($errors['grade']) ?></small><?php endif; ?>
                </div>
            </div>

            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#education" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php elseif ($education): ?>
        <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div>
                <h5><?= dashValue($education['degree']) ?></h5>
                <p><?= dashValue($education['institution']) ?></p>
                <small><?= dashValue($education['field']) ?> • <?= dashValue($education['graduation_year']) ?> • Grade: <?= dashValue($education['grade']) ?></small>
            </div>
        </div>
    <?php else: ?>
        <a href="dashboard.php?edit=education#education" class="empty-add"><i class="fa fa-plus"></i>Add Education</a>
    <?php endif; ?>
</div>

<div class="dash-section" id="experience">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-briefcase"></i> Work Experience</h3>
            <p>Your career history</p>
        </div>
        <a href="dashboard.php?edit_exp=new#experience">Add New</a>
    </div>

    <?php
    $expForm = null;
    if ($editExp === 'new') {
        $expForm = $old ?: [];
    } elseif ($editExp) {
        foreach ($experiences as $ex) {
            if ((string)$ex['id'] === (string)$editExp) {
                $expForm = array_merge($ex, $old);
                break;
            }
        }
    }
    ?>

    <?php if (is_array($expForm)): ?>
        <form method="POST" class="dash-edit-form mb-4">
            <input type="hidden" name="action" value="save_experience">
            <input type="hidden" name="id" value="<?= e($expForm['id'] ?? '') ?>">

            <div class="form-grid">
                <div>
                    <label>Job Title</label>
                    <input type="text" name="job_title" class="form-control" value="<?= e($expForm['job_title'] ?? '') ?>">
                    <?php if (isset($errors['job_title'])): ?><small class="text-danger"><?= e($errors['job_title']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= e($expForm['company_name'] ?? '') ?>">
                    <?php if (isset($errors['company_name'])): ?><small class="text-danger"><?= e($errors['company_name']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Company Location</label>
                    <input type="text" name="company_location" class="form-control" value="<?= e($expForm['company_location'] ?? '') ?>">
                </div>

                <div>
                    <label>From Month</label>
                    <select name="from_month" class="form-select">
                        <option value="">Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= e($month) ?>" <?= ((($expForm['from_month'] ?? '') === $month) ? 'selected' : '') ?>><?= e($month) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>From Year</label>
                    <select name="from_year" class="form-select">
                        <option value="">Year</option>
                        <?php for ($year = 2026; $year >= 1980; $year--): ?>
                            <option value="<?= $year ?>" <?= ((string)($expForm['from_year'] ?? '') === (string)$year ? 'selected' : '') ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label>To Month</label>
                    <select name="to_month" class="form-select">
                        <option value="">Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= e($month) ?>" <?= ((($expForm['to_month'] ?? '') === $month) ? 'selected' : '') ?>><?= e($month) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>To Year</label>
                    <select name="to_year" class="form-select">
                        <option value="">Year</option>
                        <?php for ($year = 2026; $year >= 1980; $year--): ?>
                            <option value="<?= $year ?>" <?= ((string)($expForm['to_year'] ?? '') === (string)$year ? 'selected' : '') ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-check mt-4">
                    <input type="checkbox" name="is_present" value="1" class="form-check-input" <?= !empty($expForm['is_present']) ? 'checked' : '' ?>>
                    <label class="form-check-label">To Present</label>
                </div>
            </div>

            <?php if (isset($errors['from_date'])): ?><small class="text-danger d-block mt-2"><?= e($errors['from_date']) ?></small><?php endif; ?>
            <?php if (isset($errors['to_date'])): ?><small class="text-danger d-block mt-2"><?= e($errors['to_date']) ?></small><?php endif; ?>
            <?php if (isset($errors['date'])): ?><small class="text-danger d-block mt-2"><?= e($errors['date']) ?></small><?php endif; ?>

            <label class="mt-3">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= e($expForm['description'] ?? '') ?></textarea>

            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#experience" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php endif; ?>

    <?php foreach ($experiences as $exp): ?>
        <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content-full">
                <div class="timeline-row">
                    <div>
                        <h5><?= dashValue($exp['job_title']) ?></h5>
                        <p><?= dashValue($exp['company_name']) ?></p>
                        <small>
                            <?= dashValue($exp['company_location']) ?> •
                            <?= dashValue($exp['from_month']) ?> <?= dashValue($exp['from_year']) ?>
                            -
                            <?= !empty($exp['is_present']) ? 'Present' : dashValue(($exp['to_month'] ?? '') . ' ' . ($exp['to_year'] ?? '')) ?>
                        </small>
                    </div>

                    <div class="mini-actions">
                        <a href="dashboard.php?edit_exp=<?= e($exp['id']) ?>#experience">Edit</a>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_experience">
                            <input type="hidden" name="id" value="<?= e($exp['id']) ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
                <?php if (!empty($exp['description'])): ?>
                    <p class="timeline-desc"><?= e($exp['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!$experiences && !is_array($expForm)): ?>
        <a href="dashboard.php?edit_exp=new#experience" class="empty-add"><i class="fa fa-plus"></i>Add Experience</a>
    <?php endif; ?>
</div>

<?php
require_once(__DIR__ . "/../components/dashboard-sections.php");
renderCourseSection($courses, $editCourse, $old, $errors, $months);
renderLanguageSection($languages, $editLang, $old, $errors, $languageLevels, $languageRatings);
renderSkillSection($skills, $editSkill, $old, $errors, $skillLevels, $skillYears);
?>

</section>

<aside class="dashboard-side">

    <div class="side-card profile-score-card">
        <div class="score-circle" style="--score: <?= $completion ?>;">
            <span><?= $completion ?>%</span>
        </div>

        <h4>Profile Completion</h4>
        <p>Complete your profile to increase your chances.</p>

        <h6 class="completion-title">Profile Completion Steps</h6>

        <div class="completion-list">
            <span class="<?= $user ? 'done' : '' ?>"><i class="fa fa-check"></i>Account</span>
            <span class="<?= $profile ? 'done' : '' ?>"><i class="fa fa-check"></i>Personal Information</span>
            <span class="<?= !empty($user['email']) && !empty($user['mobile']) ? 'done' : '' ?>"><i class="fa fa-check"></i>Contact Information</span>
            <span class="<?= $education ? 'done' : '' ?>"><i class="fa fa-check"></i>Education</span>
            <span class="<?= !empty($experiences) ? 'done' : '' ?>"><i class="fa fa-check"></i>Experience</span>
            <span class="<?= !empty($courses) ? 'done' : '' ?>"><i class="fa fa-check"></i>Courses</span>
            <span class="<?= !empty($languages) ? 'done' : '' ?>"><i class="fa fa-check"></i>Languages</span>
            <span class="<?= !empty($skills) ? 'done' : '' ?>"><i class="fa fa-check"></i>Skills</span>
        </div>

        <a href="dashboard.php?edit=profile#personal" class="btn btn-primary w-100">
            Complete your profile
        </a>
    </div>

    <div class="side-card contact-card">
        <h4><i class="fa fa-address-book"></i> Contact</h4>

        <div class="contact-line">
            <i class="fa fa-envelope"></i>
            <?= dashValue($user['email'] ?? '-') ?>
        </div>

        <div class="contact-line">
            <i class="fa fa-mobile-alt"></i>
            <?= dashValue($user['mobile'] ?? '-') ?>
        </div>

        <div class="contact-line">
            <i class="fa fa-map-marker-alt"></i>
            <?= dashValue(($profile['residence_country'] ?? '-') . ', ' . ($profile['location'] ?? '-')) ?>
        </div>
    </div>

    <div class="side-card qr-card">

<?php if ($qrCompletion['is_complete']): ?>

    <?php
        $profileUrl =
            "http://localhost/tamkeentest/public/profile/public-profile.php?token="
            . urlencode($qrToken);

        $qrUrl =
            "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data="
            . urlencode($profileUrl);
    ?>

    <h4>
        <i class="fa fa-qrcode"></i>
        Your QR Code
    </h4>

    <div class="text-center mt-3">
        <img
            src="<?= $qrUrl ?>"
            alt="QR Code"
            style="
                width:220px;
                background:#fff;
                padding:12px;
                border-radius:18px;
            "
        >
    </div>

    <p class="mt-3">
        Companies can scan this QR code to instantly open your public profile.
    </p>

    <a
        href="<?= $qrUrl ?>"
        download="tamkeen-qr.png"
        class="btn btn-outline-primary w-100"
    >
        Download QR
    </a>

<?php else: ?>

    <h4>
        <i class="fa fa-lock"></i>
        QR Locked
    </h4>

    <div class="alert alert-warning mt-3">
        Complete your profile to unlock your QR code.
    </div>

    <ul class="small text-muted">
        <?php foreach ($qrCompletion['missing'] as $item): ?>
            <li><?= e($item) ?></li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>

</div>
    

   <div class="side-card cv-card">

    <h4>
        <i class="fa fa-file-upload"></i>
        Uploaded CV Files
    </h4>

    <?php if (!empty($cvs)): ?>

        <?php foreach ($cvs as $cv): ?>

            <div class="cv-file-row">
                <i class="fa fa-file-alt"></i>

                <a
                    href="../../public/uploads/cvs/<?= e($cv['cv_file']) ?>"
                    target="_blank"
                    style="text-decoration:none;"
                >
                    <?= dashValue($cv['cv_file']) ?>
                </a>
            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No CVs uploaded yet, Upload Now!</p>

    <?php endif; ?>



    <form
        method="POST"
        enctype="multipart/form-data"
        class="mt-3"
    >

        <input
            type="hidden"
            name="action"
            value="upload_cv"
        >

        <input
            type="file"
            name="cv_file"
            id="cv_file_input"
            accept=".pdf,.doc,.docx"
            hidden
            onchange="this.form.submit()"
        >

        <label
            for="cv_file_input"
            class="btn btn-outline-primary w-100"
            style="cursor:pointer;"
        >
            Add New
            <i class="fa fa-upload ms-2"></i>
        </label>

    </form>

</div>
   <div class="side-card">
    <h5 class="fw-bold mb-3">
        <i class="fa fa-lock text-primary me-2"></i>
        Change Password
    </h5>

    <form method="POST">

        <input type="hidden" name="action" value="change_password">

        <div class="mb-3">
            <input
                type="password"
                name="current_password"
                class="form-control"
                placeholder="Current Password"
            >

            <?php if (isset($errors['current_password'])): ?>
                <small class="text-danger">
                    <?= e($errors['current_password']) ?>
                </small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <input
                type="password"
                name="new_password"
                class="form-control"
                placeholder="New Password"
            >

            <?php if (isset($errors['new_password'])): ?>
                <small class="text-danger">
                    <?= e($errors['new_password']) ?>
                </small>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <input
                type="password"
                name="confirm_password"
                class="form-control"
                placeholder="Confirm Password"
            >

            <?php if (isset($errors['confirm_password'])): ?>
                <small class="text-danger">
                    <?= e($errors['confirm_password']) ?>
                </small>
            <?php endif; ?>
        </div>

        <button class="btn btn-primary w-100 rounded-pill">
            Update Password
        </button>

    </form>
</div>

</aside>

</div>
</main>




<?php include(__DIR__ . "/../../includes/footer.php"); ?>
<?php include(__DIR__ . "/../../includes/scripts.php"); ?>

<script>
(function () {
    const dashboard = document.querySelector('.dashboard-wrap');
    if (!dashboard) return;

    const ajaxActions = new Set([
        'update_hero',
        'update_profile',
        'update_education',
        'save_experience',
        'delete_experience',
        'save_course',
        'delete_course',
        'save_skill',
        'delete_skill',
        'save_language',
        'delete_language'
    ]);

    function getSectionFromUrl(url) {
        try {
            const parsed = new URL(url, window.location.href);
            if (parsed.hash) return parsed.hash.replace('#', '');
            if (parsed.searchParams.has('edit')) return parsed.searchParams.get('edit') === 'hero' ? 'top' : 'personal';
            if (parsed.searchParams.has('edit_exp')) return 'experience';
            if (parsed.searchParams.has('edit_course')) return 'courses';
            if (parsed.searchParams.has('edit_skill')) return 'skills';
            if (parsed.searchParams.has('edit_lang')) return 'languages';
        } catch (e) {}
        return '';
    }

    function setBusy(element, busy) {
        if (!element) return;
        element.dataset.ajaxBusy = busy ? '1' : '0';
        if (busy) {
            element.setAttribute('aria-busy', 'true');
        } else {
            element.removeAttribute('aria-busy');
        }
    }

    async function replaceDashboard(url, sectionId) {
        const targetUrl = new URL(url || 'dashboard.php', window.location.href);
        const response = await fetch(targetUrl.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const freshDashboard = doc.querySelector('.dashboard-wrap');
        const currentDashboard = document.querySelector('.dashboard-wrap');

        if (!freshDashboard || !currentDashboard) {
            window.location.href = targetUrl.toString();
            return;
        }

        currentDashboard.innerHTML = freshDashboard.innerHTML;

        const cleanUrl = targetUrl.pathname + targetUrl.search + targetUrl.hash;
        window.history.replaceState({}, '', cleanUrl);

        const section = sectionId || getSectionFromUrl(targetUrl.toString());
        if (section) {
            const el = document.getElementById(section);
            if (el) {
                el.scrollIntoView({ block: 'start', behavior: 'auto' });
            }
        }
    }

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.dashboard-wrap form');
        if (!form) return;

        const actionInput = form.querySelector('input[name="action"]');
        const action = actionInput ? actionInput.value : '';
        if (!ajaxActions.has(action)) return;

        event.preventDefault();

        if (form.dataset.ajaxBusy === '1') return;
        setBusy(form, true);

        const submitButton = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
        if (submitButton) submitButton.disabled = true;

        try {
            const formData = new FormData(form);
            formData.append('ajax', '1');

            const response = await fetch(form.getAttribute('action') || window.location.href, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                window.location.reload();
                return;
            }

            const result = await response.json();
            const nextUrl = result.redirect || window.location.href;
            const section = result.section || getSectionFromUrl(nextUrl);

            await replaceDashboard(nextUrl, section);
        } catch (error) {
            form.submit();
        } finally {
            setBusy(form, false);
            if (submitButton) submitButton.disabled = false;
        }
    });

    document.addEventListener('click', async function (event) {
        const link = event.target.closest('.dashboard-wrap a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || !href.includes('dashboard.php')) return;

        const isDashboardEditLink =
            href.includes('edit=') ||
            href.includes('edit_exp=') ||
            href.includes('edit_course=') ||
            href.includes('edit_skill=') ||
            href.includes('edit_lang=') ||
            href.includes('#personal') ||
            href.includes('#education') ||
            href.includes('#experience') ||
            href.includes('#courses') ||
            href.includes('#skills') ||
            href.includes('#languages') ||
            href.includes('#top');

        if (!isDashboardEditLink) return;

        event.preventDefault();

        try {
            const section = getSectionFromUrl(href);
            await replaceDashboard(href, section);
        } catch (error) {
            window.location.href = href;
        }
    });
})();
function syncLocationField() {
    const country = document.getElementById('residence_country');
    const select = document.getElementById('location_select');
    const input = document.getElementById('location_input');
    const final = document.getElementById('location_final');

    if (!country || !select || !input || !final) return;

    if (country.value === 'Jordan') {
        select.style.display = 'block';
        input.style.display = 'none';
        final.value = select.value;
    } else {
        select.style.display = 'none';
        input.style.display = 'block';
        final.value = input.value;
    }
}

document.addEventListener('change', function(e) {
    if (
        e.target.id === 'residence_country' ||
        e.target.id === 'location_select' ||
        e.target.id === 'location_input'
    ) {
        syncLocationField();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.id === 'location_input') {
        syncLocationField();
    }
});

syncLocationField();
</script>
