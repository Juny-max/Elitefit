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
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow: hidden;
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
    <div id="vanta-bg" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:-2;"></div>
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