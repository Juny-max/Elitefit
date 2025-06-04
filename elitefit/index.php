<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Login</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --secondary: #1e40af;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --gray-lighter: #f1f5f9;
            --success: #10b981;
            --danger: #ef4444;
            --white: #ffffff;
            --border-radius: 16px;
            --border-radius-sm: 8px;
            --border-radius-lg: 20px;
            --box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
            --box-shadow-lg: 0 20px 40px rgba(37, 99, 235, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #1e40af;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Elements */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        /* Floating Circles */
        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 15s infinite ease-in-out;
        }

        .floating-circle:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
            animation-duration: 20s;
        }

        .floating-circle:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            left: 80%;
            animation-delay: 5s;
            animation-duration: 25s;
        }

        .floating-circle:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 50%;
            left: 5%;
            animation-delay: 10s;
            animation-duration: 18s;
        }

        .floating-circle:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 20%;
            left: 70%;
            animation-delay: 15s;
            animation-duration: 22s;
        }

        .floating-circle:nth-child(5) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 8s;
            animation-duration: 16s;
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) translateX(0px) rotate(0deg);
                opacity: 0.3;
            }
            25% { 
                transform: translateY(-30px) translateX(20px) rotate(90deg);
                opacity: 0.6;
            }
            50% { 
                transform: translateY(-60px) translateX(-10px) rotate(180deg);
                opacity: 0.4;
            }
            75% { 
                transform: translateY(-20px) translateX(-30px) rotate(270deg);
                opacity: 0.7;
            }
        }

        /* Subtle Grid Pattern */
        .grid-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 30s linear infinite;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Pulsing Dots */
        .pulse-dot {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            animation: pulse 3s infinite ease-in-out;
        }

        .pulse-dot:nth-child(1) {
            top: 15%;
            left: 25%;
            animation-delay: 0s;
        }

        .pulse-dot:nth-child(2) {
            top: 60%;
            left: 75%;
            animation-delay: 1s;
        }

        .pulse-dot:nth-child(3) {
            top: 85%;
            left: 45%;
            animation-delay: 2s;
        }

        .pulse-dot:nth-child(4) {
            top: 30%;
            left: 85%;
            animation-delay: 1.5s;
        }

        .pulse-dot:nth-child(5) {
            top: 75%;
            left: 15%;
            animation-delay: 0.5s;
        }

        @keyframes pulse {
            0%, 100% { 
                opacity: 0.2;
                transform: scale(1);
            }
            50% { 
                opacity: 1;
                transform: scale(3);
            }
        }
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 44px 36px 36px 36px;
            border-radius: 28px;
            box-shadow: 0 12px 36px rgba(30,60,114,0.18), 0 2px 8px rgba(30,60,114,0.12);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1.5px solid #e3e6ee;
            transition: box-shadow 0.25s, border 0.25s;
        }
        .login-container:hover {
            box-shadow: 0 18px 48px rgba(30,60,114,0.22), 0 4px 16px rgba(30,60,114,0.14);
            border: 1.5px solid #6C63FF;
        }
        .logo {
            margin-bottom: 30px;
        }
        .logo h1 {
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            color: #222;
            letter-spacing: 1.5px;
            margin-bottom: 0.3em;
            text-shadow: 0 2px 12px rgba(30,60,114,0.09);
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
        input {
            width: 100%;
            padding: 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            background: #f8fafc;
            transition: border 0.2s, box-shadow 0.2s;
            color: #222;
        }
        input:focus {
            border: 1.5px solid #6C63FF;
            box-shadow: 0 2px 12px rgba(108,99,255,0.10);
            outline: none;
            background: #fff;
        }
        button {
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
        button:hover, button:focus {
            background: #5145cd;
            box-shadow: 0 6px 24px rgba(108,99,255,0.13);
            transform: translateY(-2px) scale(1.01);
        }
        .links {
            margin-top: 18px;
            font-size: 14px;
        }
        .links a {
            color: #6C63FF;
            text-decoration: none;
            border-bottom: none;
            transition: color 0.2s;
        }
        .links a:hover {
            color: #48BB78;
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .login-container {
                padding: 24px 6vw 24px 6vw;
                border-radius: 18px;
            }
            .logo h1 {
                font-size: 1.6rem;
            }
        }
    </style>
    <!-- Vanta.js & Three.js for animated background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
</head>
<body>
    <!-- Animated background elements -->
    <div class="bg-animation">
        <!-- Grid Pattern -->
        <div class="grid-pattern"></div>
        
        <!-- Floating Circles -->
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        
        <!-- Pulsing Dots -->
        <div class="pulse-dot"></div>
        <div class="pulse-dot"></div>
        <div class="pulse-dot"></div>
        <div class="pulse-dot"></div>
        <div class="pulse-dot"></div>
    </div>
    <div class="login-container">
        <div class="logo" style="text-align:center;">
            <h1 style="display:block; margin:0 auto 8px auto;">EliteFit</h1>
            <span style="display:block; margin:0 auto;">
                <svg id="dumbbell-icon" width="220" height="24" viewBox="0 0 220 24" style="vertical-align:middle; filter: drop-shadow(0 2px 8px rgba(108,99,255,0.14));" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="6" width="18" height="12" rx="3" fill="#6C63FF">
                        <animate attributeName="y" values="6;2;6" dur="1s" repeatCount="indefinite"/>
                        <animate attributeName="height" values="12;20;12" dur="1s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="200" y="6" width="18" height="12" rx="3" fill="#6C63FF">
                        <animate attributeName="y" values="6;10;6" dur="1s" repeatCount="indefinite"/>
                        <animate attributeName="height" values="12;6;12" dur="1s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="20" y="10" width="180" height="4" rx="2" fill="#222"/>
                </svg>
            </span>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div style="color: red; margin-bottom: 15px;">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
    <div style="color: green; margin-bottom: 15px;">
        <?= htmlspecialchars($_GET['success']) ?>
    </div>
<?php endif; ?>
        
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="">
            </div>
            
            <button type="submit">Login</button>
            
            <div class="links">
                <a href="./password-reset/forgot_password.php">Forgot Password?</a> | 
                <a href="register.php">Create Account</a>
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
<script>
    // Animated placeholder typing effect
    function typePlaceholder(input, text, speed = 40) {
        input.placeholder = '';
        let i = 0;
        function type() {
            if (i < text.length) {
                input.placeholder += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        }
        type();
    }
    window.onload = function() {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        typePlaceholder(emailInput, 'Enter your email...');
        setTimeout(() => {
            typePlaceholder(passwordInput, 'Enter your password...');
        }, 800);
    };
</script>
</html>