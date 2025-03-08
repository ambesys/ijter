<?php
// views/partials/flash_messages.php

$flashMessage = getFlashMessage();

if ($flashMessage):
    $alertClass = 'alert-info';
    
    switch ($flashMessage['type']) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
    }
?>
<div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
    <?= $flashMessage['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
