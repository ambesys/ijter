<?php
// views/pages/about.php
$pageTitle = 'About Us | ' . ($journalDetails['journal_full_name'] ?? 'Research Journal');
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">About the Journal</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Overview</h2>
                    <p><?= $journalDetails['journal_description'] ?? 'Our research journal is dedicated to publishing high-quality, peer-reviewed research across multiple disciplines. We aim to promote scholarly excellence and contribute to the advancement of knowledge in various fields.' ?></p>
                    
                    <h2 class="h4 mb-3 mt-4">Aims and Scope</h2>
                    <p>The journal publishes original research articles, review papers, case studies, and short communications in the following areas:</p>
                    <ul>
                        <?php
                        $categories = model('category')->getAllCategories();
                        foreach ($categories as $category):
                        ?>
                            <li><?= $category['category_name'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h2 class="h4 mb-3 mt-4">Publication Frequency</h2>
                    <p>The journal is published <?= $journalDetails['journal_frequency'] ?? 'quarterly' ?> with issues released in <?= $journalDetails['journal_publication_months'] ?? 'March, June, September, and December' ?>.</p>
                    
                    <h2 class="h4 mb-3 mt-4">Peer Review Process</h2>
                    <p>All submitted manuscripts undergo a rigorous double-blind peer review process to ensure quality and originality. The typical review process takes <?= $journalDetails['journal_review_time'] ?? '4-6 weeks' ?>.</p>
                    
                    <h2 class="h4 mb-3 mt-4">Indexing and Abstracting</h2>
                    <p>The journal is indexed in the following databases:</p>
                    <ul>
                        <?php
                        $indexing = explode(',', $journalDetails['journal_indexing'] ?? 'Google Scholar,Scopus,Web of Science');
                        foreach ($indexing as $index):
                        ?>
                            <li><?= trim($index) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h2 class="h4 mb-3">Publication Ethics</h2>
                    <p>The journal adheres to the highest standards of publication ethics and takes all possible measures against publication malpractice. Authors, reviewers, and editors are expected to follow these ethical guidelines:</p>
                    
                    <h3 class="h5 mt-3">For Authors</h3>
                    <ul>
                        <li>Submit only original work that has not been published elsewhere</li>
                        <li>Properly cite all sources used in the research</li>
                        <li>Disclose any conflicts of interest</li>
                        <li>List all contributors who meet authorship criteria</li>
                        <li>Promptly report any errors discovered after publication</li>
                    </ul>
                    
                    <h3 class="h5 mt-3">For Reviewers</h3>
                    <ul>
                        <li>Maintain confidentiality of the review process</li>
                        <li>Disclose any conflicts of interest</li>
                        <li>Provide objective and constructive feedback</li>
                        <li>Complete reviews within the specified timeframe</li>
                    </ul>
                    
                    <h3 class="h5 mt-3">For Editors</h3>
                    <ul>
                        <li>Ensure fair and unbiased review process</li>
                        <li>Maintain confidentiality of submitted manuscripts</li>
                        <li>Make decisions based solely on academic merit</li>
                        <li>Address any ethical concerns promptly</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Journal Information</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><strong>Title:</strong> <?= $journalDetails['journal_full_name'] ?? 'Research Journal' ?></li>
                        <li class="mb-2"><strong>Abbreviation:</strong> <?= $journalDetails['journal_abbreviation'] ?? 'Res J' ?></li>
                        <li class="mb-2"><strong>ISSN (Print):</strong> <?= $journalDetails['journal_issn'] ?? 'XXXX-XXXX' ?></li>
                        <li class="mb-2"><strong>ISSN (Online):</strong> <?= $journalDetails['journal_eissn'] ?? 'XXXX-XXXX' ?></li>
                        <li class="mb-2"><strong>Publisher:</strong> <?= $journalDetails['journal_publisher'] ?? 'Academic Publishing House' ?></li>
                        <li class="mb-2"><strong>Country:</strong> <?= $journalDetails['journal_country'] ?? 'International' ?></li>
                        <li class="mb-2"><strong>Language:</strong> <?= $journalDetails['journal_language'] ?? 'English' ?></li>
                        <li><strong>Open Access:</strong> <?= $journalDetails['journal_open_access'] ? 'Yes' : 'No' ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Contact Information</h3>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong>Editorial Office:</strong><br>
                        <?= $journalDetails['journal_address'] ?? '123 Academic Street, Research City' ?><br><br>
                        <strong>Email:</strong><br>
                        <a href="mailto:<?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?>"><?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?></a><br><br>
                        <strong>Phone:</strong><br>
                        <?= $journalDetails['journal_phone'] ?? '+1 234 567 8900' ?>
                    </address>
                </div>
            </div>
        </div>
    </div>
</div>
