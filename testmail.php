<?php
require_once 'bootstrap.php';

$result = send_email(
    'patelsarthakr@gmail.com',
    'Test Email',
    '<h1>Test Email</h1><p>This is a test email to verify the email configuration.</p>'
);

var_dump($result);
