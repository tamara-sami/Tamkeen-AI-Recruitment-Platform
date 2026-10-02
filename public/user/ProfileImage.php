<?php
include("../../config.php");
include("../functionsuser/user-profile-image-functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: register.php");
    exit;
}

$user_id = $_SESSION['job_seeker_id'];

$profile = getJobSeekerProfileImage(
    $conn,
    $user_id
);

$savedImage = $profile['profile_image'] ?? null;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        isset($_POST['remove_image']) &&
        $_POST['remove_image'] == "1"
    ) {

        removeProfileImage(
            $conn,
            $user_id,
            $savedImage
        );

        header("Location: ProfileImage.php");
        exit;
    }

    $file = $_FILES['profile_image'] ?? null;

    $errors = validateProfileImage(
        $file,
        $savedImage
    );

    if (empty($errors)) {

        if (
            $savedImage &&
            (
                !isset($file) ||
                $file['error'] === UPLOAD_ERR_NO_FILE
            )
        ) {

            header("Location: buildeducation.php");
            exit;
        }

        $uploaded = uploadProfileImage(
            $conn,
            $user_id,
            $file,
            $savedImage
        );

        if ($uploaded) {

            header("Location: buildeducation.php");
            exit;
        }

        $errors['profile_image'] = "Failed to upload image";
    }
}

$base_url = "../../";
$body_class = "setup-page";

include("../../includes/header.php");
?>

<div class="setup-layout">

<?php
$current_sidebar = 4;
include("../../includes/setup-sidebar.php");
?>

<main class="setup-main">

<form method="POST"
      enctype="multipart/form-data"
      class="setup-form">

    <div class="d-flex justify-content-between mb-4">

        <a href="uploadcv.php"
           class="text-primary text-decoration-none">

            <i class="fa fa-arrow-left me-2"></i>
            Previous
        </a>

        <small class="text-muted">Step 4</small>

        <a href="buildeducation.php"
           class="text-primary text-decoration-none">

            Skip
            <i class="fa fa-forward ms-1"></i>
        </a>

    </div>

    <h2>Profile Image</h2>

    <div class="upload-box text-center pt-4 pb-4 mt-4">

        <img id="previewImg"
             src="<?= $savedImage ? '../uploads/profile_images/' . htmlspecialchars($savedImage) : '' ?>"
             style="max-width:150px; <?= $savedImage ? 'display:block;' : 'display:none;' ?> border-radius:50%; margin:0 auto 15px; object-fit:cover;">

        <div id="placeholder"
             style="<?= $savedImage ? 'display:none;' : '' ?>">

            <i class="far fa-image fa-5x text-secondary"></i>

            <h6 class="mt-3">
                Choose your profile image
            </h6>

        </div>

        <?php if ($savedImage): ?>

            <button type="submit"
                    name="remove_image"
                    value="1"
                    class="btn btn-danger px-4 mb-3">

                Remove

            </button>

        <?php endif; ?>

        <input type="file"
               name="profile_image"
               id="fileInput"
               class="form-control mt-3"
               accept=".jpg,.jpeg,.png,.gif,.webp">

    </div>

    <?php if (isset($errors['profile_image'])): ?>

        <small class="text-danger d-block mb-3">
            <?= htmlspecialchars($errors['profile_image']); ?>
        </small>

    <?php endif; ?>

    <button type="submit"
            class="btn btn-primary w-100 mt-4">

        Save & Next
        <i class="fa fa-arrow-right ms-2"></i>

    </button>

</form>

<div class="col-lg-3">

<?php
$current_step = 4;
include("../../includes/setup-steps.php");
?>

</div>

</main>

</div>
<script>
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('previewImg');
const placeholder = document.getElementById('placeholder');

fileInput.addEventListener('change', function() {

    const file = this.files[0];

    if (file) {

        const reader = new FileReader();

        reader.onload = function(e) {

            preview.src = e.target.result;

            preview.style.display = "block";

            placeholder.style.display = "none";
        };

        reader.readAsDataURL(file);
    }
});
</script>
<?php include("../../includes/scripts.php"); ?>

