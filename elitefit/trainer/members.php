<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get all members with fitness details
$members_sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.contact_number, 
               u.profile_picture, mf.height, mf.weight, mf.body_type, mf.experience_level
               FROM users u
               LEFT JOIN member_fitness mf ON u.user_id = mf.member_id
               WHERE u.role = 'member'";
$members_result = $conn->query($members_sql);
$members = $members_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - EliteFit Gym</title>
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

        /* Table Styles */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: var(--gray-light);
        }

        /* Member Profile */
        .member-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .member-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-light);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-info {
            background: var(--warning);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
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
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
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
                <a href="members.php" class="nav-item active">
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
            <a href="dashboard.php" class="back-button">
                <i>←</i> Back to Dashboard
            </a>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Manage Members</h2>
                </div>
                
                <?php if (count($members) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Contact</th>
                                <th>Height</th>
                                <th>Weight</th>
                                <th>Body Type</th>
                                <th>Experience</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <div class="member-profile">
                                            <?php if (!empty($member['profile_picture'])): ?>
                                                <img src="<?= '../' . htmlspecialchars($member['profile_picture']) ?>" class="member-pic" alt="Member Profile">
                                            <?php else: ?>
                                                <div class="member-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                                                    <span style="font-size: 16px;">👤</span>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.875rem;">
                                            <?= htmlspecialchars($member['email']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--gray);">
                                            <?= htmlspecialchars($member['contact_number'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td><?= $member['height'] ? htmlspecialchars($member['height']) . ' cm' : 'N/A' ?></td>
                                    <td><?= $member['weight'] ? htmlspecialchars($member['weight']) . ' kg' : 'N/A' ?></td>
                                    <td><?= $member['body_type'] ? htmlspecialchars(ucfirst($member['body_type'])) : 'N/A' ?></td>
                                    <td><?= $member['experience_level'] ? htmlspecialchars(ucfirst($member['experience_level'])) : 'N/A' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="assign_workout.php?member_id=<?= $member['user_id'] ?>" class="btn btn-primary">
                                                Assign Workout
                                            </a>
                                            <a href="progress_tracker.php?member_id=<?= $member['user_id'] ?>" class="btn btn-success">
                                                Track Progress
                                            </a>
                                            <a href="messages.php?to=<?= $member['user_id'] ?>" class="btn btn-info">
                                                Message
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No members found in the system.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>