<?php
include("../../config.php");
include("../functions/register-functions.php");

$emailError = "";
$passwordError = "";
$generalError = "";

$old = [
    "company_name" => "",
    "responsible_person" => "",
    "phone" => "",
    "country" => "",
    "location" => "",
    "company_type" => "",
    "industry" => "",
    "company_size" => "",
    "email" => ""
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    foreach ($old as $key => $value) {
        $old[$key] = $_POST[$key] ?? "";
    }

    $validation = validateCompanyRegister($conn, $_POST);

    $emailError = $validation["emailError"];
    $passwordError = $validation["passwordError"];
    $generalError = $validation["generalError"];

    if ($generalError === "" && $emailError === "" && $passwordError === "") {

if (registerCompany($conn, $_POST)) {

    $company = loginCompanyAfterRegister(
        $conn,
        $_POST['email']
    );

    $_SESSION['company_id'] = $company['id'];

    $_SESSION['company_name'] = $company['company_name'];

    $_SESSION['company_email'] = $company['email'];

    header("Location: employer-dashboard.php");

    exit();
} else {
            $generalError = "Something went wrong. Please try again.";
        }
    }
}

function oldValue($old, $key)
{
    return htmlspecialchars($old[$key] ?? "", ENT_QUOTES, "UTF-8");
}

function selected($old, $key, $value)
{
    return (($old[$key] ?? "") === $value) ? "selected" : "";
}

$base_url = "../../";
$body_class = "employer-register-page";
$extra_css = ["css/employer-register.css"];

include("../../includes/header.php");
?>

