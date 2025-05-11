<?php
include_once __DIR__ . "/../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords don't match";

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, contact_number, password_hash, role) 
                                   VALUES (?, ?, ?, ?, ?, 'equipment_manager')");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $contact_number, $password_hash);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
                header("Location: ../index.php?success=" . urlencode($success));
                exit();
            } else {
                $errors[] = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Manager Registration - EliteFit Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #fff;
            overflow-x: hidden;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #vanta-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
        }
        .registration-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 44px 36px 36px 36px;
            border-radius: 28px;
            box-shadow: 0 12px 36px rgba(30,60,114,0.18), 0 2px 8px rgba(30,60,114,0.12);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1.5px solid #e3e6ee;
            transition: box-shadow 0.25s, border 0.25s;
            z-index: 2;
            margin: 40px 0;
        }
        .registration-outer {
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-title {
            color: #222;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 500;
            text-align: left;
        }
        .form-control:focus {
            border-color: #6C63FF;
            box-shadow: 0 0 0 2px #6C63FF33;
        }
        .btn-primary {
            background: linear-gradient(90deg, #4361ee 0%, #2a5298 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2a5298 0%, #4361ee 100%);
        }
        @media (max-width: 600px) {
            body {
                align-items: flex-start;
            }
            .registration-container {
                padding: 24px 6vw 24px 6vw;
                border-radius: 18px;
                margin: 24px 0;
            }
            .form-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div id="vanta-bg"></div>
    <div class="registration-outer">
        <div class="registration-container">
            <h2 class="form-title">Equipment Manager Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>Already have an account? <a href="../index.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Vanta.js & Three.js for animated background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
    <script>
      VANTA.WAVES({
        el: "#vanta-bg",
        color: 0x1e3c72,
        shininess: 50,
        waveHeight: 20,
        waveSpeed: 1.2,
        zoom: 0.85
      });
    </script>
</body>
</html>