<?php
// views/auth/reset_password.php

$authTitle = 'Reset Password';
$pageTitle = 'Reset Password | Research Journal';

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();

// Start output buffering
ob_start();
?>

<div class="text-center mb-4">
    <p>Please enter your new password.</p>
</div>

<form action="<?= config('app.url') ?>auth/reset-password" method="post">
    <?= csrfField() ?>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
    
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input type="password" class="form-control" id="password" name="password" required autofocus>
        <div class="form-text">Minimum 8 characters</div>
    </div>
    
    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>
    
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
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
