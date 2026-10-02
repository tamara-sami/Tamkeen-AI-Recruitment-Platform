
<aside class="admin-sidebar">

   <a class="admin-brand" href="../../public/index.php">
        <span class="sidebar-logo-box">
            <span class="logo-t">T</span>
            <span class="logo-dot dot1"></span>
            <span class="logo-dot dot2"></span>
            <span class="logo-dot dot3"></span>
        </span>

        <span class="sidebar-logo-text">
            <span>Tam</span><span>keen</span>
        </span>
    </a>

    <div class="sidebar-label">HR Portal</div>

    <nav class="sidebar-menu">
        <a href="hr-dashboard.php" class="<?= ($activePage == 'hr-dashboard') ? 'active' : '' ?>">
            <i class="fa fa-chart-line"></i> Dashboard
        </a>

        <a href="candidates.php" class="<?= ($activePage == 'candidates') ? 'active' : '' ?>">
            <i class="fa fa-users"></i> Candidates
        </a>
     
        <a href="shortlist.php" class="<?= ($activePage == 'shortlist') ? 'active' : '' ?>">
            <i class="fa fa-star"></i> Shortlist
        </a>

        <a href="interviews.php" class="<?= ($activePage == 'interviews') ? 'active' : '' ?>">
            <i class="fa fa-calendar-check"></i> Interviews
        </a>

        <a href="hiring-requests.php" class="<?= ($activePage == 'hiring-requests') ? 'active' : '' ?>">
            <i class="fa fa-shield-alt"></i> Hiring Approval
        </a>
         <a href="/tamkeentest/public/hr/ai-candidates.php" class="<?= ($activePage ?? '') === 'ai-candidates' ? 'active' : '' ?>">
       <i class="fa fa-robot"></i>
         AI Candidates
</a>
    </nav>

    <a href="../../logout.php" class="logout-link">
        <i class="fa fa-sign-out-alt"></i> Logout
    </a>

</aside>
