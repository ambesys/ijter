<?php
$pageTitle = 'Dashboard | Research Journal';
$userDetails = $_SESSION['user_details'] ?? [];
$basicInfo = $userDetails['basic_info'] ?? [];
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Helper::config('app.url') ?>">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div>
  <pre>
    <code>
    <?php print_r($userDetails) ?>
    </code>
  </pre>

  <!-- Statistics Cards -->
  <div class="row">
    <div class="col-xxl-3 col-md-6">
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

    <div class="col-xxl-3 col-md-6">
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
    <div class="col-xxl-6 col-md-6">
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

  <!-- Call for Papers Section -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Call for Papers</h5>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Journal</th>
                  <th>Deadline</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($userDetails['call_for_papers'] ?? [] as $call): ?>
                  <tr>
                    <td><?= htmlspecialchars($call['journal_name']) ?></td>
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
    <div class="col-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">My Papers</h5>
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
    <?php if (!empty($userDetails['reviews'])): ?>

      <div class="col-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Pending Reviews</h5>
            <div class="table-responsive">
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
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</main>