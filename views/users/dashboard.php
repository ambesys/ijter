<?php
$pageTitle = 'User Profile | IJTER';
$userDetails = $_SESSION['user_details'] ?? [];
$basicInfo = $userDetails['basic_info'] ?? [];
// Get flash message if exists
$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']); // Clear the flash message after getting it
?>


<main id="main" class="main">
  <div class="pagetitle">
    <h1>Home</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/user">Dashboard</a></li>
        <!-- <li class="breadcrumb-item active">Dashboard</li> -->
      </ol>
    </nav>

  </div>

  <!-- In views/users/dashboard.php, at the top of the content area -->
  <?php if (!$_SESSION['user_details']['basic_info']['user_email_verified']): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      Your email address is not verified.
      <a href="<?php echo config('app.url'); ?>verify-email" class="btn btn-secondary">Click here to verify your email</a>.
      <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
    </div>
  <?php endif; ?>




  <!-- Add this section for flash messages right after main opening tag -->
  <?php if ($flashMessage): ?>
    <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
      <?= $flashMessage['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="row profile">
    <div class="col-xl-4">
      <div class="card">
        <div class="card-body profile-card pt-4 d-flex flex-row align-items-left ">
          <img src="<?= Helper::config('app.url') ?>uploads/users/<?= !empty($basicInfo['user_profile_image'])
              ? $basicInfo['user_profile_image']
              : 'user-profile-image.jpg' ?>" alt="Profile" class="rounded-circle" style="width: 100px; height: 100px;">
          <div class="d-flex flex-column align-items-center" style="margin-left: 20px;">
            <h2><?= htmlspecialchars($basicInfo['user_fname'] ?? '') ?>
              <?= htmlspecialchars($basicInfo['user_lname'] ?? '') ?>
            </h2>
            <h3><?= htmlspecialchars($basicInfo['user_designation'] ?? '') ?></h3>
            <!-- show button for edit profile page -->
            <a href="<?= Helper::config('app.url') ?>user/profile" class="btn btn-primary">Edit Profile</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xxl-2 col-md-6 col-xs-6">
      <div class="card info-card sales-card">
        <div class="card-body">
          <h5 class="card-title">My Papers</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-journal-text"></i>
            </div>
            <div class="ps-3">
              <h6><?= count($userDetails['papers'] ?? []) ?></h6>
              <span class="text-muted small pt-2">Total Submissions</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xxl-2 col-md-6 col-xs-6">
      <div class="card info-card revenue-card">
        <div class="card-body">
          <h5 class="card-title">Reviews</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <div class="ps-3">
              <h6><?= count($userDetails['reviews'] ?? []) ?></h6>
              <span class="text-muted small pt-2">Pending Reviews</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xxl-4 col-md-6 col-xs-12">
      <div class="card info-card user-card">
        <div class="card-body">
          <h5 class="card-title
                    ">Call for Papers</h5>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <!-- <th>Journal</th> -->
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($userDetails['call_for_papers'] ?? [] as $call): ?>
                  <tr>
                    <!-- <td><?= htmlspecialchars($call['journal_name']) ?></td> -->
                    <td><?= date('M d, Y', strtotime($call['submission_deadline'])) ?></td>
                    <td>
                      <span class="badge bg-success">Open</span>
                    </td>
                    <td>
                      <a href="<?= Helper::config('app.url') ?>papers/submit/<?= $call['journal_id'] ?>"
                        class="btn btn-sm btn-primary">Submit Paper</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>



  <!-- Papers Section with Tabs -->
  <div class="row">
    <div class="col-md-6 col-xs-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Author Snapshot</h5>
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#unpublished">
                Unpublished Papers
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#published">
                Published Papers
              </a>
            </li>
          </ul>
          <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="unpublished">
              <!-- Unpublished Papers Table -->
              <?php
              $unpublishedPapers = array_filter($userDetails['papers'] ?? [], function ($paper) {
                return $paper['status_name'] !== 'PUBLISHED';
              });
              ?>
              <?php if (!empty($unpublishedPapers)): ?>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($unpublishedPapers as $paper): ?>
                        <tr>
                          <td><?= htmlspecialchars($paper['paper_title']) ?></td>
                          <td>
                            <span class="badge bg-<?= $paper['status_name'] === 'UNDER_REVIEW' ? 'warning' : 'info' ?>">
                              <?= htmlspecialchars($paper['status_name']) ?>
                            </span>
                          </td>
                          <td><?= date('M d, Y', strtotime($paper['created_at'])) ?></td>
                          <td>
                            <a href="<?= Helper::config('app.url') ?>papers/view/<?= $paper['paper_id'] ?>"
                              class="btn btn-sm btn-info">View</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <p class="text-muted mt-3">No unpublished papers found.</p>
              <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="published">
              <!-- Published Papers Table -->
              <?php
              $publishedPapers = array_filter($userDetails['papers'] ?? [], function ($paper) {
                return $paper['status_name'] === 'PUBLISHED';
              });
              ?>
              <?php if (!empty($publishedPapers)): ?>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Title</th>
                        <th>Journal</th>
                        <th>Published Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($publishedPapers as $paper): ?>
                        <tr>
                          <td><?= htmlspecialchars($paper['paper_title']) ?></td>
                          <td><?= htmlspecialchars($paper['journal_name']) ?></td>
                          <td><?= date('M d, Y', strtotime($paper['published_at'])) ?></td>
                          <td>
                            <a href="<?= Helper::config('app.url') ?>papers/view/<?= $paper['paper_id'] ?>"
                              class="btn btn-sm btn-info">View</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <p class="text-muted mt-3">No published papers found.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Reviews Section -->
    <?php if ($basicInfo['user_roles'] === 4): ?>


      <!-- if (!empty($userDetails['reviews']) and  -->
      <div class="col-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Reviewer Snapshot</h5>
            <div class="table-responsive">

              <?php if (empty($userDetails['reviews'])): ?>
                <p class="text-muted mt-3">You currently have no pending reviews assigned. Please check back later or
                  contact the administrator for more information.</p>

              <?php else: ?>
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Paper Title</th>
                      <th>Author</th>
                      <th>Assigned Date</th>
                      <th>Due Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($userDetails['reviews'] as $review): ?>
                      <tr>
                        <td><?= htmlspecialchars($review['paper_title']) ?></td>
                        <td><?= htmlspecialchars($review['author_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($review['assigned_date'])) ?></td>
                        <td><?= date('M d, Y', strtotime($review['due_date'])) ?></td>
                        <td>
                          <a href="<?= Helper::config('app.url') ?>reviews/submit/<?= $review['review_id'] ?>"
                            class="btn btn-sm btn-primary">Review</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="col-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Pending Reviews</h5>
            <p class="text-muted
                        ">No pending reviews found.</p>
          </div>
        </div>
      </div>

      <?php
      var_dump($basicInfo);
    endif; ?>

  </div>
  <!-- Call for Papers Section -->
  <div class="row">
    <!-- Add this after your existing cards in the statistics section -->
    <div class="col-xxl-4 col-md-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">
            <i class="bi bi-google me-2"></i>Google Scholar Metrics
            <a href="https://scholar.google.com/citations?user=ETXHFAcAAAAJ" target="_blank" class="float-end">
              <i class="bi bi-box-arrow-up-right"></i>
            </a>
          </h5>
        </div>
        <div class="card-body">
          <div class="d-flex flex-column">
            <div class="scholar-stat d-flex justify-content-between align-items-center mb-3">
              <span>Citations</span>
              <span class="badge bg-primary rounded-pill">2,534</span>
            </div>
            <div class="scholar-stat d-flex justify-content-between align-items-center mb-3">
              <span>h-index</span>
              <span class="badge bg-success rounded-pill">27</span>
            </div>
            <div class="scholar-stat d-flex justify-content-between align-items-center mb-3">
              <span>i10-index</span>
              <span class="badge bg-info rounded-pill">64</span>
            </div>
            <div class="mt-3">
              <small class="text-muted">Last updated: <?= date('F j, Y') ?></small>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>