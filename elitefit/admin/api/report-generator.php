<?php
// This file would be placed in your PHP backend
session_start();
require_once('../config.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get report parameters
$reportType = $_GET['type'] ?? 'members';
$format = $_GET['format'] ?? 'pdf';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$includeCharts = isset($_GET['include_charts']) ? (bool)$_GET['include_charts'] : true;
$includeTables = isset($_GET['include_tables']) ? (bool)$_GET['include_tables'] : true;

// Set headers based on format
if ($format === 'excel') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="elitefit_report_' . $reportType . '_' . date('Y-m-d') . '.xlsx"');
} else {
    // Default to PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="elitefit_report_' . $reportType . '_' . date('Y-m-d') . '.pdf"');
}

// Include necessary libraries
// For PDF: require_once('vendor/tcpdf/tcpdf.php');
// For Excel: require_once('vendor/phpspreadsheet/autoload.php');

// Generate report based on type
switch ($reportType) {
    case 'members':
        generateMembersReport($format, $startDate, $endDate, $includeCharts, $includeTables);
        break;
    case 'trainers':
        generateTrainersReport($format, $startDate, $endDate, $includeCharts, $includeTables);
        break;
    case 'equipment':
        generateEquipmentReport($format, $startDate, $endDate, $includeCharts, $includeTables);
        break;
    case 'sessions':
        generateSessionsReport($format, $startDate, $endDate, $includeCharts, $includeTables);
        break;
    case 'financial':
        generateFinancialReport($format, $startDate, $endDate, $includeCharts, $includeTables);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid report type']);
        exit();
}

// Example function for generating members report
function generateMembersReport($format, $startDate, $endDate, $includeCharts, $includeTables) {
    global $conn;
    
    // Build query with date filters if provided
    $query = "SELECT u.*, mf.height, mf.weight, mf.body_type, mf.experience_level 
              FROM users u 
              LEFT JOIN member_fitness mf ON u.user_id = mf.member_id 
              WHERE u.role = 'member'";
    
    if ($startDate && $endDate) {
        $query .= " AND u.date_registered BETWEEN ? AND ?";
    }
    
    $query .= " ORDER BY u.date_registered DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($startDate && $endDate) {
        $stmt->bind_param('ss', $startDate, $endDate);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get member registration stats by month
    $statsQuery = "SELECT 
                    DATE_FORMAT(date_registered, '%Y-%m') as month,
                    COUNT(*) as count
                  FROM users
                  WHERE role = 'member'";
    
    if ($startDate && $endDate) {
        $statsQuery .= " AND date_registered BETWEEN ? AND ?";
    }
    
    $statsQuery .= " GROUP BY DATE_FORMAT(date_registered, '%Y-%m')
                    ORDER BY month";
    
    $statsStmt = $conn->prepare($statsQuery);
    
    if ($startDate && $endDate) {
        $statsStmt->bind_param('ss', $startDate, $endDate);
    }
    
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $registrationStats = $statsResult->fetch_all(MYSQLI_ASSOC);
    
    // Get body type distribution
    $bodyTypeQuery = "SELECT 
                        body_type,
                        COUNT(*) as count
                      FROM member_fitness
                      GROUP BY body_type";
    
    $bodyTypeResult = $conn->query($bodyTypeQuery);
    $bodyTypeStats = $bodyTypeResult->fetch_all(MYSQLI_ASSOC);
    
    // Get experience level distribution
    $expLevelQuery = "SELECT 
                        experience_level,
                        COUNT(*) as count
                      FROM member_fitness
                      GROUP BY experience_level";
    
    $expLevelResult = $conn->query($expLevelQuery);
    $expLevelStats = $expLevelResult->fetch_all(MYSQLI_ASSOC);
    
    if ($format === 'excel') {
        generateExcelReport('Members Report', $members, $registrationStats, $bodyTypeStats, $expLevelStats, $includeCharts, $includeTables);
    } else {
        generatePdfReport('Members Report', $members, $registrationStats, $bodyTypeStats, $expLevelStats, $includeCharts, $includeTables);
    }
}

// Example function for generating PDF report
function generatePdfReport($title, $data, $registrationStats, $bodyTypeStats, $expLevelStats, $includeCharts, $includeTables) {
    // In a real implementation, you would use a library like TCPDF or FPDF
    // This is a simplified example
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('EliteFit Gym Management System');
    $pdf->SetAuthor('EliteFit Admin');
    $pdf->SetTitle($title);
    
    // Set default header and footer
    $pdf->setHeaderData('', 0, 'EliteFit Gym', $title . ' - Generated on ' . date('Y-m-d H:i:s'));
    
    // Set margins
    $pdf->SetMargins(15, 20, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', 'B', 16);
    
    // Title
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(10);
    
    // Summary
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Summary', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Total Members: ' . count($data), 0, 1, 'L');
    $pdf->Cell(0, 10, 'Report Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
    $pdf->Ln(10);
    
    // Charts (if enabled)
    if ($includeCharts) {
        // In a real implementation, you would generate charts using a library
        // and embed them in the PDF
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Member Registration Trend', 0, 1, 'L');
        // Chart would be inserted here
        $pdf->Ln(10);
        
        $pdf->Cell(0, 10, 'Member Body Types', 0, 1, 'L');
        // Chart would be inserted here
        $pdf->Ln(10);
    }
    
    // Data table (if enabled)
    if ($includeTables) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Member Details', 0, 1, 'L');
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 7, 'Name', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Email', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Registration Date', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Body Type', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Experience', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('helvetica', '', 9);
        foreach ($data as $member) {
            $name = ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '');
            $email = $member['email'] ?? '';
            $date = date('Y-m-d', strtotime($member['date_registered'] ?? ''));
            $bodyType = $member['body_type'] ?? 'N/A';
            $experience = $member['experience_level'] ?? 'N/A';
            
            $pdf->Cell(40, 6, $name, 1, 0, 'L');
            $pdf->Cell(50, 6, $email, 1, 0, 'L');
            $pdf->Cell(30, 6, $date, 1, 0, 'C');
            $pdf->Cell(30, 6, $bodyType, 1, 0, 'C');
            $pdf->Cell(30, 6, $experience, 1, 1, 'C');
        }
    }
    
    // Output the PDF
    $pdf->Output('elitefit_members_report.pdf', 'D');
}

