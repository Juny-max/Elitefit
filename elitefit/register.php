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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4cc9f0;
            --secondary: #f72585;
            --dark: #212529;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #4cc9f0;
            --danger: #ef233c;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            /* Remove flex centering to allow scrolling */
        }
        #vanta-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
        }

        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 800px;
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
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        h1 {
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        h1 i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .form-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        input, select, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            box-sizing: border-box;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary {
            background: var(--gray);
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .nav-buttons {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
        }

        .profile-picture-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            display: none;
            border: 3px solid #e9ecef;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #009688;
            color: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.15rem;
            font-weight: 500;
            padding: 1.25rem 2rem;
            margin-bottom: 1.5rem;
            border-left: 7px solid #00796b;
            animation: fadeInUp 0.5s;
            position: relative;
        }

        .alert-error {
            background: #f44336;
            color: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.15rem;
            font-weight: 500;
            padding: 1.25rem 2rem;
            margin-bottom: 1.5rem;
            border-left: 7px solid #d32f2f;
            animation: fadeInUp 0.5s;
            position: relative;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    border: none;
    opacity: 1 !important;
    z-index: 9999;
    padding: 2rem 2.5rem;
    text-align: center;
    font-size: 1.1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
}
.registration-popup-alert.alert-success {
    background: #e6faff;
    color: #157e90;
    border-left: 6px solid #4cc9f0;
}
.registration-popup-alert.alert-error {
    background: #fff0f1;
    color: #d90429;
    border-left: 6px solid #ef233c;
}

            flex-shrink: 0;
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
        }

        .alert-close:hover { opacity: 1; }

        small {
            display: block;
            margin-top: 0.5rem;
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Multi-select dropdown styling */
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
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            background-color: white;
            cursor: pointer;
            appearance: none;
            padding-right: 2.5rem;
        }

        .dropdown-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--gray);
            transition: var(--transition);
        }

        .select-box.active .dropdown-icon {
            transform: translateY(-50%) rotate(180deg);
        }

        #checkboxes {
            display: none;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            margin-top: 0.5rem;
            padding: 0.75rem;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            position: absolute;
            width: 100%;
            z-index: 100;
            box-shadow: var(--box-shadow);
        }

        #checkboxes label {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        #checkboxes label:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        #checkboxes input[type="checkbox"] {
            width: auto;
            margin-right: 0.75rem;
        }

        /* Password Notification Styles */
        .password-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1.5rem;
            max-width: 350px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            border-left: 4px solid var(--primary);
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

        .notification-text {
            flex: 1;
        }

        .notification-text h3 {
            margin: 0 0 0.5rem 0;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .password-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 4px;
            flex: 1;
            letter-spacing: 1px;
        }

        .copy-btn {
            background: var(--primary-light);
            color: var(--primary-dark);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .copy-btn:hover {
            background: var(--primary);
            color: white;
        }

        .close-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--danger);
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-btn:hover {
            transform: scale(1.1);
        }

        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .password-notification {
                max-width: calc(100% - 40px);
                right: 20px;
                left: 20px;
            }
        }

        /* New Age Validation Styles */
        .age-error-container {
            background-color: rgba(239, 35, 60, 0.1);
            border-left: 4px solid var(--danger);
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transform: translateY(-10px);
            opacity: 0;
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }

        .age-error-container.show {
            transform: translateY(0);
            opacity: 1;
            max-height: 100px;
            margin-bottom: 1rem;
        }

        .age-error-icon {
            color: var(--danger);
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .age-error-content h4 {
            margin: 0 0 0.25rem 0;
            color: var(--danger);
            font-weight: 600;
        }

        .age-error-content p {
            margin: 0;
            color: var(--dark);
            font-size: 0.9rem;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .date-select-container {
            display: flex;
            gap: 10px;
        }

        .date-select-container select {
            flex: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .date-select-container {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
    <!-- Vanta.js & Three.js for animated background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.waves.min.js"></script>
</head>
<body>
    <div id="vanta-bg" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:-2;"></div>
    <div class="form-container">
        <h1><i class="fas fa-dumbbell"></i> EliteFit Gym Registration</h1>
        
        <!-- Notification area for registration errors/success -->
        <div id="registration-alert-anchor"></div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        
        <form id="registrationForm" method="POST" enctype="multipart/form-data">
            <!-- Section 1: Personal Info -->
            <div id="section1" class="form-section active">
                <h2>Personal Information</h2>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <img id="profilePreview" class="profile-picture-preview" src="#" alt="Profile Preview">
                    <div class="file-upload">
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-camera"></i> Choose Profile Picture
                        </button>
                        <input type="file" name="profile_picture" id="profile_picture" class="file-upload-input" accept="image/*">
                    </div>
                </div>
                <div class="form-group">
                    <label>First Name*</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name*</label>
                    <input type="text" name="last_name" required>
                </div>
                <div class="form-group">
                    <label>Email*</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Contact Number*</label>
                    <input type="tel" name="contact_number" required>
                </div>
                <div class="form-group">
                    <label>Gender*</label>
                    <select name="gender" required>
                        <option value="">Select...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Location*</label>
                    <input type="text" name="location" required>
                </div>
                <div class="form-group">
                    <label>Date of Birth*</label>
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
                <h2>Fitness Information</h2>
                <div class="form-group">
                    <label>Height (cm)</label>
                    <input type="number" name="height" step="0.1">
                </div>
                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" step="0.1">
                </div>
                <div class="form-group">
                    <label>Body Type</label>
                    <select name="body_type">
                        <option value="">Select...</option>
                        <option value="ectomorph">Ectomorph</option>
                        <option value="mesomorph">Mesomorph</option>
                        <option value="endomorph">Endomorph</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Experience Level</label>
                    <select name="experience_level">
                        <option value="">Select...</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Preferred Workout Plans (Select up to 3)</label>
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
                    <label>Fitness Goals</label>
                    <textarea name="fitness_goals" rows="3" placeholder="Enter your fitness goals, one per line"></textarea>
                </div>
                <div class="form-group">
                    <label>Health Conditions (if any)</label>
                    <textarea name="health_conditions" rows="2" placeholder="List any health conditions we should know about"></textarea>
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
            ageError.classList.add('shake');
            
            // Remove shake animation after it completes
            setTimeout(() => {
                ageError.classList.remove('shake');
            }, 500);
            
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
        btn.style.pointerEvents = 'auto'; // 
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
        '        <button class="btn" style="width:100%;margin-top:10px;" onclick="' + (otpRedirect ? 'redirectToOtp()' : 'redirectNow()') + '">',
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