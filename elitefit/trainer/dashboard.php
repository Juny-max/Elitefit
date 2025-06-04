<?php
session_start();
include_once __DIR__ . "/../config.php";

// Verify session based on role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Member dashboard
if ($_SESSION['role'] == 'member') {
    // Member dashboard logic
} 
// Trainer dashboard
elseif ($_SESSION['role'] == 'trainer') {
    // Trainer dashboard logic
} 
else {
    // Invalid role
    header("Location: ../index.php");
    exit();
}

// Get trainer data
$trainer_id = $_SESSION['user_id'];
$sql = "SELECT u.*, t.* FROM users u 
        JOIN trainers t ON u.user_id = t.trainer_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();

// Count assigned members
$members_sql = "SELECT COUNT(DISTINCT member_id) as member_count 
               FROM assigned_workouts 
               WHERE trainer_id = ?";
$members_stmt = $conn->prepare($members_sql);
$members_stmt->bind_param("i", $trainer_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$member_count = $members_result->fetch_assoc()['member_count'];

// Get recent assigned workouts
$workouts_sql = "SELECT aw.*, u.first_name, u.last_name, wp.plan_name 
                FROM assigned_workouts aw
                JOIN users u ON aw.member_id = u.user_id
                JOIN workout_plans wp ON aw.plan_id = wp.plan_id
                WHERE aw.trainer_id = ?
                ORDER BY aw.created_at DESC
                LIMIT 5";
$workouts_stmt = $conn->prepare($workouts_sql);
$workouts_stmt->bind_param("i", $trainer_id);
$workouts_stmt->execute();
$workouts_result = $workouts_stmt->get_result();
$recent_workouts = $workouts_result->fetch_all(MYSQLI_ASSOC);

// Get upcoming progress check-ins
$progress_sql = "SELECT pt.*, u.first_name, u.last_name 
                FROM progress_tracking pt
                JOIN users u ON pt.member_id = u.user_id
                WHERE pt.trainer_id = ? AND pt.measurement_date >= CURDATE()
                ORDER BY pt.measurement_date ASC
                LIMIT 5";
$progress_stmt = $conn->prepare($progress_sql);
$progress_stmt->bind_param("i", $trainer_id);
$progress_stmt->execute();
$progress_result = $progress_stmt->get_result();
$upcoming_progress = $progress_result->fetch_all(MYSQLI_ASSOC);

// Get booked sessions
$bookings_sql = "SELECT bs.*, u.first_name, u.last_name 
                FROM booked_sessions bs
                JOIN users u ON bs.member_id = u.user_id
                WHERE bs.trainer_id = ? AND bs.status = 'scheduled'
                ORDER BY bs.session_date ASC, bs.start_time ASC
                LIMIT 5";
$bookings_stmt = $conn->prepare($bookings_sql);
$bookings_stmt->bind_param("i", $trainer_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$booked_sessions = $bookings_result->fetch_all(MYSQLI_ASSOC);

// Get unread messages
$messages_sql = "SELECT COUNT(*) as unread_count 
                FROM messages 
                WHERE receiver_id = ? AND is_read = FALSE";
$messages_stmt = $conn->prepare($messages_sql);
$messages_stmt->bind_param("i", $trainer_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$unread_messages = $messages_result->fetch_assoc()['unread_count'];

// Get equipment status
$equipment_sql = "SELECT * FROM equipment ORDER BY status, name";
$equipment_result = $conn->query($equipment_sql);
$equipment = $equipment_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Trainer Dashboard</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
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
            flex-direction: column;
            gap: 0.5rem;
        }

        .list-item:last-child {
            border-bottom: none;
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

        .badge-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }

        .badge-danger {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .quick-action {
            background: var(--primary);
            color: white;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            text-align: center;
            text-decoration: none;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .quick-action:hover {
            background: var(--secondary);
        }

        /* View All Link */
        .view-all {
            display: block;
            margin-top: 1rem;
            text-align: right;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
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
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .quick-actions {
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
                <a href="dashboard.php" class="nav-item active">
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
                    <h1>Trainer Dashboard</h1>
                    <p>Welcome back, <?= htmlspecialchars($trainer['first_name'] . ' ' . ($trainer['last_name'] ?? '')) ?></p>
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

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Assigned Members</span>
                        <div class="stat-icon primary">👥</div>
                    </div>
                    <div class="stat-value"><?= $member_count ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Active Workouts</span>
                        <div class="stat-icon success">💪</div>
                    </div>
                    <div class="stat-value"><?= count($recent_workouts) ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Upcoming Check-ins</span>
                        <div class="stat-icon warning">📅</div>
                    </div>
                    <div class="stat-value"><?= count($upcoming_progress) ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Unread Messages</span>
                        <div class="stat-icon danger">✉️</div>
                    </div>
                    <div class="stat-value"><?= $unread_messages ?></div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Workouts Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Workouts</h2>
                    </div>
                    <ul class="list">
                        <?php if (!empty($recent_workouts)): ?>
                            <?php foreach ($recent_workouts as $workout): ?>
                                <li class="list-item">
                                    <div style="display: flex; justify-content: space-between;">
                                        <strong><?= htmlspecialchars($workout['first_name'] . ' ' . $workout['last_name']) ?></strong>
                                        <span class="badge <?= $workout['status'] == 'active' ? 'badge-success' : ($workout['status'] == 'completed' ? 'badge-primary' : 'badge-warning') ?>">
                                            <?= ucfirst($workout['status']) ?>
                                        </span>
                                    </div>
                                    <div><?= htmlspecialchars($workout['plan_name']) ?></div>
                                    <small><?= date('M d, Y', strtotime($workout['start_date'])) ?> - <?= date('M d, Y', strtotime($workout['end_date'])) ?></small>
                                </li>
                            <?php endforeach; ?>
                            <a href="all_workouts.php" class="view-all">View All Workouts</a>
                        <?php else: ?>
                            <li class="list-item">No recent workout assignments</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Upcoming Progress Check-ins -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Upcoming Check-ins</h2>
                    </div>
                    <ul class="list">
                        <?php if (!empty($upcoming_progress)): ?>
                            <?php foreach ($upcoming_progress as $checkin): ?>
                                <li class="list-item">
                                    <strong><?= htmlspecialchars($checkin['first_name'] . ' ' . $checkin['last_name']) ?></strong>
                                    <div><?= date('M d, Y', strtotime($checkin['measurement_date'])) ?></div>
                                    <div class="badge badge-primary">Progress Check</div>
                                </li>
                            <?php endforeach; ?>
                            <a href="progress_tracker.php" class="view-all">View All Check-ins</a>
                        <?php else: ?>
                            <li class="list-item">No upcoming check-ins scheduled</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Booked Sessions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Booked Sessions</h2>
                    </div>
                    <ul class="list">
                        <?php if (!empty($booked_sessions)): ?>
                            <?php foreach ($booked_sessions as $session): ?>
                                <li class="list-item">
                                    <strong><?= htmlspecialchars($session['first_name'] . ' ' . $session['last_name']) ?></strong>
                                    <div><?= date('M d, Y', strtotime($session['session_date'])) ?></div>
                                    <div><?= date('g:i A', strtotime($session['start_time'])) ?> - <?= date('g:i A', strtotime($session['end_time'])) ?></div>
                                    <div class="badge badge-success">Scheduled</div>
                                </li>
                            <?php endforeach; ?>
                            <a href="bookings.php" class="view-all">View All Bookings</a>
                        <?php else: ?>
                            <li class="list-item">No upcoming sessions booked</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Equipment Status -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Equipment Status</h2>
                    </div>
                    <ul class="list">
                        <?php if (!empty($equipment)): ?>
                            <?php foreach ($equipment as $item): ?>
                                <li class="list-item" style="flex-direction: row; align-items: center; justify-content: space-between;">
                                    <span><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="badge <?= 
                                        $item['status'] == 'available' ? 'badge-success' : 
                                        ($item['status'] == 'maintenance' ? 'badge-warning' : 'badge-danger')
                                    ?>">
                                        <?= ucfirst(str_replace('_', ' ', $item['status'])) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-item">No equipment information available</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="assign_workout.php" class="quick-action">Assign Workout</a>
                        <a href="members.php" class="quick-action">Manage Members</a>
                        <a href="schedule.php" class="quick-action">View Schedule</a>
                        <a href="messages.php" class="quick-action">Messages</a>
                        <a href="progress_tracker.php" class="quick-action">Track Progress</a>
                        <a href="availability.php" class="quick-action">Set Availability</a>
                    </div>
                </div>

                <!-- Trainer Profile -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Your Profile</h2>
                    </div>
                    <ul class="list">
                        <li class="list-item">
                            <strong>Specialization:</strong> <?= htmlspecialchars($trainer['specialization']) ?>
                        </li>
                        <li class="list-item">
                            <strong>Certification:</strong> <?= htmlspecialchars($trainer['certification']) ?>
                        </li>
                        <li class="list-item">
                            <strong>Experience:</strong> <?= htmlspecialchars($trainer['years_experience']) ?> years
                        </li>
                        <li class="list-item">
                            <strong>Bio:</strong> <?= htmlspecialchars($trainer['bio']) ?>
                        </li>
                        <a href="edit_profile.php" class="view-all">Edit Profile</a>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script src="../member/ai_chat_widget.js"></script>
</body>
</html>