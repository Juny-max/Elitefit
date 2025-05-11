<?php
session_start();
include_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle password change if submitted
    if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password_hash'])) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                // Validate password strength
                if (strlen($new_password) >= 8) {
                    // Update password
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $new_password_hash, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['success_message'] = "Password changed successfully!";
                        header("Location: edit_profile.php?success=password_changed");
                        exit();
                    } else {
                        $error = "Error updating password: " . $conn->error;
                    }
                } else {
                    $error = "New password must be at least 8 characters long";
                }
            } else {
                $error = "New passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
    
    // Handle profile picture upload (still allowed)
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Delete old profile picture if it exists
                if ($user['profile_picture'] && file_exists('../../' . $user['profile_picture'])) {
                    unlink('../../' . $user['profile_picture']);
                }
                $profile_picture = 'uploads/profile_pictures/' . $file_name;
                
                // Update profile picture in database
                $update_sql = "UPDATE users SET profile_picture = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $profile_picture, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = "Profile picture updated successfully!";
                    header("Location: edit_profile.php?success=profile_picture_updated");
                    exit();
                } else {
                    $error = "Error updating profile picture: " . $conn->error;
                }
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
    <title>Edit Profile - EliteFit Gym</title>
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

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
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

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-btn:hover {
            background: var(--primary);
            color: var(--white);
        }

        .profile-picture-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin-bottom: 1rem;
        }

        .file-upload {
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .file-upload-btn {
            background: var(--primary);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .file-upload-btn:hover {
            background: var(--secondary);
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

        .form-group {
            margin-bottom: 1.5rem;
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
        input[type="password"],
        input[type="date"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-submit {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            transition: var(--transition);
        }

        .btn-submit:hover {
            background: var(--secondary);
        }

        .error {
            color: var(--danger);
            background: #fee2e2;
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .success {
            color: #065f46;
            background: #d1fae5;
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .placeholder-icon {
            font-size: 4rem;
            color: var(--gray-light);
        }

        .view-only {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-light);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-light);
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="page-title">
                <h1>Edit Profile</h1>
            </div>
            <a href="../dashboard.php" class="back-btn">
                <span>←</span> Back to Dashboard
            </a>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'password_changed'): ?>
            <div class="success">Password changed successfully!</div>
        <?php elseif (isset($_GET['success']) && $_GET['success'] == 'profile_picture_updated'): ?>
            <div class="success">Profile picture updated successfully!</div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="profile-picture-container">
                <?php if (!empty($user['profile_picture'])): ?>
                    <?php
                    // Get absolute server path to check if file exists
                    $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($user['profile_picture'], '/');
                    
                    // Check if file actually exists
                    if (file_exists($absolute_path)): ?>
                        <img src="/<?= htmlspecialchars(ltrim($user['profile_picture'], '/')) ?>" class="profile-pic" id="profilePreview" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-pic" id="profilePreview" style="display: flex; align-items: center; justify-content: center;">
                            <span class="placeholder-icon">👤</span>
                        </div>
                        <?php error_log("Profile picture not found: " . $absolute_path); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="profile-pic" id="profilePreview" style="display: flex; align-items: center; justify-content: center;">
                        <span class="placeholder-icon">👤</span>
                    </div>
                <?php endif; ?>
                <div class="file-upload">
                    <button type="button" class="file-upload-btn">Change Profile Picture</button>
                    <input type="file" name="profile_picture" id="profile_picture" class="file-upload-input" accept="image/*">
                </div>
            </div>
            
            <h2 class="section-title">Personal Information</h2>
            
            <div class="form-group">
                <label for="first_name">First Name</label>
                <div class="view-only"><?= htmlspecialchars($user['first_name'] ?? '') ?></div>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <div class="view-only"><?= htmlspecialchars($user['last_name'] ?? '') ?></div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <div class="view-only"><?= htmlspecialchars($user['email'] ?? '') ?></div>
            </div>
            
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <div class="view-only"><?= htmlspecialchars($user['contact_number'] ?? '') ?></div>
            </div>
            
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <div class="view-only"><?= !empty($user['date_of_birth']) ? date('d-m-Y', strtotime($user['date_of_birth'])) : 'Not specified' ?></div>
            </div>
            
            <h2 class="section-title">Change Password</h2>
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>Password must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn-submit">Save Changes</button>
        </form>
    </div>

    <script>
        // Profile picture preview
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const preview = document.getElementById('profilePreview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview.tagName === 'DIV') {
                        // Replace the div with an img element
                        const newImg = document.createElement('img');
                        newImg.id = 'profilePreview';
                        newImg.className = 'profile-pic';
                        newImg.src = e.target.result;
                        preview.parentNode.replaceChild(newImg, preview);
                    } else {
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>