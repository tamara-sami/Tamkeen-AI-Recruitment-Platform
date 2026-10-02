<?php

if (!function_exists('renderPostTaskPage')) {

    function renderPostTaskPage(array $page)
    {
        extract($page);
?>

<main class="container py-5">


    <div class="alert bg-white border border-primary rounded-3 shadow-sm d-flex align-items-center gap-2 mb-4">
        <i class="fa fa-info-circle text-primary"></i>
        <strong>Create a task</strong>
        <span class="text-muted">that candidates can solve to prove real skills.</span>
    </div>


    
    <div class="row g-4">

        <div class="col-lg-8">

         <form method="POST" enctype="multipart/form-data">

    <h5 class="fw-bold mb-3">Task Details</h5>

    <div class="mb-3">
        <label class="form-label fw-bold">Task Title</label>
        <input 
            type="text" 
            name="title" 
            class="form-control task-input <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
            placeholder="Example: Clean Sales Dataset"
            value="<?= htmlspecialchars($old['title'] ?? '') ?>"
        >
        <?php if (isset($errors['title'])): ?>
            <div class="invalid-feedback d-block"><?= $errors['title'] ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Task Description</label>
        <textarea 
            name="description" 
            class="form-control task-input <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
            rows="5" 
            placeholder="Explain what candidates need to do..."
        ><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <div class="invalid-feedback d-block"><?= $errors['description'] ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">Task Category</label>
            <select name="category" class="form-select task-input <?= isset($errors['category']) ? 'is-invalid' : '' ?>">
                <option value="">Choose category</option>
                <option value="Data Analysis" <?= ($old['category'] ?? '') === 'Data Analysis' ? 'selected' : '' ?>>Data Analysis</option>
                <option value="Design" <?= ($old['category'] ?? '') === 'Design' ? 'selected' : '' ?>>Design</option>
                <option value="Marketing" <?= ($old['category'] ?? '') === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                <option value="Customer Service" <?= ($old['category'] ?? '') === 'Customer Service' ? 'selected' : '' ?>>Customer Service</option>
                <option value="Programming" <?= ($old['category'] ?? '') === 'Programming' ? 'selected' : '' ?>>Programming</option>
            </select>
            <?php if (isset($errors['category'])): ?>
                <div class="invalid-feedback d-block"><?= $errors['category'] ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold">Difficulty Level</label>
            <select name="difficulty" class="form-select task-input <?= isset($errors['difficulty']) ? 'is-invalid' : '' ?>">
                <option value="">Choose difficulty</option>
                <option value="Beginner" <?= ($old['difficulty'] ?? '') === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                <option value="Intermediate" <?= ($old['difficulty'] ?? '') === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                <option value="Advanced" <?= ($old['difficulty'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
            </select>
            <?php if (isset($errors['difficulty'])): ?>
                <div class="invalid-feedback d-block"><?= $errors['difficulty'] ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
    <label class="form-label fw-bold">Estimated Time</label>

  <input 
    type="number"
    name="estimated_time"
    min="1"
    step="1"
    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
    class="form-control task-input <?= isset($errors['estimated_time']) ? 'is-invalid' : '' ?>"
    placeholder="60"
    value="<?= htmlspecialchars($old['estimated_time'] ?? '') ?>"
>
    
</div>

<div class="col-md-4">
    <label class="form-label fw-bold">Minimum Focus Minutes</label>

    <input 
        type="number"
        name="minimum_focus_minutes"
        class="form-control task-input <?= isset($errors['minimum_focus_minutes']) ? 'is-invalid' : '' ?>"
        placeholder="10"
        min="1"
        value="<?= htmlspecialchars($old['minimum_focus_minutes'] ?? '10') ?>"
    >

    <?php if (isset($errors['minimum_focus_minutes'])): ?>
        <div class="invalid-feedback d-block"><?= $errors['minimum_focus_minutes'] ?></div>
    <?php endif; ?>
</div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Points</label>
            <input 
                type="number" 
                name="points" 
                class="form-control task-input <?= isset($errors['points']) ? 'is-invalid' : '' ?>" 
                placeholder=""
                value="<?= htmlspecialchars($old['points'] ?? '') ?>"
            >
            <?php if (isset($errors['points'])): ?>
                <div class="invalid-feedback d-block"><?= $errors['points'] ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold">Deadline</label>
           <input 
    type="date" 
    name="deadline" 
    class="form-control task-input <?= isset($errors['deadline']) ? 'is-invalid' : '' ?>"
    value="<?= htmlspecialchars($old['deadline'] ?? '') ?>"
    min="<?= date('Y-m-d') ?>"
>

<?php if (isset($errors['deadline'])): ?>
    <div class="invalid-feedback d-block"><?= $errors['deadline'] ?></div>
<?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Required Skills</label>
        <input 
            type="text" 
            name="required_skills" 
            class="form-control task-input <?= isset($errors['required_skills']) ? 'is-invalid' : '' ?>" 
            placeholder="Excel, Data Cleaning, Reporting"
            value="<?= htmlspecialchars($old['required_skills'] ?? '') ?>"
        >
        <?php if (isset($errors['required_skills'])): ?>
            <div class="invalid-feedback d-block"><?= $errors['required_skills'] ?></div>
        <?php endif; ?>
    </div>
<div class="mb-3">
    <label class="form-label fw-bold">
        Evaluation Criteria
    </label>
   

    <textarea
        name="evaluation_criteria"
        class="form-control task-input"
        rows="4"
        placeholder="Example: Responsive design, clean code, correct logic, complete explanation..."><?= htmlspecialchars($old['evaluation_criteria'] ?? '') ?></textarea>
</div>
 <div class="mb-3">
    <label class="form-label fw-bold">
        Model Answer / Expected Answer
    </label>

    <textarea
        name="model_answer"
        class="form-control task-input <?= isset($errors['model_answer']) ? 'is-invalid' : '' ?>"
        rows="5"
        placeholder="Enter the expected answer that AI should compare against"><?= htmlspecialchars($old['model_answer'] ?? '') ?></textarea>

    <?php if (isset($errors['model_answer'])): ?>
        <div class="invalid-feedback d-block">
            <?= $errors['model_answer'] ?>
        </div>
    <?php endif; ?>
</div>
    <div class="mb-3">
    <label class="form-label fw-bold">Upload Task File <span class="text-muted">(Optional)</span></label>

<input 
    type="file" 
    name="task_file" 
    id="taskFile" 
    class="d-none"
    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv"
>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <button type="button" class="btn btn-outline-primary"
                onclick="document.getElementById('taskFile').click()">
            Choose File
        </button>

        <button type="button" id="removeFileBtn" class="btn btn-outline-danger d-none">
            Delete File
        </button>

        <span id="fileName" class="text-muted">No file chosen</span>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">Task Image <span class="text-muted">(Optional)</span></label>

    <div class="image-upload-box text-center p-4">
        <input type="file" name="task_image" id="taskImage" class="d-none" accept="image/*">

        <img id="imagePreview"
             src=""
             class="d-none mb-3"
             style="max-width: 220px; max-height: 180px; border-radius: 12px; object-fit: cover;">

        <i id="imageIcon" class="fa fa-image fa-2x text-primary mb-3"></i>

        <p class="mb-2 fw-bold">Upload task preview image</p>

        <small class="text-muted d-block mb-3">
            JPG or PNG — helps candidates understand the task better.
        </small>

        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <button type="button" class="btn btn-outline-primary px-4 fw-bold"
                    onclick="document.getElementById('taskImage').click()">
                Choose 
            </button>

            <button type="button" id="removeImageBtn" class="btn btn-outline-danger px-4 fw-bold d-none">
                Delete Image
            </button>
        </div>
    </div>
</div>

    
    <?php if (isset($errors['status'])): ?>
        <div class="alert alert-danger"><?= $errors['status'] ?></div>
    <?php endif; ?>

    <div class="d-flex gap-3 flex-wrap">
        <button type="submit" name="status" value="published" class="btn btn-primary px-5 py-3 fw-bold">
            Publish Task
        </button>

        <button type="submit" name="status" value="draft" class="btn btn-outline-primary px-5 py-3 fw-bold">
            Save Draft
        </button>
    </div>

</form>
    </div>

       
        <?php include(__DIR__ . "/../../includes/task-side.php"); ?>

    </div> 





</main>

<?php
    }
}
