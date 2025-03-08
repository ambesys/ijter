<?php
// views/partials/admin_sidebar.php

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= config('app.url') ?>admin">
        <div class="sidebar-brand-icon">
            <?php if (!empty($journalDetails['journal_logo'])): ?>
                <img src="<?= config('app.url') ?>uploads/<?= $journalDetails['journal_logo'] ?>" alt="<?= $journalDetails['journal_name'] ?>" class="img-fluid" style="max-height: 40px;">
            <?php else: ?>
                <i class="fas fa-book-open"></i>
            <?php endif; ?>
        </div>
        <div class="sidebar-brand-text mx-3">Admin</div>
    </a>
    
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    
    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= activeClass('/admin$', 'active') ?>">
        <a class="nav-link" href="<?= config('app.url') ?>admin">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    
    <!-- Divider -->
    <hr class="sidebar-divider">
    
    <!-- Heading -->
    <div class="sidebar-heading">
        Content Management
    </div>
    
    <!-- Nav Item - Papers Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePapers" aria-expanded="true" aria-controls="collapsePapers">
            <i class="fas fa-fw fa-file-alt"></i>
            <span>Papers</span>
        </a>
        <div id="collapsePapers" class="collapse" aria-labelledby="headingPapers" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Paper Management:</h6>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers">All Papers</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers?status=SUBMITTED">Submitted</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers?status=UNDER_REVIEW">Under Review</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers?status=ACCEPTED">Accepted</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers?status=REJECTED">Rejected</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/papers?status=PUBLISHED">Published</a>
            </div>
        </div>
    </li>
    
    <!-- Nav Item - Users Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUsers" aria-expanded="true" aria-controls="collapseUsers">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span>
        </a>
        <div id="collapseUsers" class="collapse" aria-labelledby="headingUsers" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">User Management:</h6>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/users">All Users</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/users?role=author">Authors</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/users?role=reviewer">Reviewers</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/users?role=moderator">Moderators</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/users?role=admin">Admins</a>
            </div>
        </div>
    </li>
    
    <!-- Nav Item - Payments Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayments" aria-expanded="true" aria-controls="collapsePayments">
            <i class="fas fa-fw fa-dollar-sign"></i>
            <span>Payments</span>
        </a>
        <div id="collapsePayments" class="collapse" aria-labelledby="headingPayments" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Payment Management:</h6>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments">All Payments</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments?status=PENDING">Pending</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments?status=COMPLETED">Completed</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments?status=FAILED">Failed</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments?status=WAIVED">Waived</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/payments/statistics">Statistics</a>
            </div>
        </div>
    </li>
    
    <!-- Divider -->
    <hr class="sidebar-divider">
    
    <!-- Heading -->
    <div class="sidebar-heading">
        Journal Settings
    </div>
    
    <!-- Nav Item - Settings Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings" aria-expanded="true" aria-controls="collapseSettings">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span>
        </a>
        <div id="collapseSettings" class="collapse" aria-labelledby="headingSettings" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Journal Configuration:</h6>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/journal/settings">General</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/journal/appearance">Appearance</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/journal/content">Content</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/journal/email">Email</a>
                <a class="collapse-item" href="<?= config('app.url') ?>admin/journal/payment">Payment</a>
            </div>
        </div>
    </li>
    
    <!-- Nav Item - Statistics -->
    <li class="nav-item <?= activeClass('/admin/journal/statistics', 'active') ?>">
        <a class="nav-link" href="<?= config('app.url') ?>admin/journal/statistics">
            <i class="fas fa-fw fa-chart-bar"></i>
            <span>Statistics</span>
        </a>
    </li>
    
    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
    
    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