<section class="employer-register-section">

    <div class="employer-left">
        <div class="employer-left-content">
            <h1>Employer Account</h1>

            <p>
                Create your company account and start discovering verified candidates
                with skill-based profiles.
            </p>

            <h6>
                Are you a job seeker?
                <a href="../user/login.php">Log in now</a>
            </h6>
        </div>
    </div>

    <div class="employer-right">

        <div class="register-label">Register</div>

        <div class="employer-form-card">
            <h2>Create Employer Account</h2>
            <p>Please fill out the form below</p>

            <?php if ($generalError): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($generalError); ?>
                </small>
            <?php endif; ?>

            <form method="POST">

                <div class="row g-3 mb-3">

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-building"></i>
                            </span>

                            <input type="text"
                                   name="company_name"
                                   class="form-control"
                                   placeholder="Company Name *"
                                   value="<?= oldValue($old, 'company_name'); ?>"
                                   minlength="2"
                                   maxlength="100"
                                   pattern="^[A-Za-z0-9\s&.,'-]+$"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-user"></i>
                            </span>

                            <input type="text"
                                   name="responsible_person"
                                   class="form-control"
                                   placeholder="Responsible Person *"
                                   value="<?= oldValue($old, 'responsible_person'); ?>"
                                   minlength="2"
                                   maxlength="80"
                                   pattern="^[A-Za-z\s'-]+$"
                                   required>
                        </div>
                    </div>

                </div>

                <div class="mb-3">
                    <label>Mobile Phone *</label>

                    <div class="input-group">
                        <span class="input-group-text">🇯🇴</span>

                        <input type="tel"
                               name="phone"
                               class="form-control"
                               placeholder="07XXXXXXXX"
                               value="<?= oldValue($old, 'phone'); ?>"
                               pattern="^07[789][0-9]{7}$"
                               minlength="10"
                               maxlength="10"
                               required>
                    </div>

                    <small class="text-muted">
                        Enter a valid Jordanian number like 0791234567.
                    </small>
                </div>

                <div class="row g-3 mb-3">

                    <div class="col-md-6">
                        <label>Country *</label>

                        <select name="country" class="form-select" required>
                            <option value="" disabled <?= empty($old['country']) ? "selected" : ""; ?>>
                                Select country
                            </option>
                            <option value="Jordan" <?= selected($old, 'country', 'Jordan'); ?>>Jordan</option>
                            <option value="Saudi Arabia" <?= selected($old, 'country', 'Saudi Arabia'); ?>>Saudi Arabia</option>
                            <option value="United Arab Emirates" <?= selected($old, 'country', 'United Arab Emirates'); ?>>United Arab Emirates</option>
                            <option value="Qatar" <?= selected($old, 'country', 'Qatar'); ?>>Qatar</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Location *</label>

                        <select name="location" class="form-select" required>
                            <option value="" disabled <?= empty($old['location']) ? "selected" : ""; ?>>
                                Select location
                            </option>
                            <option value="Amman" <?= selected($old, 'location', 'Amman'); ?>>Amman</option>
                            <option value="Irbid" <?= selected($old, 'location', 'Irbid'); ?>>Irbid</option>
                            <option value="Zarqa" <?= selected($old, 'location', 'Zarqa'); ?>>Zarqa</option>
                            <option value="Aqaba" <?= selected($old, 'location', 'Aqaba'); ?>>Aqaba</option>
                        </select>
                    </div>

                </div>

                <div class="row g-3 mb-3">

                    <div class="col-md-4">
                        <label>Company Type *</label>

                        <select name="company_type" class="form-select" required>
                            <option value="" disabled <?= empty($old['company_type']) ? "selected" : ""; ?>>
                                Select type
                            </option>
                            <option value="Public Company" <?= selected($old, 'company_type', 'Public Company'); ?>>Public Company</option>
                            <option value="Private Company" <?= selected($old, 'company_type', 'Private Company'); ?>>Private Company</option>
                            <option value="Startup" <?= selected($old, 'company_type', 'Startup'); ?>>Startup</option>
                            <option value="NGO" <?= selected($old, 'company_type', 'NGO'); ?>>NGO</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Company Industry *</label>

                        <select name="industry" class="form-select" required>
                            <option value="" disabled <?= empty($old['industry']) ? "selected" : ""; ?>>
                                Select industry
                            </option>
                            <option value="Accounting" <?= selected($old, 'industry', 'Accounting'); ?>>Accounting</option>
                            <option value="IT" <?= selected($old, 'industry', 'IT'); ?>>IT</option>
                            <option value="Education" <?= selected($old, 'industry', 'Education'); ?>>Education</option>
                            <option value="Healthcare" <?= selected($old, 'industry', 'Healthcare'); ?>>Healthcare</option>
                            <option value="Marketing" <?= selected($old, 'industry', 'Marketing'); ?>>Marketing</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Company Size *</label>

                        <select name="company_size" class="form-select" required>
                            <option value="" disabled <?= empty($old['company_size']) ? "selected" : ""; ?>>
                                Select size
                            </option>
                            <option value="Myself only" <?= selected($old, 'company_size', 'Myself only'); ?>>Myself only</option>
                            <option value="2 - 10 Employees" <?= selected($old, 'company_size', '2 - 10 Employees'); ?>>2 - 10 Employees</option>
                            <option value="11 - 50 Employees" <?= selected($old, 'company_size', '11 - 50 Employees'); ?>>11 - 50 Employees</option>
                            <option value="51 - 200 Employees" <?= selected($old, 'company_size', '51 - 200 Employees'); ?>>51 - 200 Employees</option>
                            <option value="200+ Employees" <?= selected($old, 'company_size', '200+ Employees'); ?>>200+ Employees</option>
                        </select>
                    </div>

                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-envelope"></i>
                        </span>

                        <input type="email"
                               name="email"
                               class="form-control"
                               placeholder="Email *"
                               value="<?= oldValue($old, 'email'); ?>"
                               maxlength="120"
                               required>
                    </div>

                    <?php if ($emailError): ?>
                        <small class="text-danger d-block mt-1">
                            <?= htmlspecialchars($emailError); ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa fa-lock"></i>
                        </span>

                        <input type="password"
                               name="password"
                               id="companyRegisterPassword"
                               class="form-control"
                               placeholder="Password *"
                               minlength="8"
                               pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$"
                               required>
                    </div>

                    <div class="form-check mt-2">
                        <input class="form-check-input toggle-password"
                               type="checkbox"
                               id="showCompanyRegisterPassword"
                               data-target="companyRegisterPassword">
                        <label class="form-check-label small" for="showCompanyRegisterPassword">
                            Show Password
                        </label>
                    </div>

                    <small class="text-muted d-block mt-1">
                        Must contain 8+ characters, a letter, a number, and a symbol.
                    </small>

                    <?php if ($passwordError): ?>
                        <small class="text-danger d-block mt-1">
                            <?= htmlspecialchars($passwordError); ?>
                        </small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn signup-btn">
                    Sign Up <i class="fa fa-arrow-right ms-2"></i>
                </button>

                <p class="terms">
                    By clicking Sign Up, you agree to Tamkeen’s
                    <a href="#">Terms and Conditions</a>
                    and
                    <a href="#">Privacy Policy</a>.
                </p>

            </form>
        </div>

        <a href="login.php" class="already-btn">
            Already have account? Log in now
        </a>

    </div>

</section>


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