<?php
include("../../config.php");
include("../functionsuser/user-profile-setup-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: register.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$errors = [];
$old = [];

$savedProfile = getJobSeekerSavedProfile($conn, $user_id);

if ($savedProfile) {
    $old = $savedProfile;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = saveJobSeekerProfile($conn, $_POST, $user_id);

    if ($result['success']) {
        header("Location: preferences.php");
        exit;
    }

    $errors = $result['errors'];
    $old = $_POST;
}

$countries = json_decode(
    file_get_contents("../../data/countries.json"),
    true
) ?? [];

$locations = json_decode(
    file_get_contents("../../data/jordan_locations.json"),
    true
) ?? [];

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

    <?php
    $current_sidebar = 2;
    include("../../includes/setup-sidebar.php");
    ?>

    <main class="setup-main">

        <form method="POST" class="setup-form">

            <div class="text-center text-muted mb-3">Step 1</div>

            <h2>Basic Information</h2>

            <label>Birth Date *</label>
            <input type="date"
                   name="birth_date"
                   class="form-control mb-2"
                   value="<?= htmlspecialchars($old['birth_date'] ?? ''); ?>"
                   required>

            <?php if (isset($errors['birth_date'])): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['birth_date']); ?>
                </small>
            <?php endif; ?>

            <label>Gender *</label>

            <div class="gender-box mb-2">
                <label>
                    <input type="radio"
                           name="gender"
                           value="male"
                           <?= ($old['gender'] ?? '') === 'male' ? 'checked' : ''; ?>
                           required>
                    Male
                </label>

                <label>
                    <input type="radio"
                           name="gender"
                           value="female"
                           <?= ($old['gender'] ?? '') === 'female' ? 'checked' : ''; ?>
                           required>
                    Female
                </label>
            </div>

            <?php if (isset($errors['gender'])): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['gender']); ?>
                </small>
            <?php endif; ?>

            <label>Nationality *</label>

            <select name="nationality" class="form-select mb-2" required>
                <option value="">Select nationality</option>

                <?php foreach ($countries as $country): ?>
                    <option value="<?= htmlspecialchars($country); ?>"
                        <?= ($old['nationality'] ?? '') === $country ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($country); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($errors['nationality'])): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['nationality']); ?>
                </small>
            <?php endif; ?>

            <label>Residence Country *</label>

            <select name="residence_country"
                    id="residenceCountry"
                    class="form-select mb-2"
                    required>
                <option value="">Select residence country</option>

                <?php foreach ($countries as $country): ?>
                    <option value="<?= htmlspecialchars($country); ?>"
                        <?= ($old['residence_country'] ?? '') === $country ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($country); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (isset($errors['residence_country'])): ?>
                <small class="text-danger d-block mb-3">
                    <?= htmlspecialchars($errors['residence_country']); ?>
                </small>
            <?php endif; ?>

            <div class="row g-4 mb-4">

                <div class="col-md-6">

                    <label>Location *</label>

                    <select name="location_select"
                            id="locationSelect"
                            class="form-select mb-2">
                        <option value="">Select location</option>

                        <?php foreach ($locations as $location): ?>
                            <option value="<?= htmlspecialchars($location); ?>"
                                <?= ($old['location'] ?? '') === $location ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text"
                           name="location_input"
                           id="locationInput"
                           class="form-control mb-2"
                           placeholder="Enter your location"
                           value="<?= htmlspecialchars($old['location'] ?? ''); ?>"
                           minlength="2"
                           maxlength="80"
                           pattern="^[A-Za-z\s\-'.,]+$">

                    <?php if (isset($errors['location'])): ?>
                        <small class="text-danger d-block">
                            <?= htmlspecialchars($errors['location']); ?>
                        </small>
                    <?php endif; ?>

                </div>

                <div class="col-md-6">

                   

                   
                </div>

            </div>

            <button type="submit" class="btn btn-primary w-100">
                Next Step <i class="fa fa-arrow-right ms-2"></i>
            </button>

        </form>

        <div class="col-lg-3">
            <?php
            $current_step = 1;
            include("../../includes/setup-steps.php");
            ?>
        </div>

    </main>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const residenceCountry = document.getElementById("residenceCountry");
    const locationSelect = document.getElementById("locationSelect");
    const locationInput = document.getElementById("locationInput");

    function toggleLocation() {
        if (residenceCountry.value === "Jordan") {
            locationSelect.style.display = "block";
            locationInput.style.display = "none";

            locationSelect.disabled = false;
            locationInput.disabled = true;

            locationSelect.required = true;
            locationInput.required = false;
        } else {
            locationSelect.style.display = "none";
            locationInput.style.display = "block";

            locationSelect.disabled = true;
            locationInput.disabled = false;

            locationSelect.required = false;
            locationInput.required = true;
        }
    }

    residenceCountry.addEventListener("change", toggleLocation);
    toggleLocation();
});
</script>

<?php include("../../includes/scripts.php"); ?>