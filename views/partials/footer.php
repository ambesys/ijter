<?php
// views/partials/footer.php

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();
?>

<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>About the Journal</h5>
                <p>
                    <?php if (!empty($journalDetails['journal_description'])): ?>
                        <?= substr($journalDetails['journal_description'], 0, 200) ?>...
                    <?php else: ?>
                        A peer-reviewed journal dedicated to publishing high-quality research papers across various disciplines.
                    <?php endif; ?>
                </p>
        <?php if (!empty($journalDetails['journal_issn']) || !empty($journalDetails['journal_eissn'])): ?>
    <p class="mb-1">
        <?php if (!empty($journalDetails['journal_issn'])): ?>
            <strong>ISSN:</strong> <?= $journalDetails['journal_issn'] ?><br>
        <?php endif; ?>
        <?php if (!empty($journalDetails['journal_eissn'])): ?>
            <strong>E-ISSN:</strong> <?= $journalDetails['journal_eissn'] ?>
        <?php endif; ?>
    </p>
<?php endif; ?>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= config('app.url') ?>about" class="text-white">About</a></li>
                    <li><a href="<?= config('app.url') ?>editorial-board" class="text-white">Editorial Board</a></li>
                    <li><a href="<?= config('app.url') ?>guidelines/author" class="text-white">Author Guidelines</a></li>
                    <li><a href="<?= config('app.url') ?>guidelines/reviewer" class="text-white">Reviewer Guidelines</a></li>
                    <li><a href="<?= config('app.url') ?>guidelines/ethics" class="text-white">Ethics Policy</a></li>
                    <li><a href="<?= config('app.url') ?>papers/published" class="text-white">Published Papers</a></li>
                    <li><a href="<?= config('app.url') ?>contact" class="text-white">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Contact Information</h5>
                <address>
                    <?php if (!empty($journalDetails['journal_address'])): ?>
                        <p><i class="fas fa-map-marker-alt me-2"></i> <?= nl2br($journalDetails['journal_address']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($journalDetails['journal_email'])): ?>
                        <p><i class="fas fa-envelope me-2"></i> <a href="mailto:<?= $journalDetails['journal_email'] ?>" class="text-white"><?= $journalDetails['journal_email'] ?></a></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($journalDetails['journal_phone'])): ?>
                        <p><i class="fas fa-phone me-2"></i> <a href="tel:<?= $journalDetails['journal_phone'] ?>" class="text-white"><?= $journalDetails['journal_phone'] ?></a></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($journalDetails['journal_website'])): ?>
                        <p><i class="fas fa-globe me-2"></i> <a href="<?= $journalDetails['journal_website'] ?>" class="text-white" target="_blank"><?= $journalDetails['journal_website'] ?></a></p>
                    <?php endif; ?>
                </address>
                
                <h5>Follow Us</h5>
                <div class="social-icons">
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-google-scholar fa-lg"></i></a>
                </div>
            </div>
        </div>
        
        <hr class="mt-4 mb-3">
        
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <?= $journalDetails['journal_full_name'] ?? 'Research Journal' ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">
                    <a href="<?= config('app.url') ?>privacy-policy" class="text-white me-3">Privacy Policy</a>
                    <a href="<?= config('app.url') ?>terms-conditions" class="text-white">Terms & Conditions</a>
                </p>
            </div>
        </div>
        
        <?php if (!empty($journalDetails['journal_footer_text'])): ?>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <small><?= $journalDetails['journal_footer_text'] ?></small>
            </div>
        </div>
        <?php endif; ?>
    </div>
</footer>

