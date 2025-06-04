<?php
session_start();
require_once __DIR__ . "/../config.php";

// Verify session and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    header("Location: /elitefit/index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build base query with filters
$query = "SELECT aw.*, u.first_name, u.last_name, u.email, u.contact_number, wp.plan_name,
          CASE 
              WHEN aw.end_date < CURDATE() THEN 'completed'
              WHEN aw.start_date <= CURDATE() AND aw.end_date >= CURDATE() THEN 'active'
              ELSE 'upcoming'
          END as status
          FROM assigned_workouts aw
          JOIN users u ON aw.member_id = u.user_id
          JOIN workout_plans wp ON aw.plan_id = wp.plan_id
          WHERE aw.trainer_id = ?";

$params = [$trainer_id];
$types = "i";

// Apply status filter
if (in_array($status_filter, ['active', 'upcoming', 'completed'])) {
    if ($status_filter === 'active') {
        $query .= " AND aw.start_date <= CURDATE() AND aw.end_date >= CURDATE()";
    } elseif ($status_filter === 'upcoming') {
        $query .= " AND aw.start_date > CURDATE()";
    } elseif ($status_filter === 'completed') {
        $query .= " AND aw.end_date < CURDATE()";
    }
}

// Apply search filter
if (!empty($search)) {
    $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR wp.plan_name LIKE ? OR aw.notes LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
}

// Add sorting
$query .= " ORDER BY aw.start_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);

// Bind parameters dynamically
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$workouts = $result->fetch_all(MYSQLI_ASSOC);

// Get counts for status filters
$counts_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN end_date < CURDATE() THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN start_date <= CURDATE() AND end_date >= CURDATE() THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN start_date > CURDATE() THEN 1 ELSE 0 END) as upcoming
    FROM assigned_workouts 
    WHERE trainer_id = ?";

