<?php
session_start();
include_once __DIR__ . "/../config.php";

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get all members
$members_sql = "SELECT u.user_id, u.first_name, u.last_name 
               FROM users u
               WHERE u.role = 'member'";
$members_result = $conn->query($members_sql);
$members = $members_result->fetch_all(MYSQLI_ASSOC);

// Get all workout plans
$plans_sql = "SELECT * FROM workout_plans";
$plans_result = $conn->query($plans_sql);
$plans = $plans_result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $plan_id = $_POST['plan_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $notes = $_POST['notes'];
    
    $insert_sql = "INSERT INTO assigned_workouts 
                  (trainer_id, member_id, plan_id, start_date, end_date, notes) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iiisss", $trainer_id, $member_id, $plan_id, $start_date, $end_date, $notes);
    
    if ($insert_stmt->execute()) {
        $success = "Workout assigned successfully!";
    } else {
        $error = "Error assigning workout: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Workout - EliteFit Gym</title>
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

        .card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto;
        }

        .card-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .card-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.875rem;
        }

        select, input, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Date Select Container */
        .date-select-container {
            display: flex;
            gap: 10px;
        }

        .date-select-container select {
            flex: 1;
        }

        /* Button Styles */
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-size: 1rem;
            border: none;
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

        /* Alert Styles */
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

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }

            .date-select-container {
                flex-direction: column;
                gap: 8px;
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
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Assign Workout Plan</h1>
                    <p class="card-subtitle">Create a customized fitness plan for your member</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="member_id">Select Member</label>
                        <select id="member_id" name="member_id" required>
                            <option value="">Choose a member...</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= htmlspecialchars($member['user_id']) ?>">
                                    <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="plan_id">Workout Plan</label>
                        <select id="plan_id" name="plan_id" required>
                            <option value="">Select a workout plan...</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= htmlspecialchars($plan['plan_id']) ?>" data-duration="<?= htmlspecialchars($plan['duration_weeks']) ?>">
                                    <?= htmlspecialchars($plan['plan_name']) ?> (<?= ucfirst(htmlspecialchars($plan['difficulty'])) ?>) - <?= htmlspecialchars($plan['duration_weeks']) ?> weeks
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <div class="date-select-container">
                            <select name="start_day" id="start_day" required>
                                <option value="">Day</option>
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="start_month" id="start_month" required>
                                <option value="">Month</option>
                                <?php 
                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                          'July', 'August', 'September', 'October', 'November', 'December'];
                                foreach ($months as $index => $month): ?>
                                    <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="start_year" id="start_year" required>
                                <option value="">Year</option>
                                <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="start_date" name="start_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <div class="date-select-container">
                            <select name="end_day" id="end_day" required>
                                <option value="">Day</option>
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <select name="end_month" id="end_month" required>
                                <option value="">Month</option>
                                <?php foreach ($months as $index => $month): ?>
                                    <option value="<?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>"><?= $month ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="end_year" id="end_year" required>
                                <option value="">Year</option>
                                <?php for ($i = date('Y'); $i <= date('Y') + 5; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <input type="hidden" id="end_date" name="end_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Personalized Notes</label>
                        <textarea id="notes" name="notes" placeholder="Add any specific instructions or notes for this member..."></textarea>
                    </div>
                    
                    <div class="button-group">
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary">Assign Workout</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the elements we need
            const planSelect = document.getElementById('plan_id');
            const startDay = document.getElementById('start_day');
            const startMonth = document.getElementById('start_month');
            const startYear = document.getElementById('start_year');
            const endDay = document.getElementById('end_day');
            const endMonth = document.getElementById('end_month');
            const endYear = document.getElementById('end_year');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // Function to update the hidden date inputs
            function updateDateInputs() {
                if (startDay.value && startMonth.value && startYear.value) {
                    startDateInput.value = `${startYear.value}-${startMonth.value}-${startDay.value}`;
                }
                if (endDay.value && endMonth.value && endYear.value) {
                    endDateInput.value = `${endYear.value}-${endMonth.value}-${endDay.value}`;
                }
            }

            // Function to calculate end date based on start date and duration
            function calculateEndDate() {
                const selectedPlan = planSelect.options[planSelect.selectedIndex];
                const durationWeeks = selectedPlan.dataset.duration;
                
                if (!durationWeeks || !startDay.value || !startMonth.value || !startYear.value) {
                    return;
                }

                // Create a Date object from the selected start date
                const startDate = new Date(
                    parseInt(startYear.value),
                    parseInt(startMonth.value) - 1, // Months are 0-indexed in JS
                    parseInt(startDay.value)
                );

                // Calculate end date by adding duration weeks
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + (parseInt(durationWeeks) * 7));

                // Update the end date select fields
                endYear.value = endDate.getFullYear();
                endMonth.value = String(endDate.getMonth() + 1).padStart(2, '0');
                endDay.value = String(endDate.getDate()).padStart(2, '0');

                // Update the hidden inputs
                updateDateInputs();
            }

            // Add event listeners
            planSelect.addEventListener('change', calculateEndDate);
            startDay.addEventListener('change', function() {
                updateDateInputs();
                calculateEndDate();
            });
            startMonth.addEventListener('change', function() {
                updateDateInputs();
                calculateEndDate();
            });
            startYear.addEventListener('change', function() {
                updateDateInputs();
                calculateEndDate();
            });
            endDay.addEventListener('change', updateDateInputs);
            endMonth.addEventListener('change', updateDateInputs);
            endYear.addEventListener('change', updateDateInputs);
        });
    </script>
</body>
</html>