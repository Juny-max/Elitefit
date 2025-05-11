<?php
session_start();
include_once "config.php";
require_once 'vendor/autoload.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;

if (!isset($_SESSION['otp_data'])) {
    header("Location: register.php");
    exit();
}

// Generate new OTP
$otp = rand(100000, 999999);
$otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Update session with new OTP
$_SESSION['otp_data']['otp'] = $otp;
$_SESSION['otp_data']['expiry'] = $otp_expiry;

// Send new OTP via Brevo
$config = Configuration::getDefaultConfiguration()->setApiKey('api-key', 'YOUR_BREVO_API_KEY');
$apiInstance = new TransactionalEmailsApi(null, $config);

$sender = new SendSmtpEmailSender([
    'name' => 'EliteFit Gym',
    'email' => 'no-reply@elitefitgym.com'
]);

$to = [new SendSmtpEmailTo([
    'email' => $_SESSION['otp_data']['email'],
    'name' => $_SESSION['otp_data']['user_data']['first_name'] . ' ' . $_SESSION['otp_data']['user_data']['last_name']
])];

$sendSmtpEmail = new SendSmtpEmail([
    'sender' => $sender,
    'to' => $to,
    'subject' => 'Your New EliteFit Gym Verification Code',
    'htmlContent' => '<p>Dear ' . $_SESSION['otp_data']['user_data']['first_name'] . ',</p>
                    <p>Your new verification code is: <strong>' . $otp . '</strong></p>
                    <p>This code will expire in 10 minutes.</p>
                    <p>Thank you for registering with EliteFit Gym!</p>'
]);

try {
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP. Please try again.']);
}
?>