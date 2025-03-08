
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="mb-4">Author Guidelines</h2>

                    <!-- Manuscript Preparation -->
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2">Manuscript Preparation</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Format: Microsoft Word (.doc, .docx)
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Paper Size: A4 (210 × 297 mm)
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Margins: 1 inch (2.54 cm) on all sides
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Font: Times New Roman, 12-point
                            </li>
                        </ul>
                    </div>

                    <!-- Paper Structure -->
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2">Paper Structure</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <ol class="mb-0">
                                    <li class="mb-2">Title Page
                                        <ul>
                                            <li>Paper title</li>
                                            <li>Author names and affiliations</li>
                                            <li>Corresponding author details</li>
                                        </ul>
                                    </li>
                                    <li class="mb-2">Abstract (250 words maximum)</li>
                                    <li class="mb-2">Keywords (4-6 words)</li>
                                    <li class="mb-2">Introduction</li>
                                    <li class="mb-2">Literature Review</li>
                                    <li class="mb-2">Methodology</li>
                                    <li class="mb-2">Results and Discussion</li>
                                    <li class="mb-2">Conclusion</li>
                                    <li class="mb-2">References</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Reference Style -->
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2">Reference Style</h4>
                        <p>The journal follows IEEE citation style. Examples:</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-2"><strong>Journal Article:</strong><br>
                                [1] J. K. Author, "Name of paper," Title of Journal, vol. X, no. X, pp. xxx-xxx, Month Year.</p>
                                
                                <p class="mb-2"><strong>Book:</strong><br>
                                [2] Name of Manual/Handbook, x ed., Abbrev. Name of Co., City of Co., Abbrev. State, Year.</p>
                                
                                <p class="mb-0"><strong>Conference Paper:</strong><br>
                                [3] J. K. Author, "Title of paper," in Abbreviated Name of Conf., City of Conf., Abbrev. State, Year, pp. xxx-xxx.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Checklist -->
                    <div class="mb-4">
                        <h4 class="border-bottom pb-2">Submission Checklist</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-file-word text-primary"></i></td>
                                        <td>Manuscript file in Word format</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-image text-primary"></i></td>
                                        <td>High-resolution figures (300 dpi minimum)</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-envelope text-primary"></i></td>
                                        <td>Cover letter with author details</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-check-square text-primary"></i></td>
                                        <td>Signed copyright form</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-download me-2"></i>
                                Download Template
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-file-pdf me-2"></i>
                                Author Guidelines PDF
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-decoration-none">
                                <i class="fas fa-copyright me-2"></i>
                                Copyright Form
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- <?php
// views/guidelines/author.php
// $pageTitle = 'Author Guidelines | ' . ($journalDetails['journal_full_name'] ?? 'Research Journal');
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Author Guidelines</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Manuscript Preparation</h2>
                    
                    <h3 class="h5 mt-4">General Format</h3>
                    <ul>
                        <li>Manuscripts should be submitted in Microsoft Word (.doc or .docx) format</li>
                        <li>Use A4 page size with 1-inch (2.54 cm) margins on all sides</li>
                        <li>Text should be double-spaced with 12-point Times New Roman font</li>
                        <li>Pages should be numbered consecutively</li>
                        <li>Recommended length: <?= $journalDetails['journal_article_length'] ?? '5,000-8,000' ?> words including references</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Title Page</h3>
                    <p>The title page should include:</p>
                    <ul>
                        <li>A concise and informative title (maximum 15 words)</li>
                        <li>Full names of all authors (without titles or degrees)</li>
                        <li>Institutional affiliations for each author</li>
                        <li>Contact information for the corresponding author</li>
                        <li>Word count (excluding references, tables, and figures)</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Abstract and Keywords</h3>
                    <ul>
                        <li>Abstract should be <?= $journalDetails['journal_abstract_length'] ?? '150-250' ?> words</li>
                        <li>Structured abstract with sections: Background, Methods, Results, and Conclusion</li>
                        <li>Include 4-6 keywords below the abstract</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Main Text Structure</h3>
                    <p>Research articles should follow the IMRAD structure:</p>
                    <ul>
                        <li><strong>Introduction:</strong> Background, rationale, and objectives of the study</li>
                        <li><strong>Methods:</strong> Study design, participants, materials, procedures, and data analysis</li>
                        <li><strong>Results:</strong> Main findings without interpretation</li>
                        <li><strong>Discussion:</strong> Interpretation of results, limitations, and implications</li>
                        <li><strong>Conclusion:</strong> Summary of key findings and recommendations</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">References</h3>
                    <p>Use <?= $journalDetails['journal_citation_style'] ?? 'APA' ?> citation style for all references. Examples:</p>
                    
                    <div class="card bg-light mt-2 mb-3">
                        <div class="card-body">
                            <h6>Journal Article:</h6>
                            <p class="mb-0">Smith, J. D., & Johnson, R. T. (2020). Title of article. <em>Journal Name</em>, <em>Volume</em>(Issue), page range. DOI</p>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Book:</h6>
                            <p class="mb-0">Author, A. A. (2020). <em>Title of book</em>. Publisher.</p>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Book Chapter:</h6>
                            <p class="mb-0">Author, A. A. (2020). Title of chapter. In E. Editor (Ed.), <em>Title of book</em> (pp. xx-xx). Publisher.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Tables and Figures</h2>
                    
                    <h3 class="h5 mt-3">Tables</h3>
                    <ul>
                        <li>Tables should be numbered consecutively with Arabic numerals</li>
                        <li>Each table should have a brief title and be self-explanatory</li>
                        <li>Place tables after the reference list, one per page</li>
                        <li>Use footnotes (not endnotes) for explanations</li>
                    </ul>
                    
                    <h3 class="h5 mt-3">Figures</h3>
                    <ul>
                        <li>Figures should be numbered consecutively with Arabic numerals</li>
                        <li>Each figure should have a brief title and caption</li>
                        <li>Submit figures as separate high-resolution files (TIFF, JPEG, or PNG)</li>
                        <li>Minimum resolution: 300 dpi for photographs, 600 dpi for line drawings</li>
                        <li>Maximum figure width: 6 inches (15 cm)</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h2 class="h4 mb-3">Submission Process</h2>
                    
                    <h3 class="h5 mt-3">Online Submission</h3>
                    <p>All manuscripts must be submitted through our online submission system. Authors need to:</p>
                    <ol>
                        <li><a href="<?= config('app.url') ?>auth/register">Register</a> for an account if they don't already have one</li>
                        <li>Log in to their account</li>
                        <li>Click on "Submit Paper" in the author dashboard</li>
                        <li>Follow the step-by-step submission process</li>
                    </ol>
                    
                    <h3 class="h5 mt-4">Required Files</h3>
                    <ul>
                        <li>Manuscript file (without author information for blind review)</li>
                        <li>Title page (with complete author information)</li>
                        <li>Figures (if any) as separate files</li>
                        <li>Supplementary materials (if any)</li>
                        <li>Cover letter addressed to the Editor-in-Chief</li>
                        <li>Signed copyright transfer form</li>
                    </ul>
                    
                    <div class="alert alert-info mt-4">
                        <h4 class="alert-heading h5">Peer Review Process</h4>
                        <p>All submissions undergo initial screening by the editorial team, followed by double-blind peer review by at least two independent reviewers. Authors can expect the first decision within <?= $journalDetails['journal_review_time'] ?? '4-6 weeks' ?>.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= config('app.url') ?>downloads/sample-paper" class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>
                        <a href="<?= config('app.url') ?>papers/submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit Paper
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Quick Links</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>downloads/sample-paper" class="text-decoration-none">
                                <i class="fas fa-file-alt me-2"></i> Sample Paper Template
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>downloads/undertaking-form" class="text-decoration-none">
                                <i class="fas fa-file-signature me-2"></i> Author Undertaking Form
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>publication-charges" class="text-decoration-none">
                                <i class="fas fa-dollar-sign me-2"></i> Publication Charges
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>doi-details" class="text-decoration-none">
                                <i class="fas fa-link me-2"></i> DOI Information
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>papers/submit" class="text-decoration-none">
                                <i class="fas fa-paper-plane me-2"></i> Submit Manuscript
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Ethical Guidelines</h3>
                </div>
                <div class="card-body">
                    <p>Authors must adhere to the following ethical guidelines:</p>
                    <ul class="mb-0">
                        <li>Original work that has not been published elsewhere</li>
                        <li>Proper acknowledgment of all sources</li>
                        <li>Disclosure of conflicts of interest</li>
                        <li>Ethical approval for research involving humans or animals</li>
                        <li>Informed consent for studies involving human subjects</li>
                        <li>Accurate reporting of results without fabrication or manipulation</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Need Help?</h3>
                </div>
                <div class="card-body">
                    <p>If you have questions about manuscript preparation or submission, please contact our editorial office:</p>
                    <address class="mb-0">
                        <strong>Editorial Assistant</strong><br>
                        <a href="mailto:<?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?>">
                            <?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?>
                        </a><br>
                        <?= $journalDetails['journal_phone'] ?? '+1 234 567 8900' ?>
                    </address>
                </div>
            </div>
        </div>
    </div>
