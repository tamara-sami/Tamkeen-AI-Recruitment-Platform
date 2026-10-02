<?php
$current_cv_step = $current_cv_step ?? 1;
?>

<div class="steps-box">

    <h5 class="steps-title">Build Your CV</h5>

    <div class="profile-steps progress-step-<?php echo $current_cv_step; ?>">

        <div class="profile-step <?php echo $current_cv_step > 1 ? 'done' : ($current_cv_step == 1 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_cv_step > 1 ? '<i class="fa fa-check"></i>' : '1'; ?></div>
            <div class="step-content">
                <h6>Education Information</h6>
                <p>Add your education entries.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_cv_step > 2 ? 'done' : ($current_cv_step == 2 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_cv_step > 2 ? '<i class="fa fa-check"></i>' : '2'; ?></div>
            <div class="step-content">
                <h6>Work Experience</h6>
                <p>Add your experience entries.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_cv_step > 3 ? 'done' : ($current_cv_step == 3 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_cv_step > 3 ? '<i class="fa fa-check"></i>' : '3'; ?></div>
            <div class="step-content">
                <h6>Courses</h6>
                <p>Add your courses entries.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_cv_step > 4 ? 'done' : ($current_cv_step == 4 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_cv_step > 4 ? '<i class="fa fa-check"></i>' : '4'; ?></div>
            <div class="step-content">
                <h6>Skills</h6>
                <p>Add all your professional skills.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_cv_step == 5 ? 'active' : ''; ?>">
            <div class="step-number">5</div>
            <div class="step-content">
                <h6>Languages</h6>
                <p>Add all the languages you know.</p>
            </div>
        </div>

    </div>

</div>