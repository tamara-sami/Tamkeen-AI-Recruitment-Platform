<?php
include("employer-auth.php");
include("../../config.php");
include("../functions/settings-functions.php");

$company_id = $_SESSION['company_id'];

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_company_profile'])) {
        $result = updateCompanyProfile($conn, $company_id, $_POST, $_FILES);
    }

    if (isset($_POST['save_contact_info'])) {
        $result = updateCompanyContactInfo($conn, $company_id, $_POST);
    }

    if (isset($_POST['change_password'])) {
        $result = changeCompanyPassword($conn, $company_id, $_POST);
    }

    if (!empty($result)) {
        if ($result['success']) {
            $success_message = $result['message'];
        } else {
            $error_message = $result['message'];
        }
    }
}

$company = getCompanySettingsData($conn, $company_id);

$company_name = $company['company_name'] ?? ($_SESSION['company_name'] ?? 'Company');
$company_logo = $company['company_logo'] ?? '';

$base_url = "../../";
$activePage = "settings";
$body_class = "employer-dashboard-body";
$extra_css = ["css/employer-dashboard.css"];

include("../../includes/header.php");
?>

<style>
.company-logo-preview{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #EAF1FF;
    box-shadow:0 12px 30px rgba(15,23,42,.12);
}
.logo-placeholder{
    width:120px;
    height:120px;
    border-radius:50%;
    background:#2563EB;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:42px;
    font-weight:800;
    margin:auto;
    border:4px solid #EAF1FF;
    box-shadow:0 12px 30px rgba(15,23,42,.12);
}
</style>

