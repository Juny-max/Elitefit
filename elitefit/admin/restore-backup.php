<?php
session_start();
require_once('../config.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if a file was uploaded
if (!isset($_FILES['backupFile']) || $_FILES['backupFile']['error'] !== UPLOAD_ERR_OK) {
    header('Location: settings.php?error=No file uploaded or upload error');
    exit();
}

// Validate file type (should be .sql)
$fileInfo = pathinfo($_FILES['backupFile']['name']);
if ($fileInfo['extension'] !== 'sql') {
    header('Location: settings.php?error=Invalid file type. Only SQL files are allowed');
    exit();
}

// Read the SQL file
$sqlFile = file_get_contents($_FILES['backupFile']['tmp_name']);
if ($sqlFile === false) {
    header('Location: settings.php?error=Could not read the backup file');
    exit();
}

// Split the SQL file into individual queries
$queries = explode(';', $sqlFile);

// Begin transaction
$conn->begin_transaction();

try {
    // Execute each query
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $result = $conn->query($query);
            if ($result === false) {
                throw new Exception($conn->error);
            }
        }
    }
    
    // Commit the transaction
    $conn->commit();
    header('Location: settings.php?success=Database restored successfully');
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    header('Location: settings.php?error=Error restoring database: ' . urlencode($e->getMessage()));
}

exit();
?>
