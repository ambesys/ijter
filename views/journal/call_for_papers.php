

<div class="container my-4">
    <h1><?php echo htmlspecialchars($cfpDetails['cfp_title']); ?></h1>

    <?php if ($cfpDetails['cfp_active']): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="cfp-content">
                    <?php echo nl2br(htmlspecialchars($cfpDetails['cfp_content'])); ?>
                </div>

                <?php if (!empty($cfpDetails['cfp_topics'])): ?>
                    <div class="topics-section mt-4">
                        <h3>Topics of Interest</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <?php foreach ($cfpDetails['cfp_topics'] as $topic): ?>
                                        <li><i class="fas fa-check-circle text-success"></i> <?php echo htmlspecialchars($topic); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="important-dates mt-4">
                    <h3>Important Dates</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <?php if ($cfpDetails['cfp_deadline']): ?>
                                    <tr>
                                        <th>Submission Deadline</th>
                                        <td><?php echo date('F j, Y', strtotime($cfpDetails['cfp_deadline'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if ($cfpDetails['cfp_notification_date']): ?>
                                    <tr>
                                        <th>Notification of Acceptance</th>
                                        <td><?php echo date('F j, Y', strtotime($cfpDetails['cfp_notification_date'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if ($cfpDetails['cfp_camera_ready_date']): ?>
                                    <tr>
                                        <th>Camera-ready Submission</th>
                                        <td><?php echo date('F j, Y', strtotime($cfpDetails['cfp_camera_ready_date'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if ($cfpDetails['cfp_publication_date']): ?>
                                    <tr>
                                        <th>Publication Date</th>
                                        <td><?php echo date('F j, Y', strtotime($cfpDetails['cfp_publication_date'])); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="submission-button mt-4">
                    <a href="<?php echo config('app.url'); ?>paper/submit" class="btn btn-primary btn-lg">Submit Your Paper</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>There is currently no active call for papers. Please check back later.</p>
        </div>
    <?php endif; ?>
</div>