<?php
session_start();
include_once __DIR__ . "/../config.php";

// Initialize defaults array at the start
$defaults = [
    'height' => null,
    'weight' => null,
    'body_type' => null,
    'experience_level' => null,
    'first_name' => '',
    'last_name' => '',
    'profile_picture' => null,
    'date_registered' => date('Y-m-d H:i:s')
];

// Verify this is actually a member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    session_unset();
    session_destroy();
    header("Location: ../error.php?error=Access denied - Member area only");
    exit();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit();
}

// Get user data with error handling
try {
    $user_id = $_SESSION['user_id'];
    
    // First try getting complete data
    $sql = "SELECT u.*, mf.height, mf.weight, mf.body_type, mf.experience_level 
            FROM users u 
            LEFT JOIN member_fitness mf ON u.user_id = mf.member_id 
            WHERE u.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Database query failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user = array_merge($defaults, $user);
    } else {
        // Fallback to just user data if join failed
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Fallback query failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $user = array_merge($defaults, $user);
            
            // Log this for debugging
            error_log("Used fallback query for user: " . $user_id);
        } else {
            throw new Exception("User not found in database");
        }
    }
    
    // Get workout preferences
    $workout_sql = "SELECT wp.plan_name 
                   FROM member_workout_preferences mwp
                   JOIN workout_plans wp ON mwp.plan_id = wp.plan_id
                   WHERE mwp.member_id = ?
                   ORDER BY mwp.preference_order";
    $workout_stmt = $conn->prepare($workout_sql);
    $workout_stmt->bind_param("i", $user_id);
    $workout_stmt->execute();
    $workout_result = $workout_stmt->get_result();
    $workout_preferences = $workout_result->fetch_all(MYSQLI_ASSOC);

    // Get fitness goals
    $goals_sql = "SELECT goal_text FROM fitness_goals WHERE member_id = ?";
    $goals_stmt = $conn->prepare($goals_sql);
    $goals_stmt->bind_param("i", $user_id);
    $goals_stmt->execute();
    $goals_result = $goals_stmt->get_result();
    $fitness_goals = $goals_result->fetch_all(MYSQLI_ASSOC);

    // Get assigned trainer's availability
$trainer_availability = [];
$trainer_name = null;

$trainer_sql = "SELECT aw.trainer_id, u.first_name, u.last_name 
               FROM assigned_workouts aw
               JOIN users u ON aw.trainer_id = u.user_id
               WHERE aw.member_id = ?
               LIMIT 1";
$trainer_stmt = $conn->prepare($trainer_sql);
$trainer_stmt->bind_param("i", $user_id);
$trainer_stmt->execute();
$trainer_result = $trainer_stmt->get_result();

