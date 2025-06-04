<?php
include_once "config.php";
require_once 'vendor/autoload.php'; // Add this to load Brevo SDK

// Fetch workout plans for the form
$workout_plans = [];
if ($result = mysqli_query($conn, "SELECT * FROM workout_plans")) {
    while ($row = mysqli_fetch_assoc($result)) {
        $workout_plans[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            overflow-x: hidden;
            position: relative;
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

        /* Glass morphism container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-lg);
            width: 100%;
            max-width: 900px;
            margin: 2rem auto;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
        }

        /* Header Styles */
        h1 {
            color: var(--primary);
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: center;
            justify-content: center;
        }

        h1 i {
            font-size: 2.2rem;
            color: var(--primary-light);
        }

        h2 {
            color: var(--primary);
            font-size: 1.75rem;
            margin-bottom: 2rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 1rem;
            text-align: center;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        /* Form Section Animations */
        .form-section {
            display: none;
            animation: slideInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }

        .form-section.active {
            display: block;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        /* Modern Input Styles */
        input, select, textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            box-sizing: border-box;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            background: var(--white);
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }

        input:hover, select:hover, textarea:hover {
            border-color: var(--primary-light);
            transform: translateY(-1px);
        }

        /* Modern Button Styles */
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--gray);
        }

        .btn-secondary:hover {
            background: var(--dark);
            box-shadow: 0 15px 30px rgba(100, 116, 139, 0.3);
        }

        /* Navigation Buttons */
        .nav-buttons {
            margin-top: 3rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        /* Profile Picture Styles */
        .profile-picture-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1.5rem;
            display: none;
            border: 4px solid var(--primary-light);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
            transition: var(--transition);
        }

        .profile-picture-preview:hover {
            transform: scale(1.05);
            border-color: var(--primary);
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Alert Styles */
        .alert {
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-close {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            color: inherit;
            margin-left: auto;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            padding: 0.25rem;
            border-radius: 50%;
        }

        .alert-close:hover { 
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        /* Multi-select Dropdown */
        .multiselect {
            position: relative;
            width: 100%;
        }

        .select-box {
            position: relative;
            width: 100%;
        }

        .select-box select {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            background: var(--white);
            cursor: pointer;
            appearance: none;
            padding-right: 3rem;
        }

        .dropdown-icon {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--gray);
            transition: var(--transition);
        }

        .select-box.active .dropdown-icon {
            transform: translateY(-50%) rotate(180deg);
            color: var(--primary);
        }

        #checkboxes {
            display: none;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            margin-top: 0.75rem;
            padding: 1rem;
            max-height: 250px;
            overflow-y: auto;
            background: var(--white);
            position: absolute;
            width: 100%;
            z-index: 100;
            box-shadow: var(--box-shadow);
        }

        #checkboxes label {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 0.5rem;
        }

        #checkboxes label:hover {
            background: rgba(37, 99, 235, 0.1);
            transform: translateX(5px);
        }

        #checkboxes input[type="checkbox"] {
            width: auto;
            margin-right: 1rem;
            transform: scale(1.2);
        }

        /* Date Select Container */
        .date-select-container {
            display: flex;
            gap: 1rem;
        }

        .date-select-container select {
            flex: 1;
        }

        /* Age Error Styles */
        .age-error-container {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transform: translateY(-10px);
            opacity: 0;
            transition: var(--transition);
            max-height: 0;
            overflow: hidden;
        }

        .age-error-container.show {
            transform: translateY(0);
            opacity: 1;
            max-height: 120px;
            margin-bottom: 1rem;
        }

        .age-error-icon {
            color: var(--danger);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .age-error-content h4 {
            margin: 0 0 0.5rem 0;
            color: var(--danger);
            font-weight: 700;
        }

        .age-error-content p {
            margin: 0;
            color: var(--dark);
            font-size: 0.95rem;
        }

        /* Password Notification */
        .password-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-lg);
            padding: 2rem;
            max-width: 400px;
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            z-index: 1000;
            border: 1px solid var(--primary-light);
        }

        .password-notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification-content {
            display: flex;
            gap: 1rem;
            position: relative;
        }

        .notification-content i.fa-key {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .notification-text h3 {
            margin: 0 0 1rem 0;
            color: var(--primary);
            font-weight: 700;
        }

        .password-display {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1rem 0;
        }

        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            background: var(--gray-lighter);
            padding: 0.75rem;
            border-radius: var(--border-radius-sm);
            flex: 1;
            letter-spacing: 1px;
            border: 1px solid var(--gray-light);
        }

        .copy-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--danger);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .close-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-container {
                padding: 2rem;
                margin: 1rem;
                border-radius: var(--border-radius);
            }
            
            h1 {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .date-select-container {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .password-notification {
                max-width: calc(100% - 2rem);
                right: 1rem;
                left: 1rem;
                bottom: 1rem;
            }

            .floating-circle {
                display: none;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Form validation styles */
        input:invalid:not(:focus):not(:placeholder-shown) {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        input:valid:not(:focus):not(:placeholder-shown) {
            border-color: var(--success);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        /* Small text styling */
        small {
            display: block;
            margin-top: 0.75rem;
            color: var(--gray);
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
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

    <div class="form-container">
        <h1><i class="fas fa-dumbbell"></i> EliteFit Gym Registration</h1>
        
        <!-- Notification area for registration errors/success -->
        <div id="registration-alert-anchor"></div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_GET['error']) ?>
                <button class="alert-close" onclick="this.parentNode.remove()">&times;</button>
            </div>
        <?php endif; ?>
        
        <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <!-- Section 1: Personal Info -->
            <div id="section1" class="form-section active">
                <h2><i class="fas fa-user"></i> Personal Information</h2>
                <div class="form-group">
                    <label><i class="fas fa-camera"></i> Profile Picture</label>
                    <img id="profilePreview" class="profile-picture-preview" src="#" alt="Profile Preview">
                    <div class="file-upload">
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-camera"></i> Choose Profile Picture
                        </button>
                        <input type="file" name="profile_picture" id="profile_picture" class="file-upload-input" accept="image/*">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> First Name*</label>
                    <input type="text" name="first_name" required placeholder="Enter your first name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Last Name*</label>
                    <input type="text" name="last_name" required placeholder="Enter your last name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email*</label>
                    <input type="email" name="email" required placeholder="Enter your email address">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Contact Number*</label>
                    <input type="tel" name="contact_number" required placeholder="Enter your phone number">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Gender*</label>
                    <select name="gender" required>
                        <option value="">Select your gender...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Location*</label>
                    <input type="text" name="location" required placeholder="Enter your location">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date of Birth*</label>
                    <div class="date-select-container">
                        <select name="dob_day" id="dob_day" required>
                            <option value="">Day</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="dob_month" id="dob_month" required>
                            <option value="">Month</option>
                            <?php 
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                      'July', 'August', 'September', 'October', 'November', 'December'];
                            foreach ($months as $index => $month): ?>
                                <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="dob_year" id="dob_year" required>
                            <option value="">Year</option>
                            <?php for ($i = date('Y') - 16; $i >= date('Y') - 100; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div id="age-error" class="age-error-container">
                        <div class="age-error-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="age-error-content">
                            <h4>Age Restriction</h4>
                            <p>You must be at least 16 years old to register. Please adjust your date of birth.</p>
                        </div>
                    </div>
                    <input type="hidden" id="date_of_birth" name="date_of_birth">
                </div>
                <div class="form-group" style="display: none;">
                    <input type="hidden" id="generated_password" name="password" required>
                </div>
                <input type="hidden" name="role" value="member">
            </div>
            
            <!-- Section 2: Fitness Details -->
            <div id="section2" class="form-section">
                <h2><i class="fas fa-heartbeat"></i> Fitness Information</h2>
                <div class="form-group">
                    <label><i class="fas fa-ruler-vertical"></i> Height (cm)</label>
                    <input type="number" name="height" step="0.1" placeholder="Enter your height in cm">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-weight"></i> Weight (kg)</label>
                    <input type="number" name="weight" step="0.1" placeholder="Enter your weight in kg">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-circle"></i> Body Type</label>
                    <select name="body_type">
                        <option value="">Select your body type...</option>
                        <option value="ectomorph">Ectomorph (Lean & Long)</option>
                        <option value="mesomorph">Mesomorph (Muscular & Well-built)</option>
                        <option value="endomorph">Endomorph (Big & High Body Fat)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Experience Level</label>
                    <select name="experience_level">
                        <option value="">Select your experience level...</option>
                        <option value="beginner">Beginner (0-6 months)</option>
                        <option value="intermediate">Intermediate (6 months - 2 years)</option>
                        <option value="advanced">Advanced (2+ years)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-list-check"></i> Preferred Workout Plans (Select up to 3)</label>
                    <div class="multiselect">
                        <div class="select-box" id="selectBox">
                            <select id="workout_plan_dropdown" onclick="showCheckboxes(event)" readonly>
                                <option>Select workout plans (max 3)</option>
                            </select>
                            <div class="dropdown-icon"><i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div id="checkboxes">
                            <?php foreach ($workout_plans as $plan): ?>
                                <label>
                                    <input type="checkbox" name="workout_preferences[]" value="<?= $plan['plan_id'] ?>" onchange="updateSelectedCount()">
                                    <?= $plan['plan_name'] ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small id="selected-count">0 selected (max 3)</small>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-bullseye"></i> Fitness Goals</label>
                    <textarea name="fitness_goals" rows="4" placeholder="Describe your fitness goals (e.g., lose weight, build muscle, improve endurance)"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-notes-medical"></i> Health Conditions (if any)</label>
                    <textarea name="health_conditions" rows="3" placeholder="List any health conditions, injuries, or medical concerns we should know about"></textarea>
                </div>
            </div>
            
            <!-- Navigation Buttons -->
            <div class="nav-buttons">
                <button type="button" class="btn" id="prevBtn" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="btn" id="nextBtn">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn" id="submitBtn" style="display: none;">
                    <i class="fas fa-paper-plane"></i> Submit Registration
                </button>
            </div>
        </form>
    </div>

    <script>
    let currentSection = 1;
    const totalSections = 2;
    let expanded = false;
    let redirectTimer;
    let secondsLeft = 5;
    
    // Generate a random password
    function generatePassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('generated_password').value = password;
        return password;
    }
    
    // Age validation function
    function validateAge() {
        const day = document.getElementById('dob_day').value;
        const month = document.getElementById('dob_month').value;
        const year = document.getElementById('dob_year').value;
        const ageError = document.getElementById('age-error');
        
        if (!day || !month || !year) {
            ageError.classList.remove('show');
            disableForm(false);
            return false;
        }
        
        // Create date objects
        const birthDate = new Date(`${year}-${month}-${day}`);
        const today = new Date();
        
        // Calculate age
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        // Set the combined date_of_birth field
        document.getElementById('date_of_birth').value = `${year}-${month}-${day}`;
        
        // Validate age
        if (age < 16) {
            // Show error with animation
            ageError.style.display = 'flex';
            ageError.classList.add('show');
            
            disableForm(true);
            return false;
        } else {
            ageError.classList.remove('show');
            setTimeout(() => {
                ageError.style.display = 'none';
            }, 300);
            disableForm(false);
            return true;
        }
    }

    function disableForm(disabled) {
        const form = document.getElementById('registrationForm');
        const inputs = form.querySelectorAll('input, select, textarea, button');
        
        inputs.forEach(input => {
            // Only disable if it's not a date input and not the navigation buttons
            if (input.name !== 'dob_day' && 
                input.name !== 'dob_month' && 
                input.name !== 'dob_year' && 
                input.id !== 'nextBtn' && 
                input.id !== 'prevBtn' && 
                input.id !== 'submitBtn') {
                input.disabled = disabled;
            }
        });
        
        // Visual indication for disabled state
        if (disabled) {
            form.style.opacity = '0.7';
            form.style.pointerEvents = 'none';

            // Keep date inputs and navigation buttons enabled
            ['dob_day', 'dob_month', 'dob_year'].forEach(name => {
                const input = document.querySelector(`[name="${name}"]`);
                input.disabled = false;
                input.style.opacity = '1';
                input.style.pointerEvents = 'auto';
            });

            ['nextBtn', 'prevBtn'].forEach(id => {
                const btn = document.getElementById(id);
                btn.disabled = false;
                btn.style.pointerEvents = 'auto';
            });

        } else {
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';

            document.getElementById('prevBtn').style.display = currentSection === 1 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = currentSection === totalSections ? 'none' : 'block';
            document.getElementById('submitBtn').style.display = currentSection === totalSections ? 'block' : 'none';
        }
    }

    // Show password notification
    function showPasswordNotification(password, otpRedirect = false) {
        // Remove any existing notifications first
        const existing = document.querySelector('.password-notification');
        if (existing) existing.remove();
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = 'password-notification show';
        
        notification.innerHTML = [
            '<div class="notification-content">',
            '    <i class="fas fa-key"></i>',
            '    <div class="notification-text">',
            '        <h3>Your Generated Password</h3>',
            '        <div class="password-display">',
            '            <span class="password-value">' + password + '</span>',
            '            <button class="copy-btn" onclick="copyToClipboard(\'' + password + '\')">',
            '                <i class="fas fa-copy"></i> Copy',
            '            </button>',
            '        </div>',
            '        <small>Please save this password as it won\'t be shown again</small>',
            '        <div id="countdown-timer">' + (otpRedirect ? 'Redirecting to OTP verification in 5 seconds...' : 'Redirecting in 5 seconds...') + '</div>',
            '        <button class="btn" style="width:100%;margin-top:15px;" onclick="' + (otpRedirect ? 'redirectToOtp()' : 'redirectNow()') + '">',
            '            <i class="fas ' + (otpRedirect ? 'fa-shield-alt' : 'fa-sign-in-alt') + '"></i> ' + (otpRedirect ? 'Go to OTP Verification Now' : 'Go to Login Now'),
            '        </button>',
            '    </div>',
            '    <button class="close-btn" onclick="closeNotification()">',
            '        <i class="fas fa-times"></i>',
            '    </button>',
            '</div>'
        ].join('');
        
        document.body.appendChild(notification);
        
        // Start countdown timer
        secondsLeft = 5;
        updateCountdown();
        redirectTimer = setInterval(updateCountdown, 1000);
        
        // Update the redirect button and countdown
        if (otpRedirect) {
            const countdownElement = document.getElementById('countdown-timer');
            if (countdownElement) {
                countdownElement.textContent = `Redirecting to OTP verification in ${secondsLeft} second${secondsLeft !== 1 ? 's' : ''}...`;
            }
            
            const redirectBtn = notification.querySelector('.btn');
            if (redirectBtn) {
                redirectBtn.innerHTML = '<i class="fas fa-shield-alt"></i> Go to OTP Verification Now';
                redirectBtn.onclick = function() {
                    redirectToOtp();
                };
            }
        }
    }

    function redirectToOtp() {
        closeNotification();
        window.location.href = 'otp_verification.php';
    }

    function updateCountdown() {
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            countdownElement.textContent = `Redirecting in ${secondsLeft} second${secondsLeft !== 1 ? 's' : ''}...`;
        }
        
        if (secondsLeft <= 0) {
            clearInterval(redirectTimer);
            redirectNow();
        }
        secondsLeft--;
    }

    function redirectNow() {
        clearInterval(redirectTimer);
        const notification = document.querySelector('.password-notification');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 500);
        }
        
        // Check if we should go to OTP or login
        if (window.otpRequired) {
            window.location.href = 'otp_verification.php';
        } else {
            window.location.href = 'index.php';
        }
    }

    function closeNotification() {
        clearInterval(redirectTimer);
        const notification = document.querySelector('.password-notification');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 500);
        }
    }

    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const copyBtns = document.querySelectorAll('.copy-btn');
            copyBtns.forEach(btn => {
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                }, 2000);
            });
        });
    }
    
    // Profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const preview = document.getElementById('profilePreview');
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
    
    function showSection(sectionNum) {
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`section${sectionNum}`).classList.add('active');
        
        document.getElementById('prevBtn').style.display = sectionNum === 1 ? 'none' : 'block';
        document.getElementById('nextBtn').style.display = sectionNum === totalSections ? 'none' : 'block';
        document.getElementById('submitBtn').style.display = sectionNum === totalSections ? 'block' : 'none';
        
        currentSection = sectionNum;
    }
    
    function validateSection(sectionNum) {
        if (sectionNum === 1) {
            const form = document.getElementById('registrationForm');
            const requiredFields = form.querySelectorAll('#section1 [required]');
            
            // First check if all required fields are filled
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    const label = field.closest('.form-group').querySelector('label');
                    alert(`Please fill in ${label.textContent.replace('*', '').trim()}`);
                    field.focus();
                    return false;
                }
            }
            
            // Then validate age
            if (!validateAge()) {
                return false;
            }
        }
        return true;
    }
    
    document.getElementById('nextBtn').addEventListener('click', () => {
        if (validateSection(currentSection)) {
            showSection(currentSection + 1);
            // Scroll to the top of the new section for better UX
            setTimeout(() => {
                const activeSection = document.querySelector('.form-section.active');
                if (activeSection) {
                    activeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 150);
        }
    });
    
    document.getElementById('prevBtn').addEventListener('click', () => {
        showSection(currentSection - 1);
    });
    
    // Utility to show styled notification
    function showRegistrationNotification(type, message) {
        console.log('showRegistrationNotification called', type, message); // DEBUG
        // Remove any existing notification
        const old = document.getElementById('registration-alert');
        if (old) old.remove();
        // Icons for each type
        let icon = '<i class="fas fa-info-circle alert-icon"></i>';
        if (type === 'success') icon = '<i class="fas fa-check-circle alert-icon" style="color:var(--success)"></i>';
        if (type === 'network') icon = '<i class="fas fa-wifi alert-icon" style="color:#f59e42"></i>';
        if (type === 'brevo') icon = '<i class="fas fa-envelope-open-text alert-icon" style="color:#3a0ca3"></i>';
        if (type === 'system') icon = '<i class="fas fa-server alert-icon" style="color:var(--danger)"></i>';
        if (type === 'error') icon = '<i class="fas fa-times-circle alert-icon" style="color:var(--danger)"></i>';
        // Container
        const div = document.createElement('div');
        div.id = 'registration-alert';
        div.className = type === 'success' ? 'alert-success' : 'alert-error';
        div.innerHTML = `${icon}<span>${message}</span><button class='alert-close' onclick='this.parentNode.remove()' title='Dismiss'>&times;</button>`;
        // Always inject into anchor
        const anchor = document.getElementById('registration-alert-anchor');
        if (anchor) {
            anchor.innerHTML = '';
            anchor.appendChild(div);
            // Scroll to notification smoothly
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    // Make notification function globally accessible for test button
    window.showRegistrationNotification = showRegistrationNotification;

    // AJAX form submission
    function submitForm(password) {
        const form = document.getElementById('registrationForm');
        const formData = new FormData(form);
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner spinner"></i> Processing...';
        
        fetch('register_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Registration fetch response:', data); // DEBUG
            if (data.status === 'success') {
                if (data.otp_required) {
                    // Set flag for OTP requirement
                    window.otpRequired = true;
                    // Show password and redirect to OTP page
                    showPasswordNotification(password, true);
                } else {
                    window.otpRequired = false;
                    // Old behavior (shouldn't happen with our changes)
                    showPasswordNotification(password);
                }
            } else {
                // Show styled error notification based on error_type
                showRegistrationNotification(data.error_type || 'error', data.message || 'Registration failed.');
            }
            // Reset submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Registration';
        })
        .catch(error => {
            console.error('Error:', error);
            showRegistrationNotification('error', error.message || 'Registration failed. Please try again.');
            // Reset submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Registration';
        });
    }
    
    // Validate entire form before submission
    function validateForm() {
        // Validate all required fields in both sections
        const form = document.getElementById('registrationForm');
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                const label = field.closest('.form-group')?.querySelector('label');
                if (label) {
                    alert(`Please fill in ${label.textContent.replace('*', '').trim()}`);
                    field.focus();
                    
                    // Show the section containing this field
                    const section = field.closest('.form-section');
                    if (section) {
                        showSection(parseInt(section.id.replace('section', '')));
                    }
                }
                valid = false;
                break;
            }
        }
        
        // Validate workout preferences selection (max 3)
        const selectedWorkouts = form.querySelectorAll('input[name="workout_preferences[]"]:checked');
        if (selectedWorkouts.length > 3) {
            alert('Please select no more than 3 workout plans');
            valid = false;
        }
        
        // Validate age
        if (!validateAge()) {
            valid = false;
        }
        
        if (valid) {
            const password = document.getElementById('generated_password').value;
            submitForm(password);
        }
        
        return false; // Prevent default form submission
    }
    
    // Workout plans dropdown functionality
    function showCheckboxes(e) {
        e.stopPropagation();
        const checkboxes = document.getElementById("checkboxes");
        const selectBox = document.getElementById("selectBox");
        
        if (!expanded) {
            checkboxes.style.display = "block";
            selectBox.classList.add("active");
            expanded = true;
        } else {
            checkboxes.style.display = "none";
            selectBox.classList.remove("active");
            expanded = false;
        }
    }
    
    function updateSelectedCount() {
        const selected = document.querySelectorAll('input[name="workout_preferences[]"]:checked').length;
        document.getElementById('selected-count').textContent = `${selected} selected (max 3)`;
        
        // Update the select box display
        const selectedPlans = Array.from(document.querySelectorAll('input[name="workout_preferences[]"]:checked'))
            .map(checkbox => checkbox.nextSibling.textContent.trim())
            .join(", ");
        
        const dropdown = document.getElementById("workout_plan_dropdown");
        if (selected > 0) {
            dropdown.options[0].text = selectedPlans || "Select workout plans (max 3)";
        } else {
            dropdown.options[0].text = "Select workout plans (max 3)";
        }
        
        // Disable unchecked boxes if 3 are already selected
        if (selected >= 3) {
            document.querySelectorAll('input[name="workout_preferences[]"]:not(:checked)').forEach(checkbox => {
                checkbox.disabled = true;
            });
        } else {
            document.querySelectorAll('input[name="workout_preferences[]"]').forEach(checkbox => {
                checkbox.disabled = false;
            });
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.multiselect')) {
            const checkboxes = document.getElementById("checkboxes");
            const selectBox = document.getElementById("selectBox");
            if (expanded) {
                checkboxes.style.display = "none";
                selectBox.classList.remove("active");
                expanded = false;
            }
        }
    });

    // --- Date of Birth Logic: Restrict months based on selected day ---
    const dobDay = document.getElementById('dob_day');
    const dobMonth = document.getElementById('dob_month');
    const dobYear = document.getElementById('dob_year');

    // Store original month options (skip placeholder, only real months)
    let originalMonthOptions = [];
    for (let i = 1; i < dobMonth.options.length; i++) {
        originalMonthOptions.push({value: dobMonth.options[i].value, text: dobMonth.options[i].text});
    }
    let placeholderOption = {value: dobMonth.options[0].value, text: dobMonth.options[0].text};

    function updateMonthOptions() {
        const day = parseInt(dobDay.value, 10);
        let monthsToEnable = [];
        if (!day || day < 29) {
            monthsToEnable = originalMonthOptions.map(opt => opt.value);
        } else if (day === 29) {
            monthsToEnable = originalMonthOptions.map(opt => opt.value);
        } else if (day === 30) {
            monthsToEnable = ['01','03','04','05','06','07','08','09','10','11','12']; // All except Feb
        } else if (day === 31) {
            monthsToEnable = ['01','03','05','07','08','10','12'];
        }
        // Remove all options
        dobMonth.options.length = 0;
        // Add placeholder first
        dobMonth.options.add(new Option(placeholderOption.text, placeholderOption.value));
        // Add back only valid months
        originalMonthOptions.forEach(opt => {
            if (monthsToEnable.includes(opt.value)) {
                dobMonth.options.add(new Option(opt.text, opt.value));
            }
        });
        // If current month is now invalid, reset
        if (!monthsToEnable.includes(dobMonth.value)) {
            dobMonth.value = '';
        }
    }
    dobDay.addEventListener('change', updateMonthOptions);
    // On page load, ensure correct months
    updateMonthOptions();

    // --- Leap year check for 29 Feb ---
    function updateDayOptionsForLeapYear() {
        const year = parseInt(dobYear.value, 10);
        const month = dobMonth.value;
        if (month === '02') {
            let daysInFeb = 28;
            if (year && ((year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0))) {
                daysInFeb = 29;
            }
            for (let i = dobDay.options.length - 1; i > 0; i--) {
                if (parseInt(dobDay.options[i].value, 10) > daysInFeb) {
                    dobDay.remove(i);
                }
            }
            for (let i = dobDay.options.length; i <= daysInFeb; i++) {
                dobDay.options.add(new Option(i, String(i).padStart(2, '0')));
            }
            if (parseInt(dobDay.value, 10) > daysInFeb) {
                dobDay.value = '';
            }
        } else {
            for (let i = dobDay.options.length; i <= 31; i++) {
                dobDay.options.add(new Option(i, String(i).padStart(2, '0')));
            }
        }
    }
    dobMonth.addEventListener('change', updateDayOptionsForLeapYear);
    dobYear.addEventListener('change', updateDayOptionsForLeapYear);
    updateDayOptionsForLeapYear();
    
    // Initialize on page load
    window.onload = function() {
        generatePassword();
        document.getElementById('age-error').style.display = 'none';
        
        // Add event listeners for date of birth fields
        document.getElementById('dob_day').addEventListener('change', validateAge);
        document.getElementById('dob_month').addEventListener('change', validateAge);
        document.getElementById('dob_year').addEventListener('change', validateAge);
        
        // Set up form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            validateForm();
        });
    };
    </script>
</body>
</html>