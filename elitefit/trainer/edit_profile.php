<?php
session_start();

// Use absolute path to config.php
$config_path = realpath(dirname(__FILE__)) . '/../config.php';
if (file_exists($config_path)) {
    include_once $config_path;
} else {
    die("Configuration file not found");
}

// Check if connection exists
if (!isset($conn)) {
    die("Database connection failed");
}

// Redirect if not logged in as trainer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get trainer data
$trainer_sql = "SELECT u.*, t.* FROM users u 
               JOIN trainers t ON u.user_id = t.trainer_id 
               WHERE u.user_id = ?";
$trainer_stmt = $conn->prepare($trainer_sql);
$trainer_stmt->bind_param("i", $trainer_id);
$trainer_stmt->execute();
$trainer_result = $trainer_stmt->get_result();
$trainer = $trainer_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $location = $_POST['location'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $specialization = $_POST['specialization'];
    $certification = $_POST['certification'];
    $years_experience = $_POST['years_experience'];
    $bio = $_POST['bio'];
    
    // Handle file upload
    $profile_picture = $trainer['profile_picture']; // Keep existing if no new file uploaded
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = 'trainer_' . $trainer_id . '_' . time() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;
        
        // Check if file is an image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                // Delete old profile picture if it exists
                if (!empty($trainer['profile_picture']) && file_exists('../' . $trainer['profile_picture'])) {
                    unlink('../' . $trainer['profile_picture']);
                }
                $profile_picture = 'uploads/profile_pictures/' . $file_name;
            }
        }
    }
    
    // Update users table
    $update_user_sql = "UPDATE users SET 
                       first_name = ?, 
                       last_name = ?, 
                       email = ?, 
                       contact_number = ?, 
                       location = ?, 
                       gender = ?, 
                       date_of_birth = ?, 
                       profile_picture = ? 
                       WHERE user_id = ?";
    $user_stmt = $conn->prepare($update_user_sql);
    $user_stmt->bind_param("ssssssssi", 
        $first_name, 
        $last_name, 
        $email, 
        $contact_number, 
        $location, 
        $gender, 
        $date_of_birth, 
        $profile_picture, 
        $trainer_id
    );
    
    // Update trainers table
    $update_trainer_sql = "UPDATE trainers SET 
                          specialization = ?, 
                          certification = ?, 
                          years_experience = ?, 
                          bio = ? 
                          WHERE trainer_id = ?";
    $trainer_stmt = $conn->prepare($update_trainer_sql);
    $trainer_stmt->bind_param("ssisi", 
        $specialization, 
        $certification, 
        $years_experience, 
        $bio, 
        $trainer_id
    );
    
    // Execute both updates
    $user_success = $user_stmt->execute();
    $trainer_success = $trainer_stmt->execute();
    
    if ($user_success && $trainer_success) {
        $_SESSION['success'] = "Profile updated successfully!";
        // Refresh the trainer data
        header("Location: edit_profile.php");
        exit();
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// Get unread messages count for sidebar
$unread_sql = "SELECT COUNT(*) as unread_count 
              FROM messages 
              WHERE receiver_id = ? AND is_read = FALSE";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $trainer_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_messages = $unread_result->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EliteFit Gym</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .brand-name {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--gray);
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-item:hover, .nav-item.active {
            background: var(--primary-light);
            color: var(--primary);
        }

        .nav-item i {
            width: 24px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        .page-title p {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Profile Form */
        .profile-form {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Profile Picture Upload */
        .profile-picture-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-light);
            margin-bottom: 1rem;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .file-upload-btn:hover {
            background: var(--secondary);
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Buttons */
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-save {
            background: var(--success);
        }

        /* Alerts */
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(247, 37, 133, 0.2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
        }

        .profile-name {
            font-weight: 600;
        }

        .profile-role {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* View-only fields */
        .view-only {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-light);
        }

        /* Profile pic in header */
        .header .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Date of Birth Select Fields */
        .date-select-container {
            display: flex;
            gap: 10px;
        }

        .date-select-container select {
            flex: 1;
        }

        @media (max-width: 768px) {
            .date-select-container {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">EF</div>
                <div class="brand-name">EliteFit</div>
            </div>
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    <i>📊</i> Dashboard
                </a>
                <a href="members.php" class="nav-item">
                    <i>👥</i> Members
                </a>
                <a href="workout_plans.php" class="nav-item">
                    <i>💪</i> Workouts
                </a>
                <a href="progress_tracker.php" class="nav-item">
                    <i>📈</i> Progress
                </a>
                <a href="schedule.php" class="nav-item">
                    <i>📅</i> Schedule
                </a>
                <a href="messages.php" class="nav-item">
                    <i>✉️</i> Messages
                    <?php if ($unread_messages > 0): ?>
                        <span style="margin-left: auto; background: var(--danger); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                            <?= $unread_messages ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="availability.php" class="nav-item">
                    <i>⏱️</i> Availability
                </a>
                <a href="edit_profile.php" class="nav-item active">
                    <i>👤</i> Profile
                </a>
                <a href="../logout.php" class="nav-item">
                    <i>🚪</i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="page-title">
                    <h1>Edit Profile</h1>
                    <p>Update your personal and professional information</p>
                </div>
                <div class="user-profile">
                    <?php if (!empty($trainer['profile_picture'])): ?>
                        <img src="<?= '../' . htmlspecialchars($trainer['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 20px;">👤</span>
                        </div>
                    <?php endif; ?>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars($trainer['first_name'] . ' ' . ($trainer['last_name'] ?? '')) ?>
                        </span>
                        <span class="profile-role"><?= htmlspecialchars($trainer['specialization']) ?> Trainer</span>
                    </div>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="profile-form" enctype="multipart/form-data">
                <!-- Profile Picture Section -->
                <div class="profile-picture-container">
                    <?php if (!empty($trainer['profile_picture'])): ?>
                        <img src="<?= '../' . htmlspecialchars($trainer['profile_picture']) ?>" class="profile-picture" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-picture" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 40px;">👤</span>
                        </div>
                    <?php endif; ?>
                    <div class="file-upload">
                        <button type="button" class="file-upload-btn">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                        <input type="file" name="profile_picture" accept="image/*">
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="form-section">
                    <h2>Personal Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <div class="view-only"><?= htmlspecialchars($trainer['first_name'] ?? '') ?></div>
                            <input type="hidden" name="first_name" value="<?= htmlspecialchars($trainer['first_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <div class="view-only"><?= htmlspecialchars($trainer['last_name'] ?? '') ?></div>
                            <input type="hidden" name="last_name" value="<?= htmlspecialchars($trainer['last_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="view-only"><?= htmlspecialchars($trainer['email'] ?? '') ?></div>
                            <input type="hidden" name="email" value="<?= htmlspecialchars($trainer['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <div class="view-only"><?= htmlspecialchars($trainer['contact_number'] ?? '') ?></div>
                            <input type="hidden" name="contact_number" value="<?= htmlspecialchars($trainer['contact_number'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?= htmlspecialchars($trainer['location'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?= ($trainer['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($trainer['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($trainer['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <?php 
                        // Parse the date from database (Y-m-d format)
                        $dob = !empty($trainer['date_of_birth']) ? $trainer['date_of_birth'] : '';
                        $dob_day = '';
                        $dob_month = '';
                        $dob_year = '';
                        
                        if ($dob) {
                            $date_parts = explode('-', $dob);
                            if (count($date_parts) === 3) {
                                $dob_year = $date_parts[0];
                                $dob_month = $date_parts[1];
                                $dob_day = $date_parts[2];
                            }
                        }
                        ?>
                        <div class="date-select-container">
                            <select name="dob_day" id="dob_day">
                                <option value="">Day</option>
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $dob_day == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="dob_month" id="dob_month">
                                <option value="">Month</option>
                                <?php 
                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                          'July', 'August', 'September', 'October', 'November', 'December'];
                                foreach ($months as $index => $month): ?>
                                    <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>" <?= $dob_month == str_pad($index + 1, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>><?= $month ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="dob_year" id="dob_year">
                                <option value="">Year</option>
                                <?php for ($i = date('Y') - 16; $i >= date('Y') - 100; $i--): ?>
                                    <option value="<?= $i ?>" <?= $dob_year == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="date_of_birth" name="date_of_birth" value="<?= $dob ?>">
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="form-section">
                    <h2>Professional Information</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <input type="text" id="specialization" name="specialization" value="<?= htmlspecialchars($trainer['specialization'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="certification">Certification</label>
                            <input type="text" id="certification" name="certification" value="<?= htmlspecialchars($trainer['certification'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="years_experience">Years of Experience</label>
                        <input type="number" id="years_experience" name="years_experience" min="0" value="<?= htmlspecialchars($trainer['years_experience'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio"><?= htmlspecialchars($trainer['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </main>
    </div>

    <script>
        // Preview profile picture before upload
        document.querySelector('input[name="profile_picture"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('.profile-picture').src = event.target.result;
                    // Also update the small profile pic in header
                    const headerPic = document.querySelector('.header .profile-pic');
                    if (headerPic) {
                        headerPic.src = event.target.result;
                    }
                }
                reader.readAsDataURL(file);
            }
        });

        // Update the hidden date_of_birth field when any date part changes
        document.getElementById('dob_day').addEventListener('change', updateDateOfBirth);
        document.getElementById('dob_month').addEventListener('change', updateDateOfBirth);
        document.getElementById('dob_year').addEventListener('change', updateDateOfBirth);

        function updateDateOfBirth() {
            const day = document.getElementById('dob_day').value;
            const month = document.getElementById('dob_month').value;
            const year = document.getElementById('dob_year').value;
            
            if (day && month && year) {
                document.getElementById('date_of_birth').value = `${year}-${month}-${day}`;
            } else {
                document.getElementById('date_of_birth').value = '';
            }
        }
    </script>
</body>
</html>