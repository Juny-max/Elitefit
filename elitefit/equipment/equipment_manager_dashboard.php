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
    $status = $_POST['status'];
    $maintenance_date = !empty($_POST['maintenance_date']) ? $_POST['maintenance_date'] : null;
    
    $update_sql = "UPDATE equipment SET status = ?, last_maintenance_date = ? WHERE equipment_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $maintenance_date, $equipment_id);
    
    if ($stmt->execute()) {
        $success = "Equipment status updated successfully!";
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
        body {
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .status-available {
            color: #28a745;
        }
        .status-maintenance {
            color: #ffc107;
        }
        .status-out_of_service {
            color: #dc3545;
        }
        .badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .action-btn {
            margin-right: 5px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .sidebar {
            background-color: #343a40 !important;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-item {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-flex flex-column bg-dark sidebar" style="min-height: 100vh; position:fixed; left:0; top:0; height:100vh; z-index:1040; width:16.6667%;">
                <div class="flex-grow-1">
                    <div class="position-sticky pt-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="equipment_manager_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="?filter=available"><i class="fas fa-check-circle me-2 text-success"></i>Available</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="?filter=maintenance"><i class="fas fa-tools me-2 text-warning"></i>Maintenance</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="?filter=out_of_service"><i class="fas fa-ban me-2 text-danger"></i>Out of Service</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="?filter=archive"><i class="fas fa-archive me-2"></i>Archive</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Logout Button at Bottom -->
                <div class="mt-auto p-3">
                    <a href="../logout.php" class="btn btn-light w-100">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="margin-left:16.6667%;">
                <div class="dashboard-header mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-dumbbell me-2"></i>Equipment Manager Dashboard</h2>
                        <a href="../logout.php" class="btn btn-light">
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
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Bar -->
                <form class="mb-3" method="get" action="">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search equipment..." value="<?= htmlspecialchars($search) ?>">
                        <?php if ($filter): ?>
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <?php endif; ?>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>

                <!-- Equipment Management Section -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-dumbbell me-2"></i>Equipment Management</h4>
                    </div>
                    <div class="card-body">
                        <!-- Add New Equipment Form -->
                        <div class="mb-4">
                            <h5>Add New Equipment</h5>
                            <form method="POST" class="row g-3">
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Equipment Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="out_of_service">Out of Service</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="maintenance_date" class="form-label">Last Maintenance Date</label>
                                    <input type="date" class="form-control" id="maintenance_date" name="maintenance_date">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="add_equipment" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Equipment List -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
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
                                                    <!-- Archive Button: no confirmation, just archive -->
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                                        <button type="submit" name="archive_equipment" class="btn btn-sm btn-danger action-btn" title="Archive">
                                                            <i class="fas fa-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-primary action-btn" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['equipment_id'] ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                <?php else: ?>
                                                    <!-- Restore Button -->
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                                        <button type="submit" name="restore_equipment" class="btn btn-sm btn-success action-btn" title="Restore">
                                                            <i class="fas fa-undo"></i> Restore
                                                        </button>
                                                    </form>
                                                    <!-- Delete Button: opens confirmation modal -->
                                                    <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['equipment_id'] ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?= $item['equipment_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $item['equipment_id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?= $item['equipment_id'] ?>">Edit Equipment</h5>
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
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Confirmation Modal (only for archive) -->
                                        <?php if ($item['is_archived'] == 1): ?>
                                        <div class="modal fade" id="deleteModal<?= $item['equipment_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $item['equipment_id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="deleteModalLabel<?= $item['equipment_id'] ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="equipment_id" value="<?= $item['equipment_id'] ?>">
                                                            <p>Are you sure you want to permanently delete the equipment "<strong><?= htmlspecialchars($item['name']) ?></strong>"?</p>
                                                            <p class="text-danger">This action cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_equipment" class="btn btn-danger">Delete Equipment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>