if ($trainer_result->num_rows > 0) {
    $trainer = $trainer_result->fetch_assoc();
    $trainer_id = $trainer['trainer_id'];
    $trainer_name = $trainer['first_name'] . ' ' . $trainer['last_name'];
    
    $availability_sql = "SELECT availability_id, trainer_id, day_of_week, availability_type
                        FROM trainer_availability 
                        WHERE trainer_id = ?
                        ORDER BY 
                            FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
    $availability_stmt = $conn->prepare($availability_sql);
    $availability_stmt->bind_param("i", $trainer_id);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();
    
    while ($row = $availability_result->fetch_assoc()) {
        $trainer_availability[$row['day_of_week']][] = [
            'availability_type' => $row['availability_type']
        ];
    }
}

    // Get messages
    $messages_sql = "SELECT m.*, u.first_name, u.last_name 
                    FROM messages m
                    JOIN users u ON m.sender_id = u.user_id
                    WHERE m.receiver_id = ?
                    ORDER BY m.sent_at DESC
                    LIMIT 3";
    $messages_stmt = $conn->prepare($messages_sql);
    $messages_stmt->bind_param("i", $user_id);
    $messages_stmt->execute();
    $messages_result = $messages_stmt->get_result();
    $messages = $messages_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Dashboard error for user " . ($_SESSION['user_id'] ?? 'unknown') . ": " . $e->getMessage());
    $_SESSION['error_message'] = "Profile loading failed";
    $_SESSION['error_details'] = "Error: " . $e->getMessage();
    header("Location: ../error.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Dashboard</title>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .stat-icon.primary {
            background: var(--primary);
        }

        .stat-icon.success {
            background: var(--success);
        }

        .stat-icon.warning {
            background: var(--warning);
        }

        .stat-icon.danger {
            background: var(--danger);
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .stat-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
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

        /* List Styles */
        .list {
            list-style: none;
        }

        .list-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .list-content {
            flex: 1;
        }

        .list-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .list-subtitle {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .list-meta {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-primary {
            background: var(--primary-light);
            color: var(--primary);
        }

        /* Availability Styles */
        .availability-container {
            margin-top: 1rem;
        }

        .availability-day {
            margin-bottom: 1rem;
        }

        .day-header {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .time-slot {
            background: var(--primary-light);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 500;
        }

        .off-day {
            color: var(--gray);
            font-style: italic;
            font-size: 0.875rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Calendar Styles */
        .calendar {
            display: grid;
            grid-template-rows: auto;
            gap: 2px;
            width: 100%;
        }
        .calendar-row {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }
        .calendar-cell {
            padding: 10px;
            text-align: center;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .calendar-cell.empty {
            visibility: hidden;
        }
        .calendar-header-cell {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
            cursor: default;
        }
        .calendar-cell.completed {
            background: var(--success);
            color: white;
            box-shadow: 0 0 8px var(--success);
        }
        .calendar-cell.today {
            border: 2px solid var(--primary);
            font-weight: bold;
        }
        .calendar-cell.missed {
            background: var(--danger);
            color: white;
            box-shadow: 0 0 8px var(--danger);
        }
        .calendar-cell:hover:not(.completed):not(.empty) {
            background: var(--primary-light);
            cursor: pointer;
        }
        .calendar-cell .tick {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.8em;
        }
        .calendar-cell .missed {
            position: absolute;
            bottom: 2px;
            right: 2px;
            font-size: 0.8em;
        }
        .calendar-cell .dot {
            position: absolute;
            bottom: 2px;
            left: 2px;
            font-size: 0.8em;
            color: var(--primary);
        }
        /* Error Message Styles */
        .error-msg {
            color: #f72585;
            padding: 1rem;
            text-align: center;
            background: #fff5f7;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .retry-btn {
            margin-top: 10px;
            padding: 8px 16px;
            background: #4361ee;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .retry-btn:hover {
            background: #3f37c9;
        }

        /* Calendar Lock Overlay */
        #calendar-lock-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            border-radius: 0; /* Make overlay rectangular */
            transition: background 0.3s;
            pointer-events: all;
        }
        #calendar-lock-overlay .padlock {
            font-size: 48px;
            color: #3f37c9;
            background: rgba(255,255,255,0.5);
            border-radius: 16px; /* Less rounding for a rectangular look */
            padding: 24px 32px;
            box-shadow: 0 2px 16px #0002;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
        }
        
        /* Add margin between cards */
        .card + .card, .card + [style*='height: 24px'] { margin-top: 1.5rem; }
        
        .calendar-loading {
            text-align: center;
            color: var(--gray);
            padding: 2rem 0;
            font-size: 1.1em;
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
                <a href="#" class="nav-item active">
                    <i>📊</i> Dashboard
                </a>
                <a href="actions/workout_plans.php" class="nav-item">
                    <i>💪</i> Workouts
                </a>
                <a href="actions/progress_tracker.php" class="nav-item">
                    <i>📈</i> Progress
                </a>
                <a href="actions/schedule.php" class="nav-item">
                    <i>📅</i> Schedule
                </a>
                <a href="actions/trainers.php" class="nav-item">
                    <i>👨‍🏫</i> Trainers
                </a>
                <a href="messages.php" class="nav-item">
                    <i>✉️</i> Messages
                </a>
                <a href="actions/edit_profile.php" class="nav-item">
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
                    <h1>Welcome back, <?= htmlspecialchars($user['first_name'] ?? 'Member') ?>!</h1>
                    <p>Here's what's happening with your fitness journey</p>
                </div>
                <div class="user-profile">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= '../' . htmlspecialchars($user['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 20px;">👤</span>
                        </div>
                    <?php endif; ?>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                        </span>
                        <span class="profile-role">Member</span>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Height</span>
                        <div class="stat-icon primary">📏</div>
                    </div>
                    <div class="stat-value">
                        <?= isset($user['height']) && $user['height'] !== null ? 
                            htmlspecialchars($user['height']) . ' cm' : 'Not set' ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Weight</span>
                        <div class="stat-icon success">⚖️</div>
                    </div>
                    <div class="stat-value">
                        <?= isset($user['weight']) && $user['weight'] !== null ? 
                            htmlspecialchars($user['weight']) . ' kg' : 'Not set' ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Body Type</span>
                        <div class="stat-icon warning">🧬</div>
                    </div>
                    <div class="stat-value">
                        <?= isset($user['body_type']) && $user['body_type'] !== null ? 
                            htmlspecialchars(ucfirst($user['body_type'])) : 'Not set' ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Experience Level</span>
                        <div class="stat-icon danger">🏆</div>
                    </div>
                    <div class="stat-value">
                        <?= isset($user['experience_level']) && $user['experience_level'] !== null ? 
                            htmlspecialchars(ucfirst($user['experience_level'])) : 'Not set' ?>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Workout Preferences Card -->
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <h2 class="card-title">Your Workout Preferences</h2>
                        </div>
                        <ul class="list">
                            <?php if (!empty($workout_preferences)): ?>
                                <?php foreach ($workout_preferences as $workout): ?>
                                    <li class="list-item">
                                        <div class="list-icon">💪</div>
                                        <div class="list-content">
                                            <div class="list-title"><?= htmlspecialchars($workout['plan_name']) ?></div>
                                            <div class="list-subtitle">Strength & Conditioning</div>
                                        </div>
                                        <div class="list-meta">Active</div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: var(--gray); text-align: center; padding: 1rem;">No workout preferences selected yet.</p>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Fitness Goals Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Your Fitness Goals</h2>
                        </div>
                        <ul class="list">
                            <?php if (!empty($fitness_goals)): ?>
                                <?php foreach ($fitness_goals as $goal): ?>
                                    <li class="list-item">
                                        <div class="list-icon">🎯</div>
                                        <div class="list-content">
                                            <div class="list-title"><?= htmlspecialchars($goal['goal_text']) ?></div>
                                            <div class="list-subtitle">In progress</div>
                                        </div>
                                        <span class="badge badge-primary">Active</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: var(--gray); text-align: center; padding: 1rem;">No fitness goals set yet.</p>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Add space between Fitness Goals and Calendar -->
                    <div style="height: 24px;"></div>

                    <!-- Calendar & Progress Card -->
                    <div class="card" id="calendar-card" style="margin-bottom: 1.5rem; position:relative;">
                        <div class="card-header">
                            <h2 class="card-title">Workout Calendar & Progress</h2>
                        </div>
                        <div id="calendar-container"><div class="calendar-loading">Loading calendar...</div></div>
                        <div id="calendar-progress"></div>
                        <div id="calendar-panel" style="display:none;"></div>
                        <div id="calendar-lock-overlay" style="display:none;"></div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="right-column">
                    <!-- Trainer Availability Card -->
                    <?php if (isset($trainer_name) && !empty($trainer_availability)): ?>
                        <div class="card" style="margin-bottom: 1.5rem;">
                            <div class="card-header">
                                <h2 class="card-title">Trainer Availability</h2>
                            </div>
                            <p style="margin-bottom: 1rem; font-weight: 500;">Your trainer: <?= htmlspecialchars($trainer_name) ?></p>
                            <!-- In your HTML where you display availability -->
<div class="availability-container">
    <?php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($days as $day): ?>
        <div class="availability-day">
            <div class="day-header"><?= $day ?></div>
            <?php if (!empty($trainer_availability[$day])): ?>
                <div class="time-slots">
                    <?php foreach ($trainer_availability[$day] as $slot): ?>
                        <span class="time-slot">
                            <?= htmlspecialchars($slot['availability_type']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="off-day">Not available</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
                        </div>
                    <?php endif; ?>

                    <!-- Messages Card -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Recent Messages</h2>
                        </div>
                        <ul class="list">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $message): ?>
                                    <li class="list-item">
                                        <div class="list-icon">✉️</div>
                                        <div class="list-content">
                                            <div class="list-title"><?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?></div>
                                            <div class="list-subtitle"><?= htmlspecialchars(substr($message['message_text'], 0, 50)) ?>...</div>
                                            <div class="list-meta"><?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?></div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <a href="messages.php" style="display: block; margin-top: 1rem; text-align: right; color: var(--primary); text-decoration: none; font-weight: 500;">View All Messages</a>
                            <?php else: ?>
                                <p style="color: var(--gray); text-align: center; padding: 1rem;">No messages from your trainer yet.</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Calendar & Settings Scripts -->
    <script>
    const today = new Date();
    let sessions = {};
    let locked = false;
    // Fetch sessions for current month
    function loadCalendar(month = new Date().getMonth() + 1, year = new Date().getFullYear()) {
        const calendarContainer = document.getElementById('calendar-container');
        calendarContainer.innerHTML = '<div class="calendar-loading">Loading calendar data...</div>';

        // Add cache busting parameter to prevent caching issues
        const timestamp = new Date().getTime();
        const url = `actions/calendar_api.php?action=get_sessions&month=${month}&year=${year}&_=${timestamp}`;

        fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) {
                // Get the actual error message from the response if possible
                return response.json().then(err => {
                    throw new Error(err.message || 'Network response was not ok');
                }).catch(() => {
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status !== 'success') {
                throw new Error(data.message || 'Invalid response from server');
            }

            // Process the sessions data
            sessions = data.data.sessions || {};
            renderCalendar(month, year);
            updateProgress(month, year);
        })
        .catch(error => {
            console.error('Calendar loading error:', error);
            calendarContainer.innerHTML = `
                <div class="error-msg">
                    Failed to load calendar: ${error.message}<br>
                    <button onclick="loadCalendar()" class="retry-btn">Try Again</button>
                </div>`;
        });
    }
    function renderCalendar(month, year) {
        const container = document.getElementById('calendar-container');
        container.innerHTML = '';
        
        const firstDay = new Date(year, month-1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        
        let html = '<div class="calendar"><div class="calendar-row calendar-header">';
        const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        days.forEach(d => html += `<div class='calendar-cell calendar-header-cell'>${d}</div>`);
        html += '</div>';
        
        let row = '<div class="calendar-row">';
        
        // Add empty cells for days before the first day of month
        for(let i = 0; i < firstDay.getDay(); i++) {
            row += '<div class="calendar-cell empty"></div>';
        }
        
        // Add cells for each day of the month
        for(let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const session = sessions[dateStr];
            const isToday = (d === today.getDate() && month === today.getMonth()+1 && year === today.getFullYear());
            const isPast = new Date(year, month-1, d) < new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const hasSession = session !== undefined;
            
            let cellClass = 'calendar-cell';
            if (isToday) cellClass += ' today';
            if (hasSession && (session.completed === true || session.completed_status == 1)) cellClass += ' completed';
            if (isPast && !hasSession) cellClass += ' missed';
            
            row += `<div class='${cellClass}' data-date='${dateStr}'>`;
            row += d;
            if (hasSession && (session.completed === true || session.completed_status == 1)) row += '<span class="tick">✔</span>';
            if (isPast && !hasSession) row += '<span class="missed">❌</span>';
            if (isToday) row += '<span class="dot"></span>';
            row += '</div>';
            
            // Start new row at the end of week
            if ((firstDay.getDay() + d) % 7 === 0 || d === daysInMonth) {
                row += '</div>';
                html += row;
                if (d !== daysInMonth) {
                    row = '<div class="calendar-row">';
                }
            }
        }
        
        html += '</div>';
        container.innerHTML = html;
        
        // Add click event for today's cell if it exists and isn't completed
        const todayCell = document.querySelector('.calendar-cell.today:not(.completed)');
        if (todayCell) {
            todayCell.onclick = () => openWorkoutPanelWithCategories(todayCell.dataset.date);
        }
    }
    function openWorkoutPanel(date) {
        if (locked) return;
        const panel = document.getElementById('calendar-panel');
        // Build category options
        let catOptions = workoutCategories.map(cat => `<option value="${cat.name}" data-duration="${cat.default_duration}">${cat.name}</option>`).join('');
        panel.innerHTML = `<div class='workout-panel'><h3>Start Workout for ${date}</h3>
            <form id='workout-form'>
            <label>Category:</label>
            <select name='workout_type' id='workout_type_select' required>
                <option value=''>Select Category</option>
                ${catOptions}
            </select><br>
            <label>Duration (min):</label>
            <input type='number' name='duration' id='duration_input' min='5' max='180' required readonly><br>
            <div id='workout-timer' style='margin: 1em 0; display:none; font-size:1.3em; color:#4361ee;'></div>
            <button type='button' id='start-workout-btn' class='btn btn-primary'>Start Workout</button>
            <button type='button' id='end-workout-btn' class='btn btn-success' style='display:none'>End Workout</button>
            </form>
        </div>`;
        panel.style.display = 'block';

        // Set duration input when category changes
        const catSelect = document.getElementById('workout_type_select');
        const durationInput = document.getElementById('duration_input');
        catSelect.addEventListener('change', function() {
            const selected = catSelect.options[catSelect.selectedIndex];
            durationInput.value = selected.dataset.duration || '';
            // If duration < 1, show seconds
            if(durationInput.value && parseFloat(durationInput.value) < 1) {
                durationInput.type = 'text';
                const seconds = Math.round(parseFloat(durationInput.value) * 60);
                durationInput.value = `${seconds} seconds`;
            } else {
                durationInput.type = 'number';
            }
        });
        // Set default if first selected
        catSelect.selectedIndex = 1;
        catSelect.dispatchEvent(new Event('change'));

        let timerInterval = null;
        let timeLeft = 0;
        const timerDiv = document.getElementById('workout-timer');
        const startBtn = document.getElementById('start-workout-btn');
        const endBtn = document.getElementById('end-workout-btn');
        startBtn.onclick = function() {
            // Start timer
            let durationVal = durationInput.value;
            let seconds = 0;
            if(durationVal.includes('seconds')) {
                seconds = parseInt(durationVal);
            } else {
                seconds = Math.round(parseFloat(durationVal) * 60);
            }
            timeLeft = seconds;
            startBtn.style.display = 'none';
            endBtn.style.display = '';
            timerDiv.style.display = '';
            updateTimerDisplay();
            timerInterval = setInterval(() => {
                timeLeft--;
                updateTimerDisplay();
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerDiv.innerHTML = '<span style="color:green">Time is up! Please end workout.</span>';
                }
            }, 1000);
        };
        endBtn.onclick = function() {
            clearInterval(timerInterval);
            // Submit workout as completed
            const fd = new FormData(document.getElementById('workout-form'));
            fd.append('action','add_session');
            fd.append('date',date);
            fetch('actions/calendar_api.php', {method:'POST', body:fd})
            .then(r=>r.json()).then(data=>{
                if(data.success){
                    locked = true;
                    loadCalendar();
                    panel.innerHTML = `<div class="success-msg">Workout completed! Date locked until tomorrow.</div>`;
                    setTimeout(()=>panel.style.display='none', 2000);
                }else{
                    panel.innerHTML = `<div class='error-msg'>${data.error || data.message || 'Unknown error'}</div>`;
                }
            });
        };
        function updateTimerDisplay() {
            const m = Math.floor(timeLeft/60);
            const s = timeLeft%60;
            timerDiv.textContent = `Time left: ${m}:${s.toString().padStart(2,'0')}`;
        }
    }

    let workoutCategories = [];
    function fetchWorkoutCategories() {
        return fetch('actions/calendar_api.php?action=get_workout_categories')
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    workoutCategories = data.categories;
                } else {
                    workoutCategories = [
                        {id: 1, name: 'Cardio', default_duration: 30},
                        {id: 2, name: 'Strength', default_duration: 45},
                        {id: 3, name: 'Yoga', default_duration: 40}
                    ];
                }
            });
    }

    function openWorkoutPanelWithCategories(date) {
        if (workoutCategories.length === 0) {
            fetchWorkoutCategories().then(() => openWorkoutPanel(date));
        } else {
            openWorkoutPanel(date);
        }
    }

    function updateProgress(month, year) {
        const progressDiv = document.getElementById('calendar-progress');
        const total = Object.keys(sessions).length;
        let completed = 0, missed = 0;
        for(let d in sessions) {
            // Support both .completed and .completed_status for backward compatibility
            if(sessions[d].completed === true || sessions[d].completed_status == 1) completed++;
        }
        const lastDay = new Date(year, month, 0).getDate();
        for(let d=1; d<=lastDay; d++){
            const dateStr = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            // Only count as missed if it's strictly before today
            if(new Date(year,month-1,d) < new Date(today.getFullYear(), today.getMonth(), today.getDate()) && !sessions[dateStr]) missed++;
        }
        progressDiv.innerHTML = `<div class='progress-bar'><div class='progress-completed' style='width:${completed/lastDay*100}%'></div></div>
            <div class='progress-info'>Completed: ${completed} | Missed: ${missed} | Total Days: ${lastDay}</div>`;
        // Show completed count below calendar as requested
        let completedDisplay = document.getElementById('completed-count');
        if(!completedDisplay) {
            completedDisplay = document.createElement('div');
            completedDisplay.id = 'completed-count';
            const calendar = document.getElementById('calendar-progress');
            calendar.parentNode.insertBefore(completedDisplay, calendar.nextSibling);
        }
        completedDisplay.innerHTML = `Workouts completed this month: <span style='color:#3f37c9'>${completed}</span>`;
    }
    // Initial load
    loadCalendar();

    // Fetch and display completed workouts per category for the dashboard
    function loadCompletedWorkoutsPerCategory() {
        fetch('actions/calendar_api.php?action=get_completed_workouts_per_category', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
                let html = '<div class="category-completions" style="margin:10px 0 20px 0;text-align:center;">';
                html += '<strong>Completed Workouts by Category (This Month):</strong><ul style="list-style:none;padding:0;">';
                data.data.forEach(cat => {
                    html += `<li><span style='color:#3f37c9;'>${cat.name}</span>: <b>${cat.completed_count}</b></li>`;
                });
                html += '</ul></div>';
                let target = document.getElementById('completed-category-list');
                if (!target) {
                    target = document.createElement('div');
                    target.id = 'completed-category-list';
                    const calendar = document.getElementById('calendar-progress');
                    calendar.parentNode.insertBefore(target, calendar);
                }
                target.innerHTML = html;
            } else {
                // Remove the display if no completed workouts
                let target = document.getElementById('completed-category-list');
                if (target) target.innerHTML = '';
            }
        });
    }
    // Call on page load
    loadCompletedWorkoutsPerCategory();

    // Trainer Booking Lock Logic
    <?php
    // Check for active booking
    $booking_locked = false;
    $booking_sql = "SELECT booking_id FROM booked_sessions WHERE member_id = ? AND status = 'scheduled' AND session_date >= CURDATE() LIMIT 1";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param('i', $user_id);
    $booking_stmt->execute();
    $booking_stmt->store_result();
    if ($booking_stmt->num_rows > 0) {
        $booking_locked = true;
    }
    ?>
    const calendarLocked = <?= $booking_locked ? 'true' : 'false' ?>;
    function updateCalendarLock() {
        const overlay = document.getElementById('calendar-lock-overlay');
        if(calendarLocked) {
            overlay.innerHTML = `<div class='padlock'>🔒<div class='padlock-label'>Calendar locked<br>Active booking with trainer</div></div>`;
            overlay.style.display = 'flex';
            document.getElementById('calendar-container').style.pointerEvents = 'none';
        } else {
            overlay.style.display = 'none';
            document.getElementById('calendar-container').style.pointerEvents = '';
        }
    }
    document.addEventListener('DOMContentLoaded', updateCalendarLock);
    // Also call after calendar loads in case of AJAX navigation
    setTimeout(updateCalendarLock, 200);
    </script>
    <script src="ai_chat_widget.js"></script>
</body>
</html>