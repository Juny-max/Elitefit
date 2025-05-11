<?php
session_start();

if (!isset($_SESSION['otp_data'])) {
    header("Location: register.php");
    exit();
}

// Check if OTP is expired
if (strtotime($_SESSION['otp_data']['expiry']) < time()) {
    unset($_SESSION['otp_data']);
    header("Location: register.php?error=OTP expired. Please register again.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    
    if ($entered_otp == $_SESSION['otp_data']['otp']) {
        // OTP verified - complete registration
        include_once "config.php";
        
        $user_data = $_SESSION['otp_data']['user_data'];
        
        // Insert user into database
        $sql = "INSERT INTO users (first_name, last_name, email, contact_number, password_hash, role, profile_picture, gender, location, date_of_birth) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", 
            $user_data['first_name'], 
            $user_data['last_name'], 
            $user_data['email'], 
            $user_data['contact_number'], 
            $user_data['password_hash'], 
            $user_data['role'], 
            $user_data['profile_picture'], 
            $user_data['gender'], 
            $user_data['location'], 
            $user_data['date_of_birth']
        );
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Insert fitness data if member
            if ($user_data['role'] === 'member') {
                $fitness_data = $user_data['fitness_data'];
                
                // Insert basic fitness info
                $fitness_sql = "INSERT INTO member_fitness 
                               (member_id, height, weight, body_type, experience_level, health_conditions) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $fitness_stmt = $conn->prepare($fitness_sql);
                $fitness_stmt->bind_param("iddsss", $user_id, 
                    $fitness_data['height'], 
                    $fitness_data['weight'], 
                    $fitness_data['body_type'], 
                    $fitness_data['experience_level'], 
                    $fitness_data['health_conditions']
                );
                $fitness_stmt->execute();
                
                // Insert workout preferences
                if (!empty($fitness_data['workout_preferences'])) {
                    $preference_order = 1;
                    foreach ($fitness_data['workout_preferences'] as $plan_id) {
                        if ($preference_order > 3) break;
                        
                        $pref_sql = "INSERT INTO member_workout_preferences 
                                    (member_id, plan_id, preference_order) 
                                    VALUES (?, ?, ?)";
                        $pref_stmt = $conn->prepare($pref_sql);
                        $pref_stmt->bind_param("iii", $user_id, $plan_id, $preference_order);
                        $pref_stmt->execute();
                        $preference_order++;
                    }
                }
                
                // Insert fitness goals
                if (!empty($fitness_data['fitness_goals'])) {
                    $goals = explode("\n", $fitness_data['fitness_goals']);
                    foreach ($goals as $goal) {
                        if (trim($goal)) {
                            $goal_sql = "INSERT INTO fitness_goals 
                                        (member_id, goal_text) 
                                        VALUES (?, ?)";
                            $goal_stmt = $conn->prepare($goal_sql);
                            $goal_text = trim($goal);
                            $goal_stmt->bind_param("is", $user_id, $goal_text);
                            $goal_stmt->execute();
                        }
                    }
                }
            }
            
            // Clear session and redirect to login
            unset($_SESSION['otp_data']);
echo '<script>window.otpVerified = true;</script>';
// header("Location: index.php?registration=success");
// exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - EliteFit Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Add your existing styles here */
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }

        .otp-container {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .otp-container h1 {
            color: #3a0ca3;
            margin-bottom: 1.5rem;
        }

        .otp-form {
            margin-top: 2rem;
        }

        .otp-input {
            font-size: 1.5rem;
            letter-spacing: 0.5rem;
            text-align: center;
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .btn {
            background: #4361ee;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #3a0ca3;
        }

        .error-message {
            color: #ef233c;
            margin-bottom: 1rem;
        }

        .resend-link {
            margin-top: 1rem;
            display: block;
            color: #4361ee;
            cursor: pointer;
        }


        .success-modal {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.3);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  visibility: hidden;
  opacity: 0;
  transition: opacity 0.3s;
}
.success-modal.active {
  visibility: visible;
  opacity: 1;
}
.success-popup {
  background: #fff;
  padding: 2rem 2.5rem 1.5rem 2.5rem;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(30,60,114,0.15);
  text-align: center;
  min-width: 300px;
}
.success-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 1.2rem auto;
  display: flex;
  align-items: center;
  justify-content: center;
}
.success-icon-circle {
  background: #4BB543;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.success-icon-check {
  color: #fff;
  font-size: 2.5rem;
}
.success-message {
  color: #222;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}
    </style>
</head>
<body>
    <div class="otp-container">
        <h1><i class="fas fa-shield-alt"></i> OTP Verification</h1>
        <p>We've sent a 6-digit verification code to your email address.</p>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form class="otp-form" method="POST">
            <input type="text" name="otp" class="otp-input" placeholder="Enter 6-digit code" maxlength="6" required>
            <button type="submit" class="btn">Verify</button>
        </form>
        
        <a href="#" class="resend-link" id="resendOtp">Resend OTP</a>
        <small id="countdown">OTP expires in 10:00</small>
    </div>

    <div class="success-modal" id="successModal">
  <div class="success-popup">
    <div class="success-icon">
      <div class="success-icon-circle">
        <i class="fas fa-check success-icon-check"></i>
      </div>
    </div>
    <div class="success-message">You've been verified</div>
  </div>
</div>

    <script>
        // Countdown timer
        let minutes = 9;
        let seconds = 59;
        
        function updateCountdown() {
            document.getElementById('countdown').textContent = 
                `OTP expires in ${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            
            if (seconds === 0) {
                if (minutes === 0) {
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }
            
            setTimeout(updateCountdown, 1000);
        }
        
        updateCountdown();
        
        // Resend OTP functionality
        document.getElementById('resendOtp').addEventListener('click', function(e) {
            e.preventDefault();
            
            fetch('resend_otp.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('New OTP sent to your email');
                        minutes = 9;
                        seconds = 59;
                        updateCountdown();
                    } else {
                        alert('Failed to resend OTP: ' + data.message);
                    }
                });
        });

        window.addEventListener('DOMContentLoaded', function() {
  if (window.otpVerified) {
    const modal = document.getElementById('successModal');
    modal.classList.add('active');
    setTimeout(function() {
      modal.classList.remove('active');
      window.location.href = 'index.php?registration=success';
    }, 3000);
  }
});
    </script>
</body>
</html>