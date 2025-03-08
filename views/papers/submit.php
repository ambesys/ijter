<?php
$pageTitle = 'Submit Paper | Research Journal';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Sidebar -->
            <div class="list-group">
                <a href="<?= config('app.url') ?>dashboard" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="<?= config('app.url') ?>submit-paper" class="list-group-item list-group-item-action active">Submit Paper</a>
                <a href="<?= config('app.url') ?>apply-reviewer" class="list-group-item list-group-item-action">Apply as Reviewer</a>
                <a href="<?= config('app.url') ?>download-formats" class="list-group-item list-group-item-action">Download Paper Formats</a>
                <a href="<?= config('app.url') ?>profile" class="list-group-item list-group-item-action">Update Profile</a>
            </div>
        </div>
        <div class="col-md-9">
            <!-- Main Content -->
            <h4>Submit Your Paper</h4>
            <form action="<?= config('app.url') ?>submit-paper" method="post" enctype="multipart/form-data">
                <?= Helper::csrfField() ?>
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="abstract" class="form-label">Abstract</label>
                    <textarea class="form-control" id="abstract" name="abstract" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="keywords" class="form-label">Keywords</label>
                    <input type="text" class="form-control" id="keywords" name="keywords" required>
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">Upload Paper</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
