<?php
session_start();
include_once __DIR__ . "/../config.php";

// Redirect if not logged in as equipment manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'equipment_manager') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle filtering and searching ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = [];
$params = [];
$types = '';

// Default: Show only non-archived equipment
if (!$filter) {
    $where[] = 'is_archived = 0';
}

if ($filter === 'available') {
    $where[] = 'is_archived = 0';
    $where[] = 'status = ?';
    $params[] = 'available';
    $types .= 's';
} elseif ($filter === 'maintenance') {
    $where[] = 'is_archived = 0';
    $where[] = 'status = ?';
    $params[] = 'maintenance';
    $types .= 's';
} elseif ($filter === 'out_of_service') {
    $where[] = 'is_archived = 0';
    $where[] = 'status = ?';
    $params[] = 'out_of_service';
    $types .= 's';
} elseif ($filter === 'archive') {
    $where[] = 'is_archived = 1';
}
if ($search !== '') {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$equipment_sql = "SELECT * FROM equipment $where_sql ORDER BY name";
$stmt = $conn->prepare($equipment_sql);
if (count($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$equipment_result = $stmt->get_result();
$equipment = $equipment_result->fetch_all(MYSQLI_ASSOC);

// Handle equipment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $equipment_id = $_POST['equipment_id'];
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $maintenance_date = !empty($_POST['maintenance_date']) ? $_POST['maintenance_date'] : null;
    
    $update_sql = "UPDATE equipment SET name = ?, status = ?, last_maintenance_date = ? WHERE equipment_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $name, $status, $maintenance_date, $equipment_id);
    
    if ($stmt->execute()) {
        $success = "Equipment updated successfully!";
        // Refresh equipment list
        header("Location: equipment_manager_dashboard.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Error updating equipment: " . $conn->error;
    }
}

// Handle new equipment addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_equipment'])) {
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $maintenance_date = !empty($_POST['maintenance_date']) ? $_POST['maintenance_date'] : null;
    
    $insert_sql = "INSERT INTO equipment (name, status, last_maintenance_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sss", $name, $status, $maintenance_date);
    
    if ($stmt->execute()) {
        $success = "New equipment added successfully!";
        // Refresh equipment list
        header("Location: equipment_manager_dashboard.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Error adding equipment: " . $conn->error;
    }
}

// Handle archiving equipment (move to archive)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['archive_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $archive_sql = "UPDATE equipment SET is_archived = 1, archived_at = NOW() WHERE equipment_id = ?";
    $stmt = $conn->prepare($archive_sql);
    $stmt->bind_param("i", $equipment_id);
    if ($stmt->execute()) {
        $success = "Equipment archived successfully!";
        header("Location: equipment_manager_dashboard.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Error archiving equipment: " . $conn->error;
    }
}

// Handle restoring equipment from archive
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restore_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $restore_sql = "UPDATE equipment SET is_archived = 0, archived_at = NULL WHERE equipment_id = ?";
    $stmt = $conn->prepare($restore_sql);
    $stmt->bind_param("i", $equipment_id);
    if ($stmt->execute()) {
        $success = "Equipment restored successfully!";
        header("Location: equipment_manager_dashboard.php?filter=archive&success=" . urlencode($success));
        exit();
    } else {
        $error = "Error restoring equipment: " . $conn->error;
    }
}

// Handle deleting equipment (permanent delete, only from archive)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_equipment'])) {
    $equipment_id = $_POST['equipment_id'];
    $delete_sql = "DELETE FROM equipment WHERE equipment_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $equipment_id);
    if ($stmt->execute()) {
        $success = "Equipment deleted permanently!";
        header("Location: equipment_manager_dashboard.php?filter=archive&success=" . urlencode($success));
        exit();
    } else {
        $error = "Error deleting equipment: " . $conn->error;
    }
}

