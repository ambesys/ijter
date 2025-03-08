<?php
// views/auth/register.php
$layout = 'main';
$pageTitle = 'Register | Research Journal';
$authTitle = 'Create Account';

// Get journal details
$journalModel = model('journal');
$journalDetails = $journalModel->getJournalDetails();
?>

<section class="bg-light py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <h2 class="mb-4">Join Our Research Community</h2>
            </div>
        </div>
</section>


<section id="services" class="services section publication-process">
    <div class="container">
        <div class="row">

            <div class="col-lg-3 col-xs-6">
                <div class="service-item">
                    <div class="text-center mb-2"> <!-- Reduced margin bottom -->
                        <i class="bi bi-journal-text text-primary" style="font-size: 2rem;"></i>
                        <!-- Reduced icon size -->
                        <h6 class="mt-1 mb-1">Publish Your Research</h6>
                        <!-- Changed from h5 to h6 and reduced margins -->
                        <p class="text-muted small mb-0">Submit your papers to our peer-reviewed journal
                        </p>
                        <!-- Added small class -->
                    </div>
                </div>
            </div><!-- End Service Item -->

            <div class="col-lg-3 col-xs-6">
                <div class="service-item">
                    <div class="text-center mb-2">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-1 mb-1">Join Expert Network</h6>
                        <p class="text-muted small mb-0">Connect with researchers worldwide</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="service-item ">
                    <div class="text-center mb-2">
                        <i class="bi bi-star text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-1 mb-1">Advance Your Career</h6>
                        <p class="text-muted small mb-0">Gain recognition in your field</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="service-item  position-relative">
                    <div class="text-center mb-2">
                        <i class="bi bi-book text-primary" style="font-size: 2rem;"></i>
                        <h6 class="mt-1 mb-1">Access Knowledge</h6>
                        <p class="text-muted small mb-0">Explore a vast library of research papers</p>
                    </div>
                </div>


            </div>
        </div>
</section>



