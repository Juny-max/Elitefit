<?php
session_start();
include_once __DIR__ . "/../config.php";

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Redirect if not logged in as equipment manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'equipment_manager') {
    header("Location: ../index.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $error_message = "";
    $success_message = "";
    
    // Check if file was uploaded
    if (!isset($_FILES['csv_file'])) {
        $error_message = "No file data received. Please try again.";
    } elseif ($_FILES['csv_file']['error'] == UPLOAD_ERR_NO_FILE) {
        $error_message = "No file was uploaded. Please select a CSV file.";
    } else {
        $file = $_FILES['csv_file'];
        
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
                UPLOAD_ERR_NO_FILE => "No file was uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload"
            ];
            $error_message = "File upload failed: " . ($upload_errors[$file['error']] ?? "Unknown error code: " . $file['error']);
        } else {
            // Check file type
            $file_info = pathinfo($file['name']);
            if (strtolower($file_info['extension']) !== 'csv') {
                $error_message = "Only CSV files are allowed. Uploaded file was: " . $file_info['extension'];
            } else {
                // Open the file
                $handle = fopen($file['tmp_name'], 'r');
                if (!$handle) {
                    $error_message = "Could not open the file. Please try again.";
                } else {
                    // Read the header row
                    $header = fgetcsv($handle);
                    
                    // Validate header
                    $expected_headers = ['name', 'status', 'last_maintenance_date'];
                    $header_valid = true;
                    
                    if (count($header) !== count($expected_headers)) {
                        $header_valid = false;
                    } else {
                        for ($i = 0; $i < count($expected_headers); $i++) {
                            if (strtolower(trim($header[$i])) !== $expected_headers[$i]) {
                                $header_valid = false;
                                break;
                            }
                        }
                    }
                    
                    if (!$header_valid) {
                        $error_message = "Invalid CSV format. Expected headers: " . implode(", ", $expected_headers) . 
                                 ". Found: " . implode(", ", $header);
                        fclose($handle);
                    } else {
                        // Prepare the insert statement
                        $insert_sql = "INSERT INTO equipment (name, status, last_maintenance_date) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($insert_sql);
                        
                        if (!$stmt) {
                            $error_message = "Database error: " . $conn->error;
                            fclose($handle);
                        } else {
                            // Initialize counters
                            $success_count = 0;
                            $error_count = 0;
                            $row_number = 1; // Start at 1 because we already read the header
                            $error_rows = [];
                            
                            // Process each row
                            while (($data = fgetcsv($handle)) !== FALSE) {
                                $row_number++;
                                
                                // Skip empty rows
                                if (count($data) < 3 || empty(trim($data[0]))) {
                                    continue;
                                }
                                
                                // Extract and validate data
                                $name = trim($data[0]);
                                $status = strtolower(trim($data[1]));
                                $maintenance_date = !empty(trim($data[2])) ? trim($data[2]) : null;
                                
                                // Validate status
                                $valid_statuses = ['available', 'maintenance', 'out_of_service'];
                                if (!in_array($status, $valid_statuses)) {
                                    $error_count++;
                                    $error_rows[] = "Row $row_number: Invalid status '$status'. Must be one of: " . implode(", ", $valid_statuses);
                                    continue;
                                }
                                
                                // Validate date format if provided
                                if ($maintenance_date !== null) {
                                    $date_obj = DateTime::createFromFormat('Y-m-d', $maintenance_date);
                                    if (!$date_obj || $date_obj->format('Y-m-d') !== $maintenance_date) {
                                        $maintenance_date = null;
                                    }
                                }
                                
                                // Insert the data
                                $stmt->bind_param("sss", $name, $status, $maintenance_date);
                                
                                if ($stmt->execute()) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                    $error_rows[] = "Row $row_number: Database error - " . $stmt->error;
                                }
                            }
                            
                            // Close the file
                            fclose($handle);
                            
                            // Set success message
                            if ($success_count > 0) {
                                $success_message = "Successfully imported $success_count equipment items";
                                if ($error_count > 0) {
                                    $success_message .= " with $error_count errors";
                                    if (!empty($error_rows)) {
                                        $error_message = "Import errors:<br>" . implode("<br>", $error_rows);
                                    }
                                }
                            } else {
                                $error_message = "No equipment items were imported";
                                if (!empty($error_rows)) {
                                    $error_message .= "<br>Errors:<br>" . implode("<br>", $error_rows);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    // Redirect with status message
    if (!empty($success_message)) {
        header("Location: equipment_manager_dashboard.php?success=" . urlencode($success_message) . 
               (!empty($error_message) ? "&error=" . urlencode($error_message) : ""));
    } else {
        header("Location: equipment_manager_dashboard.php?error=" . urlencode($error_message));
    }
    exit();
} else {
    // If accessed directly without a file upload
    header("Location: equipment_manager_dashboard.php");
    exit();
}
?>
