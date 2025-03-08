<?php
$pageTitle = 'User Profile | IJTER';
$userDetails = $_SESSION['user_details'] ?? [];
$basicInfo = $userDetails['basic_info'] ?? [];
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

    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <img src="<?= $basicInfo['profile_image_url'] ?? 'assets/img/profile-img.jpg' ?>" alt="Profile" class="rounded-circle">
                        <h2><?= htmlspecialchars($basicInfo['user_fname'] ?? '') ?> <?= htmlspecialchars($basicInfo['user_lname'] ?? '') ?></h2>
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

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">
                        <ul class="nav nav-tabs nav-tabs-bordered">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-settings">Settings</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
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
                                        <?php if($basicInfo['user_mobile_verified']): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8">
                                        <?= htmlspecialchars($basicInfo['user_email'] ?? '') ?>
                                        <?php if($basicInfo['user_email_verified']): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                                <!-- Profile Edit Form -->
                                <form action="<?= Helper::config('app.url') ?>user/update-profile" method="POST" enctype="multipart/form-data">
                                    <!-- Add other form fields similar to the overview section -->
                                    <!-- Include all the fields that can be edited -->
                                    <div class="row mb-3">
                                        <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                                        <div class="col-md-8 col-lg-9">
                                            <img src="<?= $basicInfo['profile_image_url'] ?? 'assets/img/profile-img.jpg' ?>" alt="Profile">
                                            <div class="pt-2">
                                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add more form fields here -->

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Keep the Settings and Change Password tabs as they were -->
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