<div class="container">
    <!-- Introduction Section -->
    <!-- Update this section in your register.php -->


    <!-- Registration Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <!-- <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle-fill"></i> Important Information</h5>
                        <ul class="mb-0">
                            <li>Please ensure all required fields (*) are completed</li>
                            <li>You will receive a confirmation email after registration</li>
                            <li>Author and reviewer applications will be reviewed by our editorial team</li>
                            <li>For technical support, contact our help desk at support@journal.com</li>
                        </ul>
                    </div> -->

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= config('app.url') ?>register" method="post">
                        <?= Helper::csrfField() ?>

                        <!-- Personal Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="prefix_name" class="form-label">Title</label>
                                <select class="form-select" id="prefix_name" name="prefix_name">
                                    <option value="">Select Title</option>
                                    <option value="Dr">Dr.</option>
                                    <option value="Prof">Prof.</option>
                                    <option value="Mr">Mr.</option>
                                    <option value="Mrs">Mrs.</option>
                                    <option value="Ms">Ms.</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                            <div class="col-md-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="form-text">This will be your login username</div>
                            </div>
                            <div class="col-md-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                    minlength="8">
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            <div class="col-md-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" required>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <!-- <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="designation" class="form-label">Designation</label>
                                <input type="text" class="form-control" id="designation" name="designation"
                                    placeholder="e.g. Associate Professor">
                            </div>
                            <div class="col-md-6">
                                <label for="institution" class="form-label">Institution/Organization</label>
                                <input type="text" class="form-control" id="institution" name="institution"
                                    placeholder="e.g. University Name">
                            </div>
                        </div> -->

                        <!-- Additional Information -->
                        <!-- <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department"
                                    placeholder="e.g. Computer Science">
                            </div>
                            <div class="col-md-6">
                                <label for="research_interests" class="form-label">Research Interests</label>
                                <input type="text" class="form-control" id="research_interests"
                                    name="research_interests" placeholder="e.g. Machine Learning, AI">
                            </div>
                        </div> -->

                        <!-- Role Selection -->
                        <div class="row g-3 mb-4">
                            <!-- <div class="col-md-6 d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_author" name="is_author">
                                    <label class="form-check-label" for="is_author">
                                        Register as Author
                                        <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                            title="Select this if you want to submit papers for publication"></i>
                                    </label>

                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_reviewer" name="is_reviewer">
                                    <label class="form-check-label" for="is_reviewer">
                                        Apply as Reviewer
                                        <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                            title="Select this if you want to review submitted papers"></i>
                                    </label>
                                </div>
                            </div> -->

                          

                            <div class="col-md-6">
                                <label for="captcha" class="form-label">Security Code *</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="captcha" name="captcha" required>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="captcha-container">
                                            <img src="<?= config('app.url') ?>captcha" alt="CAPTCHA"
                                                class="captcha-image">
                                            <button type="button" class="btn btn-sm btn-secondary refresh-captcha">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-text">Enter the code shown in the image</div>
                            </div>
                        </div>
                        <!-- Replace the existing terms-section in your register.php with this -->
                        <div class="terms-section mb-4">
                            <h6 class="border-bottom pb-2 mb-2">Terms & Conditions</h6>
                            <div class="terms-content bg-light p-2"
                                style="max-height: 200px; overflow-y: auto; font-size: 11px; line-height: 1.2;">
                                <!-- <p class="mb-1"><strong>Last Updated: <?= date('F d, Y') ?></strong></p> -->

                                <p class="mb-1"><strong>1. ACCEPTANCE OF TERMS</strong></p>
                                <p class="mb-2">By accessing and using the International Journal of Technology &
                                    Emerging Research ("IJTER"), ISSN: 1234-5678 ("Service"), you acknowledge that you
                                    have read, understood, and agree to be bound by these Terms and Conditions
                                    ("Terms"). If you do not agree to these Terms, do not use the Service.</p>

                                <p class="mb-1"><strong>2. ACCOUNT REGISTRATION AND SECURITY</strong></p>
                                <p class="mb-2">2.1. Users must provide accurate, current, and complete information
                                    during registration.<br>
                                    2.2. Users are responsible for maintaining the confidentiality of their account
                                    credentials.<br>
                                    2.3. Any unauthorized use of accounts must be reported immediately.<br>
                                    2.4. We reserve the right to suspend or terminate accounts violating these Terms.
                                </p>

                                <p class="mb-1"><strong>3. PUBLICATION ETHICS AND MALPRACTICE STATEMENT</strong></p>
                                <p class="mb-2">3.1. Authors must:<br>
                                    - Submit only original work.<br>
                                    - Properly cite all sources.<br>
                                    - Disclose conflicts of interest.<br>
                                    - Not submit the same manuscript to multiple journals simultaneously.<br>
                                    - Ensure all co-authors have approved the manuscript.<br>
                                    3.2. Reviewers must:<br>
                                    - Maintain confidentiality of manuscripts.<br>
                                    - Disclose conflicts of interest.<br>
                                    - Provide objective and timely reviews.<br>
                                    - Not use information from manuscripts for personal advantage.</p>

                                <p class="mb-1"><strong>4. COPYRIGHT, LICENSING, AND INTELLECTUAL PROPERTY</strong></p>
                                <p class="mb-2">4.1. Authors retain copyright and grant IJTER the right of first
                                    publication.<br>
                                    4.2. Articles are licensed under the Creative Commons Attribution License (CC BY
                                    4.0).<br>
                                    4.3. Authors warrant that their submission does not infringe on existing
                                    copyrights.<br>
                                    4.4. AI-generated content must be explicitly disclosed in submissions.<br>
                                    4.5. Authors indemnify IJTER against any copyright-related disputes.</p>

                                <p class="mb-1"><strong>5. PEER REVIEW PROCESS</strong></p>
                                <p class="mb-2">5.1. All submissions undergo double-blind peer review.<br>
                                    5.2. Editorial decisions are final and binding.<br>
                                    5.3. Authors must address all reviewer comments in revisions.<br>
                                    5.4. IJTER reserves the right to reject submissions at any stage.</p>

                                <p class="mb-1"><strong>6. DATA PROTECTION AND PRIVACY</strong></p>
                                <p class="mb-2">6.1. Personal data is processed in accordance with our Privacy
                                    Policy.<br>
                                    6.2. User information may be used for managing accounts, processing submissions, and
                                    journal communications.<br>
                                    6.3. Appropriate security measures are implemented to protect user data.</p>

                                <p class="mb-1"><strong>7. PUBLICATION FEES AND REFUND POLICY</strong></p>
                                <p class="mb-2">7.1. Authors are responsible for applicable publication fees.<br>
                                    7.2. Fee waivers may be available in certain circumstances.<br>
                                    7.3. Fees are non-refundable once the publication process begins, except in cases of
                                    administrative errors.</p>

                                <p class="mb-1"><strong>8. MISCONDUCT, RETRACTION, AND CORRECTIONS</strong></p>
                                <p class="mb-2">8.1. IJTER reserves the right to retract articles that:<br>
                                    - Contain plagiarism or fraudulent data.<br>
                                    - Violate ethical guidelines.<br>
                                    - Have undisclosed conflicts of interest.<br>
                                    8.2. Authors found guilty of misconduct may be banned from future submissions.<br>
                                    8.3. Authors may request post-publication corrections in case of errors.</p>

                                <p class="mb-1"><strong>9. LIMITATION OF LIABILITY</strong></p>
                                <p class="mb-2">9.1. The Service is provided "as is" without warranties of any kind.<br>
                                    9.2. IJTER is not liable for any damages arising from the use of the Service.<br>
                                    9.3. Users indemnify IJTER against claims arising from their use of the Service.</p>

                                <p class="mb-1"><strong>10. GOVERNING LAW AND DISPUTE RESOLUTION</strong></p>
                                <p class="mb-2">10.1. These Terms are governed by the laws of [Specify
                                    Jurisdiction].<br>
                                    10.2. Any disputes shall be resolved through arbitration before litigation.<br>
                                    10.3. Courts in [Specify Jurisdiction] shall have exclusive jurisdiction.</p>

                                <p class="mb-1"><strong>11. MODIFICATIONS TO TERMS</strong></p>
                                <p class="mb-2">11.1. IJTER reserves the right to modify these Terms at any time.<br>
                                    11.2. Continued use of the Service constitutes acceptance of modified Terms.</p>

                                <p class="mb-1"><strong>12. TERMINATION</strong></p>
                                <p class="mb-2">12.1. IJTER may terminate or suspend access to the Service immediately
                                    for violations.<br>
                                    12.2. All provisions of these Terms which should survive termination shall remain in
                                    effect.</p>

                                <p class="mb-1"><strong>13. ADVERTISING, SPONSORSHIPS, AND THIRD-PARTY LINKS</strong>
                                </p>
                                <p class="mb-2">13.1. IJTER may display advertisements and sponsored content.<br>
                                    13.2. IJTER is not responsible for third-party websites or content.<br>
                                    13.3. Users engaging with third-party links do so at their own risk.</p>

                                <p class="mb-1"><strong>14. CONTACT INFORMATION</strong></p>
                                <p class="mb-2">For questions about these Terms, contact:<br>
                                    General Inquiries: <a href="mailto:editor@ijter.org">editor@ijter.org</a><br>
                                    Legal Matters: <a href="mailto:legal@ijter.org">legal@ijter.org</a></p>
                            </div>



                            <!-- E-Signature Consent -->
                            <div class="e-signature-section mt-2">
                                <div class="form-check" style="font-size: 12px;">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I acknowledge that I have read, understood, and agree to be bound by the above
                                        Terms and Conditions. I understand this constitutes a legally binding agreement.
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                    <i class="bi bi-info-circle"></i>
                                    Electronic Signature Confirmation | IP: <?= $_SERVER['REMOTE_ADDR'] ?> | Date:
                                    <?= date('Y-m-d H:i:s T') ?>
                                </small>
                            </div>
                        </div>


                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Already have an account? <a href="<?= config('app.url') ?>login">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>