<?php
// otp_helper.php - Shared OTP logic for registration and password reset
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;

function generate_otp() {
    return rand(100000, 999999);
}

function hash_otp($otp) {
    return password_hash($otp, PASSWORD_DEFAULT);
}

function verify_otp_hash($entered, $hash) {
    return password_verify($entered, $hash);
}

function send_otp_email($to_email, $to_name, $otp, $purpose = 'registration') {
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
    $apiInstance = new TransactionalEmailsApi(null, $config);

    $sender = new SendSmtpEmailSender([
        'name' => 'EliteFit Gym',
        'email' => 'junyappteam@gmail.com'
    ]);
    $to = [new SendSmtpEmailTo([
        'email' => $to_email,
        'name' => $to_name
    ])];

    $subject = $purpose === 'registration' ? 'Your EliteFit Verification Code' : 'EliteFit Password Reset Code';
    $htmlContent = '<div style="font-family:Poppins,Arial,sans-serif;background:#f4f6fb;padding:32px 0;">'
        . '<div style="max-width:430px;margin:0 auto;background:white;border-radius:12px;box-shadow:0 2px 8px rgba(30,60,114,0.10);padding:32px 24px;text-align:center;">'
        . '<div style="font-size:2.2rem;margin-bottom:12px;color:#4361ee;"><span style="display:inline-block;background:#eaf0fb;border-radius:50%;padding:16px 20px;"><i style="font-style:normal;font-weight:700;">&#128274;</i></span></div>'
        . '<h2 style="margin:0 0 12px 0;font-size:1.4rem;color:#222;font-weight:600;">'
        . ($purpose === 'registration' ? 'EliteFit Email Verification' : 'EliteFit Password Reset')
        . '</h2>'
        . '<p style="font-size:1rem;color:#444;margin:0 0 18px 0;">'
        . ($purpose === 'registration'
            ? 'Thank you for joining EliteFit! Please use the code below to verify your email address.'
            : 'You requested to reset your password. Please use the code below to proceed. If you did not request this, please ignore this email.')
        . '</p>'
        . '<div style="font-size:2.2rem;letter-spacing:0.4rem;font-weight:700;color:#4361ee;background:#f7f9fd;padding:18px 0;margin:0 0 18px 0;border-radius:8px;">'
        . $otp . '</div>'
        . '<div style="font-size:0.96rem;color:#666;">This code will expire in 10 minutes.</div>'
        . '</div>'
        . '<div style="text-align:center;font-size:0.92rem;color:#8898aa;margin-top:24px;">&copy; ' . date('Y') . ' EliteFit Gym</div>'
        . '</div>';

    $sendSmtpEmail = new SendSmtpEmail([
        'sender' => $sender,
        'to' => $to,
        'subject' => $subject,
        'htmlContent' => $htmlContent
    ]);

    try {
        $apiInstance->sendTransacEmail($sendSmtpEmail);
        return true;
    } catch (Exception $e) {
        error_log('Brevo OTP Email Error: ' . $e->getMessage());
        return false;
    }
}

function store_password_otp($pdo, $user_id, $otp_hash, $expires) {
    $stmt = $pdo->prepare("UPDATE users SET password_otp_hash = ?, password_otp_expires = ?, password_otp_attempts = 0 WHERE user_id = ?");
    return $stmt->execute([$otp_hash, $expires, $user_id]);
}

function verify_password_otp($pdo, $user_id, $entered_otp) {
    $stmt = $pdo->prepare("SELECT password_otp_hash, password_otp_expires, password_otp_attempts FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return [false, 'User not found'];
    if ($row['password_otp_attempts'] >= 3) return [false, 'Too many failed attempts.'];
    if (!$row['password_otp_hash'] || !$row['password_otp_expires']) return [false, 'No OTP set.'];
    if (strtotime($row['password_otp_expires']) < time()) return [false, 'OTP expired.'];
    if (!verify_otp_hash($entered_otp, $row['password_otp_hash'])) {
        // Increment attempts
        $pdo->prepare("UPDATE users SET password_otp_attempts = password_otp_attempts + 1 WHERE user_id = ?")->execute([$user_id]);
        return [false, 'Invalid OTP.'];
    }
    // OTP is valid - clear OTP fields
    $pdo->prepare("UPDATE users SET password_otp_hash = NULL, password_otp_expires = NULL, password_otp_attempts = 0 WHERE user_id = ?")->execute([$user_id]);
    return [true, 'OTP verified!'];
}
