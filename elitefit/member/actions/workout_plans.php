<?php
session_start();

// Use absolute path to config.php
$config_path = realpath(dirname(__FILE__) . '/../../config.php');
if (file_exists($config_path)) {
    include_once $config_path;
} else {
    die("Configuration file not found");
}

// Check if connection exists
if (!isset($conn)) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get workout plans
$sql = "SELECT * FROM workout_plans";
$result = $conn->query($sql);
$workouts = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plans - EliteFit Gym</title>
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

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-button:hover {
            color: var(--secondary);
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
                <a href="../dashboard.php" class="nav-item">
                    <i>📊</i> Dashboard
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
                <a href="trainers.php" class="nav-item">
                    <i>👨‍🏫</i> Trainers
                </a>
                <a href="../messages.php" class="nav-item">
                    <i>✉️</i> Messages
                </a>
                <a href="edit_profile.php" class="nav-item">
                    <i>👤</i> Profile
                </a>
                <a href="/elitefit/logout.php" class="nav-item">
                    <i>🚪</i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <a href="../dashboard.php" class="back-button">
                <i>←</i> Back to Dashboard
            </a>

            <header class="header">
                <div class="page-title">
                    <h1>Available Workout Plans</h1>
                    <p>Choose from our professionally designed workout programs</p>
                </div>
            </header>

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
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-workouts">
                        <p>No workout plans available at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>