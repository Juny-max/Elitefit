<?php
session_start();
include_once __DIR__ . "/../config.php";

// Redirect if not logged in as member
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'member') {
    header("Location: ../dashboard.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Mark messages as read when viewed
if (!isset($_GET['no_mark_read'])) {
    $update_sql = "UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND is_read = FALSE";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $member_id);
    $update_stmt->execute();
}

// Get messages
$messages_sql = "SELECT m.*, u.first_name, u.last_name 
                FROM messages m
                JOIN users u ON m.sender_id = u.user_id
                WHERE m.receiver_id = ?
                ORDER BY m.sent_at DESC";
$messages_stmt = $conn->prepare($messages_sql);
$messages_stmt->bind_param("i", $member_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);

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
    <title>Messages - EliteFit Gym</title>
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

        /* Messages List */
        .messages-container {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .message-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-item.unread {
            background-color: #f8f9ff;
            border-left: 4px solid var(--primary);
        }

        .message-item:hover {
            background-color: var(--primary-light);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .message-sender {
            font-weight: 600;
            color: var(--primary);
        }

        .message-date {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .message-content {
            color: var(--dark);
            line-height: 1.6;
        }

        .no-messages {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .no-messages-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
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
                <a href="#" class="nav-item active">
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
                    <h1>Your Messages</h1>
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
                    </div>
                </div>
            </header>

            <a href="dashboard.php" class="back-btn">
                <span>←</span> Back to Dashboard
            </a>

            <div class="messages-container">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item <?= $message['is_read'] ? '' : 'unread' ?>">
                            <div class="message-header">
                                <span class="message-sender">From: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?></span>
                                <span class="message-date"><?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?></span>
                            </div>
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($message['message_text'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-messages">
                        <div class="no-messages-icon">✉️</div>
                        <p>You don't have any messages yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>