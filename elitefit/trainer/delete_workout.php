<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check if user is logged in and is a trainer or admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
    header('Location: /elitefit/index.php');
    exit();
}

// Check if assignment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid workout assignment ID';
    header('Location: schedule.php');
    exit();
}

$assignment_id = (int)$_GET['id'];
$trainer_id = $_SESSION['user_id'];

// First verify the assignment belongs to this trainer
$check_sql = "SELECT assignment_id FROM assigned_workouts WHERE assignment_id = ? AND trainer_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $assignment_id, $trainer_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['error'] = 'Workout assignment not found or you do not have permission to delete it';
    header('Location: schedule.php');
    exit();
}

// Delete the assignment
try {
    $delete_sql = "DELETE FROM assigned_workouts WHERE assignment_id = ? AND trainer_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $assignment_id, $trainer_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = 'Workout assignment deleted successfully';
    } else {
        throw new Exception("Error deleting workout assignment");
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting workout assignment: ' . $e->getMessage();
}

// Redirect back to the schedule page
header('Location: schedule.php');
exit();
?>
