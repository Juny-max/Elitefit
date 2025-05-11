<?php
session_start();

// Use correct path for config.php and create PDO connection
include_once __DIR__ . '/../config.php';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit();
}

$email = $_POST['email'];
$role = $_POST['role'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate passwords match
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: reset-password.php");
    exit();
}

// Validate password strength
if (strlen($password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long.";
    header("Location: reset-password.php");
    exit();
}

try {
    // Hash the new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password in users table
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
    $stmt->execute([$password_hash, $email]);
    
    // Clear reset session
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_role']);
    
    $_SESSION['success'] = "Your password has been updated successfully!";
    header("Location: reset-password.php?success=1");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating password: " . $e->getMessage();
    header("Location: reset-password.php");
    exit();
}
?>