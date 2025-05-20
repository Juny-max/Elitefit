<?php
// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="equipment_import_template.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header row
fputcsv($output, ['name', 'status', 'last_maintenance_date']);

// Add sample rows
fputcsv($output, ['Treadmill', 'available', date('Y-m-d')]);
fputcsv($output, ['Bench Press', 'maintenance', date('Y-m-d', strtotime('-1 month'))]);
fputcsv($output, ['Elliptical', 'out_of_service', '']);

// Close the output stream
fclose($output);
exit;
?>
