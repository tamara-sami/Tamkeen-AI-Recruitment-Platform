<?php
include("../../config.php");
include("../functionsuser/user-register-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$body_class = "setup-page";
$base_url = "../../";

$errors = [];
$old = [
    'full_name' => '',
    'email' => '',
    'mobile' => '',
    'job_title' => ''
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old = [
        'full_name' => $_POST['full_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'mobile' => $_POST['mobile'] ?? '',
        'job_title' => $_POST['job_title'] ?? ''
    ];

    $result = registerJobSeeker($conn, $_POST);

    if ($result['success']) {
        $_SESSION['job_seeker_id'] = $result['user_id'];
        $_SESSION['job_seeker_name'] = trim($_POST['full_name']);
        $_SESSION['job_seeker_email'] = trim($_POST['email']);
        $_SESSION['user_role'] = 'user';

        header("Location: setup-profile.php");
        exit;
    }

    $errors = $result['errors'];
}

include("../../includes/header.php");
?>

<div class="setup-layout">
    <?php
    $current_sidebar = 1;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="setup-main">
        <section class="register-form">

            <h2>Create Job Seeker Account</h2>
            <p class="text-muted">Please fill out the form below</p>

            <form method="POST">

                <div class="mb-2 input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-user text-primary"></i></span>
                    <input type="text"
                           name="full_name"
                           class="form-control"
                           placeholder="Full Name *"
                           value="<?= htmlspecialchars($old['full_name']); ?>"
                           minlength="2"
                           maxlength="80"
                           pattern="^[A-Za-z\s'-]+$"
                           required>
                </div>
                <?php if (isset($errors['full_name'])): ?>
                    <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['full_name']); ?></small>
                <?php endif; ?>

                <div class="mb-2 input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-envelope text-primary"></i></span>
                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="Email Address *"
                           value="<?= htmlspecialchars($old['email']); ?>"
                           maxlength="120"
                           required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['email']); ?></small>
                <?php endif; ?>

                <div class="mb-2 input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-phone text-primary"></i></span>
                    <input type="tel"
                           name="mobile"
                           class="form-control"
                           placeholder="07XXXXXXXX"
                           value="<?= htmlspecialchars($old['mobile']); ?>"
                           pattern="^07[789][0-9]{7}$"
                           minlength="10"
                           maxlength="10"
                           required>
                </div>
                <?php if (isset($errors['mobile'])): ?>
                    <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['mobile']); ?></small>
                <?php endif; ?>

                <div class="mb-2 input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-briefcase text-primary"></i></span>
                    <input type="text"
                           name="job_title"
                           class="form-control"
                           placeholder="Job Title *"
                           value="<?= htmlspecialchars($old['job_title']); ?>"
                           minlength="2"
                           maxlength="80"
                           pattern="^[A-Za-z\s'-]+$"
                           required>
                </div>
                <?php if (isset($errors['job_title'])): ?>
                    <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['job_title']); ?></small>
                <?php endif; ?>

                <div class="mb-2 input-group">
                    <span class="input-group-text bg-white"><i class="fa fa-lock text-primary"></i></span>
                    <input type="password"
                           id="userRegisterPassword"
                           name="password"
                           class="form-control"
                           placeholder="Password *"
                           minlength="8"
                           pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$"
                           required>
                </div>

                <div class="form-check mt-2 mb-2">
                    <input class="form-check-input toggle-password"
                           type="checkbox"
                           id="showUserRegisterPassword"
                           data-target="userRegisterPassword">
                    <label class="form-check-label small" for="showUserRegisterPassword">
                        Show Password
                    </label>
                </div>

                <small class="text-muted d-block mb-2">
                    Password must contain 8+ characters, letter, number, and symbol.
                </small>

                <?php if (isset($errors['password'])): ?>
                    <small class="text-danger d-block mb-3"><?= htmlspecialchars($errors['password']); ?></small>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    Register and Proceed <i class="fa fa-arrow-right ms-2"></i>
                </button>

                <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1">
                    <span class="mx-3 text-muted small">or</span>
                    <hr class="flex-grow-1">
                </div>

                <div class="text-end">
                    <p class="mb-1">Already registered?</p>
                    <a href="login.php" class="fw-bold text-primary text-decoration-none">
                        Log in
                    </a>
                </div>

            </form>

        </section>
    </main>
</div>


<script>
document.querySelectorAll('.toggle-password').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
        const target = document.getElementById(this.dataset.target);
        if (target) {
            target.type = this.checked ? 'text' : 'password';
        }
    });
});
</script>

<?php include("../../includes/scripts.php"); ?>