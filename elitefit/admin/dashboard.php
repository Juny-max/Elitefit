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
    (SELECT COUNT(*) FROM users WHERE role = 'member') as member_count,
    (SELECT COUNT(*) FROM users WHERE role = 'trainer') as trainer_count,
    (SELECT COUNT(*) FROM users WHERE role = 'equipment_manager') as manager_count,
    (SELECT COUNT(*) FROM equipment) as equipment_count,
    (SELECT COUNT(*) FROM booked_sessions WHERE DATE(session_date) = CURDATE()) as today_sessions");
$stmt->execute();
$result = $stmt->get_result();
$counts = $result->fetch_assoc();
$stmt->close();

// Get recent registrations
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, role, date_registered 
                       FROM users 
                       ORDER BY date_registered DESC 
                       LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$recent_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get equipment status
$stmt = $conn->prepare("SELECT status, COUNT(*) as count 
                       FROM equipment 
                       GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
$equipment_status = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get member registration growth data for chart
$stmt = $conn->prepare("SELECT DATE(date_registered) as day, COUNT(*) as registrations FROM users WHERE role = 'member' GROUP BY day ORDER BY day DESC LIMIT 30");
$stmt->execute();
$result = $stmt->get_result();
$registration_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all equipment data
$stmt = $conn->prepare("SELECT name, status, last_maintenance_date, next_maintenance_date FROM equipment");
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-4 flex-shrink-0 h-screen fixed top-0 left-0 z-30 flex flex-col">
            <div class="px-4 mt-4">
                <h1 class="text-2xl font-bold mb-8">EliteFit Admin</h1>
            </div>
            <nav class="mt-8 flex-1">
                <a href="#" class="block px-4 py-2 bg-gray-900 hover:bg-gray-700"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="members.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-users mr-2"></i> Members</a>
                <a href="trainers.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-dumbbell mr-2"></i> Trainers</a>
                <a href="equipment.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-cogs mr-2"></i> Equipment</a>
                <a href="settings.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-cog mr-2"></i> Settings</a>
                <a href="archive.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-archive mr-2"></i> Archive</a>
            </nav>
            <div class="mt-auto mb-4 px-4">
                <button id="logoutBtn" class="w-full text-left px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded flex items-center justify-start font-semibold shadow-lg"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto ml-64">
            <!-- Top Bar -->
            <div class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Dashboard Overview</h2>
                <div class="flex items-center">
                    <button id="generateReport" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 mr-4">
                        <i class="fas fa-download mr-2"></i> Generate Report
                    </button>
                    <div class="relative">
                        <!-- Admin Icon instead of profile picture -->
                        <span class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-600">
                            <i class="fas fa-user-shield text-xl"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6" id="dashboardContent">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Members</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['member_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-500">
                                <i class="fas fa-dumbbell text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Total Trainers</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['trainer_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                                <i class="fas fa-cogs text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Equipment Count</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['equipment_count']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                                <i class="fas fa-calendar-check text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Today's Sessions</h3>
                                <p class="text-2xl font-semibold"><?php echo $counts['today_sessions']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Member Registration Growth Chart -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Member Registration Growth</h3>
                        <canvas id="registrationChart"></canvas>
                    </div>

                    <!-- Equipment Status -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Equipment Status</h3>
                        <canvas id="equipmentChart"></canvas>
                    </div>
                </div>

                <!-- Recent Registrations -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Registrations</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Registered</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-4">
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
                                    <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($user['date_registered'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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
        // Pass PHP data to JS safely
        const registrationData = <?php echo json_encode(array_reverse($registration_data)); ?>;
        const equipmentStatus = <?php echo json_encode($equipment_status); ?>;
        const recentUsers = <?php echo json_encode($recent_users); ?>;
        const equipments = <?php echo json_encode($equipments); ?>;
        const dashboardCounts = <?php echo json_encode($counts); ?>;

        // Member Registration Growth Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(registrationCtx, {
            type: 'bar',
            data: {
                labels: registrationData.map(r => r.day),
                datasets: [{
                    label: 'New Registrations',
                    data: registrationData.map(r => r.registrations),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.4)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Day'
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
                labels: equipmentStatus.map(e => e.status),
                datasets: [{
                    data: equipmentStatus.map(e => e.count),
                    backgroundColor: [
                        'rgb(34, 197, 94)', // available
                        'rgb(234, 179, 8)', // maintenance
                        'rgb(239, 68, 68)'  // out of order
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
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

        // Enhanced PDF Generation
        document.getElementById('generateReport').addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // COVER PAGE
            doc.setFontSize(36);
            doc.setFont('helvetica', 'bold');
            doc.text('ELITEFIT GYM REPORT', 105, 120, { align: 'center' });
            doc.setFontSize(18);
            doc.setFont('helvetica', 'normal');
            doc.text('Generated on: ' + new Date().toLocaleString(), 105, 140, { align: 'center' });

            // DASHBOARD IMAGE PAGE
            doc.addPage();
            const content = document.getElementById('dashboardContent');
            const canvas = await html2canvas(content);
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 210;
            const imgHeight = canvas.height * imgWidth / canvas.width;
            doc.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

            // SUMMARY PAGE
            doc.addPage();
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text('EliteFit System Summary', 10, 20);
            doc.setFontSize(12);
            doc.setFont('helvetica', 'normal');
            doc.text('Total Members: ' + dashboardCounts.member_count, 10, 35);
            doc.text('Total Trainers: ' + dashboardCounts.trainer_count, 10, 45);
            doc.text('Equipment Managers: ' + dashboardCounts.manager_count, 10, 55);
            doc.text('Total Equipment: ' + dashboardCounts.equipment_count, 10, 65);

            // RECENT REGISTRATIONS TABLE (with borders)
            doc.addPage();
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text('Recent Registrations', 10, 20);
            doc.setFontSize(10);
            let y = 30;
            let x = 10;
            const rowHeight = 8;
            // Table headers
            doc.setDrawColor(0);
            doc.setLineWidth(0.2);
            doc.rect(x, y, 190, rowHeight, 'S');
            doc.text('Name', x + 2, y + 6);
            doc.text('Email', x + 52, y + 6);
            doc.text('Role', x + 122, y + 6);
            doc.text('Date Registered', x + 152, y + 6);
            y += rowHeight;
            recentUsers.forEach(user => {
                doc.rect(x, y, 190, rowHeight, 'S');
                doc.text((user.first_name + ' ' + user.last_name), x + 2, y + 6);
                doc.text(user.email, x + 52, y + 6);
                doc.text(user.role.charAt(0).toUpperCase() + user.role.slice(1), x + 122, y + 6);
                doc.text(user.date_registered, x + 152, y + 6);
                y += rowHeight;
                if (y > 270) { doc.addPage(); y = 20; }
            });

            // EQUIPMENT STATUS TABLE (with borders)
            doc.addPage();
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text('Equipment Status', 10, 20);
            doc.setFontSize(10);
            y = 30;
            doc.rect(x, y, 60, rowHeight, 'S');
            doc.rect(x + 60, y, 60, rowHeight, 'S');
            doc.text('Status', x + 2, y + 6);
            doc.text('Count', x + 62, y + 6);
            y += rowHeight;
            equipmentStatus.forEach(eq => {
                doc.rect(x, y, 60, rowHeight, 'S');
                doc.rect(x + 60, y, 60, rowHeight, 'S');
                doc.text(eq.status.charAt(0).toUpperCase() + eq.status.slice(1), x + 2, y + 6);
                doc.text(eq.count.toString(), x + 62, y + 6);
                y += rowHeight;
                if (y > 270) { doc.addPage(); y = 20; }
            });

            // ALL EQUIPMENT TABLE (with borders)
            doc.addPage();
            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text('All Equipment', 10, 20);
            doc.setFontSize(10);
            y = 30;
            // Table headers
            doc.rect(x, y, 60, rowHeight, 'S');
            doc.rect(x + 60, y, 40, rowHeight, 'S');
            doc.rect(x + 100, y, 40, rowHeight, 'S');
            doc.rect(x + 140, y, 40, rowHeight, 'S');
            doc.text('Name', x + 2, y + 6);
            doc.text('Status', x + 62, y + 6);
            doc.text('Last Maint.', x + 102, y + 6);
            doc.text('Next Maint.', x + 142, y + 6);
            y += rowHeight;
            equipments.forEach(equipment => {
                doc.rect(x, y, 60, rowHeight, 'S');
                doc.rect(x + 60, y, 40, rowHeight, 'S');
                doc.rect(x + 100, y, 40, rowHeight, 'S');
                doc.rect(x + 140, y, 40, rowHeight, 'S');
                doc.text(equipment.name, x + 2, y + 6);
                doc.text(equipment.status.charAt(0).toUpperCase() + equipment.status.slice(1), x + 62, y + 6);
                doc.text(equipment.last_maintenance_date || '-', x + 102, y + 6);
                doc.text(equipment.next_maintenance_date || '-', x + 142, y + 6);
                y += rowHeight;
                if (y > 270) { doc.addPage(); y = 20; }
            });

            doc.save('elitefit-dashboard-report.pdf');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    </script>
</body>
</html>
