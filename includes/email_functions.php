<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/mail_config.php';

/**
 * Sends an email using SMTP authentication.
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email content (HTML supported)
 * @param bool $isHtml Whether the body is HTML (default: true)
 * @return bool True on success, false on failure
 */
function send_smtp_email($to, $subject, $body, $isHtml = true) {
    if (empty($to)) return false;

    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;         // Enable verbose debug output (uncomment for testing)
        $mail->isSMTP();                                 // Send using SMTP
        $mail->Host       = SMTP_HOST;                   // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                        // Enable SMTP authentication
        $mail->Username   = SMTP_USER;                   // SMTP username
        $mail->Password   = SMTP_PASS;                   // SMTP password
        $mail->SMTPSecure = SMTP_SECURE;                 // Enable implicit TLS encryption
        $mail->Port       = SMTP_PORT;                   // TCP port to connect to

        // --- Recipients ---
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);                          // Add a recipient

        // --- Content ---
        $mail->isHTML($isHtml);                          // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if (!$isHtml) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
