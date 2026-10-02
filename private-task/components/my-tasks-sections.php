<?php

if (!function_exists('renderMyTasksPage')) {

    function renderMyTasksPage(array $page)
    {
        extract($page);
?>

<main class="container py-5">

    <div class="row g-4">

        <!-- Left Sidebar -->
        <div class="col-lg-3">
            <div class="bg-white rounded-4 shadow-sm border overflow-hidden">
                <div class="p-3 border-bottom fw-bold">
                    <i class="fa fa-bookmark me-2 text-muted"></i>
                    My items
                </div>

                <a href="/tamkeentest/public/task/my-tasks.php" class="p-3 d-flex justify-content-between align-items-center text-decoration-none border-start border-primary border-4">
                    <span class="fw-bold text-primary">Posted tasks</span>
                    <span id="tasksCount"><?= $totalCount ?></span>
                </a>

                <a href="/tamkeentest/public/task/archived-tasks.php" class="p-3 d-flex justify-content-between align-items-center text-decoration-none text-dark border-top">
                    <span>Archived tasks</span>
                    <span><?= $archivedCount ?? 0 ?></span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-6">

            <div class="bg-white rounded-4 shadow-sm border mb-4 p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-2">Posted Tasks</h4>
                        <p class="text-muted mb-0">Manage, publish, edit, and archive your tasks.</p>
                    </div>

                    <a href="/tamkeentest/public/task/post-task.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa fa-plus me-2"></i>Add Task
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-primary mb-0"><?= $totalCount ?></h4>
                        <small class="text-muted">All Tasks</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-success mb-0"><?= $publishedCount ?></h4>
                        <small class="text-muted">Published</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bg-white rounded-4 shadow-sm border p-3 text-center">
                        <h4 class="fw-bold text-secondary mb-0"><?= $draftCount ?></h4>
                        <small class="text-muted">Drafts</small>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm border">

                <!-- Header + Filters -->
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <h4 class="fw-bold mb-0">Posted Tasks</h4>

                        <div class="task-tabs">
                            <a href="?filter=all&search=<?= urlencode($search) ?>" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
                            <a href="?filter=published&search=<?= urlencode($search) ?>" class="<?= $filter === 'published' ? 'active' : '' ?>">Published</a>
                            <a href="?filter=draft&search=<?= urlencode($search) ?>" class="<?= $filter === 'draft' ? 'active' : '' ?>">Drafts</a>
                        </div>
                    </div>

                    <div class="position-relative">
    <input 
        type="text" 
        id="taskSearch"
        class="form-control rounded-pill"
        placeholder="Search tasks by title..."
        autocomplete="off"
    >

    <div id="searchSuggestions" 
         class="bg-white border rounded-4 shadow-sm position-absolute w-100 mt-2 d-none"
         style="z-index:999;">
    </div>
</div>
                </div>

                <?php if (empty($tasks)): ?>

                    <div class="p-5 text-center">
                        <i class="fa fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold">No tasks found</h5>
                        <p class="text-muted">
                            Try changing the filter or search keyword.
                        </p>

                        <a href="/tamkeentest/public/task/post-task.php" class="btn btn-primary rounded-pill px-4">
                            Post New Task
                        </a>
                    </div>

                <?php else: ?>

                    <?php foreach ($tasks as $task): ?>
<div class="p-4 border-bottom task-post-item" 
     id="task-<?= $task['id'] ?>"
     data-title="<?= strtolower(htmlspecialchars($task['title'])) ?>">
                            <div class="d-flex gap-3">

                                <div>
                                    <div style="width:56px;height:56px;background:#eef3ff;border-radius:14px;"
                                         class="d-flex align-items-center justify-content-center">

                                        <?php if ($task['category'] === 'Programming'): ?>
                                            <i class="fa fa-code text-primary"></i>
                                        <?php elseif ($task['category'] === 'Design'): ?>
                                            <i class="fa fa-paint-brush text-primary"></i>
                                        <?php elseif ($task['category'] === 'Marketing'): ?>
                                            <i class="fa fa-bullhorn text-primary"></i>
                                        <?php elseif ($task['category'] === 'Data Analysis'): ?>
                                            <i class="fa fa-chart-bar text-primary"></i>
                                        <?php elseif ($task['category'] === 'Customer Service'): ?>
                                            <i class="fa fa-headset text-primary"></i>
                                        <?php else: ?>
                                            <i class="fa fa-tasks text-primary"></i>
                                        <?php endif; ?>

                                    </div>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-start gap-3">

                                        <div>
                                            <h5 class="fw-bold mb-1">
                                                <?= htmlspecialchars($task['title'] ?: 'Untitled Task') ?>
                                            </h5>

                                            <p class="mb-1 text-muted">
                                                <?= htmlspecialchars($task['category'] ?: 'No category') ?> ·
                                                <?= htmlspecialchars($task['difficulty'] ?: 'No difficulty') ?>
                                            </p>

                                            <small class="text-muted">
                                                Points: <?= htmlspecialchars($task['points'] ?: '-') ?> ·
                                                Deadline: <?= htmlspecialchars($task['deadline'] ?: '-') ?>
                                            </small>
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-sm" data-bs-toggle="dropdown">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <?php if ($task['status'] === 'draft'): ?>
                                                    <li>
                                                        <a href="javascript:void(0)"
                                                           class="dropdown-item text-success publish-task-btn"
                                                           data-id="<?= $task['id'] ?>">
                                                            <i class="fa fa-upload me-2"></i>Publish
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <li>
                                                    <a href="javascript:void(0)"
                                                       class="dropdown-item edit-task-btn"
                                                       data-id="<?= $task['id'] ?>"
                                                       data-title="<?= htmlspecialchars($task['title']) ?>"
                                                       data-description="<?= htmlspecialchars($task['description']) ?>"
                                                       data-category="<?= htmlspecialchars($task['category']) ?>"
                                                       data-difficulty="<?= htmlspecialchars($task['difficulty']) ?>"
                                                       data-time="<?= htmlspecialchars($task['estimated_time']) ?>"
                                                       data-minimum-focus="<?= htmlspecialchars($task['minimum_focus_minutes'] ?? 10) ?>"
                                                       data-points="<?= htmlspecialchars($task['points']) ?>"
                                                       data-deadline="<?= htmlspecialchars($task['deadline']) ?>"
                                                       data-skills="<?= htmlspecialchars($task['required_skills']) ?>">
                                                        <i class="fa fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>

                                                <li>
                                                    <a href="javascript:void(0)"
                                                       class="dropdown-item text-danger delete-task-btn"
                                                       data-id="<?= $task['id'] ?>">
                                                        <i class="fa fa-archive me-2"></i>Archive
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>

                                    <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                                        <?php if ($task['status'] === 'published'): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">Draft</span>
                                        <?php endif; ?>

                                        <span class="text-muted">
                                            Created <?= date("M d, Y", strtotime($task['created_at'])) ?>
                                        </span>

                                        <?php if ($task['status'] === 'draft'): ?>
                                            <button 
                                                class="btn btn-sm btn-success rounded-pill px-3 publish-task-btn"
                                                data-id="<?= $task['id'] ?>">
                                                Publish
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-3">

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <a href="/tamkeentest/public/task/post-task.php" class="btn btn-outline-primary w-100 rounded-pill fw-bold">
                    <i class="fa fa-plus me-2"></i>
                    Post a new task
                </a>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                <h6 class="fw-bold mb-3">Quick Filters</h6>

                <a href="?filter=all" class="btn btn-light w-100 rounded-pill mb-2 text-start">
                    All Tasks <span class="float-end"><?= $totalCount ?></span>
                </a>

                <a href="?filter=published" class="btn btn-light w-100 rounded-pill mb-2 text-start">
                    Published <span class="float-end"><?= $publishedCount ?></span>
                </a>

                <a href="?filter=draft" class="btn btn-light w-100 rounded-pill text-start">
                    Drafts <span class="float-end"><?= $draftCount ?></span>
                </a>
            </div>

            <div class="bg-white rounded-4 shadow-sm border p-4">
                <h6 class="fw-bold mb-3">Task support</h6>
                <p class="text-muted small mb-3">
                    Archive old tasks, publish drafts, and keep your task list organized.
                </p>
                <a href="/tamkeentest/public/task/task-dashboard.php" class="btn btn-primary rounded-pill px-4">
                    Dashboard
                </a>
            </div>

        </div>

        <!-- Archive Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-body text-center p-4">
                        <h5 class="fw-bold mb-3">Move to Archive?</h5>
                        <p class="text-muted mb-0">
                            This task will move to Archived Tasks. You can restore it later.
                        </p>

                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                                Archive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content rounded-4">

                    <div class="modal-body p-4 p-lg-5">

                        <h3 class="fw-bold mb-4">Edit Task</h3>

                        <form id="editForm" enctype="multipart/form-data">

                            <input type="hidden" id="editId" name="id">

                            <label class="form-label fw-bold">Task Title</label>
                            <input type="text" id="editTitle" name="title" class="form-control mb-1">
                            <small class="text-danger d-none" id="error-title"></small>

                            <label class="form-label fw-bold mt-3">Task Description</label>
                            <textarea id="editDescription" name="description" class="form-control mb-1" rows="5"></textarea>
                            <small class="text-danger d-none" id="error-description"></small>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Task Category</label>
                                    <select id="editCategory" name="category" class="form-select mb-1">
                                        <option value="">Choose category</option>
                                        <option value="Data Analysis">Data Analysis</option>
                                        <option value="Design">Design</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Customer Service">Customer Service</option>
                                        <option value="Programming">Programming</option>
                                    </select>
                                    <small class="text-danger d-none" id="error-category"></small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Difficulty Level</label>
                                    <select id="editDifficulty" name="difficulty" class="form-select mb-1">
                                        <option value="">Choose difficulty</option>
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                    </select>
                                    <small class="text-danger d-none" id="error-difficulty"></small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-4">
    <label class="form-label fw-bold">Estimated Time</label>
    <input
        type="number"
        id="editTime"
        name="estimated_time"
        class="form-control mb-1"
        min="1"
        step="1"
        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
    >
    <small class="text-danger d-none" id="error-estimated_time"></small>
</div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Minimum Focus Minutes</label>
                                    <input type="number" id="editMinimumFocus" name="minimum_focus_minutes" class="form-control mb-1" min="1">
                                    <small class="text-danger d-none" id="error-minimum_focus_minutes"></small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Points</label>
                                    <input type="number" id="editPoints" name="points" class="form-control mb-1">
                                    <small class="text-danger d-none" id="error-points"></small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Deadline</label>
                                    <input type="date" id="editDeadline" name="deadline" class="form-control mb-1">
                                    <small class="text-danger d-none" id="error-deadline"></small>
                                </div>
                            </div>

                            <label class="form-label fw-bold mt-3">Required Skills</label>
                            
                            <input type="text" id="editSkills" name="required_skills" class="form-control mb-1">
                            <small class="text-danger d-none" id="error-required_skills"></small>
                            <label class="form-label fw-bold mt-3">
    Evaluation Criteria
</label>

<textarea
    name="evaluation_criteria"
    class="form-control mb-1"
    rows="4"
    placeholder="Example: Responsive design, clean code, correct logic, complete explanation..."><?= htmlspecialchars($old['evaluation_criteria'] ?? '') ?></textarea>

                            <label class="form-label fw-bold mt-3">Upload Task File</label>
                            <input type="file" name="task_file" class="form-control mb-3">

                            <label class="form-label fw-bold">Task Image</label>
                            <input type="file" name="task_image" class="form-control mb-4">

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>

        <!-- Publish Warning Modal -->
        <div class="modal fade" id="publishWarningModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-body text-center p-4">
                        <h5 class="fw-bold mb-3">Complete task details</h5>
                        <p class="text-muted mb-4">
                            This draft is incomplete. Continue to edit?
                        </p>

                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="button" class="btn btn-primary px-4" id="continueEditBtn">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php
    }
}
