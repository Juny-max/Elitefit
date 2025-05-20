<?php
session_start();
require_once('../config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit(); }

// Handle permanent delete for users (members, trainers, equipment managers)
if (isset($_GET['permadelete_user'])) {
    $user_id = intval($_GET['permadelete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: archive.php'); exit();
}
// Handle restore for users
if (isset($_GET['restore_user'])) {
    $user_id = intval($_GET['restore_user']);
    $stmt = $conn->prepare("UPDATE users SET is_archived = 0, archived_at = NULL WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: archive.php'); exit();
}
// Handle permanent delete for equipment
if (isset($_GET['permadelete_equipment'])) {
    $equipment_id = intval($_GET['permadelete_equipment']);
    $stmt = $conn->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $stmt->bind_param('i', $equipment_id);
    $stmt->execute();
    $stmt->close();
    header('Location: archive.php'); exit();
}
// Handle restore for equipment
if (isset($_GET['restore_equipment'])) {
    $equipment_id = intval($_GET['restore_equipment']);
    $stmt = $conn->prepare("UPDATE equipment SET is_archived = 0, archived_at = NULL WHERE equipment_id = ?");
    $stmt->bind_param('i', $equipment_id);
    $stmt->execute();
    $stmt->close();
    header('Location: archive.php'); exit();
}
// Fetch archived users
$archived_users = $conn->query("SELECT * FROM users WHERE is_archived = 1 ORDER BY archived_at DESC")->fetch_all(MYSQLI_ASSOC);
// Fetch archived equipment
$archived_equipment = $conn->query("SELECT * FROM equipment WHERE is_archived = 1 ORDER BY archived_at DESC")->fetch_all(MYSQLI_ASSOC);

// Get archive stats
$stmt = $conn->prepare("SELECT 
                        COUNT(*) as total_archived,
                        SUM(CASE WHEN role = 'member' THEN 1 ELSE 0 END) as members_count,
                        SUM(CASE WHEN role = 'trainer' THEN 1 ELSE 0 END) as trainers_count,
                        SUM(CASE WHEN role = 'equipment_manager' THEN 1 ELSE 0 END) as managers_count
                        FROM users 
                        WHERE is_archived = 1");
$stmt->execute();
$result = $stmt->get_result();
$user_stats = $result->fetch_assoc();
$stmt->close();

// Get equipment stats
$stmt = $conn->prepare("SELECT COUNT(*) as equipment_count FROM equipment WHERE is_archived = 1");
$stmt->execute();
$result = $stmt->get_result();
$equipment_stats = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive - EliteFit Admin</title>
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
                <a href="equipment.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-cogs mr-3 text-blue-400"></i> Equipment
                </a>
                <a href="settings.php" class="flex items-center px-4 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-cog mr-3 text-blue-400"></i> Settings
                </a>
                <a href="#" class="flex items-center px-4 py-3 bg-gray-900 hover:bg-gray-700 transition-colors">
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
                <h2 class="text-xl font-semibold">Archive Management</h2>
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
                            <div class="p-3 rounded-full bg-gray-100 text-gray-500">
                                <i class="fas fa-archive text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Archived</h3>
                                <p class="text-2xl font-semibold"><?php echo $user_stats['total_archived'] + $equipment_stats['equipment_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Archived Members</h3>
                                <p class="text-2xl font-semibold"><?php echo $user_stats['members_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-dumbbell text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Archived Trainers</h3>
                                <p class="text-2xl font-semibold"><?php echo $user_stats['trainers_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-cogs text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Archived Equipment</h3>
                                <p class="text-2xl font-semibold"><?php echo $equipment_stats['equipment_count']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button id="usersTab" class="tab-btn py-4 px-6 border-b-2 border-gray-500 font-medium text-sm text-gray-600 focus:outline-none">
                                Archived Users
                            </button>
                            <button id="equipmentTab" class="tab-btn py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Archived Equipment
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Archived Users Section -->
                <div id="usersSection" class="tab-content">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Archived Users</h3>
                            <div class="relative">
                                <input type="text" id="searchUsers" placeholder="Search archived users..." class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archived At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="usersTableBody">
                                    <?php foreach ($archived_users as $user): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full 
                                                    <?php 
                                                    echo match($user['role']) {
                                                        'member' => 'bg-blue-100 text-blue-600',
                                                        'trainer' => 'bg-green-100 text-green-600',
                                                        'equipment_manager' => 'bg-yellow-100 text-yellow-600',
                                                        default => 'bg-gray-100 text-gray-600'
                                                    };
                                                    ?> flex items-center justify-center">
                                                    <span class="font-medium">
                                                        <?php 
                                                        $initials = strtoupper(substr($user['first_name'] ?? '', 0, 1) . substr($user['last_name'] ?? '', 0, 1));
                                                        echo $initials;
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        ID: <?php echo $user['user_id']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                echo match($user['role']) {
                                                    'member' => 'bg-blue-100 text-blue-800',
                                                    'trainer' => 'bg-green-100 text-green-800',
                                                    'equipment_manager' => 'bg-yellow-100 text-yellow-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                ?>">
                                                <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'Unknown')); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php 
                                                if (!empty($user['archived_at'])) {
                                                    echo date('M d, Y H:i', strtotime($user['archived_at']));
                                                } else {
                                                    echo '<span class="text-gray-400">Unknown</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="#" data-id="<?php echo $user['user_id']; ?>" class="text-green-600 hover:text-green-900 restore-user-btn" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                                <a href="#" data-id="<?php echo $user['user_id']; ?>" class="text-red-600 hover:text-red-900 delete-user-btn" title="Delete Permanently">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($archived_users) === 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center py-6">
                                                <i class="fas fa-archive text-4xl mb-2 text-gray-400"></i>
                                                <p>No archived users found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Archived Equipment Section -->
                <div id="equipmentSection" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Archived Equipment</h3>
                            <div class="relative">
                                <input type="text" id="searchEquipment" placeholder="Search archived equipment..." class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archived At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="equipmentTableBody">
                                    <?php foreach ($archived_equipment as $equipment): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#<?php echo $equipment['equipment_id']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($equipment['name'] ?? ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                echo match($equipment['status'] ?? '') {
                                                    'available' => 'bg-green-100 text-green-800',
                                                    'maintenance' => 'bg-orange-100 text-orange-800',
                                                    'out_of_service' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                ?>">
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $equipment['status'] ?? 'Unknown'))); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php 
                                                if (!empty($equipment['archived_at'])) {
                                                    echo date('M d, Y H:i', strtotime($equipment['archived_at']));
                                                } else {
                                                    echo '<span class="text-gray-400">Unknown</span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="#" data-id="<?php echo $equipment['equipment_id']; ?>" class="text-green-600 hover:text-green-900 restore-equipment-btn" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                                <a href="#" data-id="<?php echo $equipment['equipment_id']; ?>" class="text-red-600 hover:text-red-900 delete-equipment-btn" title="Delete Permanently">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($archived_equipment) === 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center py-6">
                                                <i class="fas fa-archive text-4xl mb-2 text-gray-400"></i>
                                                <p>No archived equipment found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
        document.getElementById('usersTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-gray-500', 'text-gray-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-gray-500', 'text-gray-600');
            
            document.getElementById('usersSection').classList.remove('hidden');
            document.getElementById('equipmentSection').classList.add('hidden');
        });

        document.getElementById('equipmentTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-gray-500', 'text-gray-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-gray-500', 'text-gray-600');
            
            document.getElementById('usersSection').classList.add('hidden');
            document.getElementById('equipmentSection').classList.remove('hidden');
        });

        // Search functionality for Users
        document.getElementById('searchUsers').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('usersTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].cells.length <= 1) continue; // Skip empty state row
                
                const name = rows[i].cells[0].textContent.toLowerCase();
                const role = rows[i].cells[1].textContent.toLowerCase();
                const email = rows[i].cells[2].textContent.toLowerCase();
                
                if (name.includes(searchValue) || role.includes(searchValue) || email.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });

        // Search functionality for Equipment
        document.getElementById('searchEquipment').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.getElementById('equipmentTableBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].cells.length <= 1) continue; // Skip empty state row
                
                const id = rows[i].cells[0].textContent.toLowerCase();
                const name = rows[i].cells[1].textContent.toLowerCase();
                const status = rows[i].cells[2].textContent.toLowerCase();
                
                if (id.includes(searchValue) || name.includes(searchValue) || status.includes(searchValue)) {
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
            } else if (type === 'restore') {
                iconElement.className = 'bg-green-100 rounded-full p-4 mb-4';
                iconInner.className = 'fas fa-undo text-green-500 text-3xl';
                confirmButton.className = 'bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center transition-colors';
                confirmText.textContent = 'Yes, Restore';
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

        // Delete User button click
        const deleteUserButtons = document.querySelectorAll('.delete-user-btn');
        deleteUserButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to permanently delete this user? This action cannot be undone.',
                    function(id) { window.location.href = '?permadelete_user=' + id; },
                    id,
                    'delete'
                );
            });
        });

        // Restore User button click
        const restoreUserButtons = document.querySelectorAll('.restore-user-btn');
        restoreUserButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to restore this user?',
                    function(id) { window.location.href = '?restore_user=' + id; },
                    id,
                    'restore'
                );
            });
        });

        // Delete Equipment button click
        const deleteEquipmentButtons = document.querySelectorAll('.delete-equipment-btn');
        deleteEquipmentButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to permanently delete this equipment? This action cannot be undone.',
                    function(id) { window.location.href = '?permadelete_equipment=' + id; },
                    id,
                    'delete'
                );
            });
        });

        // Restore Equipment button click
        const restoreEquipmentButtons = document.querySelectorAll('.restore-equipment-btn');
        restoreEquipmentButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                showConfirmModal(
                    'Do you want to restore this equipment?',
                    function(id) { window.location.href = '?restore_equipment=' + id; },
                    id,
                    'restore'
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
