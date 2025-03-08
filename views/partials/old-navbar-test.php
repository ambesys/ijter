
<?php 
$BASE_PATH = config('app.url');
?>

<nav id="navmenu" class="navmenu">
    <ul>
        <li><a class="nav-link scrollto <?= activeClass('/$', 'active') ?>" href="<?= $BASE_PATH ?>">Home</a></li>
        <li class="dropdown"><a href="#"><span>About</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="<?= $BASE_PATH ?>about"><span>About Journal</span></a></li>
                <li><a href="<?= $BASE_PATH ?>editorial-board"><span>Editorial Board</span></a></li>
                <li><a href="<?= $BASE_PATH ?>guidelines/ethics"><span>Ethics Policy</span></a></li>
                <li><a href="<?= $BASE_PATH ?>guidelines/peer-review"><span>Peer Review Process</span></a></li>
                <li><a href="<?= $BASE_PATH ?>publication-charges"><span>Publication Charges</span></a></li>
            </ul>
        </li>
        <li class="dropdown"><a href="#"><span>For Authors</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="<?= $BASE_PATH ?>papers/submit">Submit Paper Online</a></li>
                <li><a href="<?= $BASE_PATH ?>guidelines/author">Author Guidelines</a></li>
                <li><a href="<?= $BASE_PATH ?>publication-charges">Processing Charges</a></li>
                <li><a href="<?= $BASE_PATH ?>doi-details">DOI Information</a></li>
                <li><a href="<?= $BASE_PATH ?>payments/pay">Pay Publication Fees</a></li>
            </ul>
        </li>
        <li class="dropdown"><a href="#"><span>Editorial Board</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="<?= $BASE_PATH ?>editorial-board">List of Members</a></li>
                <?php if (isAuthenticated() && (isReviewer() || isEditor() || isAdmin())): ?>
                <li><a href="<?= $BASE_PATH ?>dashboard">Dashboard</a></li>
                <?php else: ?>
                <li><a href="<?= $BASE_PATH ?>join-reviewer">Join as Reviewer</a></li>
                <?php endif; ?>
                <li><a href="<?= $BASE_PATH ?>guidelines/reviewer">Reviewer Guidelines</a></li>
            </ul>
        </li>
        <li><a href="<?= $BASE_PATH ?>papers/published" class="<?= activeClass('/papers/published', 'active') ?>">Published Papers</a></li>
        <li class="dropdown"><a href="#"><span>Downloads</span> <i class="bi bi-chevron-down"></i></a>
            <ul>
                <li><a href="<?= $BASE_PATH ?>downloads/sample-paper">Sample Paper Format</a></li>
                <li><a href="<?= $BASE_PATH ?>downloads/undertaking-form">Undertaking By Authors</a></li>
                <li><a href="<?= $BASE_PATH ?>downloads/sample-certificate">Sample Certificate</a></li>
                <li><a href="<?= $BASE_PATH ?>downloads/confirmation-letter">Sample Publication Letter</a></li>
                <li><a href="<?= $BASE_PATH ?>downloads/hardcopy-covers">Sample Hardcopy of Journal</a></li>
            </ul>
        </li>
        <li><a href="<?= $BASE_PATH ?>contact" class="<?= activeClass('/contact', 'active') ?>">Contact</a></li>
        
        <?php if (isAuthenticated()): ?>
        <li class="dropdown"><a href="#"><span>My Account</span> <i class="bi bi-chevron-down"></i></a>
            <ul>
                <li><a href="<?= $BASE_PATH ?>dashboard">Dashboard</a></li>
                <li><a href="<?= $BASE_PATH ?>users/profile">Profile</a></li>
                <li><a href="<?= $BASE_PATH ?>auth/logout">Logout</a></li>
            </ul>
        </li>
        <?php else: ?>
        <li><a href="<?= $BASE_PATH ?>auth/login" class="<?= activeClass('/auth/login', 'active') ?>">Login</a></li>
        <li><a href="<?= $BASE_PATH ?>auth/register" class="<?= activeClass('/auth/register', 'active') ?>">Register</a></li>
        <?php endif; ?>
    </ul>
    <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
</nav>
