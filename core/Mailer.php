<?php
// core/mailer.php

/**
 * Simple function to send emails using PHP's built-in mail() function
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @return bool Whether the email was sent successfully
 */
function send_email($to, $subject, $message) {
    // Get config
    $config = require_once ROOT_PATH . '/config/config.php';
    
    // Set up headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
    // From header
    $from_name = isset($config['mail']['from_name']) ? $config['mail']['from_name'] : 'IJTER Journal';
    $from_email = isset($config['mail']['from_address']) ? $config['mail']['from_address'] : 'noreply@ijter.org';
    $headers .= "From: {$from_name} <{$from_email}>" . "\r\n";
    
    // Send email
    $result = mail($to, $subject, $message, $headers);
    
    // Log if email fails
    if (!$result) {
        error_log("Failed to send email to: $to, Subject: $subject");
    }
    
    return $result;
}
