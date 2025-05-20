<?php
session_start();
require_once('../config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit(); }

// Handle Add Equipment Manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manager'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $gender = $_POST['gender'];
    $dob = $_POST['date_of_birth'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, contact_number, password_hash, role, gender, date_of_birth) VALUES (?, ?, ?, ?, ?, 'equipment_manager', ?, ?)");
    $stmt->bind_param('sssssss', $first_name, $last_name, $email, $contact, $password, $gender, $dob);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Handle Delete Equipment Manager
if (isset($_GET['delete_manager'])) {
    $user_id = intval($_GET['delete_manager']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'equipment_manager'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Handle Archive Equipment Manager
if (isset($_GET['archive_manager'])) {
    $user_id = intval($_GET['archive_manager']);
    $stmt = $conn->prepare("UPDATE users SET is_archived = 1, archived_at = NOW() WHERE user_id = ? AND role = 'equipment_manager'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Handle Add Equipment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    $last_maintenance = $_POST['last_maintenance_date'] ?: null;
    $next_maintenance = $_POST['next_maintenance_date'] ?: null;
    $stmt = $conn->prepare("INSERT INTO equipment (name, status, last_maintenance_date, next_maintenance_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $status, $last_maintenance, $next_maintenance);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Handle Archive Equipment
if (isset($_GET['archive_equipment'])) {
    $equipment_id = intval($_GET['archive_equipment']);
    $stmt = $conn->prepare("UPDATE equipment SET is_archived = 1, archived_at = NOW() WHERE equipment_id = ?");
    $stmt->bind_param('i', $equipment_id);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Handle Change Equipment Status
if (isset($_POST['change_status']) && isset($_POST['equipment_id'])) {
    $equipment_id = intval($_POST['equipment_id']);
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE equipment SET status = ? WHERE equipment_id = ?");
    $stmt->bind_param('si', $new_status, $equipment_id);
    $stmt->execute();
    $stmt->close();
    header('Location: equipment.php'); exit();
}

// Fetch Equipment Managers (only non-archived)
$result = $conn->query("SELECT * FROM users WHERE role = 'equipment_manager' AND is_archived = 0 ORDER BY date_registered DESC");
$managers = $result->fetch_all(MYSQLI_ASSOC);

// Fetch Equipments (only non-archived)
$result = $conn->query("SELECT * FROM equipment WHERE is_archived = 0 ORDER BY equipment_id DESC");
$equipments = $result->fetch_all(MYSQLI_ASSOC);

// Get equipment stats
$stmt = $conn->prepare("SELECT 
                        COUNT(*) as total_equipment,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_count,
                        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_count,
                        SUM(CASE WHEN status = 'out_of_service' THEN 1 ELSE 0 END) as out_of_service_count
                        FROM equipment 
                        WHERE is_archived = 0");
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

// Get manager stats
$stmt = $conn->prepare("SELECT COUNT(*) as total_managers FROM users WHERE role = 'equipment_manager' AND is_archived = 0");
$stmt->execute();
$result = $stmt->get_result();
$manager_stats = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - EliteFit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-4 flex-shrink-0 h-screen fixed top-0 left-0 z-30 flex flex-col">
            <div class="px-4 mt-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-600 text-white">
                        <span class="text-xl font-bold">EF</span>
                    </div>
                    <h1 class="text-2xl font-bold">EliteFit</h1>
                </div>
                <p class="text-gray-400 text-sm mt-1">Admin Dashboard</p>
            </div>
            <nav class="mt-8 flex-1">
                <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tachometer-alt mr-3 text-blue-400"></i> Dashboard
                </a>
                <a href="members.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-users mr-3 text-blue-400"></i> Members
                </a>
                <a href="trainers.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-dumbbell mr-3 text-blue-400"></i> Trainers
                </a>
                <a href="#" class="flex items-center px-4 py-3 bg-gray-900 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-cogs mr-3 text-blue-400"></i> Equipment
                </a>
                <a href="settings.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-cog mr-3 text-blue-400"></i> Settings
                </a>
                <a href="archive.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-archive mr-3 text-blue-400"></i> Archive
                </a>
            </nav>
            <div class="mt-auto mb-4 px-4">
                <button id="logoutBtn" class="w-full text-left px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center justify-start font-semibold shadow-lg transition-all duration-200">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto ml-64">
            <!-- Top Bar -->
            <div class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-20">
                <h2 class="text-xl font-semibold">Equipment Management</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">
                        <?php echo date('l, F j, Y'); ?>
                    </span>
                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user-shield text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-cogs text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Equipment</h3>
                                <p class="text-2xl font-semibold"><?php echo $stats['total_equipment']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Available</h3>
                                <p class="text-2xl font-semibold"><?php echo $stats['available_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 text-orange-500">
                                <i class="fas fa-tools text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">In Maintenance</h3>
                                <p class="text-2xl font-semibold"><?php echo $stats['maintenance_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                                <i class="fas fa-user-cog text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Equipment Managers</h3>
                                <p class="text-2xl font-semibold"><?php echo $manager_stats['total_managers']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button id="equipmentTab" class="tab-btn py-4 px-6 border-b-2 border-yellow-500 font-medium text-sm text-yellow-600 focus:outline-none">
                                Equipment
                            </button>
                            <button id="managersTab" class="tab-btn py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Equipment Managers
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Equipment Section -->
                <div id="equipmentSection" class="tab-content">
                    <!-- Add Equipment Form -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Add New Equipment</h3>
                            <button id="toggleEquipmentFormBtn" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 flex items-center transition-colors">
                                <i class="fas fa-plus mr-2"></i> Add Equipment
                            </button>
                        </div>
                        
                        <form method="POST" id="addEquipmentForm" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Name</label>
                                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_service">Out of Service</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Maintenance Date</label>
                                <input type="date" name="last_maintenance_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Next Maintenance Date</label>
                                <input type="date" name="next_maintenance_date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            
                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" id="cancelEquipmentBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 mr-2 transition-colors">Cancel</button>
                                <button type="submit" name="add_equipment" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors">Add Equipment</button>
                            </div>
                        </form>
                    </div>

                    <!-- Equipment Table -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">All Equipment</h3>
                            <div class="relative">
                                <input type="text" id="searchEquipment" placeholder="Search equipment..." class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Maintenance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Maintenance</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="equipmentTableBody">
                                    <?php foreach ($equipments as $equipment): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#<?php echo $equipment['equipment_id']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($equipment['name'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST" class="inline-flex">
                                                <input type="hidden" name="equipment_id" value="<?php echo $equipment['equipment_id']; ?>">
                                                <select name="new_status" onchange="this.form.submit()" class="border rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 
                                                    <?php 
                                                    echo match($equipment['status'] ?? '') {
                                                        'available' => 'bg-green-50 text-green-800 border-green-200',
                                                        'maintenance' => 'bg-orange-50 text-orange-800 border-orange-200',
                                                        'out_of_service' => 'bg-red-50 text-red-800 border-red-200',
                                                        default => 'bg-gray-50 text-gray-800 border-gray-200'
                                                    };
                                                    ?>">
                                                    <option value="available" <?php if (($equipment['status'] ?? '') === 'available') echo 'selected'; ?>>Available</option>
                                                    <option value="maintenance" <?php if (($equipment['status'] ?? '') === 'maintenance') echo 'selected'; ?>>Maintenance</option>
                                                    <option value="out_of_service" <?php if (($equipment['status'] ?? '') === 'out_of_service') echo 'selected'; ?>>Out of Service</option>
                                                </select>
                                                <input type="hidden" name="change_status" value="1">
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php 
                                                if (!empty($equipment['last_maintenance_date'])) {
                                                    echo date('M d, Y', strtotime($equipment['last_maintenance_date']));
                                                } else {
                                                    echo '<span class="text-gray-400">Not recorded</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php 
                                                if (!empty($equipment['next_maintenance_date'])) {
                                                    $next_date = strtotime($equipment['next_maintenance_date']);
                                                    $today = time();
                                                    $days_left = round(($next_date - $today) / (60 * 60 * 24));
                                                    
                                                    echo date('M d, Y', $next_date);
                                                    
                                                    if ($days_left < 0) {
                                                        echo ' <span class="text-red-600 text-xs font-medium">Overdue</span>';
                                                    } elseif ($days_left < 7) {
                                                        echo ' <span class="text-orange-600 text-xs font-medium">Soon</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-gray-400">Not scheduled</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-blue-600 hover:text-blue-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="text-indigo-600 hover:text-indigo-900" title="Schedule Maintenance">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </button>
                                                <a href="#" data-id="<?php echo $equipment['equipment_id']; ?>" class="text-yellow-600 hover:text-yellow-900 archive-equipment-btn" title="Archive">
                                                    <i class="fas fa-archive"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium"><?php echo count($equipments); ?></span> equipment
                            </div>
                            <div class="flex space-x-1">
                                <button class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="px-3 py-1 rounded bg-yellow-500 text-white">1</button>
                                <button class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">2</button>
                                <button class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Managers Section -->
                <div id="managersSection" class="tab-content hidden">
                    <!-- Add Manager Form -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Add Equipment Manager</h3>
                            <button id="toggleManagerFormBtn" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center transition-colors">
                                <i class="fas fa-plus mr-2"></i> Add Manager
                            </button>
                        </div>
                        
                        <form method="POST" id="addManagerForm" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="text" name="contact_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                                <input type="date" name="date_of_birth" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            
                            <div class="md:col-span-2 flex justify-end">
                                <button type="button" id="cancelManagerBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 mr-2 transition-colors">Cancel</button>
                                <button type="submit" name="add_manager" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">Add Manager</button>
                            </div>
                        </form>
                    </div>

                    <!-- Managers Table -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">All Equipment Managers</h3>
                            <div class="relative">
                                <input type="text" id="searchManagers" placeholder="Search managers..." class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="managersTableBody">
                                    <?php foreach ($managers as $manager): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <span class="font-medium text-purple-600">
                                                        <?php 
                                                        $initials = strtoupper(substr($manager['first_name'] ?? '', 0, 1) . substr($manager['last_name'] ?? '', 0, 1));
                                                        echo $initials;
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars(($manager['first_name'] ?? '') . ' ' . ($manager['last_name'] ?? '')); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        ID: <?php echo $manager['user_id']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($manager['email'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($manager['contact_number'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo $manager['gender'] === 'male' ? 'bg-blue-100 text-blue-800' : 
                                                        ($manager['gender'] === 'female' ? 'bg-pink-100 text-pink-800' : 
                                                        'bg-gray-100 text-gray-800'); ?>">
                                                <?php echo htmlspecialchars(ucfirst($manager['gender'] ?? 'Unknown')); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo date('M d, Y', strtotime($manager['date_registered'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-blue-600 hover:text-blue-900" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="#" data-id="<?php echo $manager['user_id']; ?>" class="text-yellow-600 hover:text-yellow-900 archive-manager-btn" title="Archive">
                                                    <i class="fas fa-archive"></i>
                                                </a>
                                                <a href="#" data-id="<?php echo $manager['user_id']; ?>" class="text-red-600 hover:text-red-900 delete-manager-btn" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="flex items-center justify-between mt-6">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium"><?php echo count($managers); ?></span> managers
                            </div>
                            <div class="flex space-x-1">
                                <button class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="px-3 py-1 rounded bg-purple-600 text-white">1</button>
                                <button class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full relative animate-fadeInUp">
            <div class="flex flex-col items-center">
                <div id="confirmIcon" class="rounded-full p-4 mb-4">
                    <i id="confirmIconInner" class="text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold mb-2">Are you sure?</h2>
                <p class="mb-6 text-center" id="confirmModalMsg"></p>
                <div class="flex justify-center gap-4">
                    <button id="confirmYes" class="px-6 py-2 rounded-lg font-semibold flex items-center transition-colors">
                        <i class="fas fa-check mr-2"></i> <span id="confirmYesText">Yes</span>
                    </button>
                    <button id="confirmNo" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-semibold flex items-center transition-colors">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40 hidden">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full relative animate-fadeInUp border-t-8 border-red-600">
        <div class="flex flex-col items-center">
          <div class="mb-4">
            <span class="bg-red-100 p-4 rounded-full"><i class="fas fa-sign-out-alt text-red-500 text-5xl animate-bounce"></i></span>
          </div>
          <h3 class="text-2xl font-extrabold mb-2 text-gray-800 tracking-tight">Logout?</h3>
          <p class="mb-6 text-gray-600 text-center">Are you sure you want to logout from your admin session?</p>
          <div class="flex gap-4">
            <button id="confirmLogout" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold shadow transition-all duration-200 flex items-center"><i class="fas fa-sign-out-alt mr-2"></i> Yes, Logout</button>
            <button id="cancelLogout" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-bold shadow transition-all duration-200">Cancel</button>
          </div>
        </div>
        <button id="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
      </div>
    </div>
    
    <style>
      .animate-fadeInUp { animation: fadeInUp .4s cubic-bezier(.39,.575,.565,1) both; }
      @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(40px) scale(0.95); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
      }
      #logoutBtn { transition: box-shadow .2s, background .2s; }
      #logoutModal { transition: background .2s; }
      #logoutModal .animate-bounce { animation: bounce 1.2s infinite alternate; }
      @keyframes bounce {
        0% { transform: translateY(0); }
        100% { transform: translateY(-10px); }
      }
    </style>

    <script>
        // Tab switching
        document.getElementById('equipmentTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-yellow-600', 'border-purple-500', 'text-purple-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-yellow-500', 'text-yellow-600');
            
            document.getElementById('equipmentSection').classList.remove('hidden');
            document.getElementById('managersSection').classList.add('hidden');
        });

        document.getElementById('managersTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-yellow-500', 'text-yellow-600', 'border-purple-500', 'text-purple-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-purple-500', 'text-purple-600');
            
            document.getElementById('equipmentSection').classList.add('hidden');
            document.getElementById('managersSection').classList.remove('hidden');
        });

        // Toggle Equipment Form
        document.getElementById('toggleEquipmentFormBtn').addEventListener('click', function() {
            const form = document.getElementById('addEquipmentForm');
            form.classList.toggle('hidden');
            this.innerHTML = form.classList.contains('hidden') ? 
                '<i class="fas fa-plus mr-2"></i> Add Equipment' : 
                '<i class="fas fa-times mr-2"></i> Cancel';
        });

        document.getElementById('cancelEquipmentBtn').addEventListener('click', function() {
            document.getElementById('addEquipmentForm').classList.add('hidden');
            document.getElementById('toggleEquipmentFormBtn').innerHTML = '<i class="fas fa-plus mr-2"></i> Add Equipment';
        });

        // Toggle Manager Form
        document.getElementById('toggleManagerFormBtn').addEventListener('click', function() {
            const form = document.getElementById('addManagerForm');
            form.classList.toggle('hidden');
            this.innerHTML = form.classList.contains('hidden') ? 
                '<i class="fas fa-plus mr-2"></i> Add Manager' : 
                '<i class="fas fa-times mr-2"></i> Cancel';
        });

        document.getElementById('cancelManagerBtn').addEventListener('click', function() {
            document.getElementById('addManagerForm').classList.add('hidden');
            document.getElementById('toggleManagerFormBtn').innerHTML = '<i class="fas fa-plus mr-2"></i> Add Manager';
        });

        // Search functionality for Equipment
        document.getElementById('searchEquipment').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('equipmentTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const status = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                
                if (name.includes(searchValue) || status.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });

        // Search functionality for Managers
        document.getElementById('searchManagers').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('managersTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const email = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const contact = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                
                if (name.includes(searchValue) || email.includes(searchValue) || contact.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });

        // Confirmation Modal
        let confirmAction = null;
        let itemId = null;

        function showConfirmModal(message, action, id, type = 'delete') {
            document.getElementById('confirmModalMsg').textContent = message;
            document.getElementById('confirmModal').classList.remove('hidden');
            
            // Set icon and button styles based on action type
            const iconElement = document.getElementById('confirmIcon');
            const iconInner = document.getElementById('confirmIconInner');
            const confirmButton = document.getElementById('confirmYes');
            const confirmText = document.getElementById('confirmYesText');
            
            if (type === 'delete') {
                iconElement.className = 'bg-red-100 rounded-full p-4 mb-4';
                iconInner.className = 'fas fa-exclamation-triangle text-red-500 text-3xl';
                confirmButton.className = 'bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center transition-colors';
                confirmText.textContent = 'Yes, Delete';
            } else if (type === 'archive') {
                iconElement.className = 'bg-yellow-100 rounded-full p-4 mb-4';
                iconInner.className = 'fas fa-archive text-yellow-500 text-3xl';
                confirmButton.className = 'bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center transition-colors';
                confirmText.textContent = 'Yes, Archive';
            }
            
            confirmAction = action;
            itemId = id;
        }

        document.getElementById('confirmYes').onclick = function() {
            document.getElementById('confirmModal').classList.add('hidden');
            if (typeof confirmAction === 'function') confirmAction(itemId);
        };

        document.getElementById('confirmNo').onclick = function() {
            document.getElementById('confirmModal').classList.add('hidden');
            confirmAction = null;
            itemId = null;
        };

        // Delete Manager button click
        const deleteManagerButtons = document.querySelectorAll('.delete-manager-btn');
        deleteManagerButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to permanently delete this equipment manager? This action cannot be undone.',
                    function(id) { window.location.href = '?delete_manager=' + id; },
                    id,
                    'delete'
                );
            });
        });

        // Archive Manager button click
        const archiveManagerButtons = document.querySelectorAll('.archive-manager-btn');
        archiveManagerButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to archive this equipment manager? This action can be reversed from the Archive section.',
                    function(id) { window.location.href = '?archive_manager=' + id; },
                    id,
                    'archive'
                );
            });
        });

        // Archive Equipment button click
        const archiveEquipmentButtons = document.querySelectorAll('.archive-equipment-btn');
        archiveEquipmentButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to archive this equipment? This action can be reversed from the Archive section.',
                    function(id) { window.location.href = '?archive_equipment=' + id; },
                    id,
                    'archive'
                );
            });
        });

        // Modern Logout Modal
        document.addEventListener('DOMContentLoaded', function() {
          var logoutBtn = document.getElementById('logoutBtn');
          var modal = document.getElementById('logoutModal');
          var confirmBtn = document.getElementById('confirmLogout');
          var cancelBtn = document.getElementById('cancelLogout');
          var closeModal = document.getElementById('closeModal');
          if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
              e.preventDefault();
              modal.classList.remove('hidden');
            });
          }
          if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
              modal.classList.add('hidden');
            });
          }
          if (closeModal) {
            closeModal.addEventListener('click', function() {
              modal.classList.add('hidden');
            });
          }
          if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
              window.location.href = '../logout.php';
            });
          }
          // Optional: Close modal on ESC
          document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
              modal.classList.add('hidden');
            }
          });
        });
    </script>
</body>
</html>