<div class="admin-layout">

    <?php include("../../includes/admin-sidebar.php"); ?>

    <main class="admin-main">

        <header class="admin-topbar">
            <div>
                <h5 class="mb-0">Settings</h5>
                <small class="text-muted">Manage company profile, contact information, and account preferences</small>
            </div>

            <div class="admin-profile">
                <div class="profile-info">
                    <strong><?= h($company_name); ?></strong>
                    <span>Company Admin</span>
                </div>

                <?php if (!empty($company_logo)): ?>
                    <img src="../../public/uploads/company/<?= h($company_logo); ?>" class="profile-avatar" style="object-fit:cover;" alt="Company Logo">
                <?php else: ?>
                    <div class="profile-avatar">
                        <?= h(strtoupper(substr($company_name, 0, 1))); ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="alert alert-success rounded-4 shadow-sm"><?= h($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger rounded-4 shadow-sm"><?= h($error_message); ?></div>
        <?php endif; ?>

        <section class="admin-hero">
            <div>
                <span class="hero-badge">Company Settings</span>
                <h1>Keep your company profile up to date</h1>
                <p>Update company details, contact information, security settings, and verification status.</p>
            </div>

            <div class="hero-actions">
                <a href="#company-profile" class="btn btn-light fw-bold rounded-pill px-4">Edit Profile</a>
                <a href="#security" class="btn btn-outline-light fw-bold rounded-pill px-4">Security</a>
            </div>
        </section>

        <div class="admin-grid">

            <section class="admin-panel" id="company-profile">
                <div class="panel-header">
                    <div>
                        <h4>Company Profile</h4>
                        <p>Basic company information visible inside the platform.</p>
                    </div>
                </div>

                <form class="company-user-form" method="POST" enctype="multipart/form-data">

                    <div class="mb-4 text-center">
                        <label class="form-label fw-bold d-block mb-3">Company Logo</label>

                        <input type="file" name="company_logo" id="companyLogo" class="d-none" accept="image/*">

                        <?php if (!empty($company_logo)): ?>
                            <img id="logoPreview" src="../../public/uploads/company/<?= h($company_logo); ?>" class="company-logo-preview mb-3" alt="Company Logo">
                            <div id="logoPlaceholder" class="logo-placeholder d-none"><?= h(strtoupper(substr($company_name, 0, 1))); ?></div>
                        <?php else: ?>
                            <img id="logoPreview" src="" class="company-logo-preview mb-3 d-none" alt="Company Logo">
                            <div id="logoPlaceholder" class="logo-placeholder"><?= h(strtoupper(substr($company_name, 0, 1))); ?></div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="document.getElementById('companyLogo').click()">
                                Upload Logo
                            </button>
                        </div>

                        <small class="text-muted d-block mt-2">JPG, PNG, JPEG, or WEBP. Max 2MB.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Name</label>
                            <input type="text"
                                   name="company_name"
                                   class="form-control"
                                   value="<?= h($company_name); ?>"
                                   minlength="2"
                                   maxlength="100"
                                   pattern="^[A-Za-z0-9\s&.,'-]+$"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Responsible Person</label>
                            <input type="text"
                                   name="responsible_person"
                                   class="form-control"
                                   value="<?= h($company['responsible_person'] ?? ''); ?>"
                                   minlength="2"
                                   maxlength="80"
                                   pattern="^[A-Za-z\s'-]+$"
                                   required>
                        </div>
                    </div>

                    <button type="submit" name="save_company_profile" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Save Changes
                    </button>

                </form>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Verification Status</h4>
                        <p>Company trust and verification progress.</p>
                    </div>
                </div>

                <div class="role-note mb-4">
                    <i class="fa fa-shield-alt"></i>
                    <p>Complete verification to unlock full hiring features and increase candidate trust.</p>
                </div>

                <div class="team-summary">
                    <div class="team-summary-item">
                        <div class="summary-icon hr"><i class="fa fa-envelope"></i></div>
                        <div><h5>Email</h5><span>Verified</span></div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon task"><i class="fa fa-building"></i></div>
                        <div><h5>Company Info</h5><span>Pending review</span></div>
                    </div>

                    <div class="team-summary-item">
                        <div class="summary-icon review"><i class="fa fa-file-alt"></i></div>
                        <div><h5>Documents</h5><span>Not uploaded yet</span></div>
                    </div>
                </div>
            </section>

        </div>

        <div class="admin-grid mt-4">

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h4>Contact Information</h4>
                        <p>Used for notifications and hiring communication.</p>
                    </div>
                </div>

                <form class="company-user-form" method="POST">

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= h($company['email'] ?? ''); ?>"
                                   placeholder="company@example.com"
                                   maxlength="120"
                                   required>
                            <small class="text-muted">Enter a valid company email address.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <<input type="tel"
       name="phone"
       class="form-control"
       value="<?= h($company['phone'] ?? ''); ?>"
       placeholder="07XXXXXXXX"
       pattern="^(07)(7|8|9)[0-9]{7}$"
       minlength="10"
       maxlength="10"
       required>

<small class="text-muted">
    Enter a valid Jordanian phone number.
</small>
                        </div>

                    </div>

                    <button type="submit" name="save_contact_info" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Update Contact
                    </button>

                </form>
            </section>

            <section class="admin-panel" id="security">
                <div class="panel-header">
                    <div>
                        <h4>Security</h4>
                        <p>Change password and manage account access.</p>
                    </div>
                </div>

                <form class="company-user-form" method="POST" id="passwordForm">

                    

                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password"
                               name="new_password"
                               id="new_password"
                               class="form-control"
                               placeholder="New password"
                               minlength="8"
                               pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$"
                               required>
                        <small class="text-muted">Must contain 8+ characters, letters, numbers, and a special symbol.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password" required>

                        <div id="passwordError" class="text-danger small mt-2 d-none">
                            Passwords do not match.
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">
                        Change Password
                    </button>

                </form>
            </section>

        </div>

    </main>
</div>

<script>
const logoInput = document.getElementById("companyLogo");
const logoPreview = document.getElementById("logoPreview");
const logoPlaceholder = document.getElementById("logoPlaceholder");

if (logoInput) {
    logoInput.addEventListener("change", function(e) {
        const file = e.target.files[0];

        if (file) {
            logoPreview.src = URL.createObjectURL(file);
            logoPreview.classList.remove("d-none");
            logoPlaceholder.classList.add("d-none");
        }
    });
}

const passwordForm = document.getElementById("passwordForm");
const newPassword = document.getElementById("new_password");
const confirmPassword = document.getElementById("confirm_password");
const passwordError = document.getElementById("passwordError");

if (passwordForm) {
    passwordForm.addEventListener("submit", function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            passwordError.classList.remove("d-none");
            confirmPassword.classList.add("is-invalid");
        } else {
            passwordError.classList.add("d-none");
            confirmPassword.classList.remove("is-invalid");
        }
    });
}
</script>

<?php include("../../includes/scripts.php"); ?>