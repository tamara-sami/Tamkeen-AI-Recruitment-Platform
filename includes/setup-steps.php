<div class="steps-box">
    <h5 class="steps-title">Set Up Profile</h5>

    <div class="profile-steps progress-step-<?php echo $current_step; ?>">

        <div class="profile-step <?php echo $current_step > 1 ? 'done' : ($current_step == 1 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_step > 1 ? '<i class="fa fa-check"></i>' : '1'; ?></div>
            <div class="step-content">
                <h6>Basic Information</h6>
                <p>Enhances your opportunities in appearing at employers search and finding matching jobs.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_step > 2 ? 'done' : ($current_step == 2 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_step > 2 ? '<i class="fa fa-check"></i>' : '2'; ?></div>
            <div class="step-content">
                <h6>Availability & Preferences</h6>
                <p>Let employers know your availability for hiring, salary preferences and experiences.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_step > 3 ? 'done' : ($current_step == 3 ? 'active' : ''); ?>">
            <div class="step-number"><?php echo $current_step > 3 ? '<i class="fa fa-check"></i>' : '3'; ?></div>
            <div class="step-content">
                <h6>Upload Your CV</h6>
                <p>Upload your most recent CV file.</p>
            </div>
        </div>

        <div class="profile-step <?php echo $current_step == 4 ? 'active' : ''; ?>">
            <div class="step-number">4</div>
            <div class="step-content">
                <h6>Profile Image</h6>
                <p>Upload a professional profile image to help employers identify you better.</p>
            </div>
        </div>

    </div>
</div>