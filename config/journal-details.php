<?php
// config/journal-details.php

// Fetch journal details from database
function getJournalDetails() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM journal_details WHERE is_active = 1 LIMIT 1");
        $journalDetails = $stmt->fetch();
        
        if (!$journalDetails) {
            // Fallback values if no journal details found
            return [
                'journal_name' => 'International Journal',
                'journal_abbreviation' => 'IJ',
                'journal_issn' => 'XXXX-XXXX',
                'journal_website' => 'https://example.com',
                'journal_email' => 'contact@example.com',
                'support_email' => 'support@example.com',
                'support_phone' => 'N/A',
                'support_hours' => 'Monday to Friday, 9:00 AM - 5:00 PM',
                'payment_currency' => 'USD',
                'publication_fee' => 100.00,
                'bank_name' => 'Bank Name',
                'account_name' => 'Account Name',
                'account_number' => 'Account Number',
                'swift_code' => 'SWIFT Code',
                'ifsc_code' => 'IFSC Code',
                'paypal_email' => 'paypal@example.com',
                'payment_support_email' => 'payment@example.com'
            ];
        }
        
        return $journalDetails;
    } catch (PDOException $e) {
        error_log('Error fetching journal details: ' . $e->getMessage());
        
        // Return fallback values on error
        return [
            'journal_name' => 'International Journal',
            'journal_abbreviation' => 'IJ',
            'journal_issn' => 'XXXX-XXXX',
            'journal_website' => 'https://example.com',
            'journal_email' => 'contact@example.com',
            'support_email' => 'support@example.com',
            'support_phone' => 'N/A',
            'support_hours' => 'Monday to Friday, 9:00 AM - 5:00 PM',
            'payment_currency' => 'USD',
            'publication_fee' => 100.00,
            'bank_name' => 'Bank Name',
            'account_name' => 'Account Name',
            'account_number' => 'Account Number',
            'swift_code' => 'SWIFT Code',
            'ifsc_code' => 'IFSC Code',
            'paypal_email' => 'paypal@example.com',
            'payment_support_email' => 'payment@example.com'
        ];
    }
}

// Get journal details
$journalDetails = getJournalDetails();

// Define constants based on journal details
define('JOURNAL_NAME', $journalDetails['journal_name']);
define('JOURNAL_ABBREVIATION', $journalDetails['journal_abbreviation']);
define('JOURNAL_ISSN', $journalDetails['journal_issn']);
define('JOURNAL_WEBSITE', $journalDetails['journal_website']);
define('JOURNAL_EMAIL', $journalDetails['journal_email']);
define('SUPPORT_EMAIL', $journalDetails['support_email']);
define('SUPPORT_PHONE', $journalDetails['support_phone']);
define('SUPPORT_HOURS', $journalDetails['support_hours']);
define('PAYMENT_CURRENCY', $journalDetails['payment_currency']);
define('PUBLICATION_FEE', $journalDetails['publication_fee']);
define('BANK_NAME', $journalDetails['bank_name']);
define('ACCOUNT_NAME', $journalDetails['account_name']);
define('ACCOUNT_NUMBER', $journalDetails['account_number']);
define('SWIFT_CODE', $journalDetails['swift_code']);
define('IFSC_CODE', $journalDetails['ifsc_code']);
define('PAYPAL_EMAIL', $journalDetails['paypal_email']);
define('PAYMENT_SUPPORT_EMAIL', $journalDetails['payment_support_email']);
