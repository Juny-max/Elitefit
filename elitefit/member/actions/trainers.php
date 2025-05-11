<?php
session_start();
include_once __DIR__ . "/../../config.php";

// Redirect if not logged in as member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: ../index.php");
    exit();
}

// Store member ID from session
$member_id = $_SESSION['user_id'];

// Check if member has any active bookings (only scheduled sessions)
$active_booking_sql = "SELECT * FROM booked_sessions 
                      WHERE member_id = ? AND status = 'scheduled' 
                      AND (session_date > CURDATE() OR 
                          (session_date = CURDATE() AND end_time > CURTIME()))";
$active_booking_stmt = $conn->prepare($active_booking_sql);
$active_booking_stmt->bind_param("i", $_SESSION['user_id']);
$active_booking_stmt->execute();
$active_booking_result = $active_booking_stmt->get_result();
$has_active_booking = $active_booking_result->num_rows > 0;
$active_trainer_id = $has_active_booking ? $active_booking_result->fetch_assoc()['trainer_id'] : null;

// Get all trainers
$trainers_sql = "SELECT u.user_id, u.first_name, u.last_name, u.profile_picture, 
                t.specialization, t.certification, t.years_experience, t.bio
                FROM users u
                JOIN trainers t ON u.user_id = t.trainer_id
                WHERE u.role = 'trainer' AND u.is_active = TRUE
                ORDER BY u.first_name, u.last_name";
$trainers_result = $conn->query($trainers_sql);
$trainers = $trainers_result->fetch_all(MYSQLI_ASSOC);

// Get availability for each trainer
foreach ($trainers as &$trainer) {
    $availability_sql = "SELECT day_of_week, availability_type
                        FROM trainer_availability 
                        WHERE trainer_id = ?
                        ORDER BY 
                            FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
    $availability_stmt = $conn->prepare($availability_sql);
    $availability_stmt->bind_param("i", $trainer['user_id']);
    $availability_stmt->execute();
    $availability_result = $availability_stmt->get_result();
    
    $trainer['availability'] = [];
    while ($row = $availability_result->fetch_assoc()) {
        $trainer['availability'][$row['day_of_week']] = $row['availability_type'];
    }
    
    // Mark if trainer is locked (only if member has an active booking with another trainer)
    $trainer['is_locked'] = $has_active_booking && $trainer['user_id'] != $active_trainer_id;
}
unset($trainer); // Break the reference

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_trainer'])) {
    $trainer_id = $_POST['trainer_id'];
    $session_date = $_POST['session_date'];
    $start_time = $_POST['start_time'];
    $day_of_week = date('l', strtotime($session_date));
    
    // Validate the booking against trainer's availability
    $check_availability_sql = "SELECT * FROM trainer_availability 
                              WHERE trainer_id = ? AND day_of_week = ?";
    $check_stmt = $conn->prepare($check_availability_sql);
    $check_stmt->bind_param("is", $trainer_id, $day_of_week);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $availability = $check_result->fetch_assoc();
        
        // Convert time to check against availability type
        $hour = date('H', strtotime($start_time));
        
        // Check if time matches availability type
        $is_available = false;
        switch ($availability['availability_type']) {
            case 'morning':
                $is_available = ($hour >= 8 && $hour < 12); // 8am-12pm
                break;
            case 'afternoon':
                $is_available = ($hour >= 12 && $hour < 17); // 12pm-5pm
                break;
            case 'full':
                $is_available = ($hour >= 8 && $hour < 17); // 8am-5pm
                break;
        }
        
        if ($is_available) {
            // Check for existing scheduled bookings at this time
            $check_conflict_sql = "SELECT * FROM booked_sessions 
                                  WHERE trainer_id = ? AND session_date = ? 
                                  AND start_time <= ? AND end_time >= ? 
                                  AND status = 'scheduled'"; // Only check scheduled sessions
            $conflict_stmt = $conn->prepare($check_conflict_sql);
            $conflict_stmt->bind_param("isss", $trainer_id, $session_date, $start_time, $start_time);
            $conflict_stmt->execute();
            $conflict_result = $conflict_stmt->get_result();
            
            if ($conflict_result->num_rows == 0) {
                // Calculate end time (assuming 1-hour sessions)
                $end_time = date('H:i:s', strtotime($start_time) + 3600);
                
                // Insert booking
                $insert_sql = "INSERT INTO booked_sessions 
                              (trainer_id, member_id, session_date, start_time, end_time)
                              VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iisss", 
                    $trainer_id, 
                    $_SESSION['user_id'], 
                    $session_date, 
                    $start_time, 
                    $end_time
                );
                
                if ($insert_stmt->execute()) {
                    $success_message = "Session booked successfully!";
                    // Refresh to show locked trainers
                    header("Location: trainers.php");
                    exit();
                } else {
                    $error_message = "Error booking session. Please try again.";
                }
            } else {
                $error_message = "Trainer is already booked at this time. Please choose another time.";
            }
        } else {
            $error_message = "Trainer is not available at this time. Please check their availability.";
        }
    } else {
        $error_message = "Trainer is not available on this day. Please choose another day.";
    }
}

