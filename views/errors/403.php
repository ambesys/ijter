// views/errors/403.php
<?php
// views/errors/403.php

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();

// Set page title
$pageTitle = '403 Forbidden | ' . $journalDetails['journal_full_name'];
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto text-center">
            <h1 class="display-1">403</h1>
            <h2 class="mb-4">Access Forbidden</h2>
            <p class="lead">You don't have permission to access this page.</p>
            <a href="<?= config('app.url') ?>" class="btn btn-primary mt-3">Go to Homepage</a>
        </div>
    </div>
</div>
