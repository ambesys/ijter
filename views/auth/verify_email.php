<?php
// views/auth/verify_email.php

$authTitle = 'Verify Email';
$pageTitle = 'Verify Email | Research Journal';

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();

// Start output buffering
ob_start();
?>

<div class="text-center mb-4">
    <div class="mb-4">
        <i class="fas fa-envelope fa-3x text-primary"></i>
    </div>
    <h5>Email Verification Required</h5>
    <p>We've sent a verification email to your registered email address. Please check your inbox and click on the verification link to activate your account.</p>
    <p>If you haven't received the email, you can request a new verification link below.</p>
</div>

<form action="<?= config('app.url') ?>auth/resend-verification" method="post">
    <?= csrfField() ?>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Resend Verification Email</button>
    </div>
</form>

<?php
$content = ob_get_clean();

// Footer links
ob_start();
?>

<div>
    <a href="<?= config('app.url') ?>auth/login" class="text-decoration-none">Back to Login</a>
</div>

<?php
$footer = ob_get_clean();

// Include the layout
include ROOT_PATH . '/views/layouts/auth.php';
?>
