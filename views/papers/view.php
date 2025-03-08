<?php
// views/papers/view.php
?>

<section class="breadcrumbs">
    <div class="container">
        <ol>
            <li><a href="<?php echo config('app.url'); ?>">Home</a></li>
            <li><a href="<?php echo config('app.url'); ?>issues">Issues</a></li>
            <?php if (isset($issue)): ?>
                <li><a href="<?php echo config('app.url'); ?>issues/<?php echo $issue['id']; ?>">Volume <?php echo $issue['volume']; ?>, Issue <?php echo $issue['issue_number']; ?></a></li>
            <?php endif; ?>
            <li>Paper</li>
        </ol>
        <h2>Paper Details</h2>
    </div>
</section>

<section class="paper-details">
    <div class="container" data-aos="fade-up">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($paper['title']); ?></h2>
                        
                        <div class="authors mb-3">
                            <strong>Authors:</strong> <?php echo htmlspecialchars($paper['authors']); ?>
                        </div>
                        
                        <?php if (isset($issue)): ?>
                            <div class="publication-info mb-3">
                                <strong>Published in:</strong> Volume <?php echo $issue['volume']; ?>, Issue <?php echo $issue['issue_number']; ?>, 
                                <?php echo formatDate($paper['publication_date']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="abstract mb-4">
                            <h5>Abstract</h5>
                            <p><?php echo nl2br(htmlspecialchars($paper['abstract'])); ?></p>
                        </div>
                        
                        <div class="keywords mb-4">
                            <h5>Keywords</h5>
                            <p>
                                <?php foreach ($paper['keywords'] as $keyword): ?>
                                    <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($keyword); ?></span>
                                <?php endforeach; ?>
                            </p>
                        </div>
                        
                        <?php if (!empty($paper['doi'])): ?>
                            <div class="doi mb-4">
                                <h5>DOI</h5>
                                <p><a href="https://doi.org/<?php echo htmlspecialchars($paper['doi']); ?>" target="_blank"><?php echo htmlspecialchars($paper['doi']); ?></a></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($paper['funding'])): ?>
                            <div class="funding mb-4">
                                <h5>Funding</h5>
                                <p><?php echo nl2br(htmlspecialchars($paper['funding'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="paper-actions mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?php echo config('app.url') . $paper['file_path']; ?>" class="btn btn-primary w-100" target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-2"></i> Download PDF
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="shareDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-share me-2"></i> Share
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="shareDropdown">
                                            <li><a class="dropdown-item" href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode($paper['title']); ?>" target="_blank"><i class="bi bi-twitter me-2"></i> Twitter</a></li>
                                            <li><a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>" target="_blank"><i class="bi bi-facebook me-2"></i> Facebook</a></li>
                                            <li><a class="dropdown-item" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(getCurrentUrl()); ?>&title=<?php echo urlencode($paper['title']); ?>" target="_blank"><i class="bi bi-linkedin me-2"></i> LinkedIn</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="mailto:?subject=<?php echo urlencode('Research Paper: ' . $paper['title']); ?>&body=<?php echo urlencode('Check out this research paper: ' . getCurrentUrl()); ?>"><i class="bi bi-envelope me-2"></i> Email</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="citation-info mt-4">
                            <h5>How to Cite</h5>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <p class="mb-2"><strong>APA:</strong></p>
                                    <p class="mb-3"><?php echo htmlspecialchars($paper['citation_apa'] ?? generateCitation($paper, 'apa')); ?></p>
                                    
                                    <p class="mb-2"><strong>MLA:</strong></p>
                                    <p class="mb-3"><?php echo htmlspecialchars($paper['citation_mla'] ?? generateCitation($paper, 'mla')); ?></p>
                                    
                                    <p class="mb-2"><strong>Chicago:</strong></p>
                                    <p class="mb-0"><?php echo htmlspecialchars($paper['citation_chicago'] ?? generateCitation($paper, 'chicago')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($paper['references'])): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">References</h5>
                        </div>
                        <div class="card-body">
                            <?php echo nl2br(htmlspecialchars($paper['references'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Paper Information</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Pages
                                <span><?php echo $paper['page_count'] ?? 'N/A'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                File Size
                                <span><?php echo formatFileSize($paper['file_size'] ?? 0); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Downloads
                                <span><?php echo $paper['downloads'] ?? 0; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Citations
                                <span><?php echo $paper['citations'] ?? 0; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Published
                                <span><?php echo formatDate($paper['publication_date']); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <?php if (!empty($relatedPapers)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Related Papers</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($relatedPapers as $relatedPaper): ?>
                                    <li class="list-group-item">
                                        <a href="<?php echo config('app.url'); ?>papers/<?php echo $relatedPaper['id']; ?>">
                                            <?php echo htmlspecialchars($relatedPaper['title']); ?>
                                        </a>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($relatedPaper['authors']); ?></p>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Author Information</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($paper['first_name'] . ' ' . $paper['last_name']); ?></h6>
                        <p class="text-muted"><?php echo htmlspecialchars($paper['affiliation']); ?></p>
                        
                        <?php if (!empty($paper['co_authors'])): ?>
                            <hr>
                            <h6>Co-Authors</h6>
                            <ul class="list-unstyled">
                                <?php foreach ($paper['co_authors'] as $coAuthor): ?>
                                    <li class="mb-2">
                                        <strong><?php echo htmlspecialchars($coAuthor['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($coAuthor['affiliation']); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
