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

// Handle form submission for new workout plan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_workout'])) {
    $plan_name = $_POST['plan_name'];
    $description = $_POST['description'];
    $difficulty = $_POST['difficulty'];
    $duration_weeks = $_POST['duration_weeks'];
    $focus_area = $_POST['focus_area'];
    
    $insert_sql = "INSERT INTO workout_plans 
                  (plan_name, description, difficulty, duration_weeks, focus_area, created_by) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssisi", $plan_name, $description, $difficulty, $duration_weeks, $focus_area, $trainer_id);
    
    if ($stmt->execute()) {
        $success = "Workout plan added successfully!";
        // Refresh the workout plans list
        header("Location: workout_plans.php");
        exit();
    } else {
        $error = "Error adding workout plan: " . $conn->error;
    }
}

// Get workout plans - removed created_at from ORDER BY since it doesn't exist in the table
$sql = "SELECT * FROM workout_plans ORDER BY plan_name";
$result = $conn->query($sql);
$workouts = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Get unread messages count for sidebar
$unread_sql = "SELECT COUNT(*) as unread_count 
              FROM messages 
              WHERE receiver_id = ? AND is_read = FALSE";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $trainer_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_messages = $unread_result->fetch_assoc()['unread_count'];

// Get trainer data for profile section
$trainer_sql = "SELECT u.*, t.* FROM users u 
               JOIN trainers t ON u.user_id = t.trainer_id 
               WHERE u.user_id = ?";
$trainer_stmt = $conn->prepare($trainer_sql);
$trainer_stmt->bind_param("i", $trainer_id);
$trainer_stmt->execute();
$trainer_result = $trainer_stmt->get_result();
$trainer = $trainer_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plans - EliteFit Gym</title>
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

        /* Workout Grid */
        .workout-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .workout-item {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .workout-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .workout-item h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .workout-item p {
            color: var(--gray);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        /* Difficulty Badges */
        .difficulty {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .beginner {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .intermediate {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .advanced {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }

        .duration {
            color: var(--gray);
            font-size: 0.875rem;
        }

        .no-workouts {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-style: italic;
            grid-column: 1 / -1;
        }

        /* Form Styles */
        .form-container {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
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
        input[type="number"],
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
        input[type="number"]:focus,
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

        .btn-add {
            background: var(--success);
        }

        /* Alerts */
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

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .back-btn:hover {
            color: var(--secondary);
        }

        /* User Profile */
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

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .workout-list {
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
                <a href="workout_plans.php" class="nav-item active">
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
                    <h1>Workout Plans</h1>
                    <p>Manage and create workout plans for your members</p>
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

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Add New Workout Plan Form -->
            <div class="form-container">
                <h2>Create New Workout Plan</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="plan_name">Plan Name</label>
                        <input type="text" id="plan_name" name="plan_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="">Select Difficulty</option>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration_weeks">Duration (weeks)</label>
                        <input type="number" id="duration_weeks" name="duration_weeks" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="focus_area">Focus Area</label>
                        <input type="text" id="focus_area" name="focus_area" required>
                    </div>
                    
                    <button type="submit" name="add_workout" class="btn btn-add">
                        <i class="fas fa-plus"></i> Add Workout Plan
                    </button>
                </form>
            </div>

            <!-- Existing Workout Plans -->
            <h2>Available Workout Plans</h2>
            <div class="workout-list">
                <?php if (!empty($workouts)): ?>
                    <?php foreach ($workouts as $workout): ?>
                        <div class="workout-item">
                            <h3><?= htmlspecialchars($workout['plan_name']) ?></h3>
                            <p><?= htmlspecialchars($workout['description']) ?></p>
                            <?php if (isset($workout['difficulty'])): ?>
                                <div class="difficulty <?= strtolower($workout['difficulty']) ?>">
                                    <?= ucfirst($workout['difficulty']) ?> level
                                </div>
                            <?php endif; ?>
                            <?php if (isset($workout['duration_weeks']) && $workout['duration_weeks']): ?>
                                <div class="duration">
                                    Duration: <?= $workout['duration_weeks'] ?> week<?= $workout['duration_weeks'] != 1 ? 's' : '' ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($workout['focus_area'])): ?>
                                <div class="focus-area">
                                    Focus: <?= htmlspecialchars($workout['focus_area']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-workouts">
                        <p>No workout plans available yet. Create your first plan!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>