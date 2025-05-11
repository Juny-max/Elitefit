<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'trainer') {
    header("Location: ../index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// Get all members assigned to this trainer
$members_sql = "SELECT DISTINCT u.user_id, u.first_name, u.last_name 
               FROM assigned_workouts aw
               JOIN users u ON aw.member_id = u.user_id
               WHERE aw.trainer_id = ?";
$members_stmt = $conn->prepare($members_sql);
$members_stmt->bind_param("i", $trainer_id);
$members_stmt->execute();
$members_result = $members_stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $measurement_date = $_POST['measurement_date'];
    $weight = $_POST['weight'];
    $body_fat = $_POST['body_fat'];
    $muscle_mass = $_POST['muscle_mass'];
    $notes = $_POST['notes'];
    
    $insert_sql = "INSERT INTO progress_tracking 
                  (member_id, trainer_id, measurement_date, weight, body_fat, muscle_mass, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisddds", $member_id, $trainer_id, $measurement_date, $weight, $body_fat, $muscle_mass, $notes);
    
    if ($insert_stmt->execute()) {
        $success = "Progress recorded successfully!";
        // Refresh the progress history after new entry
        $_GET['member_id'] = $member_id;
    } else {
        $error = "Error recording progress: " . $conn->error;
    }
}

// Get progress history for selected member
$progress_history = [];
$has_progress = false;
$progress_stats = [];
if (isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];
    $history_sql = "SELECT * FROM progress_tracking 
                   WHERE member_id = ? AND trainer_id = ?
                   ORDER BY measurement_date DESC";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bind_param("ii", $member_id, $trainer_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    $progress_history = $history_result->fetch_all(MYSQLI_ASSOC);
    
    $has_progress = count($progress_history) > 0;
    
    // Calculate progress indicators if we have at least 2 records
    if (count($progress_history) >= 2) {
        $latest = $progress_history[0];
        $previous = $progress_history[1];
        
        $progress_stats = [
            'weight_diff' => $latest['weight'] - $previous['weight'],
            'body_fat_diff' => $latest['body_fat'] - $previous['body_fat'],
            'muscle_mass_diff' => $latest['muscle_mass'] - $previous['muscle_mass']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracker - EliteFit Gym</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1, h2 {
            color: var(--dark);
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

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

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        select, input, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea {
            min-height: 120px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: var(--light);
        }

        tr:hover {
            background-color: var(--primary-light);
        }

        .progress-indicator {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }

        .progress-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
        }

        .progress-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .progress-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .progress-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .progress-positive {
            color: var(--success);
        }

        .progress-negative {
            color: var(--danger);
        }

        .progress-neutral {
            color: var(--gray);
        }

        .no-progress {
            text-align: center;
            padding: 2rem;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin: 1.5rem 0;
        }

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

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            
            .progress-indicator {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">
            <span>←</span> Back to Dashboard
        </a>
        <h1>Member Progress Tracker</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="GET" action="progress_tracker.php">
            <div class="form-group">
                <label for="member_id">Select Member</label>
                <select id="member_id" name="member_id" required>
                    <option value="">Select Member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['user_id'] ?>" <?= isset($_GET['member_id']) && $_GET['member_id'] == $member['user_id'] ? 'selected' : '' ?>>
                            <?= $member['first_name'] . ' ' . $member['last_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">View Progress</button>
        </form>
        
        <?php if (isset($_GET['member_id'])): ?>
            <?php if ($has_progress): ?>
                <h2>Progress Overview</h2>
                <div class="progress-indicator">
                    <div class="progress-card">
                        <div class="progress-title">Weight Change</div>
                        <?php if (count($progress_history) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['weight_diff'] < 0 ? 'progress-positive' : ($progress_stats['weight_diff'] > 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['weight_diff'], 1) ?> kg
                            </div>
                            <small>Since <?= date('M j', strtotime($progress_history[1]['measurement_date'])) ?></small>
                        <?php else: ?>
                            <div class="progress-value">No comparison</div>
                            <small>Need more data</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="progress-card">
                        <div class="progress-title">Body Fat Change</div>
                        <?php if (count($progress_history) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['body_fat_diff'] < 0 ? 'progress-positive' : ($progress_stats['body_fat_diff'] > 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['body_fat_diff'], 1) ?>%
                            </div>
                            <small>Since <?= date('M j', strtotime($progress_history[1]['measurement_date'])) ?></small>
                        <?php else: ?>
                            <div class="progress-value">No comparison</div>
                            <small>Need more data</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="progress-card">
                        <div class="progress-title">Muscle Mass Change</div>
                        <?php if (count($progress_history) >= 2): ?>
                            <div class="progress-value <?= $progress_stats['muscle_mass_diff'] > 0 ? 'progress-positive' : ($progress_stats['muscle_mass_diff'] < 0 ? 'progress-negative' : 'progress-neutral') ?>">
                                <?= number_format($progress_stats['muscle_mass_diff'], 1) ?> kg
                            </div>
                            <small>Since <?= date('M j', strtotime($progress_history[1]['measurement_date'])) ?></small>
                        <?php else: ?>
                            <div class="progress-value">No comparison</div>
                            <small>Need more data</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h2>Progress History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight (kg)</th>
                            <th>Body Fat (%)</th>
                            <th>Muscle Mass (kg)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progress_history as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['measurement_date'])) ?></td>
                                <td><?= number_format($record['weight'], 1) ?></td>
                                <td><?= number_format($record['body_fat'], 1) ?></td>
                                <td><?= number_format($record['muscle_mass'], 1) ?></td>
                                <td><?= htmlspecialchars($record['notes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h2>Record New Progress</h2>
                <form method="POST" action="progress_tracker.php">
                    <input type="hidden" name="member_id" value="<?= $_GET['member_id'] ?>">
                    
                    <div class="form-group">
                        <label for="measurement_date">Date</label>
                        <input type="date" id="measurement_date" name="measurement_date" required value="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="body_fat">Body Fat (%)</label>
                        <input type="number" id="body_fat" name="body_fat" step="0.1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="muscle_mass">Muscle Mass (kg)</label>
                        <input type="number" id="muscle_mass" name="muscle_mass" step="0.1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Record Progress</button>
                </form>
            <?php else: ?>
                <div class="no-progress">
                    <h3>No Progress Records Found</h3>
                    <p>This member doesn't have any progress records yet.</p>
                    
                    <h2>Record First Progress</h2>
                    <form method="POST" action="progress_tracker.php">
                        <input type="hidden" name="member_id" value="<?= $_GET['member_id'] ?>">
                        
                        <div class="form-group">
                            <label for="measurement_date">Date</label>
                            <input type="date" id="measurement_date" name="measurement_date" required value="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" step="0.1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="body_fat">Body Fat (%)</label>
                            <input type="number" id="body_fat" name="body_fat" step="0.1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="muscle_mass">Muscle Mass (kg)</label>
                            <input type="number" id="muscle_mass" name="muscle_mass" step="0.1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Record Progress</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>