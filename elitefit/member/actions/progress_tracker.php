<?php
session_start();
// Fix the config file path
$config_path = realpath(dirname(__FILE__)) . '/../../config.php';
if (file_exists($config_path)) {
    include_once $config_path;
} else {
    die("Configuration file not found");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only members can access this page
if ($_SESSION['role'] != 'member') {
    header("Location: dashboard.php");
    exit();
}

// Check if connection exists
if (!isset($conn)) {
    die("Database connection failed");
}

$member_id = $_SESSION['user_id'];

// Get all progress records for this member
$progress_sql = "SELECT pt.*, u.first_name as trainer_first_name, u.last_name as trainer_last_name 
                FROM progress_tracking pt
                JOIN users u ON pt.trainer_id = u.user_id
                WHERE pt.member_id = ?
                ORDER BY pt.measurement_date DESC";
$progress_stmt = $conn->prepare($progress_sql);
$progress_stmt->bind_param("i", $member_id);
$progress_stmt->execute();
$progress_result = $progress_stmt->get_result();
$progress_records = $progress_result->fetch_all(MYSQLI_ASSOC);

// Calculate progress if we have at least 2 records
$progress_stats = [];
if (count($progress_records) >= 2) {
    $latest = $progress_records[0];
    $previous = $progress_records[1];
    
    $progress_stats = [
        'weight_diff' => $latest['weight'] - $previous['weight'],
        'body_fat_diff' => $latest['body_fat'] - $previous['body_fat'],
        'muscle_mass_diff' => $latest['muscle_mass'] - $previous['muscle_mass']
    ];
}

// Get user data for header
$user_sql = "SELECT first_name, last_name, profile_picture FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $member_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Progress - EliteFit Gym</title>
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

        /* Progress Tracker Styles */
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-light);
        }

        .progress-indicator {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .progress-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .progress-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .progress-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .progress-positive {
            color: #10b981;
        }

        .progress-negative {
            color: var(--danger);
        }

        .progress-neutral {
            color: var(--gray);
        }

        .progress-meta {
            font-size: 0.875rem;
            color: var(--gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            overflow: hidden;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: var(--gray-light);
        }

        tr:hover {
            background-color: var(--primary-light);
        }

        .no-progress {
            text-align: center;
            padding: 3rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin: 2rem 0;
        }

        .no-progress-icon {
            font-size: 3rem;
            color: var(--gray-light);
            margin-bottom: 1rem;
        }

        .no-progress h3 {
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .no-progress p {
            color: var(--gray);
            margin-bottom: 0.5rem;
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
            margin-bottom: 1.5rem;
        }

        .back-btn:hover {
            background: var(--primary);
            color: var(--white);
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
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
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
                <a href="workout_plans.php" class="nav-item">
                    <i>💪</i> Workouts
                </a>
                <a href="#" class="nav-item active">
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
                <a href="../logout.php" class="nav-item">
                    <i>🚪</i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="page-title">
                    <h1>Your Progress Tracker</h1>
                </div>
                <div class="user-profile">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= '../../' . htmlspecialchars($user['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 20px;">👤</span>
                        </div>
                    <?php endif; ?>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
                        </span>
                    </div>
                </div>
            </header>

            <a href="../dashboard.php" class="back-btn">
                <span>←</span> Back to Dashboard
            </a>

            <?php if (!empty($progress_records)): ?>
                <h2 class="section-title">Your Progress Overview</h2>
                <div class="progress-indicator">
                    <div class="progress-card">
                        <div class="progress-title">Weight Change</div>
                        <?php if (count($progress_records) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['weight_diff'] < 0 ? 'progress-positive' : ($progress_stats['weight_diff'] > 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['weight_diff'], 1) ?> kg
                            </div>
                            <div class="progress-meta">Since <?= date('M j, Y', strtotime($previous['measurement_date'])) ?></div>
                        <?php else: ?>
                            <div class="progress-value">Initial Measurement</div>
                            <div class="progress-meta">More data needed for comparison</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="progress-card">
                        <div class="progress-title">Body Fat Change</div>
                        <?php if (count($progress_records) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['body_fat_diff'] < 0 ? 'progress-positive' : ($progress_stats['body_fat_diff'] > 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['body_fat_diff'], 1) ?>%
                            </div>
                            <div class="progress-meta">Since <?= date('M j, Y', strtotime($previous['measurement_date'])) ?></div>
                        <?php else: ?>
                            <div class="progress-value">Initial Measurement</div>
                            <div class="progress-meta">More data needed for comparison</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="progress-card">
                        <div class="progress-title">Muscle Mass Change</div>
                        <?php if (count($progress_records) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['muscle_mass_diff'] > 0 ? 'progress-positive' : ($progress_stats['muscle_mass_diff'] < 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['muscle_mass_diff'], 1) ?> kg
                            </div>
                            <div class="progress-meta">Since <?= date('M j, Y', strtotime($previous['measurement_date'])) ?></div>
                        <?php else: ?>
                            <div class="progress-value">Initial Measurement</div>
                            <div class="progress-meta">More data needed for comparison</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h2 class="section-title">Your Progress History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight (kg)</th>
                            <th>Body Fat (%)</th>
                            <th>Muscle Mass (kg)</th>
                            <th>Notes</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progress_records as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['measurement_date'])) ?></td>
                                <td><?= number_format($record['weight'], 1) ?></td>
                                <td><?= number_format($record['body_fat'], 1) ?></td>
                                <td><?= number_format($record['muscle_mass'], 1) ?></td>
                                <td><?= htmlspecialchars($record['notes']) ?></td>
                                <td><?= htmlspecialchars($record['trainer_first_name'] . ' ' . $record['trainer_last_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-progress">
                    <div class="no-progress-icon">📊</div>
                    <h3>No Progress Records Found</h3>
                    <p>Your trainer hasn't recorded any progress measurements for you yet.</p>
                    <p>Please check back later or contact your trainer.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>