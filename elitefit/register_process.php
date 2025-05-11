<?php
include_once "config.php";
require_once 'vendor/autoload.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start session early
    session_start();
    
    // Validate and sanitize inputs
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'member';
    
    // Additional fields
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);

    // Age validation
    $birthDate = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    if ($age < 16) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'You must be at least 16 years old to register']));
    }
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($contact_number) || empty($password)) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'All required fields must be filled']));
    }
    
    // Check if email exists using prepared statement
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Email already exists']));
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            }
        }
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Store data in session
    $_SESSION['otp_data'] = [
        'email' => $email,
        'otp' => $otp,
        'expiry' => $otp_expiry,
        'user_data' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'contact_number' => $contact_number,
            'password_hash' => $password_hash,
            'role' => $role,
            'profile_picture' => $profile_picture,
            'gender' => $gender,
            'location' => $location,
            'date_of_birth' => $date_of_birth,
            'fitness_data' => [
                'height' => !empty($_POST['height']) ? $_POST['height'] : null,
                'weight' => !empty($_POST['weight']) ? $_POST['weight'] : null,
                'body_type' => !empty($_POST['body_type']) ? $_POST['body_type'] : null,
                'experience_level' => !empty($_POST['experience_level']) ? $_POST['experience_level'] : null,
                'health_conditions' => !empty($_POST['health_conditions']) ? $_POST['health_conditions'] : null,
                'workout_preferences' => !empty($_POST['workout_preferences']) ? $_POST['workout_preferences'] : [],
                'fitness_goals' => !empty($_POST['fitness_goals']) ? $_POST['fitness_goals'] : null
            ]
        ]
    ];

    // Send OTP via Brevo
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
    $apiInstance = new TransactionalEmailsApi(null, $config);
    
    $sender = new SendSmtpEmailSender([
        'name' => 'EliteFit Gym',
        'email' => 'junyappteam@gmail.com' // Must be verified in Brevo
    ]);
    
    $to = [new SendSmtpEmailTo([
        'email' => $email,
        'name' => $first_name . ' ' . $last_name
    ])];
    
    $emailContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Your EliteFit Verification Code</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .content {
            padding: 30px;
        }
        
        .otp-code {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
            border: 1px dashed #4361ee;
        }
        
        .otp-code strong {
            font-size: 32px;
            letter-spacing: 5px;
            color: #3a0ca3;
            font-weight: 700;
            display: inline-block;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #3a0ca3, #4361ee);
            color: white !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(58, 12, 163, 0.2);
            transition: all 0.3s ease;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(58, 12, 163, 0.3);
        }
        
        .expiry-notice {
            background: #fff8e6;
            border-left: 4px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e9ecef, transparent);
            margin: 25px 0;
        }
        
        .social-icons {
            margin: 20px 0;
            text-align: center;
        }
        
        .social-icons a {
            display: inline-block;
            margin: 0 10px;
            color: #3a0ca3;
            font-size: 20px;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            transform: translateY(-3px);
            color: #4361ee;
        }
        
        @media (max-width: 600px) {
            .content, .header {
                padding: 20px;
            }
            
            .otp-code strong {
                font-size: 24px;
                letter-spacing: 3px;
            }
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>EliteFit Gym</h1>
        </div>
        
        <div class='content'>
            <h2 style='margin-top: 0; color: #3a0ca3;'>Your Verification Code</h2>
            <p>Hello <strong>{$first_name}</strong>,</p>
            <p>Thank you for registering with EliteFit Gym! Here's your one-time verification code:</p>
            
            <div class='otp-code'>
                <strong>{$otp}</strong>
            </div>
            
            <div class='expiry-notice'>
                ⏳ This code will expire in <strong>10 minutes</strong>. Please use it before then.
            </div>
            
            <p>If you didn't request this code, please ignore this email or contact our support team.</p>
            
            <div class='divider'></div>
            
            <p style='margin-bottom: 5px;'>Need help?</p>
            <a href='mailto:junyappteam@gmail.com' style='color: #4361ee; text-decoration: none;'>support@elitefitgym.com</a>
            
            <div class='social-icons'>
                <a href='#'>📱</a>
                <a href='#'>💻</a>
                <a href='#'>📧</a>
            </div>
        </div>
        
        <div class='footer'>
            <p>© ".date('Y')." EliteFit Gym. All rights reserved.</p>
            <p>East Legon, Boundary Road, Accra, Ghana.</p>
        </div>
    </div>
</body>
</html>
";
    
    try {
        $apiInstance->sendTransacEmail(new SendSmtpEmail([
            'sender' => $sender,
            'to' => $to,
            'subject' => 'Your EliteFit Verification Code',
            'htmlContent' => $emailContent
        ]));
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'otp_required' => true,
            'message' => 'OTP sent to your email'
        ]);
        exit();
    } catch (Exception $e) {
        error_log("Brevo Error: " . $e->getMessage());
        http_response_code(500);
        $errorMsg = $e->getMessage();
        $errorType = 'system';
        // Detect cURL/network errors
        if (stripos($errorMsg, 'cURL error') !== false || stripos($errorMsg, 'Could not resolve host') !== false || stripos($errorMsg, 'Failed to connect') !== false) {
            $errorType = 'network';
            $userMsg = 'Network error: Please check your internet connection and try again.';
        } elseif (stripos($errorMsg, 'brevo') !== false || stripos($errorMsg, 'sendinblue') !== false || stripos($errorMsg, 'API error') !== false || stripos($errorMsg, 'SMTP') !== false || stripos($errorMsg, '429') !== false || stripos($errorMsg, '503') !== false || stripos($errorMsg, '502') !== false || stripos($errorMsg, 'provider') !== false) {
            $errorType = 'brevo';
            $userMsg = 'Our email provider is currently down. Please try again later.';
        } else {
            $userMsg = 'Failed to send OTP. Please try again later.';
        }
        echo json_encode([
            'status' => 'error',
            'error_type' => $errorType,
            'message' => $userMsg
        ]);
        exit();
    }
}
?>