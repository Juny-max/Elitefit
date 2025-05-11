<?php
session_start();
// Correct the path to config.php (assuming schedule.php is in member/actions/)
include_once __DIR__ . "/../../config.php";

// Redirect if not logged in as member
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SESSION['role'] != 'member') {
    header("Location: ../../dashboard.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Check if connection exists
if (!isset($conn)) {
    die("Database connection not established");
}

// Get assigned workouts
$workouts_sql = "SELECT aw.*, wp.plan_name, wp.description, u.first_name as trainer_first_name, u.last_name as trainer_last_name 
                FROM assigned_workouts aw
                JOIN workout_plans wp ON aw.plan_id = wp.plan_id
                JOIN users u ON aw.trainer_id = u.user_id
                WHERE aw.member_id = ?
                ORDER BY aw.start_date";
$workouts_stmt = $conn->prepare($workouts_sql);
$workouts_stmt->bind_param("i", $member_id);
$workouts_stmt->execute();
$workouts_result = $workouts_stmt->get_result();
$assigned_workouts = $workouts_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Workout Schedule - EliteFit Gym</title>
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
            margin-top: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: var(--dark);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .nav-item.active, .nav-item:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .main-content {
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            margin-bottom: 2rem;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .page-title {
            flex-grow: 1;
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: var(--gray);
            font-size: 0.875rem;
        }

        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        /* Workout Card */
        .workout-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .badge-active {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .badge-upcoming {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .badge-completed {
            background: rgba(108, 117, 125, 0.1);
            color: var(--gray);
        }

        /* Notes */
        .notes {
            margin: 1rem 0;
            padding: 1rem;
            background: var(--gray-light);
            border-radius: var(--radius-sm);
            color: var(--dark);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            grid-column: 1 / -1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .header {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                width: 100%;
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
                <a href="../dashboard.php" class="nav-item"> <i>📊</i> Dashboard </a>
                <a href="workout_plans.php" class="nav-item"> <i>💪</i> Workouts </a>
                <a href="progress_tracker.php" class="nav-item"> <i>📈</i> Progress </a>
                <a href="schedule.php" class="nav-item active"> <i>📅</i> Schedule </a>
                <a href="trainers.php" class="nav-item"> <i>👨‍🏫</i> Trainers </a>
                <a href="../messages.php" class="nav-item"> <i>✉️</i> Messages </a>
                <a href="edit_profile.php" class="nav-item"> <i>👤</i> Profile </a>
                <a href="../../logout.php" class="nav-item"> <i>🚪</i> Logout </a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <div class="header">
                    <a href="../dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <h1 style="text-align: center;">Your Workout Schedule</h1>
                </div>
                <div class="schedule-grid">
                    <?php if (!empty($assigned_workouts)): ?>
                        <?php foreach ($assigned_workouts as $workout): ?>
                            <div class="workout-card">
                                <h3><?= htmlspecialchars($workout['plan_name']) ?></h3>
                                <div class="trainer-info">
                                    Assigned by: <?= htmlspecialchars($workout['trainer_first_name'] . ' ' . $workout['trainer_last_name']) ?>
                                </div>
                                <div class="date-range">
                                    <?= date('M j, Y', strtotime($workout['start_date'])) ?> - <?= date('M j, Y', strtotime($workout['end_date'])) ?>
                                </div>
                                <p><?= htmlspecialchars($workout['description']) ?></p>
                                <?php if (!empty($workout['notes'])): ?>
                                    <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                                        <strong>Trainer Notes:</strong> <?= htmlspecialchars($workout['notes']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                                    $status = 'upcoming';
                                    $today = date('Y-m-d');
                                    if ($today > $workout['end_date']) {
                                        $status = 'completed';
                                    } elseif ($today >= $workout['start_date']) {
                                        $status = 'active';
                                    }
                                ?>
                                <span class="badge badge-<?= $status ?>"><?= ucfirst($status) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="workout-card">
                            <h3>No Workouts Assigned</h3>
                            <p>You don't have any workouts assigned yet. Check back later or contact your trainer.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <!-- Add Font Awesome for the arrow icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>