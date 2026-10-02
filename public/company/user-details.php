<<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/user-details-functions.php");

$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    header("Location: company-users.php");
    exit;
}

$user_id = (int)$_GET['id'];
$user = getCompanyUserDetails($conn, $user_id, $company_id);

if (!$user) {
    header("Location: company-users.php");
    exit;
}

if (isset($_POST['toggle_status'])) {
    $_SESSION['success_message'] = toggleCompanyUserStatus($conn, $user);
    header("Location: user-details.php?id=" . $user_id);
    exit;
}

if (isset($_POST['update_user'])) {
    $_SESSION['success_message'] = updateCompanyUser($conn, $user_id, $company_id, $_POST);
    header("Location: user-details.php?id=" . $user_id);
    exit;
}

if (isset($_POST['update_password'])) {
    $_SESSION['success_message'] = updateCompanyUserPassword($conn, $user_id, $company_id, $_POST);
    header("Location: user-details.php?id=" . $user_id);
    exit;
}

$roleLabel = userRoleLabel($user['role']);
$roleClass = userRoleClass($user['role']);
$activities = getUserActivityLog($conn, $user_id);
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
                <h4 class="mb-0">User Details</h4>
                <small class="text-muted">View user information, role, status, and activity</small>
            </div>

            <div class="admin-profile">
                <div class="profile-info">
                    <strong><?= htmlspecialchars($_SESSION['company_name']); ?></strong>
                    <span>Company Admin</span>
                </div>
                <div class="profile-avatar">
                    <?= htmlspecialchars(strtoupper(substr($_SESSION['company_name'], 0, 1))); ?>
                </div>
            </div>
        </header>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Team Member</span>
                <h1><?= htmlspecialchars($user['full_name']); ?></h1>
                <p>
                    Manage this user's access, role, account status, and future activity inside your company portal.
                </p>
            </div>

            <div class="hero-actions">
                <a href="company-users.php" class="btn btn-light fw-bold rounded-pill px-4">
                    Back to Users
                </a>
            </div>
        </section>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="role-help-box success-message mb-4">
                <p class="mb-0"><?= htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="admin-grid">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Account Information</h4>
                        <p>Basic user profile and access details.</p>
                    </div>
                </div>

                <div class="table-user mb-4">
                    <div class="table-avatar">
                        <?= htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1))); ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($user['full_name']); ?></strong>
                        <span><?= htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Role:</strong>
                    <p class="mb-0">
                        <span class="role-badge <?= htmlspecialchars($roleClass); ?>">
                            <?= htmlspecialchars($roleLabel); ?>
                        </span>
                    </p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Status:</strong>
                    <p class="mb-0">
                        <span class="status-badge <?= htmlspecialchars($user['status']); ?>">
                            <?= htmlspecialchars(ucfirst($user['status'])); ?>
                        </span>
                    </p>
                </div>

                <div class="role-help-box mb-3">
                    <strong>Email:</strong>
                    <p class="mb-0"><?= htmlspecialchars($user['email']); ?></p>
                </div>

                <div class="role-help-box">
                    <strong>Created At:</strong>
                    <p class="mb-0">
                        <?= htmlspecialchars($user['created_at'] ?? 'Not available'); ?>
                    </p>
                </div>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Account Actions</h4>
                        <p>Manage this user's account access.</p>
                    </div>
                </div>

                <div class="quick-actions">

                    <a href="?id=<?= (int)$user_id; ?>&show_edit=1">
                        <i class="fa fa-edit"></i>
                        <div>
                            <strong>Edit User</strong>
                            <span>Change name or email.</span>
                        </div>
                    </a>

                    <?php if (isset($_GET['show_edit'])): ?>
                        <form method="POST" class="role-help-box mt-3">

                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control mb-3"
                                   value="<?= htmlspecialchars($user['full_name']); ?>" required>

                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control mb-3"
                                   value="<?= htmlspecialchars($user['email']); ?>" required>

                            <button type="submit" name="update_user" class="btn btn-primary rounded-pill px-4">
                                Save Changes
                            </button>

                        </form>
                    <?php endif; ?>

                    <a href="?id=<?= (int)$user_id; ?>&show_pass=1">
                        <i class="fa fa-key"></i>
                        <div>
                            <strong>Reset Password</strong>
                            <span>Create a new temporary password.</span>
                        </div>
                    </a>

                    <?php if (isset($_GET['show_pass'])): ?>
                        <form method="POST" class="mt-3">
<div class="mb-2">
    <input type="password"
        name="new_password"
        id="new_password"
        class="form-control"
        placeholder="New Password"
        required
        minlength="8"
        pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).+$"
        title="Password must contain at least 8 characters, one letter, one number, and one special character">
</div>

<div class="mb-2">
    <input type="password"
        name="confirm_password"
        id="confirm_password"
        class="form-control"
        placeholder="Confirm Password"
        required>
</div>

<div id="passwordError"
     class="text-danger small mt-2"
     style="display:none;">
    Passwords do not match.
</div>

<button type="submit"
    name="update_password"
    class="btn btn-primary rounded-pill px-4 mt-3">
    Update Password
</button>
</form>
                    <?php endif; ?>

                    <form method="POST" style="margin: 0;">
                        <button type="submit" name="toggle_status"
                            class="quick-action-btn <?= $user['status'] == 'inactive' ? '' : 'danger-action'; ?>">
                            <i class="fa <?= $user['status'] == 'inactive' ? 'fa-check' : 'fa-ban'; ?>"></i>
                            <div>
                                <strong>
                                    <?= $user['status'] == 'inactive' ? 'Activate Account' : 'Deactivate Account'; ?>
                                </strong>
                                <span>
                                    <?= $user['status'] == 'inactive'
                                        ? 'Allow this user to access the system again.'
                                        : 'Stop this user from accessing the system.'; ?>
                                </span>
                            </div>
                        </button>
                    </form>

                </div>
            </section>

        </div>


        <section class="admin-panel mt-4">
            <div class="panel-header">
                <div>
                    <h4>Activity Log</h4>
                    <p>Recent actions made by this user.</p>
                </div>
            </div>

            <div class="activity-list">

    <?php if (count($activities) > 0): ?>

        <?php foreach ($activities as $activity): ?>

            <div class="activity-item">

                <div class="activity-icon blue">
                    <i class="<?= htmlspecialchars($activity['icon']); ?>"></i>
                </div>

                <div>
                    <strong>
                        <?= htmlspecialchars($activity['title']); ?>
                    </strong>

                    <p>
                        <?= htmlspecialchars($activity['description']); ?>
                    </p>

                    <small>
                        <?= htmlspecialchars($activity['date']); ?>
                    </small>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="activity-item">

            <div class="activity-icon blue">
                <i class="fa fa-info"></i>
            </div>

            <div>
                <strong>No activity yet</strong>
                <p>This user's actions will appear here later.</p>
                <small>Pending activity tracking</small>
            </div>

        </div>

    <?php endif; ?>

</div>
        </section>

    </main>
</div>
<script>

const passwordForm =
    document.querySelector(
        'button[name="update_password"]'
    )?.closest('form');

if (passwordForm) {

    passwordForm.addEventListener('submit', function(e) {

        const pass =
            document.getElementById('new_password').value;

        const confirm =
            document.getElementById('confirm_password').value;

        const error =
            document.getElementById('passwordError');

        if (pass !== confirm) {

            e.preventDefault();
            error.style.display = 'block';

        } else {

            error.style.display = 'none';
        }
    });
}

</script>
<?php include("../../includes/scripts.php"); ?>