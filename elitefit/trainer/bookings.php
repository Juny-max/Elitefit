<?php
session_start();
include_once __DIR__ . "/../config.php";

// Redirect if not logged in as trainer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

// Store trainer ID from session
$trainer_id = $_SESSION['user_id'];

// Handle session completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_session'])) {
    $booking_id = $_POST['booking_id'];
    
    $update_sql = "UPDATE booked_sessions SET status = 'completed' WHERE booking_id = ? AND trainer_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $booking_id, $trainer_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Session marked as completed successfully!";
    } else {
        $error_message = "Error updating session status.";
    }
}

// Get all booked sessions for this trainer
$bookings_sql = "SELECT bs.*, u.first_name, u.last_name, u.profile_picture 
                FROM booked_sessions bs
                JOIN users u ON bs.member_id = u.user_id
                WHERE bs.trainer_id = ?
                ORDER BY bs.session_date DESC, bs.start_time DESC";
$bookings_stmt = $conn->prepare($bookings_sql);
$bookings_stmt->bind_param("i", $trainer_id);
$bookings_stmt->execute();
$bookings_result = $bookings_stmt->get_result();
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Manage Bookings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Include all the CSS styles from dashboard.php */
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

        /* Sidebar Styles - Same as dashboard.php */
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

        /* Main Content */
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

        /* Booking Table */
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .bookings-table th, 
        .bookings-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        .bookings-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        .bookings-table tr:last-child td {
            border-bottom: none;
        }

        .booking-member {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-scheduled {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .status-completed {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .status-cancelled {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .complete-btn {
            background: var(--success);
            color: white;
        }

        .complete-btn:hover {
            opacity: 0.9;
        }

        /* Locked Trainer Overlay */
        .trainer-locked {
            position: relative;
        }

        .trainer-locked::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            z-index: 1;
            border-radius: var(--radius);
        }

        .lock-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            color: var(--danger);
            font-size: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar - Same as dashboard.php -->
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
                    <h1>Manage Bookings</h1>
                    <p>View and manage all your training sessions</p>
                </div>
            </header>

            <?php if (isset($success_message)): ?>
                <div class="success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <div class="booking-member">
                                        <?php if (!empty($booking['profile_picture'])): ?>
                                            <img src="../<?= htmlspecialchars($booking['profile_picture']) ?>" class="member-avatar" alt="Member Avatar">
                                        <?php else: ?>
                                            <div class="member-avatar" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                                                <span>👤</span>
                                            </div>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($booking['session_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($booking['start_time'])) ?> - <?= date('g:i A', strtotime($booking['end_time'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] == 'scheduled'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                            <button type="submit" name="complete_session" class="action-btn complete-btn">
                                                Mark Complete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