// Example function for generating Excel report
function generateExcelReport($title, $data, $registrationStats, $bodyTypeStats, $expLevelStats, $includeCharts, $includeTables) {
    // In a real implementation, you would use a library like PhpSpreadsheet
    // This is a simplified example
    
    // Create new spreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('EliteFit Gym Management System')
        ->setLastModifiedBy('EliteFit Admin')
        ->setTitle($title)
        ->setSubject($title)
        ->setDescription('Report generated on ' . date('Y-m-d H:i:s'));
    
    // Get active sheet
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Summary');
    
    // Add title
    $sheet->setCellValue('A1', $title);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->mergeCells('A1:E1');
    
    // Add summary
    $sheet->setCellValue('A3', 'Total Members:');
    $sheet->setCellValue('B3', count($data));
    $sheet->setCellValue('A4', 'Report Generated:');
    $sheet->setCellValue('B4', date('Y-m-d H:i:s'));
    
    // Add data table
    if ($includeTables) {
        $sheet->setCellValue('A6', 'Member Details');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
        
        // Table header
        $sheet->setCellValue('A7', 'Name');
        $sheet->setCellValue('B7', 'Email');
        $sheet->setCellValue('C7', 'Registration Date');
        $sheet->setCellValue('D7', 'Body Type');
        $sheet->setCellValue('E7', 'Experience');
        $sheet->getStyle('A7:E7')->getFont()->setBold(true);
        
        // Table data
        $row = 8;
        foreach ($data as $member) {
            $name = ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '');
            $email = $member['email'] ?? '';
            $date = date('Y-m-d', strtotime($member['date_registered'] ?? ''));
            $bodyType = $member['body_type'] ?? 'N/A';
            $experience = $member['experience_level'] ?? 'N/A';
            
            $sheet->setCellValue('A' . $row, $name);
            $sheet->setCellValue('B' . $row, $email);
            $sheet->setCellValue('C' . $row, $date);
            $sheet->setCellValue('D' . $row, $bodyType);
            $sheet->setCellValue('E' . $row, $experience);
            
            $row++;
        }
    }
    
    // Add charts (if enabled)
    if ($includeCharts) {
        // Create a new worksheet for charts
        $chartSheet = $spreadsheet->createSheet();
        $chartSheet->setTitle('Charts');
        
        // Add registration stats data
        $chartSheet->setCellValue('A1', 'Month');
        $chartSheet->setCellValue('B1', 'Registrations');
        $chartSheet->getStyle('A1:B1')->getFont()->setBold(true);
        
        $row = 2;
        foreach ($registrationStats as $stat) {
            $chartSheet->setCellValue('A' . $row, $stat['month']);
            $chartSheet->setCellValue('B' . $row, $stat['count']);
            $row++;
        }
        
        // Create chart
        $dataSeriesLabels = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Charts!$B$1', null, 1),
        ];
        
        $xAxisTickValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Charts!$A$2:$A$' . ($row - 1), null, ($row - 2)),
        ];
        
        $dataSeriesValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', 'Charts!$B$2:$B$' . ($row - 1), null, ($row - 2)),
        ];
        
        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );
        
        $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
        $legend = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        
        $title = new \PhpOffice\PhpSpreadsheet\Chart\Title('Member Registration Trend');
        $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
            'chart1',
            $title,
            $legend,
            $plotArea
        );
        
        $chart->setTopLeftPosition('A10');
        $chart->setBottomRightPosition('H25');
        
        $chartSheet->addChart($chart);
    }
    
    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Create writer and output file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
}

// Similar functions would be implemented for other report types
function generateTrainersReport($format, $startDate, $endDate, $includeCharts, $includeTables) {
    // Implementation similar to generateMembersReport
}

function generateEquipmentReport($format, $startDate, $endDate, $includeCharts, $includeTables) {
    // Implementation similar to generateMembersReport
}

function generateSessionsReport($format, $startDate, $endDate, $includeCharts, $includeTables) {
    // Implementation similar to generateMembersReport
}

function generateFinancialReport($format, $startDate, $endDate, $includeCharts, $includeTables) {
    // Implementation similar to generateMembersReport
}
?>
