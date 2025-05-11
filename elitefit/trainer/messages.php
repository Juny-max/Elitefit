<?php
session_start();
include_once __DIR__ . "/../config.php";

// Redirect if not logged in as trainer
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
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

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $member_id = $_POST['member_id'];
    $message_text = $_POST['message_text'];
    
    $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iis", $trainer_id, $member_id, $message_text);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $success = "Message sent successfully!";
    } else {
        $error = "Failed to send message: " . $conn->error;
    }
}

// Get members assigned to this trainer - improved query
$members_sql = "SELECT u.user_id, u.first_name, u.last_name 
               FROM users u
               JOIN assigned_workouts aw ON u.user_id = aw.member_id
               WHERE aw.trainer_id = ? AND u.role = 'member'
               GROUP BY u.user_id, u.first_name, u.last_name
               ORDER BY u.first_name, u.last_name";
$members_stmt = $conn->prepare($members_sql);
$members_stmt->bind_param("i", $trainer_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);

// Get message history
$messages_sql = "SELECT m.*, u.first_name, u.last_name 
                FROM messages m
                JOIN users u ON m.receiver_id = u.user_id
                WHERE m.sender_id = ?
                ORDER BY m.sent_at DESC";
$messages_stmt = $conn->prepare($messages_sql);
$messages_stmt->bind_param("i", $trainer_id);
$messages_stmt->execute();
$messages_result = $messages_stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);

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

        /* Message Container */
        .message-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.5rem;
        }

        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
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

        /* Form Styles */
        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        select, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea {
            min-height: 150px;
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
            display: inline-block;
        }

        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        /* Message History */
        .message-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
        }

        .message-text {
            line-height: 1.5;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-sm);
            text-align: center;
            font-weight: 500;
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

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .message-container {
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
                <a href="messages.php" class="nav-item active">
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
                    <h1>Messages</h1>
                    <p>Communicate with your members</p>
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
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="message-container">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Send New Message</h2>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="member_id">Member</label>
                            <select id="member_id" name="member_id" required>
                                <option value="">Select Member</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?= $member['user_id'] ?>">
                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message_text">Message</label>
                            <textarea id="message_text" name="message_text" required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn">Send Message</button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Message History</h2>
                    </div>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message-item">
                                <div class="message-meta">
                                    <span>To: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?></span>
                                    <span><?= date('M j, Y g:i a', strtotime($message['sent_at'])) ?></span>
                                </div>
                                <div class="message-text"><?= htmlspecialchars($message['message_text']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No messages sent yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>