// Get member profile data
$member_id = $_SESSION['user_id'];
$member_sql = "SELECT * FROM users WHERE user_id = ?";
$member_stmt = $conn->prepare($member_sql);
$member_stmt->bind_param("i", $member_id);
$member_stmt->execute();
$member_result = $member_stmt->get_result();
$member = $member_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EliteFit Gym - Book a Trainer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Trainer Grid */
        .trainer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .trainer-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
        }

        .trainer-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .trainer-card h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        .trainer-profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            margin-bottom: 1rem;
        }

        .specialization {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .trainer-detail {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        /* Availability Styles */
        .availability-container {
            margin-top: 1rem;
        }

        .availability-container h4 {
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .availability-day {
            margin-bottom: 1rem;
        }

        .day-header {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .time-slot {
            background: var(--primary-light);
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--primary);
            font-weight: 500;
        }

        .off-day {
            color: var(--gray);
            font-style: italic;
            font-size: 0.875rem;
        }

        /* Booking Form */
        .booking-form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--gray-light);
            border-radius: var(--radius-sm);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-family: 'Inter', sans-serif;
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        button:hover {
            background: var(--secondary);
        }

        /* Messages */
        .success {
            color: var(--success);
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(76, 201, 240, 0.1);
            border-radius: var(--radius-sm);
        }

        .error {
            color: var(--danger);
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(247, 37, 133, 0.1);
            border-radius: var(--radius-sm);
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

        /* Locked Trainer Styles - Updated */
        .trainer-locked {
            position: relative;
            pointer-events: none;
        }

        .trainer-locked::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(3px);
            z-index: 10;
            border-radius: var(--radius);
        }

        .lock-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 20;
            color: var(--danger);
            font-size: 3rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .booking-info {
            background: var(--primary-light);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .trainer-grid {
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
                <a href="workout_plans.php" class="nav-item">
                    <i>💪</i> Workouts
                </a>
                <a href="progress_tracker.php" class="nav-item">
                    <i>📈</i> Progress
                </a>
                <a href="schedule.php" class="nav-item">
                    <i>📅</i> Schedule
                </a>
                <a href="trainers.php" class="nav-item active">
                    <i>👨‍🏫</i> Trainers
                </a>
                <a href="../messages.php" class="nav-item">
                    <i>✉️</i> Messages
                </a>
                <a href="edit_profile.php" class="nav-item">
                    <i>👤</i> Profile
                </a>
                <a href="../../logout.php" class="nav-item">
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
                    <h1>Book a Trainer</h1>
                    <p>View our certified trainers and schedule your sessions</p>
                </div>
                <div class="user-profile">
                    <?php if (!empty($member['profile_picture'])): ?>
                        <img src="<?= '../../' . htmlspecialchars($member['profile_picture']) ?>" class="profile-pic" alt="Profile Picture">
                    <?php else: ?>
                        <div class="profile-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 20px;">👤</span>
                        </div>
                    <?php endif; ?>
                    <div class="profile-info">
                        <span class="profile-name">
                            <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                        </span>
                        <span class="profile-role">Member</span>
                    </div>
                </div>
            </header>

            <?php if (isset($success_message)): ?>
                <div class="success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($has_active_booking): ?>
                <?php 
                $active_trainer_info = null;
                foreach ($trainers as $t) {
                    if ($t['user_id'] == $active_trainer_id) {
                        $active_trainer_info = $t;
                        break;
                    }
                }
                ?>
                <div class="booking-info">
                    You currently have an active booking with 
                    <strong><?= htmlspecialchars($active_trainer_info['first_name'] . ' ' . $active_trainer_info['last_name']) ?></strong>. 
                    Please complete this session before booking with another trainer.
                </div>
            <?php endif; ?>

            <div class="trainer-grid">
                <?php foreach ($trainers as $trainer): ?>
                    <div class="trainer-card <?= $trainer['is_locked'] ? 'trainer-locked' : '' ?>">
                        <?php if ($trainer['is_locked']): ?>
                            <i class="fas fa-lock lock-icon"></i>
                        <?php endif; ?>
                        
                        <?php if (!empty($trainer['profile_picture'])): ?>
                            <img src="<?= '../../' . htmlspecialchars($trainer['profile_picture']) ?>" class="trainer-profile-pic" alt="Trainer Profile">
                        <?php else: ?>
                            <div class="trainer-profile-pic" style="background: #ccc; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 40px;">👤</span>
                            </div>
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']) ?></h3>
                        <div class="specialization"><?= htmlspecialchars($trainer['specialization']) ?></div>
                        <div class="trainer-detail">Certification: <?= htmlspecialchars($trainer['certification']) ?></div>
                        <div class="trainer-detail">Experience: <?= htmlspecialchars($trainer['years_experience']) ?> years</div>
                        
                        <div class="availability-container">
                            <h4>Availability</h4>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day): ?>
                                <div class="availability-day">
                                    <div class="day-header"><?= $day ?></div>
                                    <?php if (!empty($trainer['availability'][$day])): ?>
                                        <div class="time-slots">
                                            <?php 
                                            $type = $trainer['availability'][$day];
                                            switch ($type) {
                                                case 'morning':
                                                    echo '<span class="time-slot">8:00 AM - 12:00 PM</span>';
                                                    break;
                                                case 'afternoon':
                                                    echo '<span class="time-slot">12:00 PM - 5:00 PM</span>';
                                                    break;
                                                case 'full':
                                                    echo '<span class="time-slot">8:00 AM - 5:00 PM</span>';
                                                    break;
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="off-day">Not available</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (!$trainer['is_locked']): ?>
                            <div class="booking-form">
                                <form method="POST">
                                    <input type="hidden" name="trainer_id" value="<?= $trainer['user_id'] ?>">
                                    
                                    <div class="form-group">
                                        <label for="session_date_<?= $trainer['user_id'] ?>">Session Date</label>
                                        <input type="date" id="session_date_<?= $trainer['user_id'] ?>" name="session_date" required min="<?= date('Y-m-d') ?>" onchange="formatDateToDDMMYYYY(this)">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="start_time_<?= $trainer['user_id'] ?>">Start Time</label>
                                        <input type="time" id="start_time_<?= $trainer['user_id'] ?>" name="start_time" required>
                                    </div>
                                    
                                    <button type="submit" name="book_trainer">Book Session</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script>
        function formatDateToDDMMYYYY(input) {
            if (input.value) {
                const parts = input.value.split('-');
                if(parts.length === 3) {
                    input.setAttribute('data-raw', input.value); // store original
                    input.type = 'text';
                    input.value = parts[2] + '/' + parts[1] + '/' + parts[0];
                }
            }
        }

        // When focusing, revert to date type for picker
        const dateInputs = document.querySelectorAll('input[name="session_date"]');
        dateInputs.forEach(inp => {
            inp.addEventListener('focus', function() {
                if(this.type === 'text') {
                    this.type = 'date';
                    if(this.hasAttribute('data-raw')) {
                        this.value = this.getAttribute('data-raw');
                    }
                }
            });
        });

        // On submit, convert back to yyyy-mm-dd
        const forms = document.querySelectorAll('.booking-form form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const dateInput = form.querySelector('input[name="session_date"]');
                if(dateInput && dateInput.type === 'text') {
                    const parts = dateInput.value.split('/');
                    if(parts.length === 3) {
                        dateInput.value = parts[2] + '-' + parts[1] + '-' + parts[0];
                    }
                }
            });
        });
    </script>
</body>
</html>