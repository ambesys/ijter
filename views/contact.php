<?php
// views/contact.php
?>

<section class="breadcrumbs">
    <div class="container">
        <ol>
            <li><a href="<?php echo config('app.url'); ?>">Home</a></li>
            <li>Contact</li>
        </ol>
        <h2>Contact</h2>
    </div>
</section>

<section class="contact">
    <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>Contact</h2>
            <h3>Contact <span><?php echo JOURNAL_NAME; ?></span></h3>
            <p>Get in touch with our editorial team for inquiries about submissions, reviews, or general information.</p>
        </div>

        <div class="row" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-6">
                <div class="info-box mb-4">
                    <i class="bx bx-map"></i>
                    <h3>Our Address</h3>
                    <p><?php echo $journalDetails['journal_address'] ?? '123 University Ave<br>Academic City, 12345<br>Country'; ?></p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <i class="bx bx-envelope"></i>
                    <h3>Email Us</h3>
                    <p><?php echo $journalDetails['journal_email'] ?? 'info@example.com'; ?></p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <i class="bx bx-phone-call"></i>
                    <h3>Call Us</h3>
                    <p><?php echo $journalDetails['journal_phone'] ?? '+1 234 567 8900'; ?></p>
                </div>
            </div>
        </div>

        <div class="row" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-6">
                <?php if (!empty($journalDetails['journal_map_embed'])): ?>
                    <div class="mb-4">
                        <?php echo $journalDetails['journal_map_embed']; ?>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <img src="<?php echo config('app.url'); ?>assets/img/map-placeholder.jpg" class="img-fluid" alt="Location Map">
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <form action="<?php echo config('app.url'); ?>contact" method="post" class="php-email-form">
                    <?php echo csrfTokenField(); ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col form-group">
                            <input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                id="name" placeholder="Your Name" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col form-group">
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                name="email" id="email" placeholder="Your Email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                            name="subject" id="subject" placeholder="Subject" value="<?php echo htmlspecialchars($formData['subject'] ?? ''); ?>" required>
                        <?php if (isset($errors['subject'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['subject']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                            name="message" rows="5" placeholder="Message" required><?php echo htmlspecialchars($formData['message'] ?? ''); ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['message']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="my-3">
                        <div class="loading">Loading</div>
                        <div class="error-message"></div>
                        <div class="sent-message">Your message has been sent. Thank you!</div>
                    </div>
                    <div class="text-center"><button type="submit">Send Message</button></div>
                </form>
            </div>
        </div>
    </div>
</section>
