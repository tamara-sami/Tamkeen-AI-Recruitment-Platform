<?php
function renderCourseSection($courses, $editCourse, $old, $errors, $months) {
    $formData = null;
    if ($editCourse === 'new') {
        $formData = $old ?: [];
    } elseif ($editCourse) {
        foreach ($courses as $course) {
            if ((string)$course['id'] === (string)$editCourse) {
                $formData = array_merge($course, $old);
                break;
            }
        }
    }
?>
<div class="dash-section" id="courses">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-book-open"></i> Courses</h3>
            <p>Training and certificates</p>
        </div>
        <a href="dashboard.php?edit_course=new#courses">Add New</a>
    </div>

    <?php if (is_array($formData)): ?>
        <form method="POST" class="dash-edit-form mb-4">
            <input type="hidden" name="action" value="save_course">
            <input type="hidden" name="id" value="<?= e($formData['id'] ?? '') ?>">

            <div class="form-grid">
                <div>
                    <label>Course Title</label>
                    <input type="text" name="course_title" class="form-control" value="<?= e($formData['course_title'] ?? '') ?>">
                    <?php if (isset($errors['course_title'])): ?><small class="text-danger"><?= e($errors['course_title']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Course Provider</label>
                    <input type="text" name="course_provider" class="form-control" value="<?= e($formData['course_provider'] ?? '') ?>">
                    <?php if (isset($errors['course_provider'])): ?><small class="text-danger"><?= e($errors['course_provider']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Course Month</label>
                    <select name="course_month" class="form-select">
                        <option value="">Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= e($month) ?>" <?= (($formData['course_month'] ?? '') === $month ? 'selected' : '') ?>><?= e($month) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Course Year</label>
                    <select name="course_year" class="form-select">
                        <option value="">Year</option>
                        <?php for ($year = 2026; $year >= 1980; $year--): ?>
                            <option value="<?= $year ?>" <?= ((string)($formData['course_year'] ?? '') === (string)$year ? 'selected' : '') ?>><?= $year ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <?php if (isset($errors['course_date'])): ?><small class="text-danger d-block mt-2"><?= e($errors['course_date']) ?></small><?php endif; ?>

            <label class="mt-3">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= e($formData['description'] ?? '') ?></textarea>

            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#courses" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php endif; ?>

    <?php if ($courses): ?>
        <div class="card-list">
            <?php foreach ($courses as $course): ?>
                <div class="mini-card">
                    <div>
                        <h5><?= dashValue($course['course_title']) ?></h5>
                        <p><?= dashValue($course['course_provider']) ?></p>
                        <small><?= dashValue(($course['course_month'] ?? '') . ' ' . ($course['course_year'] ?? '')) ?></small>
                    </div>
                    <div class="mini-actions">
                        <a href="dashboard.php?edit_course=<?= e($course['id']) ?>#courses">Edit</a>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_course">
                            <input type="hidden" name="id" value="<?= e($course['id']) ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!is_array($formData)): ?>
        <a href="dashboard.php?edit_course=new#courses" class="empty-add"><i class="fa fa-plus"></i>Add Course</a>
    <?php endif; ?>
</div>
<?php } ?>

<?php
function renderSkillSection($skills, $editSkill, $old, $errors, $skillLevels, $skillYears) {
    $formData = null;
    if ($editSkill === 'new') {
        $formData = $old ?: [];
    } elseif ($editSkill) {
        foreach ($skills as $skill) {
            if ((string)$skill['id'] === (string)$editSkill) {
                $formData = array_merge($skill, $old);
                break;
            }
        }
    }
?>
<div class="dash-section" id="skills">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-star"></i> Skills</h3>
            <p>Your professional strengths</p>
        </div>
        <a href="dashboard.php?edit_skill=new#skills">Add New</a>
    </div>

    <?php if (is_array($formData)): ?>
        <form method="POST" class="dash-edit-form mb-4">
            <input type="hidden" name="action" value="save_skill">
            <input type="hidden" name="id" value="<?= e($formData['id'] ?? '') ?>">

            <div class="form-grid">
                <div>
                    <label>Skill Name</label>
                    <input type="text" name="skill_name" class="form-control" value="<?= e($formData['skill_name'] ?? '') ?>">
                    <?php if (isset($errors['skill_name'])): ?><small class="text-danger"><?= e($errors['skill_name']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Skill Level</label>
                    <select name="skill_level" class="form-select">
                        <option value="">Select level</option>
                        <?php foreach ($skillLevels as $level): ?>
                            <option value="<?= e($level) ?>" <?= (($formData['skill_level'] ?? '') === $level ? 'selected' : '') ?>><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['skill_level'])): ?><small class="text-danger"><?= e($errors['skill_level']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Years Experience</label>
                    <select name="years_experience" class="form-select">
                        <option value="">Select years</option>
                        <?php foreach ($skillYears as $year): ?>
                            <option value="<?= e($year) ?>" <?= (($formData['years_experience'] ?? '') === $year ? 'selected' : '') ?>><?= e($year) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label class="mt-3">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= e($formData['description'] ?? '') ?></textarea>

            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#skills" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php endif; ?>

   <?php if ($skills): ?>
    <div class="skill-cloud">
        <?php foreach ($skills as $skill): ?>
            <div class="skill-pill">

                <div>
                    <?= dashValue($skill['skill_name']) ?>
                    <span><?= dashValue($skill['skill_level']) ?></span>
                </div>

                <div class="mini-actions ms-2">
                    <a href="dashboard.php?edit_skill=<?= e($skill['id']) ?>#skills">
                        Edit
                    </a>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_skill">
                        <input type="hidden" name="id" value="<?= e($skill['id']) ?>">

                        <button type="submit">
                            Delete
                        </button>
                    </form>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

<?php elseif (!is_array($formData)): ?>

    <a href="dashboard.php?edit_skill=new#skills" class="empty-add">
        <i class="fa fa-plus"></i>Add Skill
    </a>

<?php endif; ?>
</div>
<?php } ?>

<?php
function renderLanguageSection($languages, $editLang, $old, $errors, $languageLevels, $languageRatings) {
    $formData = null;
    if ($editLang === 'new') {
        $formData = $old ?: [];
    } elseif ($editLang) {
        foreach ($languages as $language) {
            if ((string)$language['id'] === (string)$editLang) {
                $formData = array_merge($language, $old);
                break;
            }
        }
    }
?>
<div class="dash-section" id="languages">
    <div class="dash-section-head">
        <div>
            <h3><i class="fa fa-language"></i> Languages</h3>
            <p>Languages you know</p>
        </div>
        <a href="dashboard.php?edit_lang=new#languages">Add New</a>
    </div>

    <?php if (is_array($formData)): ?>
        <form method="POST" class="dash-edit-form mb-4">
            <input type="hidden" name="action" value="save_language">
            <input type="hidden" name="id" value="<?= e($formData['id'] ?? '') ?>">

            <div class="form-grid">
                <div>
                    <label>Language Name</label>
                    <input type="text" name="language_name" class="form-control" value="<?= e($formData['language_name'] ?? '') ?>">
                    <?php if (isset($errors['language_name'])): ?><small class="text-danger"><?= e($errors['language_name']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Proficiency Level</label>
                    <select name="proficiency_level" class="form-select">
                        <option value="">Select level</option>
                        <?php foreach ($languageLevels as $level): ?>
                            <option value="<?= e($level) ?>" <?= (($formData['proficiency_level'] ?? '') === $level ? 'selected' : '') ?>><?= e($level) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['proficiency_level'])): ?><small class="text-danger"><?= e($errors['proficiency_level']) ?></small><?php endif; ?>
                </div>

                <div>
                    <label>Reading Level</label>
                    <select name="reading_level" class="form-select">
                        <option value="">Select level</option>
                        <?php foreach ($languageRatings as $rating): ?>
                            <option value="<?= e($rating) ?>" <?= (($formData['reading_level'] ?? '') === $rating ? 'selected' : '') ?>><?= e($rating) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Writing Level</label>
                    <select name="writing_level" class="form-select">
                        <option value="">Select level</option>
                        <?php foreach ($languageRatings as $rating): ?>
                            <option value="<?= e($rating) ?>" <?= (($formData['writing_level'] ?? '') === $rating ? 'selected' : '') ?>><?= e($rating) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary mt-3">Save</button>
            <a href="dashboard.php#languages" class="btn btn-light mt-3">Cancel</a>
        </form>
    <?php endif; ?>

    <?php if ($languages): ?>
        <div class="card-list">
            <?php foreach ($languages as $language): ?>
                <div class="mini-card">
                    <div>
                        <h5><?= dashValue($language['language_name']) ?></h5>
                        <p><?= dashValue($language['proficiency_level']) ?></p>
                        <small>
                            Reading: <?= dashValue($language['reading_level']) ?> |
                            Writing: <?= dashValue($language['writing_level']) ?>
                        </small>
                    </div>

                    <div class="mini-actions">
                        <a href="dashboard.php?edit_lang=<?= e($language['id']) ?>#languages">Edit</a>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_language">
                            <input type="hidden" name="id" value="<?= e($language['id']) ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!is_array($formData)): ?>
        <a href="dashboard.php?edit_lang=new#languages" class="empty-add"><i class="fa fa-plus"></i>Add Language</a>
    <?php endif; ?>
</div>

<?php } ?>

