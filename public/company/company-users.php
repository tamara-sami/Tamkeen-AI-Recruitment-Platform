<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/company-users-functions.php");

$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("
    SELECT company_logo
    FROM companies
    WHERE id = ?
");

$stmt->execute([$_SESSION['company_id']]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_POST['create_user'])) {
    $result = createCompanyUser($conn, $_POST, $company_id);

    $_SESSION['success_message'] = companyUserMessage(
        $result,
        $_POST['full_name'] ?? '',
        $_POST['role'] ?? ''
    );

    header("Location: company-users.php");
    exit;
}

$pageData = getCompanyUsersPageData($conn, $company_id);

$users = $pageData["users"];
$hr_count = $pageData["hr_count"];
$task_manager_count = $pageData["task_manager_count"];
$training_manager_count = $pageData["training_manager_count"];

$base_url = "../../";
$activePage = "users";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">Company Users</h5>
                <small class="text-muted">Manage HR, Task Managers, and Training Managers</small>
            </div>

            <div class="admin-profile">
                <div class="profile-info">
                    <strong><?= htmlspecialchars($_SESSION['company_name']); ?></strong>
                    <span>Company Admin</span>
                </div>
                               <?= renderCompanyLogo(
    $_SESSION['company_name'],
    $company['company_logo'] ?? ''
); ?>
            </div>
        </header>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Role-Based Access</span>
                <h1>Manage company team</h1>
                <p>Add internal users and assign roles so each person sees only the pages related to their job.</p>
            </div>

            <div class="hero-actions">
                <a href="#add-user-form" class="btn btn-light fw-bold rounded-pill px-4">
                    Add New User
                </a>
            </div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel" id="add-user-form">
                <div class="panel-header">
                    <div>
                        <h4>Add Company User</h4>
                        <p>Create login access for HR, Task Manager, or Training Manager.</p>
                    </div>
                </div>

                <form method="POST" class="company-user-form">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="user@company.com" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control"
                                required
                                minlength="8"
                                pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).+$"
                                title="Password must contain letters, numbers, and a special symbol"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="" selected disabled>Select role</option>
                                <option value="hr">HR</option>
                                <option value="task_manager">Task Manager</option>
                                <option value="training_manager">Training Manager</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="create_user" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Create User
                    </button>

                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="role-help-box success-message mt-3">
                            <p class="mb-0">
                                <?= htmlspecialchars($_SESSION['success_message']); ?>
                            </p>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                </form>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Team Summary</h4>
                        <p>Current company access overview</p>
                    </div>
                </div>

                <div class="team-summary">
                    <div class="team-summary-item">
                        <div class="summary-icon hr"><i class="fa fa-user-tie"></i></div>
                        <div>
                            <h5><?= (int)$hr_count; ?></h5>
                            <span>HR Users</span>
                        </div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon task"><i class="fa fa-tasks"></i></div>
                        <div>
                            <h5><?= (int)$task_manager_count; ?></h5>
                            <span>Task Managers</span>
                        </div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon task"><i class="fa fa-user-graduate"></i></div>
                        <div>
                            <h5><?= (int)$training_manager_count; ?></h5>
                            <span>Training Managers</span>
                        </div>
                    </div>
                </div>

                <div class="role-note mt-4">
                    <i class="fa fa-shield-alt"></i>
                    <p>
                        Users cannot access pages outside their role. This keeps hiring, task creation,
                        and training permissions separated.
                    </p>
                </div>
            </section>

        </div>

        <section class="admin-panel mt-4">
            <div class="panel-header">
                <div>
                    <h4>Company Users</h4>
                    <p>Users created by the company admin will appear here.</p>
                </div>

                <a href="#add-user-form">Add User</a>
            </div>

            <div class="company-users-table">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="table-user">
                                            <div class="table-avatar">
                                                <?= htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1))); ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($user['full_name']); ?></strong>
                                                <span><?= htmlspecialchars(roleLabel($user['role'])); ?></span>
                                            </div>
                                        </div>
                                    </td>

                                    <td><?= htmlspecialchars($user['email']); ?></td>

                                    <td>
                                        <span class="role-badge <?= htmlspecialchars($user['role']); ?>">
                                            <?= htmlspecialchars(roleLabel($user['role'])); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= htmlspecialchars($user['status']); ?>">
                                            <?= htmlspecialchars(ucfirst($user['status'])); ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="user-details.php?id=<?= (int)$user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    No users added yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </section>

    </main>
</div>

<?php include("../../includes/scripts.php"); ?>