// Get error and success messages from URL parameters
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Manager Dashboard - EliteFit Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
<style>
    :root {
        --primary: #4361ee;
        --primary-dark: #2a5298;
        --secondary: #6c757d;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --light: #f8f9fa;
        --dark: #1e293b;
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 70px;
    }
    
    body {
        background-color: #f1f5f9;
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        overflow-x: hidden;
        margin: 0;
        padding: 0;
    }
    
    .sidebar {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        z-index: 1000;
        transition: all 0.3s;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    
    .sidebar-brand {
        padding: 1.5rem;
        color: white;
        font-weight: 600;
        font-size: 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 1rem;
    }
    
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.7);
        border-radius: 0.5rem;
        margin: 0.25rem 0.75rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }
    
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }
    
    .sidebar .nav-link i {
        width: 24px;
        text-align: center;
        margin-right: 8px;
    }
    
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 1.5rem;
        transition: all 0.3s;
        width: calc(100% - var(--sidebar-width));
        box-sizing: border-box;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    
    .status-available {
        color: var(--success);
    }
    
    .status-maintenance {
        color: var(--warning);
    }
    
    .status-out_of_service {
        color: var(--danger);
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }
    
    .action-btn {
        margin-right: 0.25rem;
        border-radius: 0.5rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table th {
        font-weight: 600;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,0.02);
    }
    
    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e2e8f0;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.25);
    }
    
    .btn {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    /* Change the edit button color to a nicer blue */
    .btn-primary {
        background: #3b82f6;
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-success, .btn-danger, .btn-warning {
        border: none;
    }
    
    .btn-success:hover, .btn-danger:hover, .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Fixed modal styling */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1055;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
        display: none;
    }
    
    .modal-dialog {
        position: relative;
        width: auto;
        margin: 1.75rem auto;
        max-width: 500px;
        pointer-events: none;
    }
    
    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: white;
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        outline: 0;
    }
    
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1050;
        width: 100vw;
        height: 100vh;
        background-color: #000;
    }
    
    .modal-backdrop.show {
        opacity: 0.5;
    }
    
    .modal-header {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        background-color: #f8fafc;
    }
    
    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1.5rem;
    }
    
    .modal-footer {
        display: flex;
        flex-wrap: wrap;
        flex-shrink: 0;
        align-items: center;
        justify-content: flex-end;
        padding: 1.25rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        border-bottom-right-radius: 0.75rem;
        border-bottom-left-radius: 0.75rem;
        background-color: #f8fafc;
    }
    
    /* Update the action buttons styling */
    .action-btn.btn-primary {
        background-color: #3b82f6;
    }

    .action-btn.btn-primary:hover {
        background-color: #2563eb;
    }

    .action-btn.btn-success {
        background-color: #10b981;
    }

    .action-btn.btn-success:hover {
        background-color: #059669;
    }

    .action-btn.btn-danger {
        background-color: #ef4444;
    }

    .action-btn.btn-danger:hover {
        background-color: #dc2626;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: var(--secondary);
        padding: 0.75rem 1rem;
        font-weight: 500;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary);
        background-color: transparent;
        border-bottom: 2px solid var(--primary);
    }
    
    .alert {
        border-radius: 0.5rem;
        border: none;
        padding: 1rem;
    }
    
    .csv-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
        background-color: #f8fafc;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .csv-upload-zone:hover {
        border-color: var(--primary);
        background-color: #f1f5f9;
    }
    
    .csv-upload-zone.dragover {
        border-color: var(--primary);
        background-color: rgba(67, 97, 238, 0.05);
    }
    
    .stats-card {
        padding: 1.5rem;
        border-radius: 0.75rem;
        color: white;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .stats-card.available {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .stats-card.maintenance {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .stats-card.out-of-service {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .stats-card.total {
        background: linear-gradient(135deg, #4361ee, #2a5298);
    }
    
    .stats-card h3 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-card p {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .stats-card i {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        right: 1.5rem;
        bottom: 1.5rem;
    }
    
    /* Responsive styles */
    @media (max-width: 991.98px) {
        .sidebar {
            width: var(--sidebar-width-collapsed);
            overflow: hidden;
        }
        
        .sidebar .nav-link span {
            display: none;
        }
        
        .sidebar-brand {
            padding: 1.5rem 0;
            text-align: center;
        }
        
        .sidebar-brand span {
            display: none;
        }
        
        .main-content {
            margin-left: var(--sidebar-width-collapsed);
            width: calc(100% - var(--sidebar-width-collapsed));
        }
    }
    
    @media (max-width: 767.98px) {
        .sidebar {
            width: 0;
            overflow: hidden;
        }
        
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
        
        .mobile-menu-toggle {
            display: block !important;
        }
        
        .sidebar.show {
            width: var(--sidebar-width);
        }
    }
    
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1050;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: 0.5rem;
        width: 40px;
        height: 40px;
        text-align: center;
        line-height: 40px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    /* Fix for modal animation */
    .fade {
        transition: opacity .15s linear;
    }
    
    .fade:not(.show) {
        opacity: 0;
    }
    
    /* Modal animation */
    .modal.fade .modal-dialog {
        transition: transform .3s ease-out;
        transform: translate(0, -50px);
    }
    
    .modal.show .modal-dialog {
        transform: none;
    }
    
    /* File upload progress */
    .file-upload-progress {
        height: 4px;
        margin-top: 10px;
        margin-bottom: 0;
        background-color: #e9ecef;
        border-radius: 0.25rem;
        overflow: hidden;
    }
    
    .file-upload-progress .progress-bar {
        height: 100%;
        background-color: #3b82f6;
        transition: width 0.3s ease;
    }
    
    /* CSV file info */
    .csv-file-info {
        margin-top: 10px;
        padding: 10px;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }
    
    .csv-file-info .file-name {
        font-weight: 500;
        color: #1e293b;
    }
    
    .csv-file-info .file-size {
        color: #64748b;
        font-size: 0.875rem;
    }
</style>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            
<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobile-menu-toggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-dumbbell me-2"></i> <span>EliteFit Gym</span>
    </div>
    <div class="d-flex flex-column h-100">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= !isset($_GET['filter']) ? 'active' : '' ?>" href="equipment_manager_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] == 'available' ? 'active' : '' ?>" href="?filter=available">
                    <i class="fas fa-check-circle text-success"></i> <span>Available</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] == 'maintenance' ? 'active' : '' ?>" href="?filter=maintenance">
                    <i class="fas fa-tools text-warning"></i> <span>Maintenance</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] == 'out_of_service' ? 'active' : '' ?>" href="?filter=out_of_service">
                    <i class="fas fa-ban text-danger"></i> <span>Out of Service</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] == 'archive' ? 'active' : '' ?>" href="?filter=archive">
                    <i class="fas fa-archive"></i> <span>Archive</span>
                </a>
            </li>
        </ul>
        
        <div class="mt-auto p-3">
            <a href="../logout.php" class="btn btn-light w-100">
                <i class="fas fa-sign-out-alt me-1"></i> <span>Logout</span>
            </a>
        </div>
    </div>
</div>

            <!-- Main Content -->
            
<!-- Main Content -->
<div class="main-content">
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fas fa-dumbbell me-2"></i>Equipment Manager Dashboard</h2>
            <a href="../logout.php" class="btn btn-light btn-sm d-none d-md-block">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_GET['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <?php if (!isset($_GET['filter']) || $_GET['filter'] != 'archive'): ?>
            <?php
                // Get equipment stats
                $total_sql = "SELECT COUNT(*) as total FROM equipment WHERE is_archived = 0";
                $available_sql = "SELECT COUNT(*) as count FROM equipment WHERE status = 'available' AND is_archived = 0";
                $maintenance_sql = "SELECT COUNT(*) as count FROM equipment WHERE status = 'maintenance' AND is_archived = 0";
                $out_of_service_sql = "SELECT COUNT(*) as count FROM equipment WHERE status = 'out_of_service' AND is_archived = 0";
                
                $total_result = $conn->query($total_sql);
                $available_result = $conn->query($available_sql);
                $maintenance_result = $conn->query($maintenance_sql);
                $out_of_service_result = $conn->query($out_of_service_sql);
                
                $total = $total_result->fetch_assoc()['total'];
                $available = $available_result->fetch_assoc()['count'];
                $maintenance = $maintenance_result->fetch_assoc()['count'];
                $out_of_service = $out_of_service_result->fetch_assoc()['count'];
            ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card total">
                        <h3><?= $total ?></h3>
                        <p>Total Equipment</p>
                        <i class="fas fa-dumbbell"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card available">
                        <h3><?= $available ?></h3>
                        <p>Available</p>
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card maintenance">
                        <h3><?= $maintenance ?></h3>
                        <p>In Maintenance</p>
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card out-of-service">
                        <h3><?= $out_of_service ?></h3>
                        <p>Out of Service</p>
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search equipment..." value="<?= htmlspecialchars($search) ?>">
                        <?php if ($filter): ?>
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Equipment Management Section -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-dumbbell me-2"></i>Equipment Management</h5>
            </div>
            <div class="card-body">
                <!-- Tabs for Add Single/Bulk -->
                <ul class="nav nav-tabs mb-4" id="equipmentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab" aria-controls="single" aria-selected="true">
                            <i class="fas fa-plus me-1"></i> Add Single Equipment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab" aria-controls="bulk" aria-selected="false">
                            <i class="fas fa-file-upload me-1"></i> Bulk Import
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="equipmentTabsContent">
                    
<!-- Add Single Equipment Tab -->
<div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
    <div class="mb-4">
        <h5>Add New Equipment</h5>
        <form method="POST" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label for="name" class="form-label">Equipment Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="available">Available</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out_of_service">Out of Service</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="maintenance_date" class="form-label">Last Maintenance Date</label>
                <input type="date" class="form-control" id="maintenance_date" name="maintenance_date">
            </div>
            <div class="col-lg-2 col-md-6 d-flex align-items-end">
                <button type="submit" name="add_equipment" class="btn btn-success w-100">
                    <i class="fas fa-plus me-1"></i> Add
                </button>
            </div>
        </form>
    </div>
</div>

                    
<!-- Bulk Import Tab -->
<div class="tab-pane fade" id="bulk" role="tabpanel" aria-labelledby="bulk-tab">
    <div class="mb-4">
        <h5>Bulk Import Equipment</h5>
        <p class="text-muted">Upload a CSV file with equipment details. The file should have the following columns: name, status, last_maintenance_date</p>
        
        <div class="mb-3">
            <a href="download_template.php" class="btn btn-outline-primary">
                <i class="fas fa-download me-1"></i> Download CSV Template
            </a>
        </div>
        
        <!-- Simple CSV Upload Form -->
        <form action="process_csv_import.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="csv_file" class="form-label">Select CSV File</label>
                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                <div class="form-text">Only CSV files are accepted.</div>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-upload me-1"></i> Import Equipment
            </button>
        </form>
    </div>
</div>
                </div>

                <!-- Equipment List -->
                <div class="mt-4">
                    <h5>Equipment List</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Equipment Name</th>
                                    <th>Status</th>
                                    <th>Last Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipment as $item): ?>
                                    <tr>
                                        <td><?= $item['equipment_id'] ?></td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $item['status'] == 'available' ? 'success' : 
                                                ($item['status'] == 'maintenance' ? 'warning' : 
                                                ($item['status'] == 'archive' ? 'secondary' : 'danger')) 
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $item['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= $item['last_maintenance_date'] ? date('M j, Y', strtotime($item['last_maintenance_date'])) : 'N/A' ?></td>
                                        <td>
                                            <?php if ($item['is_archived'] == 0): ?>
                                                <button class="btn btn-sm btn-primary action-btn" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['equipment_id'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                                    <button type="submit" name="archive_equipment" class="btn btn-sm btn-danger action-btn" title="Archive">
                                                        <i class="fas fa-archive"></i> Archive
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                                    <button type="submit" name="restore_equipment" class="btn btn-sm btn-success action-btn" title="Restore">
                                                        <i class="fas fa-undo"></i> Restore
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['equipment_id'] ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php foreach ($equipment as $item): ?>
        <?php if ($item['is_archived'] == 0): ?>
            <div class="modal fade" id="editModal<?= $item['equipment_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $item['equipment_id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?= $item['equipment_id'] ?>">
                                <i class="fas fa-edit me-2"></i>Edit Equipment
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                <div class="mb-3">
                                    <label for="edit_name<?= $item['equipment_id'] ?>" class="form-label">Equipment Name</label>
                                    <input type="text" class="form-control" id="edit_name<?= $item['equipment_id'] ?>" 
                                           name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_status<?= $item['equipment_id'] ?>" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status<?= $item['equipment_id'] ?>" name="status" required>
                                        <option value="available" <?= $item['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                                        <option value="maintenance" <?= $item['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                        <option value="out_of_service" <?= $item['status'] == 'out_of_service' ? 'selected' : '' ?>>Out of Service</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_maintenance_date<?= $item['equipment_id'] ?>" class="form-label">Last Maintenance Date</label>
                                    <input type="date" class="form-control" id="edit_maintenance_date<?= $item['equipment_id'] ?>" 
                                           name="maintenance_date" value="<?= $item['last_maintenance_date'] ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_status" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Delete Confirmation Modals -->
    <?php foreach ($equipment as $item): ?>
        <?php if ($item['is_archived'] == 1): ?>
            <div class="modal fade" id="deleteModal<?= $item['equipment_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $item['equipment_id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteModalLabel<?= $item['equipment_id'] ?>">
                                <i class="fas fa-trash me-2"></i>Confirm Deletion
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                <p>Are you sure you want to permanently delete the equipment "<strong><?= htmlspecialchars($item['name']) ?></strong>"?</p>
                                <p class="text-danger">This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_equipment" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>Delete Equipment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Mobile menu toggle
        var mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('show');
            });
        }
    });
</script>
</body>
</html>
