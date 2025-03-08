<?php
// views/partials/header.php

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();
// Helper function to check roles
// At the top of header.php
$user = null;

$user = Helper::auth();
$userDetails = $_SESSION['user_details'] ?? null;
?>



<header id="header" class="header fixed-top">
    <!-- Topbar section remains unchanged -->
    <div class="topbar bg-primary d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>
                        <?php if (!empty($journalDetails['journal_issn'])): ?>
                            <span class="me-3">ISSN: <?= $journalDetails['journal_issn'] ?></span>
                        <?php endif; ?>
                        <?php if (!empty($journalDetails['journal_issn'])): ?>
                            <span>E-ISSN: <?= $journalDetails['journal_issn'] ?></span>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        
                            <a href="mailto:<?= $journalDetails['journal_email'] ?? 'editor@ijter.org' ?>" class="text-white me-3">
                                <i class="fas fa-envelope me-1"></i> <?= $journalDetails['journal_email'] ?? 'editor@ijter.org' ?>
                            </a>
                       
                            <a href="tel:+15307278425" class="text-white">
                                <i class="fas fa-phone me-1"></i> +1 (530) 727 8425
                            </a>
                    </small>
                </div>
                <!-- <div class="col-md-6 text-md-end">
                    <small>
                        <?php if (!empty($journalDetails['journal_email'])): ?>
                            <a href="mailto:<?= $journalDetails['journal_email'] ?? 'editor@ijter.org' ?>" class="text-white me-3">
                                <i class="fas fa-envelope me-1"></i> <?= $journalDetails['journal_email'] ?? 'editor@ijter.org' ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($journalDetails['journal_phone'])): ?>
                            <a href="tel:<?= $journalDetails['journal_phone'] ?>" class="text-white">
                                <i class="fas fa-phone me-1"></i> <?= $journalDetails['journal_phone'] ?>
                            </a>
                        <?php endif; ?>
                    </small>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-9 d-flex align-items-center">
                    <div class="logo-wrapper me-3">
                        <a href="<?= config('app.url') ?>">
                            <img src="<?= config('app.url') ?>assets/img/logo.png" alt="Logo" class="logo-img">
                        </a>
                    </div>
                    <div class="journal-details">
                        <h1 class="h3 text-dark mb-0"><?= $journalDetails['journal_full_name'] ?? 'Research Journal' ?></h1>
                        <div class="text-muted"><?= $journalDetails['journal_publisher'] ?></div>
                    </div>
                </div>
                <div class="col-md-3 text-md-end desktop-only">
                    <?php if ($user): ?>
                        <a href="<?= config('app.url') ?>user/dashboard" class="btn btn-outline-primary me-2">
                            <i class="fas fa-user me-1"></i> My Account
                        </a>
                        <a href="<?= config('app.url') ?>logout" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?= config('app.url') ?>login" class="btn btn-outline-primary me-2">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                        <a href="<?= config('app.url') ?>register" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <?php include ROOT_PATH . '/views/partials/navbar.php'; ?>
   
    
     <!-- User Navbar -->
 
</header>
