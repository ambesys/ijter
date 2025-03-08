<?php
// Set cache control headers
$cacheTimeout = 300; // 5 minutes
header('Cache-Control: private, must-revalidate, max-age=' . $cacheTimeout);
header('Pragma: private');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTimeout) . ' GMT');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Research Journal' ?></title>

    <!-- Meta tags -->
    <meta name="description" content="<?= $pageDescription ?? 'Research Journal for academic publications' ?>">
    <meta name="keywords" content="<?= $pageKeywords ?? 'research, journal, academic, publication' ?>">

    <!-- Favicons -->
    <link href="<?= config('app.url') ?>assets/img/favicon.png" rel="icon">
    <link href="<?= config('app.url') ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="<?= config('app.url') ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="<?= config('app.url') ?>assets/css/main.css" rel="stylesheet">
    <link href="<?= config('app.url') ?>assets/css/custom.css" rel="stylesheet">

    <?= $extraHead ?? '' ?>
</head>

<body class="index-page">
    <!-- Header -->
    <?php include ROOT_PATH . '/views/partials/header.php'; ?>

   

    <!-- Main Content -->
    <main class="main">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="container">
                <?= displayFlashMessage() ?>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer dark-background">
        <?php include ROOT_PATH . '/views/partials/footer.php'; ?>
    </footer>

    <!-- Scroll Top Button -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Preloader -->
    <!-- <div id="preloader"></div> -->

    
    <!-- Vendor JS Files -->
    <script src="<?= config('app.url') ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/aos/aos.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="<?= config('app.url') ?>assets/vendor/php-email-form/validate.js"></script>

  <!-- Main JS File -->
  <script src="<?= config('app.url') ?>assets/js/main.js"></script>
   

    <?= $extraScripts ?? '' ?>
</body>

</html>