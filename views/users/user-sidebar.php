<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?= Helper::activeClass('/user/dashboard') ?>" href="<?= config('app.url') ?>user/dashboard">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Author Section -->
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#author-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-journal-text"></i><span>Author Center</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="author-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="<?= config('app.url') ?>user/submissions/new">
                        <i class="bi bi-circle"></i><span>Submit New Manuscript</span>
                    </a>
                </li>
                <li>
                    <a href="<?= config('app.url') ?>user/submissions">
                        <i class="bi bi-circle"></i><span>My Submissions</span>
                    </a>
                </li>
                <li>
                    <a href="<?= config('app.url') ?>author/guidelines">
                        <i class="bi bi-circle"></i><span>Author Guidelines</span>
                    </a>
                </li>
                <li>
                    <a href="<?= config('app.url') ?>author/templates">
                        <i class="bi bi-circle"></i><span>Manuscript Templates</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reviewer Section -->
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#reviewer-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-search"></i><span>Reviewer Center</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="reviewer-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="<?= config('app.url') ?>user/reviews/pending">
                        <i class="bi bi-circle"></i><span>Pending Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="<?= config('app.url') ?>user/reviews/completed">
                        <i class="bi bi-circle"></i><span>Completed Reviews</span>
                    </a>
                </li>
                <li>
                    <a href="<?= config('app.url') ?>reviewer/guidelines">
                        <i class="bi bi-circle"></i><span>Reviewer Guidelines</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Resources -->
        <li class="nav-heading">Resources</li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= config('app.url') ?>resources/faq">
                <i class="bi bi-question-circle"></i>
                <span>FAQ</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= config('app.url') ?>resources/tutorials">
                <i class="bi bi-play-circle"></i>
                <span>Video Tutorials</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= config('app.url') ?>resources/downloads">
                <i class="bi bi-download"></i>
                <span>Downloads</span>
            </a>
        </li>

        <!-- Profile & Settings -->
        <li class="nav-heading">Account</li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= config('app.url') ?>user/profile">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= config('app.url') ?>user/settings">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
</aside>
