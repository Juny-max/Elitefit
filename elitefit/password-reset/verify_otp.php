<?php
// verify_otp.php - User enters OTP sent to email for password reset
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/otp_helper.php';

if (!isset($_SESSION['fp_user_id'])) {
    header('Location: forgot_password.php');
    exit();
}

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$error = '';
$locked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');
    list($valid, $msg) = verify_password_otp($pdo, $_SESSION['fp_user_id'], $entered_otp);
    if ($valid) {
        $_SESSION['fp_verified'] = true;
        header('Location: reset_password.php');
        exit();
    } else {
        $error = $msg;
        if ($msg === 'Too many failed attempts.') {
            $locked = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - EliteFit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Enter OTP</div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!$locked): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="otp" class="form-label">6-digit Code</label>
                            <input type="text" class="form-control" id="otp" name="otp" maxlength="6" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
