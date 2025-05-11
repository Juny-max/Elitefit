<?php
include_once "config.php";

// At the start of login_process.php, before any output
session_start();
session_regenerate_id(true); // Prevent session fixation
session_unset(); // Clear any existing session data

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        header("Location: index.php?error=Email and password are required");
        exit();
    }
    
    // Get user from database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, start session
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            



            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'trainer':
                    header("Location: trainer/dashboard.php");
                    break;
                case 'equipment_manager':
                    header("Location: equipment/equipment_manager_dashboard.php");
                    break;
                default: // member
                    header("Location: member/dashboard.php");
            }
            exit();
        }
    }
    
    // If we get here, login failed
    header("Location: index.php?error=Invalid email or password");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
