<?php
include_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    $required = ['first_name', 'last_name', 'email', 'contact_number', 'password', 
                'specialization', 'certification', 'years_experience'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Error: $field is required");
        }
    }
    
    // Check if email already exists
    $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $email_check->bind_param("s", $_POST['email']);
    $email_check->execute();
    $email_check->store_result();
    
    if ($email_check->num_rows > 0) {
        header("Location: register_trainer.php?error=Email+already+exists");
        exit();
    }
    
    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert into users table
        $user_sql = "INSERT INTO users (first_name, last_name, email, contact_number, password_hash, role) 
                    VALUES (?, ?, ?, ?, ?, 'trainer')";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", $_POST['first_name'], $_POST['last_name'], 
                              $_POST['email'], $_POST['contact_number'], $password_hash);
        $user_stmt->execute();
        
        $trainer_id = $conn->insert_id;
        
        // Insert into trainers table
        $trainer_sql = "INSERT INTO trainers (trainer_id, specialization, certification, years_experience, bio)
                       VALUES (?, ?, ?, ?, ?)";
        $trainer_stmt = $conn->prepare($trainer_sql);
        $trainer_stmt->bind_param("issis", $trainer_id, $_POST['specialization'], 
                                 $_POST['certification'], $_POST['years_experience'], $_POST['bio']);
        $trainer_stmt->execute();
        
        $conn->commit();
        header("Location: index.php?success=Trainer+registered+successfully.+Please+login");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: register_trainer.php?error=" . urlencode("Registration failed: " . $e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Registration - EliteFit Gym</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .registration-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 44px 36px;
            border-radius: 32px;
            box-shadow: 0 12px 36px rgba(30,60,114,0.18), 0 2px 8px rgba(30,60,114,0.12);
            width: 100%;
            max-width: 540px;
            margin: 0 auto;
            border: 1.5px solid #e3e6ee;
            transition: box-shadow 0.25s, border 0.25s;
        }
        .registration-container:hover {
            box-shadow: 0 18px 48px rgba(30,60,114,0.22), 0 4px 16px rgba(30,60,114,0.14);
            border: 1.5px solid #6C63FF;
        }
        h2 {
            color: #222;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
            font-size: 2.3rem;
            font-weight: 900;
            text-align: center;
            letter-spacing: 1.5px;
            margin-bottom: 0.5em;
            text-shadow: 0 2px 12px rgba(30,60,114,0.09);
        }
        h3 {
            color: #6C63FF;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 22px;
            text-align: left;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #1e3c72;
            letter-spacing: 0.2px;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 9px;
            font-size: 16px;
            box-sizing: border-box;
            background: #f8fafc;
            transition: border 0.2s, box-shadow 0.2s;
            color: #222;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border: 1.5px solid #6C63FF;
            box-shadow: 0 2px 12px rgba(108,99,255,0.10);
            outline: none;
            background: #fff;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        button[type="submit"] {
            background: #6C63FF;
            color: white;
            border: none;
            padding: 14px 0;
            border-radius: 7px;
            cursor: pointer;
            font-size: 17px;
            width: 100%;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(108,99,255,0.08);
        }
        button[type="submit"]:hover, button[type="submit"]:focus {
            background: #5145cd;
            box-shadow: 0 6px 24px rgba(108,99,255,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #6C63FF;
            text-decoration: none;
            border-bottom: none;
            transition: color 0.2s;
        }
        .login-link a:hover {
            color: #48BB78;
            text-decoration: underline;
        }
        .alert-error {
            background-color: #fde2e2;
            color: #b71c1c;
            border: 1px solid #f5c2c7;
            border-radius: 7px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 1rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 7px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 1rem;
        }
        @media (max-width: 768px) {
            .registration-container {
                padding: 20px 6vw;
                margin: 20px 0;
                border-radius: 18px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
    <!-- Vanta.js & Three.js for animated background -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
</head>
<body>
<div id="vanta-bg" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:-1;"></div>
    <div class="registration-container">
        <h2>Trainer Registration</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars(urldecode($_GET['success'])) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
                <small>Minimum 8 characters</small>
            </div>
            
            <h3>Professional Details</h3>
            
            <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" id="specialization" name="specialization" required>
            </div>
            
            <div class="form-group">
                <label for="certification">Certification</label>
                <input type="text" id="certification" name="certification" required>
            </div>
            
            <div class="form-group">
                <label for="years_experience">Years of Experience</label>
                <input type="number" id="years_experience" name="years_experience" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="bio">Professional Bio</label>
                <textarea id="bio" name="bio"></textarea>
            </div>
            
            <button type="submit">Register as Trainer</button>
            
            <div class="login-link">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </form>
    </div>
</body>
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
</html>