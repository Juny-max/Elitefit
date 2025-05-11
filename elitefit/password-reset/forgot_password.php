<?php
// forgot_password.php - User requests password reset OTP
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/otp_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'Please enter your email.';
    } else {
        // Connect DB
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        } catch (PDOException $e) {
            die('Database error: ' . $e->getMessage());
        }
        // Find user
        $stmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $error = 'No account found with that email.';
        } else {
            $otp = generate_otp();
            $otp_hash = hash_otp($otp);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            store_password_otp($pdo, $user['user_id'], $otp_hash, $expires);
            $send_result = send_otp_email($email, $user['first_name'] . ' ' . $user['last_name'], $otp, 'forgot');
            if (!$send_result) {
                $error = 'Failed to send OTP email. Please contact support.';
            } else {
                $_SESSION['fp_user_id'] = $user['user_id'];
                $_SESSION['fp_email'] = $email;
                header('Location: verify_otp.php');
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - EliteFit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Forgot Password</div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
