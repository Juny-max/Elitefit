<?php
session_start();
require_once('../config.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="elitefit_backup_' . date('Y-m-d') . '.sql"');
header('Pragma: no-cache');

// Function to get all tables in the database
function getTables($conn) {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    return $tables;
}

// Function to generate CREATE TABLE statement
function getTableStructure($conn, $table) {
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    
    return $row[1] . ";\n\n";
}

// Function to get table data as INSERT statements
function getTableData($conn, $table) {
    $result = $conn->query("SELECT * FROM `$table`");
    $numFields = $result->field_count;
    $numRows = $result->num_rows;
    
    if ($numRows == 0) {
        return "";
    }
    
    $output = "-- Dumping data for table `$table`\n";
    $output .= "INSERT INTO `$table` VALUES\n";
    
    $rowCount = 1;
    while ($row = $result->fetch_row()) {
        $output .= "(";
        
        for ($i = 0; $i < $numFields; $i++) {
            if (isset($row[$i])) {
                // Escape special characters
                $row[$i] = str_replace("\n", "\\n", addslashes($row[$i]));
                $output .= "'" . $row[$i] . "'";
            } else {
                $output .= "NULL";
            }
            
            if ($i < ($numFields - 1)) {
                $output .= ", ";
            }
        }
        
        if ($rowCount == $numRows) {
            $output .= ");\n\n";
        } else {
            $output .= "),\n";
        }
        
        $rowCount++;
    }
    
    return $output;
}

// Start output buffering to capture all output
ob_start();

// Output header information
echo "-- EliteFit Gym Management System Database Backup\n";
echo "-- Version: 1.0\n";
echo "-- Generation Time: " . date('Y-m-d H:i:s') . "\n";
echo "-- Server version: " . $conn->server_info . "\n";
echo "-- PHP Version: " . phpversion() . "\n\n";

echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET time_zone = \"+00:00\";\n\n";

echo "-- Database: `" . DB_NAME . "`\n";
echo "-- --------------------------------------------------------\n\n";

// Get all tables
$tables = getTables($conn);

// Process each table
foreach ($tables as $table) {
    echo "-- Table structure for table `$table`\n";
    echo getTableStructure($conn, $table);
    echo getTableData($conn, $table);
}

// Add settings data
echo "-- Settings data\n";
$settingsQuery = "SELECT * FROM settings";
$settingsResult = $conn->query($settingsQuery);

if ($settingsResult && $settingsResult->num_rows > 0) {
    echo getTableData($conn, 'settings');
} else {
    // If no settings table exists, create a settings JSON file backup
    echo "-- No settings table found, creating settings data as JSON\n";
    echo "-- You can import this data manually if needed\n";
    
    // Get settings from the settings array in settings.php
    $settingsData = [
        'site_title' => 'EliteFit',
        'admin_email' => 'admin@elitefit.com',
        'logo_url' => '',
        'theme_color' => 'blue',
        'enable_notifications' => true,
        'maintenance_mode' => false,
        'registration_enabled' => true,
        'session_timeout' => 30,
        'backup_frequency' => 'weekly',
        'backup_retention' => 30,
        'currency' => 'USD'
    ];
    
    echo "/*\n";
    echo json_encode($settingsData, JSON_PRETTY_PRINT);
    echo "\n*/\n\n";
}

// Output the backup file
$output = ob_get_clean();
echo $output;
exit();
?>
