<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - EliteFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6e9ff;
            --secondary: #3f37c9;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --white: #ffffff;
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--white);
        }

        .logout-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            border-radius: var(--radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logout-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            animation: pulse 6s infinite linear;
            z-index: -1;
        }

        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logout-icon {
            font-size: 0;
            margin: 0 auto 2rem;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .logout-icon::before {
            content: '🚪';
            font-size: 60px;
            display: block;
            animation: doorOpen 2s ease-in-out infinite;
        }

        @keyframes doorOpen {
            0%, 100% { transform: rotateY(0deg); }
            50% { transform: rotateY(60deg); }
        }

        .logout-icon::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid var(--white);
            border-radius: 50%;
            animation: ripple 2s ease-out infinite;
            opacity: 0;
        }

        @keyframes ripple {
            0% { transform: scale(0.8); opacity: 0.7; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        h2 {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            background: linear-gradient(to right, #fff, #e6e9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .countdown {
            font-size: 1.1rem;
            margin: 1.5rem 0;
            opacity: 0.9;
        }

        #countdown {
            font-weight: 600;
            color: var(--warning);
        }

        .redirect-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            text-decoration: none;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            font-weight: 500;
        }

        .redirect-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .progress-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            margin-top: 2rem;
            overflow: hidden;
        }

        .progress {
            height: 100%;
            width: 100%;
            background: var(--warning);
            animation: progress 5s linear forwards;
            transform-origin: left;
        }

        @keyframes progress {
            0% { transform: scaleX(0); }
            100% { transform: scaleX(1); }
        }

        @media (max-width: 600px) {
            .logout-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon"></div>
        <h2>Logging You Out</h2>
        <div class="countdown">
            Redirecting in <span id="countdown">5</span> seconds...
        </div>
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
        <a href="index.php" class="redirect-link">
            Click here if not redirected
        </a>
    </div>

    <script>
        // Countdown Timer
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = "index.php";
            }
        }, 1000);
    </script>
</body>
</html>