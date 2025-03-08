<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Mobile auth buttons -->
            <div class="align-items-center me-2 mobile-only">
                <?php if ($user): ?>
                    <a href="<?= config('app.url') ?>user/dashboard" class="btn btn-sm btn-outline-light me-2">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="<?= config('app.url') ?>logout" class="btn btn-sm btn-light">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= config('app.url') ?>login" class="btn btn-sm btn-outline-light me-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= config('app.url') ?>register" class="btn btn-sm btn-light">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= activeClass('/', 'active') ?>" href="<?= config('app.url') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeClass('/about', 'active') ?>" href="<?= config('app.url') ?>about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeClass('/call-for-papers', 'active') ?>" href="<?= config('app.url') ?>call-for-papers">Call for Papers</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="authorDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            For Authors
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="authorDropdown">
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>guidelines/author">Author Guidelines</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>user/paper/submit">Submit Paper</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>publication-charges">Publication Charges</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>doi-details">DOI Details</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeClass('/editorial-board', 'active') ?>" href="<?= config('app.url') ?>editorial-board">Editorial Board</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="papersDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Published Papers
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="papersDropdown">
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>papers">Current Issue</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>papers?archive=1">Archive</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>papers?type=conference">Conference Papers</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>papers?type=thesis">Thesis</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="downloadsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Downloads
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="downloadsDropdown">
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>downloads/sample-paper">Sample Paper Format</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>downloads/undertaking-form">Undertaking Form</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>downloads/sample-certificate">Sample Certificate</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>downloads/confirmation-letter">Sample Confirmation Letter</a></li>
                            <li><a class="dropdown-item" href="<?= config('app.url') ?>downloads/hardcopy-covers">Sample Hardcopy Covers</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeClass('/contact', 'active') ?>" href="<?= config('app.url') ?>contact">Contact</a>
                    </li>
                </ul>
                <form class="d-flex" action="<?= config('app.url') ?>papers/search" method="get">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search papers..." aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
