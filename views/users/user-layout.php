<?php
// Set cache control headers
$cacheTimeout = 300; // 5 minutes
header('Cache-Control: private, must-revalidate, max-age=' . $cacheTimeout);
header('Pragma: private');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheTimeout) . ' GMT');

$pageTitle = 'Dashboard | IJTER';
$user = $_SESSION['user_fname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Components / Accordion - NiceAdmin Bootstrap Template</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="<?= Helper::config('app.url') ?>assets-user/img/favicon.png" rel="icon">
  <link href="<?= Helper::config('app.url') ?>assets-user/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="<?= Helper::config('app.url') ?>assets-user/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="<?= Helper::config('app.url') ?>assets-user/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
    <?php include ROOT_PATH . '/views/users/user-header.php'; ?>

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php include ROOT_PATH . '/views/users/user-sidebar.php'; ?>
  <!-- End Sidebar-->


  <!-- Main Content -->
  <main class="main">
    <?php if (isset($_SESSION['flash_message'])): ?>
      <div class="container">
        <?= displayFlashMessage() ?>
      </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
  </main>


  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <?php include ROOT_PATH . '/views/users/user-footer.php'; ?>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/chart.js/chart.umd.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/echarts/echarts.min.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/quill/quill.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/tinymce/tinymce.min.js"></script>
  <script src="<?= Helper::config('app.url') ?>assets-user/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="<?= Helper::config('app.url') ?>assets-user/js/main.js"></script>

</body>

</html>