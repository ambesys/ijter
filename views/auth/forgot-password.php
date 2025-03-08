<?php
// views/auth/forgot_password.php

$authTitle = 'Forgot Password';
$pageTitle = 'Forgot Password | Research Journal';

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();

// Start output buffering
ob_start();
?>

<div class="text-center mb-4">
    <p>Enter your email address and we'll send you a link to reset your password.</p>
</div>

<form action="<?= config('app.url') ?>auth/forgot-password" method="post">
    <?= csrfField() ?>
    
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Send Reset Link</button>
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
