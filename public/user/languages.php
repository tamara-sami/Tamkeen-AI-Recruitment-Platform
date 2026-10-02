<?php
session_start();

require_once("../../config.php");
require_once("../functionsuser/user-languages-functions.php");

$page = initLanguagesPage($conn);

extract($page);

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>
<div class="setup-layout">

<?php
$current_sidebar = 3;
include("../../includes/setup-sidebar.php");
?>

<main class="setup-main">

<section class="setup-form">

<div class="d-flex justify-content-between mb-4">
    <a href="skill.php" class="text-primary text-decoration-none">
        <i class="fa fa-arrow-left me-2"></i>Previous
    </a>

    <small class="text-muted">Step 5</small>

    <a href="congratulations.php" class="text-primary text-decoration-none">
        Skip <i class="fa fa-forward ms-1"></i>
    </a>
</div>

<h2>Languages</h2>

<p class="text-muted mb-4">
    Add the languages you know and your level in each language.
</p>

<?php foreach ($languages as $language): ?>
    <div class="experience-box mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="fw-bold mb-1">
                    <?= htmlspecialchars($language['language_name']); ?>
                </h6>

                <p class="text-muted mb-1">
                    <?= htmlspecialchars($language['proficiency_level']); ?>
                </p>

                <small class="text-primary">
                    Reading: <?= htmlspecialchars($language['reading_level']); ?>
                    |
                    Writing: <?= htmlspecialchars($language['writing_level']); ?>
                </small>
            </div>

            <div class="text-end">
                <a href="languages.php?edit=<?= (int)$language['id']; ?>"
                   class="text-muted small me-3 text-decoration-none">
                    <i class="fa fa-edit me-1"></i>Edit
                </a>

                <form method="POST" style="display:inline;">
                    <input type="hidden"
                           name="language_id"
                           value="<?= (int)$language['id']; ?>">

                    <button type="submit"
                            name="delete_language"
                            value="1"
                            class="text-muted small border-0 bg-transparent p-0">
                        <i class="fa fa-times me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<form method="POST">

<?php if ($edit_id): ?>
    <input type="hidden"
           name="edit_id"
           value="<?= htmlspecialchars($edit_id); ?>">
<?php endif; ?>

<div class="mb-2 input-group">
    <span class="input-group-text bg-white">
        <i class="fa fa-language text-primary"></i>
    </span>

    <input type="text"
           name="language_name"
           class="form-control"
           placeholder="Language name *"
           value="<?= formValue($old, $savedData, 'language_name'); ?>"
           required>
</div>

<?php if (isset($errors['language_name'])): ?>
    <small class="text-danger d-block mb-3">
        <?= htmlspecialchars($errors['language_name']); ?>
    </small>
<?php endif; ?>

<div class="mb-2">
    <label>Proficiency Level *</label>

    <select name="proficiency_level" class="form-select" required>
        <option value="">Select level</option>

        <?php foreach ($levels as $level): ?>
            <option value="<?= htmlspecialchars($level); ?>"
                <?= formSelected($old, $savedData, 'proficiency_level', $level); ?>>
                <?= htmlspecialchars($level); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<?php if (isset($errors['proficiency_level'])): ?>
    <small class="text-danger d-block mb-3">
        <?= htmlspecialchars($errors['proficiency_level']); ?>
    </small>
<?php endif; ?>

<div class="mb-4">
    <label>Reading Level</label>

    <select name="reading_level" class="form-select">
        <option value="">Select level</option>

        <?php foreach ($ratings as $rating): ?>
            <option value="<?= htmlspecialchars($rating); ?>"
                <?= formSelected($old, $savedData, 'reading_level', $rating); ?>>
                <?= htmlspecialchars($rating); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-4">
    <label>Writing Level</label>

    <select name="writing_level" class="form-select">
        <option value="">Select level</option>

        <?php foreach ($ratings as $rating): ?>
            <option value="<?= htmlspecialchars($rating); ?>"
                <?= formSelected($old, $savedData, 'writing_level', $rating); ?>>
                <?= htmlspecialchars($rating); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<button type="submit"
        name="action"
        value="add_more"
        class="btn btn-outline-primary mb-4">
    <i class="fa fa-plus me-2"></i>Add Another Language
</button>

<button type="submit"
        name="action"
        value="proceed"
        class="btn btn-primary w-100">
    Finish <i class="fa fa-check ms-2"></i>
</button>

</form>

</section>

<div class="col-lg-3">
    <?php
    $current_cv_step = 5;
    include("../../includes/cv-steps.php");
    ?>
</div>

</main>
</div>

<?php include("../../includes/scripts.php"); ?>