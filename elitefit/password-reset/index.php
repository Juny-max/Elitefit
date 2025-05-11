<?php
session_start();
include_once __DIR__ . "/../config.php";

// Database connection (PDO)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection not established: " . $e->getMessage());
}

if (!isset($pdo)) {
    die("Database connection not established");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    try {
        // Check if email exists in users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate reset token (for email option)
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiration
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);
            
            // For direct reset (without email):
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_role'] = $user['role'];
            header("Location: reset-password.php");
            exit();
            
            /* For email option (uncomment to use):
            $resetLink = "https://yourdomain.com/password-reset/reset-password.php?token=$token";
            include 'send-reset-email.php';
            $_SESSION['message'] = "Password reset link has been sent to your email.";
            header("Location: forgot-password.php");
            exit();
            */
        } else {
            $_SESSION['error'] = "No account found with that email address.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Reset Your Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                            <a href="../index.php" class="btn btn-link">Back to Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>