<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - EliteFit Gym</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--white);
        }

        .error-container {
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

        .error-container::before {
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

        .error-icon {
            font-size: 0;
            margin: 0 auto 2rem;
            position: relative;
            width: 80px;
            height: 80px;
        }

        .error-icon::before {
            content: '❌';
            font-size: 60px;
            display: block;
            animation: shake 0.5s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-icon::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid var(--danger);
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

        .error-message {
            font-size: 1.1rem;
            margin: 1.5rem 0;
            opacity: 0.9;
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

        .error-details {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(255, 0, 0, 0.1);
            border-radius: var(--radius);
            font-size: 0.9rem;
            text-align: left;
            display: none; /* Hidden by default */
        }

        .show-details {
            display: block;
            margin-top: 1rem;
            background: none;
            border: none;
            color: var(--warning);
            cursor: pointer;
            font-size: 0.8rem;
        }

        @media (max-width: 600px) {
            .error-container {
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
    <div class="error-container">
        <div class="error-icon"></div>
        <h2>Something Went Wrong</h2>
        
        <div class="error-message">
            <?php 
            if (isset($_SESSION['error_message'])) {
                echo htmlspecialchars($_SESSION['error_message']);
            } else {
                echo "An unexpected error occurred. Please try again later.";
            }
            ?>
        </div>
        
        <a href="index.php" class="redirect-link">
            Return to Login Page
        </a>
        
        <button class="show-details" onclick="toggleDetails()">Show Technical Details</button>
        
        <div class="error-details" id="errorDetails">
            <?php 
            if (isset($_SESSION['error_details'])) {
                echo nl2br(htmlspecialchars($_SESSION['error_details']));
            } else {
                echo "No additional error details available.";
            }
            ?>
        </div>
    </div>

    <script>
        function toggleDetails() {
            const details = document.getElementById('errorDetails');
            const button = document.querySelector('.show-details');
            
            if (details.style.display === 'block') {
                details.style.display = 'none';
                button.textContent = 'Show Technical Details';
            } else {
                details.style.display = 'block';
                button.textContent = 'Hide Technical Details';
            }
        }
    </script>
</body>
</html>