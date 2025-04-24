#!/bin/php
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoload

// === CONFIGURATION ===

// SMTP settings
$smtpHost = 'mail.example.com';
$smtpPort = 587;
$smtpUser = 'your-smtp-user@example.com';
$smtpPass = 'your-smtp-password';
$fromEmail = 'monitor@example.com';
$toEmail = 'monitor-inbox@example.com';

// IMAP settings
$imapHost = 'imap.example.com';
$imapUser = 'your-imap-user@example.com';
$imapPass = 'your-imap-password';
$imapMailbox = 'INBOX';
$imapPort = 993;
$imapEncryption = '/imap/ssl';

// === BulkSMS Config ===
$bulksmsUsername = 'your_username';
$bulksmsPassword = 'your_password'; // or use API token
$smsRecipient = '447712345678'; // international format, no +
$smsSender = 'MailCheck';


//=========================================================================================================================================================

function sendSMSAlert($message, $username, $password, $recipient, $sender) {
    $url = 'https://api.bulksms.com/v1/messages';

    $postData = json_encode([
        'to' => $recipient,
        'body' => $message,
        'from' => $sender
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    // CA bundle location for Windows (required for SSL verification)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '\cacert.pem'); // Put cacert.pem in same directory
    }

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($http_status != 201) {
        echo "❌ Failed to send SMS alert. Status: $http_status. Response: $response Error: $error\n";
    } else {
        echo "📱 SMS alert sent.\n";
    }

    curl_close($ch);
}

// === STEP 1: Generate a unique hash ===
$hash = hash('sha256', uniqid('mailchk_', true));
$subject = 'Mail Server Monitor - ' . date('Y-m-d H:i:s');
$body = "This is a test email from your mail server monitoring script.\nHash: $hash";


// === STEP 2: Send Email using PHPMailer ===
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtpPort;

    $mail->setFrom($fromEmail, 'Mail Monitor');
    $mail->addAddress($toEmail);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
    echo "✅ Email sent.\n";

} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}\n";
    exit(1);
}

// === STEP 3: Wait for a short while before checking ===
sleep(30); // Give it a few seconds to arrive

// === STEP 4: Connect to IMAP and search for the hash ===
$inbox = imap_open("{" . $imapHost . ":" . $imapPort . $imapEncryption . "}" . $imapMailbox, $imapUser, $imapPass);
if (!$inbox) {
    echo "❌ IMAP connection failed: " . imap_last_error() . "\n";
    exit(1);
}

$emails = imap_search($inbox, 'UNSEEN');

if ($emails) {
    rsort($emails); // newest first
    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
        $message = imap_fetchbody($inbox, $email_number, 1);

        if (strpos($message, $hash) !== false) {
            echo "✅ Email received and hash verified.\n";
            imap_close($inbox);
            exit(0);
        }
    }
    echo "⚠️ Email received but hash not found.\n";
} else {
    echo "❌ No new emails found.\n";
    sendSMSAlert("Mail server monitor: email send FAILED", $bulksmsUsername, $bulksmsPassword, $smsRecipient, $smsSender);
}

imap_close($inbox);
exit(1);

