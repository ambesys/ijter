<?php
// views/layouts/dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard | Research Journal' ?></title>
    
    
    <!-- Bootstrap CSS -->
    <link href="<?= config('app.url') ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/css/styles.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- SB Admin 2 CSS -->
    <!-- <link href="<?= config('app.url') ?>assets/vendor/sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet"> -->
    
    <!-- Custom styles -->
   <link href="<?= config('app.url') ?>assets/css/main.css" rel="stylesheet">
    <link id="theme-stylesheet" rel="stylesheet" href="<?= config('app.url') ?>assets/css/themes/<?= $_COOKIE['theme'] ?? 'default' ?>.css">

    

    
    <!-- Favicon -->
    <?php if (isset($journalDetails) && !empty($journalDetails['journal_favicon'])): ?>
    <link rel="icon" href="<?= config('app.url') ?>uploads/<?= $journalDetails['journal_favicon'] ?>" type="image/x-icon">
    <?php endif; ?>
    
    <?= $extraHead ?? '' ?>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include ROOT_PATH . '/views/partials/sidebar.php'; ?>
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="<?= config('app.url') ?>papers/search" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" name="q" placeholder="Search for papers..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search" action="<?= config('app.url') ?>papers/search" method="get">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" name="q" placeholder="Search for papers..." aria-label="Search">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>
                        
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <?php
                            $notificationModel = model('notification');
                            $unreadCount = $notificationModel->countUnreadNotifications(getCurrentUserId());
                            ?>
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <?php if ($unreadCount > 0): ?>
                                <span class="badge badge-danger badge-counter"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Notifications
                                </h6>
                                <?php
                                $notifications = $notificationModel->getNotificationsByUserId(getCurrentUserId(), 5, 0, true);
                                if (!empty($notifications)):
                                    foreach ($notifications as $notification):
                                ?>
                                <a class="dropdown-item d-flex align-items-center" href="<?= config('app.url') ?>users/notifications/mark-read/<?= $notification['notification_id'] ?>">
                                    <div class="mr-3">
                                        <?php
                                        $iconClass = 'fa-file-alt';
                                        $bgClass = 'bg-primary';
                                        
                                        switch ($notification['notification_type']) {
                                            case 'PAPER_SUBMITTED':
                                            case 'PAPER_PUBLISHED':
                                                $iconClass = 'fa-file-alt';
                                                $bgClass = 'bg-primary';
                                                break;
                                            case 'PAPER_ACCEPTED':
                                                $iconClass = 'fa-check-circle';
                                                $bgClass = 'bg-success';
                                                break;
                                            case 'PAPER_REJECTED':
                                                $iconClass = 'fa-times-circle';
                                                $bgClass = 'bg-danger';
                                                break;
                                            case 'REVIEW_ASSIGNED':
                                            case 'REVIEW_SUBMITTED':
                                                $iconClass = 'fa-clipboard-list';
                                                $bgClass = 'bg-warning';
                                                break;
                                            case 'PAYMENT_RECEIVED':
                                                $iconClass = 'fa-dollar-sign';
                                                $bgClass = 'bg-success';
                                                break;
                                            case 'PAYMENT_FAILED':
                                                $iconClass = 'fa-exclamation-circle';
                                                $bgClass = 'bg-danger';
                                                break;
                                            default:
                                                $iconClass = 'fa-bell';
                                                $bgClass = 'bg-info';
                                        }
                                        ?>
                                        <div class="icon-circle <?= $bgClass ?>">
                                            <i class="fas <?= $iconClass ?> text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?= formatDateTime($notification['notification_created_at']) ?></div>
                                        <span class="font-weight-bold"><?= $notification['notification_message'] ?></span>
                                    </div>
                                </a>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-light">
                                            <i class="fas fa-bell-slash text-gray-500"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="font-weight-bold">No new notifications</span>
                                    </div>
                                </a>
                                <?php endif; ?>
                                <a class="dropdown-item text-center small text-gray-500" href="<?= config('app.url') ?>users/notifications">Show All Notifications</a>
                            </div>
                        </li>
                        
                        <div class="topbar-divider d-none d-sm-block"></div>
                        
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= getCurrentUserName() ?></span>
                                <img class="img-profile rounded-circle" src="<?= config('app.url') ?>assets/img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?= config('app.url') ?>users/profile">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="<?= config('app.url') ?>users/edit-profile">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="<?= config('app.url') ?>users/activity">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <?= displayFlashMessage() ?>
                    <?php endif; ?>
                    
                    <?= $content ?? '' ?>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; <?= date('Y') ?> <?= $journalDetails['journal_full_name'] ?? 'Research Journal' ?>. All rights reserved.</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= config('app.url') ?>auth/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
    
     <!-- Bootstrap JS (Bundle includes Popper.js) -->
     <script src="assets/js/bootstrap.bundle.min.js"></script>
    
    <?= $extraScripts ?? '' ?>
</body>
</html>
