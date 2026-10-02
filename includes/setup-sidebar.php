 <?php
$current_sidebar = $current_sidebar ?? 1;
?>
 <aside class="setup-sidebar">

<a href="../../public/index.php" class="setup-brand">
                 <span class="brand-icon me-2 custom-logo">
        <span class="logo-t">T</span>
        <span class="logo-dot dot1"></span>
        <span class="logo-dot dot2"></span>
        <span class="logo-dot dot3"></span>
    </span>

    <span class="fw-bold fs-3">
        <span style="color:#080808;">Tam</span><span style="color:#2563EB;">keen</span>
    </span>
    </a>
       <div class="setup-menu">

    <!-- Step 1 -->
    <div class="setup-menu-item 
        <?php echo $current_sidebar == 1 ? 'active' : ($current_sidebar > 1 ? 'done' : ''); ?>">
        <i class="fa fa-user-plus"></i>
        <span>Create Account</span>
    </div>

    <!-- Step 2 -->
    <div class="setup-menu-item 
        <?php echo $current_sidebar == 2 ? 'active' : ($current_sidebar > 2 ? 'done' : ''); ?>">
        <i class="fa fa-user-cog"></i>
        <span>Set Up Profile</span>
    </div>

    <!-- Step 3 -->
    <div class="setup-menu-item 
        <?php echo $current_sidebar == 3 ? 'active' : ($current_sidebar > 3 ? 'done' : ''); ?>">
        <i class="fa fa-file-alt"></i>
        <span>Build Your CV</span>
    </div>

</div>

<div class="setup-help">
    <i class="fa fa-headset"></i>
    <h6>Looking For Help?</h6>
    <a href="#">Contact us</a>
</div>

</aside>