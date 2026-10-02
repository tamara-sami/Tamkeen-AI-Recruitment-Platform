<?php
session_start();

include("../../config.php");

$emailError = "";
$passwordError = "";
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

function resetLoginAttempts($conn, $table, $id)
{
    if (!in_array($table, ['companies', 'users'], true)) {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE {$table}
        SET failed_login_attempts = 0,
            locked_until = NULL
        WHERE id = ?
    ");

    return $stmt->execute([$id]);
}

function addFailedLoginAttempt($conn, $table, $id, $currentAttempts)
{
    if (!in_array($table, ['companies', 'users'], true)) {
        return false;
    }

    $attempts = ((int)($currentAttempts ?? 0)) + 1;

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        $stmt = $conn->prepare("
            UPDATE {$table}
            SET failed_login_attempts = ?,
                locked_until = DATE_ADD(NOW(), INTERVAL " . LOCK_MINUTES . " MINUTE)
            WHERE id = ?
        ");

        $stmt->execute([$attempts, $id]);

        return [
            'locked' => true,
            'message' => "Too many failed attempts. Your account is locked for " . LOCK_MINUTES . " minutes."
        ];
    }

    $stmt = $conn->prepare("
        UPDATE {$table}
        SET failed_login_attempts = ?,
            locked_until = NULL
        WHERE id = ?
    ");

    $stmt->execute([$attempts, $id]);

    $remainingAttempts = MAX_LOGIN_ATTEMPTS - $attempts;

    return [
        'locked' => false,
        'message' => "Invalid email or password. {$remainingAttempts} attempt(s) remaining."
    ];
}

function getCompanyByEmail($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT id, company_name, email, password, failed_login_attempts, locked_until
        FROM companies
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getStaffByEmail($conn, $email)
{
    $stmt = $conn->prepare("
        SELECT id, company_id, full_name, email, password, role, status, failed_login_attempts, locked_until
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->execute([$email]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function activatePendingStaff($conn, $userId)
{
    $stmt = $conn->prepare("
        UPDATE users
        SET status = 'active'
        WHERE id = ?
    ");

    return $stmt->execute([$userId]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $emailError = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email.";
    }

    if ($password === '') {
        $passwordError = "Password is required.";
    }

    if ($emailError === "" && $passwordError === "") {

        /* =========================
           1) COMPANY LOGIN
        ========================== */
        $company = getCompanyByEmail($conn, $email);

        if ($company) {
            $remainingMinutes = getRemainingLockMinutes($company['locked_until'] ?? null);

            if ($remainingMinutes > 0) {
                $passwordError = "Too many failed attempts. Try again after {$remainingMinutes} minute(s).";
            } elseif (password_verify($password, $company['password'])) {
                resetLoginAttempts($conn, 'companies', $company['id']);

                $_SESSION['company_id'] = $company['id'];
                $_SESSION['company_name'] = $company['company_name'];
                $_SESSION['company_email'] = $company['email'];
                $_SESSION['role'] = 'company';

                header("Location: employer-dashboard.php");
                exit();
            } else {
                $failedResult = addFailedLoginAttempt(
                    $conn,
                    'companies',
                    $company['id'],
                    $company['failed_login_attempts'] ?? 0
                );

                $passwordError = $failedResult['message'];
            }
        } else {

            /* =========================
               2) STAFF LOGIN
               HR / Task Manager / Training Manager
            ========================== */
            $user = getStaffByEmail($conn, $email);

            if (!$user) {
                $emailError = "Invalid email or password.";
            } else {
                $remainingMinutes = getRemainingLockMinutes($user['locked_until'] ?? null);

                if ($remainingMinutes > 0) {
                    $passwordError = "Too many failed attempts. Try again after {$remainingMinutes} minute(s).";
                } elseif (password_verify($password, $user['password'])) {

                    if ($user['status'] === 'inactive') {
                        $passwordError = "Account is inactive.";
                    } else {
                        resetLoginAttempts($conn, 'users', $user['id']);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['company_id'] = $user['company_id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_email'] = $user['email'];

                        if ($user['status'] === 'pending') {
                            activatePendingStaff($conn, $user['id']);
                        }

                        if ($user['role'] === 'hr') {
                            header("Location: ../hr/hr-dashboard.php");
                            exit();
                        }

                        if ($user['role'] === 'task_manager') {
                            header("Location: ../task/task-dashboard.php");
                            exit();
                        }

                        if ($user['role'] === 'training_manager') {
                            header("Location: ../training/training-dashboard.php");
                            exit();
                        }

                        $passwordError = "Invalid role.";
                    }
                } else {
                    $failedResult = addFailedLoginAttempt(
                        $conn,
                        'users',
                        $user['id'],
                        $user['failed_login_attempts'] ?? 0
                    );

                    $passwordError = $failedResult['message'];
                }
            }
        }
    }
}

$base_url = "../../";

include("../../includes/header.php");
?>

<body class="hire-page">
<?php include("../../includes/navbar-hire.php"); ?>

<section class="hire-auth min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 text-white">
                <h1 class="mb-4">Hire Verified Talent</h1>
                <p class="lead mb-4">
                    Find candidates proven by real-world challenges, skill scores, and verified profiles.
                </p>

                <a href="../index.php" class="btn btn-outline-light rounded-pill px-4">
                    Back Home
                </a>
            </div>

            <div class="col-lg-5 ms-auto">
                <div class="auth-card bg-white rounded-4 shadow p-4 p-md-5">
                    <h2 class="mb-2">Employer Login</h2>
                    <p class="text-muted mb-4">Access your hiring dashboard.</p>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>

                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-envelope text-primary"></i>
                                </span>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control py-3"
                                    placeholder="Company or staff email"
                                    value="<?= htmlspecialchars($email ?: ($_POST['email'] ?? '')); ?>"
                                    required
                                >
                            </div>

                            <?php if ($emailError): ?>
                                <small class="text-danger d-block mt-1">
                                    <?= htmlspecialchars($emailError) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>

                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa fa-lock text-primary"></i>
                                </span>
                                <input
                                    type="password"
                                    id="companyLoginPassword"
                                    name="password"
                                    class="form-control py-3"
                                    placeholder="Password"
                                    required
                                >
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input toggle-password"
                                       type="checkbox"
                                       id="showCompanyLoginPassword"
                                       data-target="companyLoginPassword">
                                <label class="form-check-label small" for="showCompanyLoginPassword">
                                    Show Password
                                </label>
                            </div>

                            <?php if ($passwordError): ?>
                                <small class="text-danger d-block mt-1">
                                    <?= htmlspecialchars($passwordError) ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mb-4">
                            <a href="#" class="small text-primary text-decoration-none fw-bold">
                                Forgot password?
                            </a>
                        </div>

                        <button class="btn btn-primary w-100 py-3" type="submit">
                            Sign In
                        </button>

                    </form>

                    <p class="text-center mt-4 mb-0 text-muted">
                        Don’t have an account?
                        <a href="register.php" class="text-primary fw-bold text-decoration-none">
                            Register Company
                        </a>
                    </p>
                </div>
            </div>

        </div>
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

</body>
</html>
