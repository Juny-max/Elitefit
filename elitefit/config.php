<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'E)Pv1V)d[H(hYFn6');
define('DB_NAME', 'elitefit');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this to config.php
define('BREVO_API_KEY', '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'); // Replace with your actual key
?>