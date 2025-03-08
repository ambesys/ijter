

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['success']; ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= config('app.url') ?>login" method="POST">
                        <?= Helper::csrfField() ?>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>


                        <div class="mb-3">
                            <label for="captcha" class="form-label">Security Code</label>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control" id="captcha" name="captcha" required>
                                <div class="captcha-container">
                                    <img src="<?= config('app.url') ?>captcha" alt="CAPTCHA" class="captcha-image">
                                    <button type="button" class="btn btn-sm btn-secondary refresh-captcha">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>

                                </div>
                            </div>
                        </div>


                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="<?= config('app.url') ?>auth/forgot-password" class="btn btn-link">Forgot
                                Password?</a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Don't have an account? <a href="<?= config('app.url') ?>auth/register">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
