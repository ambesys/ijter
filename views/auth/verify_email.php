

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Verify Email</div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$_SESSION['user_details']['basic_info']['user_email_verified']): ?>
                        <div class="alert alert-warning">
                            Your email is not verified. Please check your email for the verification link or request a new one below.
                        </div>
                        
                        <form action="<?php echo config('app.url'); ?>resend-verification" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                        </form>

                        <hr>

                        <form action="<?php echo config('app.url'); ?>verify-code" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="verification_code" class="form-label">Verification Code</label>
                                <input type="text" class="form-control" id="verification_code" name="verification_code" required>
                                <div class="form-text">Enter the verification code sent to your email.</div>
                            </div>

                            <button type="submit" class="btn btn-success">Verify Code</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success">
                            Your email has been verified!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
