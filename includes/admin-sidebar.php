<aside class="admin-sidebar">

    <a class="admin-brand" href="../index.php">
        <span class="brand-icon custom-logo">
            <span class="logo-t">T</span>
            <span class="logo-dot dot1"></span>
            <span class="logo-dot dot2"></span>
            <span class="logo-dot dot3"></span>
        </span>
        <span><b>Tam</b><b class="text-primary">keen</b></span>
    </a>

    <div class="sidebar-label">Company Portal</div>

    <nav class="sidebar-menu">

        <a href="employer-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">
            <i class="fa fa-chart-line"></i> Dashboard
        </a>

        <a href="company-users.php" class="<?= ($activePage == 'users') ? 'active' : '' ?>">
            <i class="fa fa-users-cog"></i> Company Users
        </a>

        <a href="hr-management.php" class="<?= ($activePage == 'hr') ? 'active' : '' ?>">
            <i class="fa fa-user-tie"></i> HR Management
        </a>

        <a href="admin-tasks.php" class="<?= ($activePage == 'tasks') ? 'active' : '' ?>">
            <i class="fa fa-tasks"></i> Tasks
        </a>

        <a href="admin-training.php" class="<?= ($activePage == 'training') ? 'active' : '' ?>">
            <i class="fa fa-user-graduate"></i> Training
        </a>

        <a href="ai-center.php" class="<?= ($activePage == 'ai') ? 'active' : '' ?>">
            <i class="fa fa-robot"></i> AI Center
        </a>

        <a href="activity-log.php" class="<?= ($activePage == 'activity') ? 'active' : '' ?>">
            <i class="fa fa-history"></i> Activity Log
        </a>

        <a href="settings.php" class="<?= ($activePage == 'settings') ? 'active' : '' ?>">
            <i class="fa fa-cog"></i> Settings
        </a>

    </nav>

    <a href="../../logout.php" class="logout-link">
        <i class="fa fa-sign-out-alt"></i> Logout
    </a>

</aside>