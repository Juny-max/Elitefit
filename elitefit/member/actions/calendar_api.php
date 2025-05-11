<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header immediately
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Session expired. Please refresh the page.', 401);
    }

    // Include database configuration
    require_once __DIR__ . '/../../config.php';

    // Verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed', 500);
    }

    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Process different actions
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');

    switch ($action) {
        case 'get_sessions':
            if ($method !== 'GET') {
                throw new Exception('Invalid request method', 405);
            }

            // Validate and sanitize input
            $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => date('n'),
                    'min_range' => 1,
                    'max_range' => 12
                ]
            ]);
            
            $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => date('Y'),
                    'min_range' => 2020,
                    'max_range' => 2100
                ]
            ]);

            // Get sessions from database
            $stmt = $conn->prepare("SELECT date, workout_type, duration, completed_status 
                                  FROM workout_sessions 
                                  WHERE user_id = ? 
                                  AND YEAR(date) = ? 
                                  AND MONTH(date) = ?");
            if (!$stmt) {
                throw new Exception('Database query preparation failed: ' . $conn->error, 500);
            }

            $stmt->bind_param('iii', $_SESSION['user_id'], $year, $month);
            
            if (!$stmt->execute()) {
                throw new Exception('Database query failed: ' . $stmt->error, 500);
            }

            $result = $stmt->get_result();
            $sessions = [];

            while ($row = $result->fetch_assoc()) {
                $date = date('Y-m-d', strtotime($row['date']));
                $sessions[$date] = [
                    'workout_type' => $row['workout_type'],
                    'duration' => (float)$row['duration'],
                    'completed_status' => (int)$row['completed_status'],
                    'completed' => (bool)$row['completed_status']
                ];
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'sessions' => $sessions
                ]
            ]);
            exit;

        case 'get_workout_categories':
            // Fetch all workout categories and their default durations
            $categories = [];
            $result = $conn->query("SELECT category_id, name, default_duration FROM workout_categories ORDER BY name ASC");
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => (int)$row['category_id'],
                    'name' => $row['name'],
                    'default_duration' => (int)$row['default_duration']
                ];
            }
            echo json_encode([
                'status' => 'success',
                'categories' => $categories
            ]);
            exit;

        case 'get_completed_workouts_per_category':
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT wc.name, COUNT(ws.session_id) AS completed_count
                    FROM workout_sessions ws
                    JOIN workout_categories wc ON ws.workout_type = wc.name
                    WHERE ws.user_id = ?
                      AND ws.completed_status = 1
                      AND MONTH(ws.date) = MONTH(CURRENT_DATE())
                      AND YEAR(ws.date) = YEAR(CURRENT_DATE())
                    GROUP BY wc.category_id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $data]);
            exit;

        case 'add_session':
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'error' => 'Invalid request method']);
                exit;
            }
            $user_id = $_SESSION['user_id'];
            $date = $_POST['date'] ?? null;
            $workout_type = $_POST['workout_type'] ?? null;
            $duration = $_POST['duration'] ?? null;
            if (!$date || !$workout_type || !$duration) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }
            // Parse duration (support "15 seconds" or float)
            if (strpos($duration, 'seconds') !== false) {
                $duration = floatval(str_replace(' seconds', '', $duration)) / 60;
            } else {
                $duration = floatval($duration);
            }
            // Prevent duplicate for same user/date
            $stmt = $conn->prepare("SELECT session_id FROM workout_sessions WHERE user_id=? AND date=?");
            $stmt->bind_param('is', $user_id, $date);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                echo json_encode(['success' => false, 'error' => 'Workout already logged for this date']);
                exit;
            }
            // Insert session
            $stmt = $conn->prepare("INSERT INTO workout_sessions (user_id, date, workout_type, duration, completed_status) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param('issd', $user_id, $date, $workout_type, $duration);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to log workout']);
            }
            exit;

        default:
            throw new Exception('Invalid action specified', 400);
    }

} catch (Exception $e) {
    // Return JSON error response
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
    exit;
}