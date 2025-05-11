<?php
session_start();
require_once __DIR__ . '/../config.php';

// Redirect if not logged in as trainer
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit();
} elseif ($_SESSION['role'] != 'trainer') {
    header("Location: ./index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get unread messages count for sidebar
$unread_sql = "SELECT COUNT(*) as unread_count 
              FROM messages 
              WHERE receiver_id = ? AND is_read = FALSE";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $trainer_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_messages = $unread_result->fetch_assoc()['unread_count'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_availability'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete old availability
        $delete_sql = "DELETE FROM trainer_availability WHERE trainer_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $trainer_id);
        $delete_stmt->execute();
        
        // Insert new availability if provided
        if (!empty($_POST['availability'])) {
            $insert_sql = "INSERT INTO trainer_availability (trainer_id, day_of_week, availability_type) 
                           VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            
            foreach ($_POST['availability'] as $day => $type) {
                if (!empty($type)) {
                    $insert_stmt->bind_param("iss", $trainer_id, $day, $type);
                    $insert_stmt->execute();
                }
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = "Availability updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating availability: " . $e->getMessage();
    }
    
    header("Location: availability.php");
    exit();
}

// Get current availability
$availability = [];
$get_sql = "SELECT day_of_week, availability_type 
           FROM trainer_availability 
           WHERE trainer_id = ?
           ORDER BY day_of_week";
$get_stmt = $conn->prepare($get_sql);
$get_stmt->bind_param("i", $trainer_id);
$get_stmt->execute();
$result = $get_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $availability[$row['day_of_week']] = $row['availability_type'];
}

// Get trainer data for profile section
$trainer_sql = "SELECT u.*, t.* FROM users u 
               JOIN trainers t ON u.user_id = t.trainer_id 
               WHERE u.user_id = ?";
$trainer_stmt = $conn->prepare($trainer_sql);
$trainer_stmt->bind_param("i", $trainer_id);
$trainer_stmt->execute();
$trainer_result = $trainer_stmt->get_result();
$trainer = $trainer_result->fetch_assoc();

// Generate availability summary text
$summary = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
foreach ($days as $day) {
    if (isset($availability[$day])) {
        $type = $availability[$day];
        $shortDay = substr($day, 0, 3);
        switch ($type) {
            case 'morning':
                $summary[] = "$shortDay (Morning)";
                break;
            case 'afternoon':
                $summary[] = "$shortDay (Afternoon)";
                break;
            case 'full':
                $summary[] = "$shortDay (Full Day)";
                break;
        }
    }
}
$summaryText = !empty($summary) ? "You are available: " . implode(", ", $summary) : "You haven't set any availability yet";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability - EliteFit Gym</title>
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

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-pic {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
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

        /* Card Styles */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        /* New styles for toggle interface */
        .availability-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .day-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .day-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .day-header i {
            font-size: 1.25rem;
            color: var(--primary);
        }
        
        .toggle-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        
        .toggle-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            background: var(--gray-light);
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .toggle-option:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .toggle-option.selected {
            border-color: var(--primary);
            background: var(--primary-light);
        }
        
        .toggle-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .morning {
            color: #4cc9f0;
        }
        
        .afternoon {
            color: #f8961e;
        }
        
        .full-day {
            color: #4ade80;
        }
        
        .unavailable {
            color: #f72585;
        }
        
        .toggle-label {
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
        }
        
        .availability-summary {
            background: var(--primary-light);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin: 1.5rem 0;
            font-size: 0.95rem;
            color: var(--dark);
            border-left: 4px solid var(--primary);
        }
        
        .repeat-button {
            background: var(--gray-light);
            color: var(--dark);
            margin-top: 1rem;
        }
        
        .repeat-button:hover {
            background: var(--gray);
            color: var(--white);
        }
        
        /* Button Styles */
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

        .btn-submit {
            margin-top: 1.5rem;
            font-size: 1.1rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }

        .alert-error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border: 1px solid rgba(247, 37, 133, 0.3);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .availability-grid {
                grid-template-columns: 1fr;
            }
            
            .toggle-options {
                grid-template-columns: 1fr;
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
                            <?= htmlspecialchars($unread_messages) ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="availability.php" class="nav-item active">
                    <i>⏱️</i> Availability
                </a>
                <a href="edit_profile.php" class="nav-item">
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
                    <h1>Set Your Availability</h1>
                    <p>Manage when you're available for training sessions</p>
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
                        <span class="profile-role"><?= htmlspecialchars($trainer['specialization'] ?? 'Fitness') ?> Trainer</span>
                    </div>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card">
                <h2 class="card-title">Set Your Weekly Availability</h2>
                <p>Click to select your availability for each day</p>
                
                <div class="availability-summary">
                    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($summaryText) ?>
                </div>
                
                <form method="POST" id="availability-form">
                    <div class="availability-grid">
                        <?php
                        $days = [
                            'Monday' => 'fas fa-calendar-day',
                            'Tuesday' => 'fas fa-calendar-day',
                            'Wednesday' => 'fas fa-calendar-day',
                            'Thursday' => 'fas fa-calendar-day',
                            'Friday' => 'fas fa-calendar-day',
                            'Saturday' => 'fas fa-calendar-week',
                            'Sunday' => 'fas fa-calendar-week'
                        ];
                        
                        foreach ($days as $day => $icon): 
                            $currentType = $availability[$day] ?? '';
                        ?>
                        <div class="day-card">
                            <div class="day-header">
                                <i class="<?= $icon ?>"></i>
                                <span><?= $day ?></span>
                            </div>
                            
                            <div class="toggle-options">
                                <div class="toggle-option morning <?= $currentType == 'morning' ? 'selected' : '' ?>" 
                                     onclick="selectOption(this, '<?= $day ?>', 'morning')">
                                    <i class="fas fa-sun"></i>
                                    <span class="toggle-label">Morning</span>
                                    <input type="radio" name="availability[<?= $day ?>]" value="morning" 
                                           <?= $currentType == 'morning' ? 'checked' : '' ?> style="display: none;">
                                </div>
                                
                                <div class="toggle-option afternoon <?= $currentType == 'afternoon' ? 'selected' : '' ?>" 
                                     onclick="selectOption(this, '<?= $day ?>', 'afternoon')">
                                    <i class="fas fa-cloud-sun"></i>
                                    <span class="toggle-label">Afternoon</span>
                                    <input type="radio" name="availability[<?= $day ?>]" value="afternoon" 
                                           <?= $currentType == 'afternoon' ? 'checked' : '' ?> style="display: none;">
                                </div>
                                
                                <div class="toggle-option full-day <?= $currentType == 'full' ? 'selected' : '' ?>" 
                                     onclick="selectOption(this, '<?= $day ?>', 'full')">
                                    <i class="fas fa-calendar-check"></i>
                                    <span class="toggle-label">Full Day</span>
                                    <input type="radio" name="availability[<?= $day ?>]" value="full" 
                                           <?= $currentType == 'full' ? 'checked' : '' ?> style="display: none;">
                                </div>
                                
                                <div class="toggle-option unavailable <?= empty($currentType) ? 'selected' : '' ?>" 
                                     onclick="selectOption(this, '<?= $day ?>', '')">
                                    <i class="fas fa-calendar-times"></i>
                                    <span class="toggle-label">Unavailable</span>
                                    <input type="radio" name="availability[<?= $day ?>]" value="" 
                                           <?= empty($currentType) ? 'checked' : '' ?> style="display: none;">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="button" class="btn repeat-button" onclick="repeatNextWeek()">
                            <i class="fas fa-redo"></i> Repeat for Next Week
                        </button>
                        
                        <button type="submit" name="save_availability" class="btn btn-submit">
                            <i class="fas fa-save"></i> Save Availability
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Select availability option
        function selectOption(element, day, type) {
            // Remove selected class from all options in this day card
            const card = element.closest('.day-card');
            card.querySelectorAll('.toggle-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update the hidden radio input
            const radio = element.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Update availability summary
            updateSummary();
        }
        
        // Update the availability summary text
        function updateSummary() {
            const summary = [];
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            
            document.querySelectorAll('.day-card').forEach((card, index) => {
                const selectedOption = card.querySelector('.toggle-option.selected');
                if (selectedOption) {
                    const type = selectedOption.querySelector('input').value;
                    if (type) {
                        let typeText = '';
                        switch(type) {
                            case 'morning': typeText = 'Morning'; break;
                            case 'afternoon': typeText = 'Afternoon'; break;
                            case 'full': typeText = 'Full Day'; break;
                        }
                        summary.push(`${days[index]} (${typeText})`);
                    }
                }
            });
            
            const summaryText = summary.length > 0 
                ? `You are available: ${summary.join(', ')}` 
                : "You haven't set any availability yet";
                
            document.querySelector('.availability-summary').innerHTML = 
                `<i class="fas fa-info-circle"></i> ${summaryText}`;
        }
        
        // Repeat for next week (placeholder function)
        function repeatNextWeek() {
            // This would be implemented based on your specific requirements
            alert('This would copy your current availability to next week in a real implementation');
        }
        
        // Initialize summary on load
        document.addEventListener('DOMContentLoaded', updateSummary);
    </script>
</body>
</html>