$counts_stmt = $conn->prepare($counts_query);
$counts_stmt->bind_param('i', $trainer_id);
$counts_stmt->execute();
$counts = $counts_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Workouts - Trainer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6e9ff;
            --primary-dark: #3f37c9;
            --secondary: #3f37c9;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gray-lighter: #f8f9fa;
            --white: #ffffff;
            --success-bg: #e3fafc;
            --success-text: #15aabf;
            --warning-bg: #fff3bf;
            --warning-text: #e67700;
            --completed-bg: #ebfbee;
            --completed-text: #2b8a3e;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #e6e9ff 100%);
            min-height: 100vh;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header Styles */
        .header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title h1 {
            color: var(--dark);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: var(--gray);
            font-size: 1rem;
            font-weight: 400;
        }

        .back-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Filters Section */
        .filters {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }

        .filters-content {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
            max-width: 400px;
        }

        .search-form input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .search-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .status-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .status-filter {
            padding: 0.75rem 1.25rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--gray);
            background: var(--gray-lighter);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-filter:hover {
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .status-filter.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-filter.active .count {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Workouts Grid */
        .workouts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .workout-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(67, 97, 238, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .workout-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .workout-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .workout-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .workout-title {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
            margin: 0;
            line-height: 1.4;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
        }

        .status-active { 
            background: var(--success-bg); 
            color: var(--success-text);
            border: 1px solid rgba(21, 170, 191, 0.2);
        }
        
        .status-upcoming { 
            background: var(--warning-bg); 
            color: var(--warning-text);
            border: 1px solid rgba(230, 119, 0, 0.2);
        }
        
        .status-completed { 
            background: var(--completed-bg); 
            color: var(--completed-text);
            border: 1px solid rgba(43, 138, 62, 0.2);
        }

        .workout-details {
            margin-top: 1rem;
            space-y: 0.75rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .detail-label {
            color: var(--gray);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-light);
        }

        .member-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: var(--shadow);
        }

        .member-details {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .member-since {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        .no-workouts {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid rgba(67, 97, 238, 0.1);
        }

        .no-workouts i {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 1rem;
        }

        .no-workouts h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .no-workouts p {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                padding: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .page-title h1 {
                font-size: 1.75rem;
            }

            .filters-content {
                gap: 1rem;
            }

            .search-form {
                max-width: 100%;
            }

            .status-filters {
                justify-content: center;
            }

            .workouts-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .workout-card {
                padding: 1.25rem;
            }

            .workout-header {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }

            .status-badge {
                align-self: flex-start;
            }
        }

        /* Animation for smooth loading */
        .workout-card {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover effects for interactive elements */
        .detail-row:hover .detail-label {
            color: var(--primary);
        }

        .member-info:hover .member-avatar {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="page-title">
                    <h1><i class="fas fa-dumbbell"></i> All Assigned Workouts</h1>
                    <p>Manage and track all workout assignments for your members</p>
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="filters">
            <div class="filters-content">
                <div class="search-box">
                    <form method="GET" action="" class="search-form">
                        <input type="text" name="search" placeholder="Search members, plans, or notes..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                        <?php endif; ?>
                    </form>
                </div>
                <div class="status-filters">
                    <a href="?<?= !empty($search) ? 'search=' . urlencode($search) : '' ?>" class="status-filter <?= empty($status_filter) ? 'active' : '' ?>">
                        <i class="fas fa-list"></i>
                        All <span class="count"><?= $counts['total'] ?></span>
                    </a>
                    <a href="?status=active<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="status-filter <?= $status_filter === 'active' ? 'active' : '' ?>">
                        <i class="fas fa-play-circle"></i>
                        Active <span class="count"><?= $counts['active'] ?></span>
                    </a>
                    <a href="?status=upcoming<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="status-filter <?= $status_filter === 'upcoming' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i>
                        Upcoming <span class="count"><?= $counts['upcoming'] ?></span>
                    </a>
                    <a href="?status=completed<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="status-filter <?= $status_filter === 'completed' ? 'active' : '' ?>">
                        <i class="fas fa-check-circle"></i>
                        Completed <span class="count"><?= $counts['completed'] ?></span>
                    </a>
                </div>
            </div>
        </div>

        <div class="workouts-grid">
            <?php if (count($workouts) > 0): ?>
                <?php foreach ($workouts as $workout): 
                    $initials = strtoupper(substr($workout['first_name'], 0, 1) . substr($workout['last_name'], 0, 1));
                    $status_class = 'status-' . $workout['status'];
                ?>
                    <div class="workout-card">
                        <div class="workout-header">
                            <h3 class="workout-title"><?= htmlspecialchars($workout['plan_name']) ?></h3>
                            <span class="status-badge <?= $status_class ?>">
                                <?= ucfirst($workout['status']) ?>
                            </span>
                        </div>
                        
                        <div class="workout-details">
                            <div class="detail-row">
                                <span class="detail-label">
                                    <i class="fas fa-calendar-plus"></i>
                                    Start Date:
                                </span>
                                <span class="detail-value"><?= date('M j, Y', strtotime($workout['start_date'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">
                                    <i class="fas fa-calendar-check"></i>
                                    End Date:
                                </span>
                                <span class="detail-value"><?= date('M j, Y', strtotime($workout['end_date'])) ?></span>
                            </div>
                            <?php if (!empty($workout['notes'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">
                                        <i class="fas fa-sticky-note"></i>
                                        Notes:
                                    </span>
                                    <span class="detail-value"><?= htmlspecialchars(substr($workout['notes'], 0, 50)) ?><?= strlen($workout['notes']) > 50 ? '...' : '' ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="member-info">
                            <div class="member-avatar" title="<?= htmlspecialchars($workout['first_name'] . ' ' . $workout['last_name']) ?>">
                                <?= $initials ?>
                            </div>
                            <div class="member-details">
                                <div class="member-name"><?= htmlspecialchars($workout['first_name'] . ' ' . $workout['last_name']) ?></div>
                                <div class="member-since">
                                    <i class="fas fa-calendar-alt"></i>
                                    Assigned on <?= date('M j, Y', strtotime($workout['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-workouts">
                    <i class="fas fa-dumbbell"></i>
                    <h3>No workouts found</h3>
                    <p>You haven't assigned any workouts matching the current filters. Try adjusting your search or create new workout assignments.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>