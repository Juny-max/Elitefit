<?php
// reset_password.php - User sets new password after OTP verified
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['fp_verified']) || !isset($_SESSION['fp_user_id'])) {
    header('Location: forgot_password.php');
    exit();
}

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
        $stmt->execute([$hash, $_SESSION['fp_user_id']]);
        unset($_SESSION['fp_verified'], $_SESSION['fp_user_id'], $_SESSION['fp_email']);
        $success = 'Your password has been reset successfully. <a href="../index.php" class="btn btn-success mt-2">Continue to Login</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - EliteFit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Reset Password</div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success text-center"><?= $success ?></div>
                    <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm" name="confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
