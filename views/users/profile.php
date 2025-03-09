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
    <h1>Profile</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/user">Home</a></li>
        <li class="breadcrumb-item active">Profile</li>
      </ol>
    </nav>
  </div>

 <!-- Verification Alert Banners -->
 <?php if (!$basicInfo['user_email_verified']): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i>
      Your email address is not verified.
      <a href="<?= Helper::config('app.url') ?>users/verify-email" class="alert-link" style="text-decoration:underline">Click here to verify your email</a>.
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

  <section class="section profile">
    <div class="row">
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
              <div class="social-links mt-2">
                <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="col-xl-8">
        <div class="card">
          <div class="card-body pt-3">
            <ul class="nav nav-tabs nav-tabs-bordered">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab"
                  data-bs-target="#profile-overview">Overview</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
              </li>
              <!-- <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-settings">Settings</button>
              </li> -->
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change
                  Password</button>
              </li>
            </ul>

            <div class="tab-content pt-2">
              <div class="tab-pane fade show active profile-overview" id="profile-overview">
                <h5 class="card-title">About</h5>
                <p class="small fst-italic">
                  <?= htmlspecialchars($basicInfo['user_about_me'] ?? 'No bio available.') ?>
                </p>

                <h5 class="card-title">Profile Details</h5>
                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Full Name</div>
                  <div class="col-lg-9 col-md-8">
                    <?= htmlspecialchars($basicInfo['user_prefixname'] ?? '') ?>
                    <?= htmlspecialchars($basicInfo['user_fname'] ?? '') ?>
                    <?= htmlspecialchars($basicInfo['user_mname'] ?? '') ?>
                    <?= htmlspecialchars($basicInfo['user_lname'] ?? '') ?>
                  </div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Designation</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($basicInfo['user_designation'] ?? '') ?></div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Institution</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($basicInfo['user_institution'] ?? '') ?></div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Country</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($basicInfo['user_country'] ?? '') ?></div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Address</div>
                  <div class="col-lg-9 col-md-8"><?= htmlspecialchars($basicInfo['full_address'] ?? '') ?></div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Phone</div>
                  <div class="col-lg-9 col-md-8">
                    <?= htmlspecialchars($basicInfo['full_phone_number'] ?? '') ?>
                    <?php if ($basicInfo['user_mobile_verified']): ?>
                      <span class="badge bg-success">Verified</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Email</div>
                  <div class="col-lg-9 col-md-8">
                    <?= htmlspecialchars($basicInfo['user_email'] ?? '') ?>
                    <?php if ($basicInfo['user_email_verified']): ?>
                      <span class="badge bg-success">Verified</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                <!-- Profile Edit Form -->
                <form action="<?= Helper::config('app.url') ?>user/profile" method="POST" enctype="multipart/form-data">
                  <?= Helper::csrfField() ?>

                  <!-- Profile Image -->
                  <div class="row mb-3">
                    <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                    <div class="col-md-8 col-lg-9">
                      <img src="<?= Helper::config('app.url') ?>uploads/users/<?= !empty($basicInfo['user_profile_image'])
                          ? $basicInfo['user_profile_image']
                          : 'user-profile-image.jpg' ?>" alt="Profile" class="rounded-circle">
                      <div class="pt-2">
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        <small class="text-muted">Allowed formats: JPG, PNG. Max size: 2MB</small>
                      </div>
                    </div>
                  </div>

                  <!-- Personal Information -->
                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Title</label>
                    <div class="col-md-8 col-lg-9">
                      <select name="user_prefixname" class="form-select">
                        <?php
                        $titles = ['Mr', 'Ms', 'Mrs', 'Dr', 'Prof'];
                        foreach ($titles as $title):
                          $selected = ($basicInfo['user_prefixname'] ?? '') === $title ? 'selected' : '';
                          ?>
                          <option value="<?= $title ?>" <?= $selected ?>><?= $title ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">First Name</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_fname" type="text" disabled class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_fname'] ?? '') ?>" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Middle Name</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_mname" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_mname'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Last Name</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_lname" type="text" disabled class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_lname'] ?? '') ?>" required>
                    </div>
                  </div>

                  <!-- Contact Information -->
                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Email</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_email" type="email" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_email'] ?? '') ?>" required
                        <?= $basicInfo['user_email_verified'] ? 'readonly' : '' ?>>
                      <?php if ($basicInfo['user_email_verified']): ?>
                        <small class="text-success"><i class="bi bi-check-circle"></i> Verified</small>
                      <?php else: ?>
                        <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Not verified</small>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Phone</label>
                    <div class="col-md-8 col-lg-9">
                      <div class="input-group">
                        <select name="user_countryCode" class="form-select" style="max-width: 120px;">
                          <?php foreach (Helper::getCountryCodes() as $code => $name): ?>
                            <option value="<?= $code ?>" <?= ($basicInfo['user_countryCode'] ?? '') === $code ? 'selected' : '' ?>>
                              +<?= $code ?> (<?= $name ?>)
                            </option>
                          <?php endforeach; ?>
                        </select>
                        <input name="user_mobile" type="text" class="form-control"
                          value="<?= htmlspecialchars($basicInfo['user_mobile'] ?? '') ?>">
                      </div>
                      <?php if ($basicInfo['user_mobile_verified']): ?>
                        <small class="text-success"><i class="bi bi-check-circle"></i> Verified</small>
                      <?php else: ?>
                        <small class="text-warning"><i class="bi bi-exclamation-circle"></i> Not verified</small>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Professional Information -->
                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Designation</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_designation" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_designation'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Institution</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_institution" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_institution'] ?? '') ?>">
                    </div>
                  </div>

                  <!-- Address Information -->
                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Address Line 1</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_address_line1" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_address_line1'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Address Line 2</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_address_line2" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_address_line2'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">City</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_city" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_city'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">State/Province</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_state" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_state'] ?? '') ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">Country</label>
                    <div class="col-md-8 col-lg-9">
                      <select name="user_country" class="form-select">
                        <?php foreach (Helper::getCountries() as $code => $name): ?>
                          <option value="<?= $code ?>" <?= ($basicInfo['user_country'] ?? '') === $code ? 'selected' : '' ?>>
                            <?= $name ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">PIN/ZIP Code</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="user_pin_code" type="text" class="form-control"
                        value="<?= htmlspecialchars($basicInfo['user_pin_code'] ?? '') ?>">
                    </div>
                  </div>

                  <!-- About Me -->
                  <div class="row mb-3">
                    <label class="col-md-4 col-lg-3 col-form-label">About Me</label>
                    <div class="col-md-8 col-lg-9">
                      <textarea name="user_about_me" class="form-control"
                        rows="5"><?= htmlspecialchars($basicInfo['user_about_me'] ?? '') ?></textarea>
                    </div>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>

              <div class="tab-pane fade pt-3" id="profile-change-password">
                <form action="<?= Helper::config('app.url') ?>user/profile/change-password" method="POST"
                  enctype="multipart/form-data">
                  <?= Helper::csrfField() ?>

                  <div class="row mb-3">
                    <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="currentPassword" type="password" class="form-control" id="currentPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="newPassword" type="password" class="form-control" id="newPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="renewPassword" type="password" class="form-control" id="renewPassword" required>
                    </div>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                  </div>
                </form>
              </div>
              <div class="tab-pane fade pt-3" id="profile-change-password">
                <form action="<?= Helper::config('app.url') ?>user/change-password" method="POST">
                  <?= Helper::csrfField() ?>

                  <div class="row mb-3">
                    <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="currentPassword" type="password" class="form-control" id="currentPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="newPassword" type="password" class="form-control" id="newPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="renewPassword" type="password" class="form-control" id="renewPassword" required>
                    </div>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                  </div>
                </form>
              </div>
              <div class="tab-pane fade pt-3" id="profile-change-password">
                <form action="<?= Helper::config('app.url') ?>user/change-password" method="POST">
                  <?= Helper::csrfField() ?>

                  <div class="row mb-3">
                    <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="currentPassword" type="password" class="form-control" id="currentPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="newPassword" type="password" class="form-control" id="newPassword" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="renewPassword" type="password" class="form-control" id="renewPassword" required>
                    </div>
                  </div>

                  <div class="text-center">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                  </div>
                </form>
              </div>

              <!-- <div class="tab-pane fade pt-3" id="profile-settings">
                <h5 class="card-title">Settings</h5>
                <div class="row">
                  <div class="col-lg-3 col-md-4 label">Email Notifications</div>
                  <div class="col-lg-9 col-md-8">

                  </div>
                </div>
              </div> -->


              <!-- Keep the Settings and Change Password tabs as they were -->


            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>