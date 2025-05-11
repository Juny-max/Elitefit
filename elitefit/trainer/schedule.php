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

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_workout'])) {
    $assignment_id = $_POST['assignment_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $notes = $_POST['notes'];
    
    $update_sql = "UPDATE assigned_workouts 
                  SET start_date = ?, end_date = ?, notes = ?
                  WHERE assignment_id = ? AND trainer_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssii", $start_date, $end_date, $notes, $assignment_id, $trainer_id);
    
    if ($stmt->execute()) {
        $success = "Workout updated successfully!";
    } else {
        $error = "Error updating workout: " . $conn->error;
    }
}

// Get assigned workouts
$workouts_sql = "SELECT aw.*, wp.plan_name, u.first_name as member_first_name, u.last_name as member_last_name 
                FROM assigned_workouts aw
                JOIN workout_plans wp ON aw.plan_id = wp.plan_id
                JOIN users u ON aw.member_id = u.user_id
                WHERE aw.trainer_id = ?
                ORDER BY aw.start_date";
$workouts_stmt = $conn->prepare($workouts_sql);
$workouts_stmt->bind_param("i", $trainer_id);
$workouts_stmt->execute();
$workouts_result = $workouts_stmt->get_result();
$assigned_workouts = $workouts_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Schedule - EliteFit Gym</title>
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
            padding: 1.5rem;
            background: var(--white);
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

        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-size: 1rem;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
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

        .workout-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .workout-card h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .member-info {
            color: var(--gray);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .date-range {
            background: var(--primary-light);
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            margin: 1rem 0;
            font-weight: 500;
            color: var(--primary);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
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
            font-size: 0.875rem;
        }

        .notes strong {
            color: var(--dark);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background: #e3173a;
            transform: translateY(-2px);
        }

        /* Edit Form */
        .edit-form {
            display: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-light);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.875rem;
        }

        input, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border: 1px solid rgba(76, 201, 240, 0.2);
        }

        .alert-error {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border: 1px solid rgba(247, 37, 133, 0.2);
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
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .schedule-grid {
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
                <a href="schedule.php" class="nav-item active">
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
            <div class="header">
                <div class="page-title">
                    <h1>Training Schedule</h1>
                    <p>View and manage your assigned workout sessions</p>
                </div>
                <a href="assign_workout.php" class="btn btn-primary">
                    <i>➕</i> Assign New Workout
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="schedule-grid">
                <?php if (!empty($assigned_workouts)): ?>
                    <?php foreach ($assigned_workouts as $workout): ?>
                        <div class="workout-card" id="workout-<?= $workout['assignment_id'] ?>">
                            <h3><?= htmlspecialchars($workout['plan_name']) ?></h3>
                            <div class="member-info">
                                Assigned to: <?= htmlspecialchars($workout['member_first_name'] . ' ' . $workout['member_last_name']) ?>
                            </div>
                            
                            <!-- Display View -->
                            <div class="view-mode">
                                <div class="date-range">
                                    <?= date('M j, Y', strtotime($workout['start_date'])) ?> - <?= date('M j, Y', strtotime($workout['end_date'])) ?>
                                </div>
                                
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
                                
                                <?php if (!empty($workout['notes'])): ?>
                                    <div class="notes">
                                        <strong>Your Notes:</strong> <?= htmlspecialchars($workout['notes']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="action-buttons">
                                    <button class="btn btn-outline btn-sm" onclick="enableEditMode(<?= $workout['assignment_id'] ?>)">
                                        <i>✏️</i> Edit
                                    </button>
                                    <a href="delete_workout.php?id=<?= $workout['assignment_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this workout assignment?')">
                                        <i>🗑️</i> Delete
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Edit Form -->
                            <div class="edit-form" id="edit-form-<?= $workout['assignment_id'] ?>">
                                <form method="POST" onsubmit="return validateDates(<?= $workout['assignment_id'] ?>)">
                                    <input type="hidden" name="assignment_id" value="<?= $workout['assignment_id'] ?>">
                                    
                                    <div class="form-group">
                                        <label for="start_date_<?= $workout['assignment_id'] ?>">Start Date</label>
                                        <input type="date" id="start_date_<?= $workout['assignment_id'] ?>" name="start_date" value="<?= $workout['start_date'] ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="end_date_<?= $workout['assignment_id'] ?>">End Date</label>
                                        <input type="date" id="end_date_<?= $workout['assignment_id'] ?>" name="end_date" value="<?= $workout['end_date'] ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="notes_<?= $workout['assignment_id'] ?>">Notes</label>
                                        <textarea id="notes_<?= $workout['assignment_id'] ?>" name="notes" placeholder="Add any notes or instructions..."><?= htmlspecialchars($workout['notes']) ?></textarea>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <button type="submit" name="update_workout" class="btn btn-primary btn-sm">
                                            <i>💾</i> Save
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="disableEditMode(<?= $workout['assignment_id'] ?>)">
                                            <i>❌</i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No Workouts Assigned</h3>
                        <p>You haven't assigned any workouts to members yet.</p>
                        <a href="assign_workout.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i>➕</i> Assign Your First Workout
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function enableEditMode(assignmentId) {
            // Hide the view mode
            document.querySelector(`#workout-${assignmentId} .view-mode`).style.display = 'none';
            
            // Show the edit form
            document.querySelector(`#edit-form-${assignmentId}`).style.display = 'block';
        }
        
        function disableEditMode(assignmentId) {
            // Show the view mode
            document.querySelector(`#workout-${assignmentId} .view-mode`).style.display = 'block';
            
            // Hide the edit form
            document.querySelector(`#edit-form-${assignmentId}`).style.display = 'none';
        }
        
        function validateDates(assignmentId) {
            const startDate = new Date(document.getElementById(`start_date_${assignmentId}`).value);
            const endDate = new Date(document.getElementById(`end_date_${assignmentId}`).value);
            
            if (startDate > endDate) {
                alert('End date must be after start date');
                return false;
            }
            return true;
        }
        
        // Auto-close success message after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>