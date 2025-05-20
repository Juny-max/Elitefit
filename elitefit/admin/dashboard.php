<?php
session_start();
require_once('../config.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get counts for dashboard cards
$stmt = $conn->prepare("SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'member' AND is_archived = 0) as member_count,
    (SELECT COUNT(*) FROM users WHERE role = 'trainer' AND is_archived = 0) as trainer_count,
    (SELECT COUNT(*) FROM users WHERE role = 'equipment_manager' AND is_archived = 0) as manager_count,
    (SELECT COUNT(*) FROM equipment WHERE is_archived = 0) as equipment_count,
    (SELECT COUNT(*) FROM booked_sessions WHERE DATE(session_date) = CURDATE()) as today_sessions,
    (SELECT COUNT(*) FROM booked_sessions WHERE DATE(session_date) = CURDATE() AND status = 'completed') as completed_sessions");
$stmt->execute();
$result = $stmt->get_result();
$counts = $result->fetch_assoc();
$stmt->close();

// Get recent registrations
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, role, date_registered 
                       FROM users 
                       WHERE is_archived = 0
                       ORDER BY date_registered DESC 
                       LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$recent_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get equipment status
$stmt = $conn->prepare("SELECT status, COUNT(*) as count 
                       FROM equipment 
                       WHERE is_archived = 0
                       GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
$equipment_status = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get member registration growth data for chart (last 6 months)
$stmt = $conn->prepare("SELECT 
                          DATE_FORMAT(date_registered, '%Y-%m') as month,
                          COUNT(*) as registrations 
                        FROM users 
                        WHERE role = 'member' AND is_archived = 0
                        GROUP BY month 
                        ORDER BY month DESC 
                        LIMIT 6");
$stmt->execute();
$result = $stmt->get_result();
$registration_data = $result->fetch_all(MYSQLI_ASSOC);
$registration_data = array_reverse($registration_data);
$stmt->close();

// Get workout plan popularity
$stmt = $conn->prepare("SELECT 
                          wp.plan_name,
                          COUNT(mwp.member_id) as member_count
                        FROM workout_plans wp
                        LEFT JOIN member_workout_preferences mwp ON wp.plan_id = mwp.plan_id
                        GROUP BY wp.plan_id
                        ORDER BY member_count DESC
                        LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$workout_plan_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get member body types distribution
$stmt = $conn->prepare("SELECT 
                          body_type,
                          COUNT(*) as count
                        FROM member_fitness
                        GROUP BY body_type");
$stmt->execute();
$result = $stmt->get_result();
$body_type_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all equipment data
$stmt = $conn->prepare("SELECT name, status, last_maintenance_date, next_maintenance_date 
                        FROM equipment 
                        WHERE is_archived = 0
                        ORDER BY equipment_id DESC
                        LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
$equipments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EliteFit</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                <a href="#" class="flex items-center px-4 py-3 bg-gray-900 hover:bg-gray-700 transition-colors">
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
                <h2 class="text-xl font-semibold">Dashboard Overview</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">
                        <?php echo date('l, F j, Y'); ?>
                    </span>
                    <div class="relative">
                        <button id="reportDropdown" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center transition-colors">
                            <i class="fas fa-file-alt mr-2"></i> Reports <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div id="reportMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg hidden z-50">
                            <div class="py-1">
                                <button id="generatePdfReport" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i> Export as PDF
                                </button>
                                <button id="generateExcelReport" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="fas fa-file-excel text-green-500 mr-2"></i> Export as Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user-shield text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6" id="dashboardContent">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Members</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['member_count']; ?></p>
                                <p class="text-xs text-green-500 flex items-center mt-1">
                                    <i class="fas fa-arrow-up mr-1"></i> Active
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-dumbbell text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Trainers</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['trainer_count']; ?></p>
                                <p class="text-xs text-green-500 flex items-center mt-1">
                                    <i class="fas fa-arrow-up mr-1"></i> Available
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-cogs text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Equipment Count</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['equipment_count']; ?></p>
                                <p class="text-xs text-yellow-500 flex items-center mt-1">
                                    <?php 
                                    $maintenance_count = 0;
                                    foreach ($equipment_status as $status) {
                                        if ($status['status'] === 'maintenance') {
                                            $maintenance_count = $status['count'];
                                            break;
                                        }
                                    }
                                    echo "<i class='fas fa-tools mr-1'></i> {$maintenance_count} in maintenance";
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 transform hover:scale-105 transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Today's Sessions</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['today_sessions']; ?></p>
                                <p class="text-xs text-purple-500 flex items-center mt-1">
                                    <i class="fas fa-check-circle mr-1"></i> <?php echo $counts['completed_sessions']; ?> completed
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Member Registration Growth Chart -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Member Registration Growth</h3>
                            <div class="text-sm text-gray-500">Last 6 months</div>
                        </div>
                        <canvas id="registrationChart" height="300"></canvas>
                    </div>

                    <!-- Equipment Status -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Equipment Status</h3>
                            <div class="text-sm text-gray-500">Current status</div>
                        </div>
                        <canvas id="equipmentChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Workout Plan Popularity & Body Types -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Workout Plan Popularity -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Workout Plan Popularity</h3>
                            <div class="text-sm text-gray-500">Member preferences</div>
                        </div>
                        <canvas id="workoutPlanChart" height="300"></canvas>
                    </div>

                    <!-- Member Body Types -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Member Body Types</h3>
                            <div class="text-sm text-gray-500">Distribution</div>
                        </div>
                        <canvas id="bodyTypeChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Recent Registrations -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Recent Registrations</h3>
                        <a href="members.php" class="text-blue-600 hover:underline text-sm">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_users as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-600 font-semibold">
                                                    <?php 
                                                    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                                                    echo $initials;
                                                    ?>
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            echo match($user['role']) {
                                                'admin' => 'bg-red-100 text-red-800',
                                                'trainer' => 'bg-green-100 text-green-800',
                                                'member' => 'bg-blue-100 text-blue-800',
                                                'equipment_manager' => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($user['date_registered'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Equipment Status Table -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Equipment Status</h3>
                        <a href="equipment.php" class="text-blue-600 hover:underline text-sm">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Maintenance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Maintenance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($equipments as $equipment): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($equipment['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            echo match($equipment['status']) {
                                                'available' => 'bg-green-100 text-green-800',
                                                'maintenance' => 'bg-yellow-100 text-yellow-800',
                                                'out_of_service' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $equipment['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $equipment['last_maintenance_date'] ? date('M d, Y', strtotime($equipment['last_maintenance_date'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $equipment['next_maintenance_date'] ? date('M d, Y', strtotime($equipment['next_maintenance_date'])) : 'Not scheduled'; ?>
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

    <!-- Report Configuration Modal -->
    <div id="reportModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full relative animate-fadeInUp border-t-4 border-blue-600">
            <button id="closeReportModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="flex flex-col items-center mb-6">
                <div class="mb-4 p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-file-alt text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-gray-800">Generate Report</h3>
                <p class="text-gray-600 text-center">Configure your report options below</p>
            </div>
            
            <form id="reportForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select id="reportType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="members">Members Report</option>
                        <option value="trainers">Trainers Report</option>
                        <option value="equipment">Equipment Report</option>
                        <option value="sessions">Sessions Report</option>
                        <option value="overview">Full Overview</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">From</label>
                            <input type="date" id="startDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">To</label>
                            <input type="date" id="endDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Include</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="includeCharts" class="rounded text-blue-600 focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">Charts and Graphs</span>
                        </label>
                        <label class="inline-flex items-center block">
                            <input type="checkbox" id="includeDetails" class="rounded text-blue-600 focus:ring-blue-500" checked>
                            <span class="ml-2 text-sm text-gray-700">Detailed Data Tables</span>
                        </label>
                    </div>
                </div>
                
                <div class="pt-4 flex justify-between">
                    <button type="button" id="generatePdfBtn" class="flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                    </button>
                    <button type="button" id="generateExcelBtn" class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-file-excel mr-2"></i> Export as Excel
                    </button>
                </div>
            </form>
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
        // Pass PHP data to JS safely
        const registrationData = <?php echo json_encode($registration_data); ?>;
        const equipmentStatus = <?php echo json_encode($equipment_status); ?>;
        const workoutPlanData = <?php echo json_encode($workout_plan_data); ?>;
        const bodyTypeData = <?php echo json_encode($body_type_data); ?>;
        const recentUsers = <?php echo json_encode($recent_users); ?>;
        const equipments = <?php echo json_encode($equipments); ?>;
        const dashboardCounts = <?php echo json_encode($counts); ?>;

        // Format data for charts
        const formattedRegistrationData = registrationData.map(item => ({
            month: item.month.split('-')[1] + '/' + item.month.split('-')[0].substring(2),
            registrations: parseInt(item.registrations)
        }));

        const formattedEquipmentStatus = [];
        let totalEquipment = 0;
        
        equipmentStatus.forEach(status => {
            totalEquipment += parseInt(status.count);
            formattedEquipmentStatus.push({
                name: status.status === 'out_of_service' ? 'Out of Service' : 
                      status.status.charAt(0).toUpperCase() + status.status.slice(1),
                count: parseInt(status.count)
            });
        });

        const formattedWorkoutPlanData = workoutPlanData.map(plan => ({
            name: plan.plan_name,
            count: parseInt(plan.member_count)
        }));

        const formattedBodyTypeData = [];
        let totalBodyTypes = 0;
        
        bodyTypeData.forEach(type => {
            totalBodyTypes += parseInt(type.count);
            formattedBodyTypeData.push({
                name: type.body_type ? (type.body_type.charAt(0).toUpperCase() + type.body_type.slice(1)) : 'Unknown',
                count: parseInt(type.count)
            });
        });

        // Member Registration Growth Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: formattedRegistrationData.map(r => r.month),
                datasets: [{
                    label: 'New Registrations',
                    data: formattedRegistrationData.map(r => r.registrations),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Registrations'
                        },
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Equipment Status Pie Chart
        const equipmentCtx = document.getElementById('equipmentChart').getContext('2d');
        new Chart(equipmentCtx, {
            type: 'doughnut',
            data: {
                labels: formattedEquipmentStatus.map(e => e.name),
                datasets: [{
                    data: formattedEquipmentStatus.map(e => e.count),
                    backgroundColor: [
                        'rgb(34, 197, 94)', // available
                        'rgb(234, 179, 8)', // maintenance
                        'rgb(239, 68, 68)'  // out of order
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = Math.round((value / totalEquipment) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Workout Plan Popularity Chart
        const workoutPlanCtx = document.getElementById('workoutPlanChart').getContext('2d');
        new Chart(workoutPlanCtx, {
            type: 'bar',
            data: {
                labels: formattedWorkoutPlanData.map(p => p.name),
                datasets: [{
                    label: 'Member Count',
                    data: formattedWorkoutPlanData.map(p => p.count),
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgb(79, 70, 229)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Body Type Distribution Chart
        const bodyTypeCtx = document.getElementById('bodyTypeChart').getContext('2d');
        new Chart(bodyTypeCtx, {
            type: 'pie',
            data: {
                labels: formattedBodyTypeData.map(t => t.name),
                datasets: [{
                    data: formattedBodyTypeData.map(t => t.count),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)', // blue
                        'rgba(16, 185, 129, 0.7)', // green
                        'rgba(245, 158, 11, 0.7)'  // yellow
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = Math.round((value / totalBodyTypes) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Report Dropdown Toggle
        document.getElementById('reportDropdown').addEventListener('click', function() {
            document.getElementById('reportMenu').classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('reportMenu');
            const button = document.getElementById('reportDropdown');
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Report Modal
        document.getElementById('generatePdfReport').addEventListener('click', function() {
            document.getElementById('reportMenu').classList.add('hidden');
            document.getElementById('reportModal').classList.remove('hidden');
        });

        document.getElementById('generateExcelReport').addEventListener('click', function() {
            document.getElementById('reportMenu').classList.add('hidden');
            document.getElementById('reportModal').classList.remove('hidden');
        });

        document.getElementById('closeReportModal').addEventListener('click', function() {
            document.getElementById('reportModal').classList.add('hidden');
        });

        // Set default dates for report
        const today = new Date();
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setMonth(today.getMonth() - 6);
        
        document.getElementById('startDate').valueAsDate = sixMonthsAgo;
        document.getElementById('endDate').valueAsDate = today;

        // Generate PDF Report
        document.getElementById('generatePdfBtn').addEventListener('click', async function() {
            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const includeCharts = document.getElementById('includeCharts').checked;
            const includeDetails = document.getElementById('includeDetails').checked;
            
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
            this.disabled = true;
            
            try {
                // Initialize jsPDF
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Add title page
                doc.setFontSize(24);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(59, 130, 246); // Blue color
                doc.text('ELITEFIT GYM', 105, 50, { align: 'center' });
                
                doc.setFontSize(20);
                doc.setTextColor(0, 0, 0);
                doc.text(reportType.toUpperCase() + ' REPORT', 105, 70, { align: 'center' });
                
                doc.setFontSize(12);
                doc.setFont('helvetica', 'normal');
                doc.text('Generated on: ' + new Date().toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }), 105, 85, { align: 'center' });
                
                doc.text('Date Range: ' + new Date(startDate).toLocaleDateString('en-US') + 
                         ' to ' + new Date(endDate).toLocaleDateString('en-US'), 
                         105, 95, { align: 'center' });
                
                // Add logo or image
                // In a real implementation, you would add your gym logo here
                doc.setDrawColor(59, 130, 246);
                doc.setLineWidth(1);
                doc.rect(65, 110, 80, 40);
                doc.setFontSize(16);
                doc.setFont('helvetica', 'bold');
                doc.text('ELITEFIT LOGO', 105, 130, { align: 'center' });
                
                // Add summary page
                doc.addPage();
                doc.setFontSize(18);
                doc.setFont('helvetica', 'bold');
                doc.text('Summary', 14, 20);
                
                doc.setFontSize(12);
                doc.setFont('helvetica', 'normal');
                
                // Add summary data based on report type
                if (reportType === 'members' || reportType === 'overview') {
                    doc.text('Total Members: ' + dashboardCounts.member_count, 14, 35);
                    doc.text('Recent Registrations: ' + formattedRegistrationData.reduce((sum, item) => sum + item.registrations, 0) + ' (last 6 months)', 14, 45);
                }
                
                if (reportType === 'trainers' || reportType === 'overview') {
                    doc.text('Total Trainers: ' + dashboardCounts.trainer_count, 14, reportType === 'overview' ? 55 : 35);
                }
                
                if (reportType === 'equipment' || reportType === 'overview') {
                    const yPos = reportType === 'overview' ? 65 : 35;
                    doc.text('Total Equipment: ' + dashboardCounts.equipment_count, 14, yPos);
                    doc.text('Equipment Status:', 14, yPos + 10);
                    
                    let statusYPos = yPos + 20;
                    formattedEquipmentStatus.forEach(status => {
                        doc.text('- ' + status.name + ': ' + status.count, 20, statusYPos);
                        statusYPos += 10;
                    });
                }
                
                if (reportType === 'sessions' || reportType === 'overview') {
                    const yPos = reportType === 'overview' ? 95 : 35;
                    doc.text('Today\'s Sessions: ' + dashboardCounts.today_sessions, 14, yPos);
                    doc.text('Completed Sessions: ' + dashboardCounts.completed_sessions, 14, yPos + 10);
                }
                
                // Add charts if requested
                if (includeCharts) {
                    doc.addPage();
                    doc.setFontSize(18);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Charts & Graphs', 14, 20);
                    
                    // Capture charts as images
                    if (reportType === 'members' || reportType === 'overview') {
                        const registrationCanvas = document.getElementById('registrationChart');
                        const registrationImg = registrationCanvas.toDataURL('image/png');
                        doc.text('Member Registration Growth', 14, 35);
                        doc.addImage(registrationImg, 'PNG', 14, 40, 180, 90);
                        
                        if (reportType === 'members') {
                            const bodyTypeCanvas = document.getElementById('bodyTypeChart');
                            const bodyTypeImg = bodyTypeCanvas.toDataURL('image/png');
                            doc.text('Member Body Types', 14, 145);
                            doc.addImage(bodyTypeImg, 'PNG', 14, 150, 180, 90);
                        }
                    }
                    
                    if ((reportType === 'equipment' || reportType === 'overview') && reportType !== 'members') {
                        const yPos = reportType === 'overview' ? 145 : 35;
                        const equipmentCanvas = document.getElementById('equipmentChart');
                        const equipmentImg = equipmentCanvas.toDataURL('image/png');
                        doc.text('Equipment Status', 14, yPos);
                        doc.addImage(equipmentImg, 'PNG', 14, yPos + 5, 180, 90);
                    }
                    
                    if (reportType === 'overview') {
                        doc.addPage();
                        const workoutCanvas = document.getElementById('workoutPlanChart');
                        const workoutImg = workoutCanvas.toDataURL('image/png');
                        doc.text('Workout Plan Popularity', 14, 20);
                        doc.addImage(workoutImg, 'PNG', 14, 25, 180, 90);
                        
                        const bodyTypeCanvas = document.getElementById('bodyTypeChart');
                        const bodyTypeImg = bodyTypeCanvas.toDataURL('image/png');
                        doc.text('Member Body Types', 14, 130);
                        doc.addImage(bodyTypeImg, 'PNG', 14, 135, 180, 90);
                    }
                }
                
                // Add detailed data if requested
                if (includeDetails) {
                    doc.addPage();
                    doc.setFontSize(18);
                    doc.setFont('helvetica', 'bold');
                    doc.text('Detailed Data', 14, 20);
                    
                    if (reportType === 'members' || reportType === 'overview') {
                        doc.setFontSize(14);
                        doc.text('Recent Member Registrations', 14, 35);
                        
                        // Add table header
                        doc.setFontSize(10);
                        doc.setFont('helvetica', 'bold');
                        doc.text('Name', 14, 45);
                        doc.text('Email', 70, 45);
                        doc.text('Role', 140, 45);
                        doc.text('Date', 170, 45);
                        
                        // Add table data
                        doc.setFont('helvetica', 'normal');
                        let yPos = 55;
                        recentUsers.forEach((user, index) => {
                            if (user.role === 'member' || reportType === 'overview') {
                                doc.text(user.first_name + ' ' + user.last_name, 14, yPos);
                                doc.text(user.email, 70, yPos);
                                doc.text(user.role, 140, yPos);
                                doc.text(new Date(user.date_registered).toLocaleDateString('en-US'), 170, yPos);
                                yPos += 10;
                                
                                // Add a line between rows
                                if (index < recentUsers.length - 1) {
                                    doc.setDrawColor(200, 200, 200);
                                    doc.line(14, yPos - 5, 195, yPos - 5);
                                }
                            }
                        });
                    }
                    
                    if (reportType === 'equipment' || reportType === 'overview') {
                        const yStart = reportType === 'overview' ? 120 : 35;
                        
                        doc.setFontSize(14);
                        doc.setFont('helvetica', 'bold');
                        doc.text('Equipment Status', 14, yStart);
                        
                        // Add table header
                        doc.setFontSize(10);
                        doc.text('Name', 14, yStart + 10);
                        doc.text('Status', 80, yStart + 10);
                        doc.text('Last Maintenance', 120, yStart + 10);
                        doc.text('Next Maintenance', 170, yStart + 10);
                        
                        // Add table data
                        doc.setFont('helvetica', 'normal');
                        let yPos = yStart + 20;
                        equipments.forEach((equipment, index) => {
                            doc.text(equipment.name, 14, yPos);
                            doc.text(equipment.status.replace('_', ' '), 80, yPos);
                            doc.text(equipment.last_maintenance_date || 'N/A', 120, yPos);
                            doc.text(equipment.next_maintenance_date || 'Not scheduled', 170, yPos);
                            yPos += 10;
                            
                            // Add a line between rows
                            if (index < equipments.length - 1) {
                                doc.setDrawColor(200, 200, 200);
                                doc.line(14, yPos - 5, 195, yPos - 5);
                            }
                            
                            // Add a new page if we're running out of space
                            if (yPos > 270 && index < equipments.length - 1) {
                                doc.addPage();
                                yPos = 20;
                            }
                        });
                    }
                }
                
                // Save the PDF
                doc.save('elitefit-' + reportType + '-report.pdf');
                
                // Hide modal after generating
                document.getElementById('reportModal').classList.add('hidden');
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF report. Please try again.');
            } finally {
                // Reset button state
                this.innerHTML = '<i class="fas fa-file-pdf mr-2"></i> Export as PDF';
                this.disabled = false;
            }
        });

        // Generate Excel Report
        document.getElementById('generateExcelBtn').addEventListener('click', function() {
            const reportType = document.getElementById('reportType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
            this.disabled = true;
            
            try {
                // Create a new workbook
                const wb = XLSX.utils.book_new();
                wb.Props = {
                    Title: "EliteFit " + reportType.charAt(0).toUpperCase() + reportType.slice(1) + " Report",
                    Subject: "Gym Management Report",
                    Author: "EliteFit Admin",
                    CreatedDate: new Date()
                };
                
                // Add Summary worksheet
                wb.SheetNames.push("Summary");
                
                const summaryData = [
                    ["ELITEFIT GYM " + reportType.toUpperCase() + " REPORT"],
                    ["Generated on: " + new Date().toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })],
                    ["Date Range: " + new Date(startDate).toLocaleDateString('en-US') + 
                     " to " + new Date(endDate).toLocaleDateString('en-US')],
                    [""],
                    ["SUMMARY STATISTICS"]
                ];
                
                // Add summary data based on report type
                if (reportType === 'members' || reportType === 'overview') {
                    summaryData.push(["Total Members", dashboardCounts.member_count]);
                    summaryData.push(["Recent Registrations (last 6 months)", 
                                     formattedRegistrationData.reduce((sum, item) => sum + item.registrations, 0)]);
                }
                
                if (reportType === 'trainers' || reportType === 'overview') {
                    summaryData.push(["Total Trainers", dashboardCounts.trainer_count]);
                }
                
                if (reportType === 'equipment' || reportType === 'overview') {
                    summaryData.push(["Total Equipment", dashboardCounts.equipment_count]);
                    summaryData.push(["Equipment Status Breakdown:"]);
                    
                    formattedEquipmentStatus.forEach(status => {
                        summaryData.push(["  " + status.name, status.count]);
                    });
                }
                
                if (reportType === 'sessions' || reportType === 'overview') {
                    summaryData.push(["Today's Sessions", dashboardCounts.today_sessions]);
                    summaryData.push(["Completed Sessions", dashboardCounts.completed_sessions]);
                }
                
                const summaryWs = XLSX.utils.aoa_to_sheet(summaryData);
                wb.Sheets["Summary"] = summaryWs;
                
                // Add data worksheets based on report type
                if (reportType === 'members' || reportType === 'overview') {
                    // Add Members worksheet
                    wb.SheetNames.push("Members");
                    
                    const membersHeader = ["Name", "Email", "Role", "Registration Date"];
                    const membersData = recentUsers.map(user => [
                        user.first_name + " " + user.last_name,
                        user.email,
                        user.role,
                        new Date(user.date_registered).toLocaleDateString('en-US')
                    ]);
                    
                    const membersWs = XLSX.utils.aoa_to_sheet([membersHeader, ...membersData]);
                    wb.Sheets["Members"] = membersWs;
                    
                    // Add Registration Trend worksheet
                    wb.SheetNames.push("Registration Trend");
                    
                    const trendHeader = ["Month", "Registrations"];
                    const trendData = formattedRegistrationData.map(item => [
                        item.month,
                        item.registrations
                    ]);
                    
                    const trendWs = XLSX.utils.aoa_to_sheet([trendHeader, ...trendData]);
                    wb.Sheets["Registration Trend"] = trendWs;
                    
                    // Add Body Types worksheet
                    wb.SheetNames.push("Body Types");
                    
                    const bodyTypeHeader = ["Body Type", "Count", "Percentage"];
                    const bodyTypeData = formattedBodyTypeData.map(item => [
                        item.name,
                        item.count,
                        Math.round((item.count / totalBodyTypes) * 100) + "%"
                    ]);
                    
                    const bodyTypeWs = XLSX.utils.aoa_to_sheet([bodyTypeHeader, ...bodyTypeData]);
                    wb.Sheets["Body Types"] = bodyTypeWs;
                }
                
                if (reportType === 'equipment' || reportType === 'overview') {
                    // Add Equipment worksheet
                    wb.SheetNames.push("Equipment");
                    
                    const equipmentHeader = ["Name", "Status", "Last Maintenance", "Next Maintenance"];
                    const equipmentData = equipments.map(item => [
                        item.name,
                        item.status.replace('_', ' '),
                        item.last_maintenance_date || "N/A",
                        item.next_maintenance_date || "Not scheduled"
                    ]);
                    
                    const equipmentWs = XLSX.utils.aoa_to_sheet([equipmentHeader, ...equipmentData]);
                    wb.Sheets["Equipment"] = equipmentWs;
                    
                    // Add Equipment Status worksheet
                    wb.SheetNames.push("Equipment Status");
                    
                    const statusHeader = ["Status", "Count", "Percentage"];
                    const statusData = formattedEquipmentStatus.map(item => [
                        item.name,
                        item.count,
                        Math.round((item.count / totalEquipment) * 100) + "%"
                    ]);
                    
                    const statusWs = XLSX.utils.aoa_to_sheet([statusHeader, ...statusData]);
                    wb.Sheets["Equipment Status"] = statusWs;
                }
                
                if (reportType === 'overview') {
                    // Add Workout Plans worksheet
                    wb.SheetNames.push("Workout Plans");
                    
                    const planHeader = ["Plan Name", "Member Count"];
                    const planData = formattedWorkoutPlanData.map(item => [
                        item.name,
                        item.count
                    ]);
                    
                    const planWs = XLSX.utils.aoa_to_sheet([planHeader, ...planData]);
                    wb.Sheets["Workout Plans"] = planWs;
                }
                
                // Generate Excel file
                const wbout = XLSX.write(wb, {bookType:'xlsx', type:'binary'});
                
                // Convert to blob and download
                function s2ab(s) {
                    const buf = new ArrayBuffer(s.length);
                    const view = new Uint8Array(buf);
                    for (let i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                    return buf;
                }
                
                const blob = new Blob([s2ab(wbout)], {type:"application/octet-stream"});
                const url = URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.href = url;
                a.download = 'elitefit-' + reportType + '-report.xlsx';
                document.body.appendChild(a);
                a.click();
                
                setTimeout(function() {
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }, 0);
                
                // Hide modal after generating
                document.getElementById('reportModal').classList.add('hidden');
            } catch (error) {
                console.error('Error generating Excel:', error);
                alert('Error generating Excel report. Please try again.');
            } finally {
                // Reset button state
                this.innerHTML = '<i class="fas fa-file-excel mr-2"></i> Export as Excel';
                this.disabled = false;
            }
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
