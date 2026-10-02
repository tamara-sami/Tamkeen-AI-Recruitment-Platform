<?php
session_start();

include("../../config.php");

$error = "";
$email = "";

const MAX_LOGIN_ATTEMPTS = 4;
const LOCK_MINUTES = 5;

function getRemainingLockMinutes($lockedUntil)
{
    if (empty($lockedUntil)) {
        return 0;
    }

    $remainingSeconds = strtotime($lockedUntil) - time();

    if ($remainingSeconds <= 0) {
        return 0;
    }

    return (int)ceil($remainingSeconds / 60);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("
            SELECT id, full_name, email, password, failed_login_attempts, locked_until
            FROM job_seekers
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid email or password.";
        } else {
            $remainingMinutes = getRemainingLockMinutes($user['locked_until'] ?? null);

            if ($remainingMinutes > 0) {
                $error = "Too many failed attempts. Try again after {$remainingMinutes} minute(s).";
            } elseif (password_verify($password, $user['password'])) {
                $reset = $conn->prepare("
                    UPDATE job_seekers
                    SET failed_login_attempts = 0,
                        locked_until = NULL
                    WHERE id = ?
                ");
                $reset->execute([$user['id']]);

                $_SESSION['job_seeker_id'] = $user['id'];
                $_SESSION['job_seeker_name'] = $user['full_name'];
                $_SESSION['job_seeker_email'] = $user['email'];
                $_SESSION['user_role'] = 'user';

                header("Location: dashboard.php");
                exit();
            } else {
                $attempts = ((int)($user['failed_login_attempts'] ?? 0)) + 1;

                if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                    $lock = $conn->prepare("
                        UPDATE job_seekers
                        SET failed_login_attempts = ?,
                            locked_until = DATE_ADD(NOW(), INTERVAL " . LOCK_MINUTES . " MINUTE)
                        WHERE id = ?
                    ");
                    $lock->execute([$attempts, $user['id']]);

                    $error = "Too many failed attempts. Your account is locked for " . LOCK_MINUTES . " minutes.";
                } else {
                    $update = $conn->prepare("
                        UPDATE job_seekers
                        SET failed_login_attempts = ?,
                            locked_until = NULL
                        WHERE id = ?
                    ");
                    $update->execute([$attempts, $user['id']]);

                    $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts;
                    $error = "Invalid email or password. {$remainingAttempts} attempt(s) remaining.";
                }
            }
        }
    }
}

$base_url = "../../";
$body_class = "auth-page";
$extra_css = [];

include("../../includes/header.php");
include("../../includes/navbar-auth.php");
?>

<section class="min-vh-100 d-flex align-items-center justify-content-center px-3">
    <div class="auth-card bg-white rounded-4 shadow p-5">

        <div class="text-center mb-4">
            <h2 class="mb-2">Welcome Back</h2>
            <p class="text-muted mb-0">
                Log in to continue building your verified skill profile.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label fw-bold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-envelope text-primary"></i>
                    </span>

                    <input type="email"
                           name="email"
                           class="form-control py-3"
                           placeholder="Enter your email"
                           value="<?= htmlspecialchars($email ?: ($_POST['email'] ?? '')); ?>"
                           maxlength="120"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-lock text-primary"></i>
                    </span>

                    <input type="password"
                           id="userLoginPassword"
                           name="password"
                           class="form-control py-3"
                           placeholder="Enter your password"
                           required>
                </div>

                <div class="form-check mt-2">
                    <input class="form-check-input toggle-password"
                           type="checkbox"
                           id="showUserLoginPassword"
                           data-target="userLoginPassword">
                    <label class="form-check-label small" for="showUserLoginPassword">
                        Show Password
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3">
                Log In
            </button>
        </form>

        <p class="text-center mt-4 mb-0 text-muted">
            Don’t have an account?
            <a href="register.php" class="text-primary fw-bold text-decoration-none">
                Create Profile
            </a>
        </p>

    </div>
</section>

<script>
document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const target = document.getElementById(this.dataset.target);
        if (target) {
            target.type = this.checked ? 'text' : 'password';
        }
    });
});
</script>

<?php include("../../includes/scripts.php"); ?>