</div> -->
<!-- <?php
// views/guidelines/author.php
// $pageTitle = 'Author Guidelines | ' . ($journalDetails['journal_full_name'] ?? 'Research Journal');
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Author Guidelines</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Manuscript Preparation</h2>
                    
                    <h3 class="h5 mt-4">General Format</h3>
                    <ul>
                        <li>Manuscripts should be submitted in Microsoft Word (.doc or .docx) format</li>
                        <li>Use A4 page size with 1-inch (2.54 cm) margins on all sides</li>
                        <li>Text should be double-spaced with 12-point Times New Roman font</li>
                        <li>Pages should be numbered consecutively</li>
                        <li>Recommended length: <?= $journalDetails['journal_article_length'] ?? '5,000-8,000' ?> words including references</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Title Page</h3>
                    <p>The title page should include:</p>
                    <ul>
                        <li>A concise and informative title (maximum 15 words)</li>
                        <li>Full names of all authors (without titles or degrees)</li>
                        <li>Institutional affiliations for each author</li>
                        <li>Contact information for the corresponding author</li>
                        <li>Word count (excluding references, tables, and figures)</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Abstract and Keywords</h3>
                    <ul>
                        <li>Abstract should be <?= $journalDetails['journal_abstract_length'] ?? '150-250' ?> words</li>
                        <li>Structured abstract with sections: Background, Methods, Results, and Conclusion</li>
                        <li>Include 4-6 keywords below the abstract</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">Main Text Structure</h3>
                    <p>Research articles should follow the IMRAD structure:</p>
                    <ul>
                        <li><strong>Introduction:</strong> Background, rationale, and objectives of the study</li>
                        <li><strong>Methods:</strong> Study design, participants, materials, procedures, and data analysis</li>
                        <li><strong>Results:</strong> Main findings without interpretation</li>
                        <li><strong>Discussion:</strong> Interpretation of results, limitations, and implications</li>
                        <li><strong>Conclusion:</strong> Summary of key findings and recommendations</li>
                    </ul>
                    
                    <h3 class="h5 mt-4">References</h3>
                    <p>Use <?= $journalDetails['journal_citation_style'] ?? 'APA' ?> citation style for all references. Examples:</p>
                    
                    <div class="card bg-light mt-2 mb-3">
                        <div class="card-body">
                            <h6>Journal Article:</h6>
                            <p class="mb-0">Smith, J. D., & Johnson, R. T. (2020). Title of article. <em>Journal Name</em>, <em>Volume</em>(Issue), page range. DOI</p>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Book:</h6>
                            <p class="mb-0">Author, A. A. (2020). <em>Title of book</em>. Publisher.</p>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Book Chapter:</h6>
                            <p class="mb-0">Author, A. A. (2020). Title of chapter. In E. Editor (Ed.), <em>Title of book</em> (pp. xx-xx). Publisher.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Tables and Figures</h2>
                    
                    <h3 class="h5 mt-3">Tables</h3>
                    <ul>
                        <li>Tables should be numbered consecutively with Arabic numerals</li>
                        <li>Each table should have a brief title and be self-explanatory</li>
                        <li>Place tables after the reference list, one per page</li>
                        <li>Use footnotes (not endnotes) for explanations</li>
                    </ul>
                    
                    <h3 class="h5 mt-3">Figures</h3>
                    <ul>
                        <li>Figures should be numbered consecutively with Arabic numerals</li>
                        <li>Each figure should have a brief title and caption</li>
                        <li>Submit figures as separate high-resolution files (TIFF, JPEG, or PNG)</li>
                        <li>Minimum resolution: 300 dpi for photographs, 600 dpi for line drawings</li>
                        <li>Maximum figure width: 6 inches (15 cm)</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h2 class="h4 mb-3">Submission Process</h2>
                    
                    <h3 class="h5 mt-3">Online Submission</h3>
                    <p>All manuscripts must be submitted through our online submission system. Authors need to:</p>
                    <ol>
                        <li><a href="<?= config('app.url') ?>auth/register">Register</a> for an account if they don't already have one</li>
                        <li>Log in to their account</li>
                        <li>Click on "Submit Paper" in the author dashboard</li>
                        <li>Follow the step-by-step submission process</li>
                    </ol>
                    
                    <h3 class="h5 mt-4">Required Files</h3>
                    <ul>
                        <li>Manuscript file (without author information for blind review)</li>
                        <li>Title page (with complete author information)</li>
                        <li>Figures (if any) as separate files</li>
                        <li>Supplementary materials (if any)</li>
                        <li>Cover letter addressed to the Editor-in-Chief</li>
                        <li>Signed copyright transfer form</li>
                    </ul>
                    
                    <div class="alert alert-info mt-4">
                        <h4 class="alert-heading h5">Peer Review Process</h4>
                        <p>All submissions undergo initial screening by the editorial team, followed by double-blind peer review by at least two independent reviewers. Authors can expect the first decision within <?= $journalDetails['journal_review_time'] ?? '4-6 weeks' ?>.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= config('app.url') ?>downloads/sample-paper" class="btn btn-outline-primary me-2">
                            <i class="fas fa-download me-1"></i> Download Template
                        </a>
                        <a href="<?= config('app.url') ?>papers/submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Submit Paper
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Quick Links</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>downloads/sample-paper" class="text-decoration-none">
                                <i class="fas fa-file-alt me-2"></i> Sample Paper Template
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>downloads/undertaking-form" class="text-decoration-none">
                                <i class="fas fa-file-signature me-2"></i> Author Undertaking Form
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>publication-charges" class="text-decoration-none">
                                <i class="fas fa-dollar-sign me-2"></i> Publication Charges
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>doi-details" class="text-decoration-none">
                                <i class="fas fa-link me-2"></i> DOI Information
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?= config('app.url') ?>papers/submit" class="text-decoration-none">
                                <i class="fas fa-paper-plane me-2"></i> Submit Manuscript
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Ethical Guidelines</h3>
                </div>
                <div class="card-body">
                    <p>Authors must adhere to the following ethical guidelines:</p>
                    <ul class="mb-0">
                        <li>Original work that has not been published elsewhere</li>
                        <li>Proper acknowledgment of all sources</li>
                        <li>Disclosure of conflicts of interest</li>
                        <li>Ethical approval for research involving humans or animals</li>
                        <li>Informed consent for studies involving human subjects</li>
                        <li>Accurate reporting of results without fabrication or manipulation</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="h5 mb-0">Need Help?</h3>
                </div>
                <div class="card-body">
                    <p>If you have questions about manuscript preparation or submission, please contact our editorial office:</p>
                    <address class="mb-0">
                        <strong>Editorial Assistant</strong><br>
                        <a href="mailto:<?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?>">
                            <?= $journalDetails['journal_email'] ?? 'editor@journal.com' ?>
                        </a><br>
                        <?= $journalDetails['journal_phone'] ?? '+1 234 567 8900' ?>
                    </address>
                </div>
            </div>
        </div>
    </div>
</div> -->
