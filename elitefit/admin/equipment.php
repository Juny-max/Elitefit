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
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - EliteFit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-4 flex-shrink-0 h-screen fixed top-0 left-0 z-30">
            <div class="px-4 mt-4"><h1 class="text-2xl font-bold mb-8">EliteFit Admin</h1></div>
            <nav class="mt-8">
                <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="members.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-users mr-2"></i> Members</a>
                <a href="trainers.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-dumbbell mr-2"></i> Trainers</a>
                <a href="#" class="block px-4 py-2 bg-gray-900 hover:bg-gray-700"><i class="fas fa-cogs mr-2"></i> Equipment</a>
                <a href="settings.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-cog mr-2"></i> Settings</a>
                <a href="archive.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-archive mr-2"></i> Archive</a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-8 ml-64">
            <h2 class="text-2xl font-semibold mb-6">Equipment Managers & Equipments</h2>
            <!-- Add Manager Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Add Equipment Manager</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="first_name" placeholder="First Name" required class="border rounded px-3 py-2">
                    <input type="text" name="last_name" placeholder="Last Name" required class="border rounded px-3 py-2">
                    <input type="email" name="email" placeholder="Email" required class="border rounded px-3 py-2">
                    <input type="text" name="contact_number" placeholder="Contact Number" required class="border rounded px-3 py-2">
                    <select name="gender" required class="border rounded px-3 py-2">
                        <option value="">Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="date" name="date_of_birth" placeholder="Date of Birth" required class="border rounded px-3 py-2">
                    <input type="password" name="password" placeholder="Password" required class="border rounded px-3 py-2">
                    <button type="submit" name="add_manager" class="bg-yellow-500 text-white rounded px-4 py-2 col-span-1 md:col-span-2">Add Manager</button>
                </form>
            </div>
            <!-- Managers Table -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">All Equipment Managers</h3>
                <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DOB</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($managers as $manager): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo htmlspecialchars(($manager['first_name'] ?? '') . ' ' . ($manager['last_name'] ?? '')); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($manager['email'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($manager['contact_number'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars(ucfirst($manager['gender'] ?? '')); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($manager['date_of_birth'] ?? ''); ?></td>
                            <td class="px-6 py-4">
                                <a href="?archive_manager=<?php echo $manager['user_id']; ?>" data-confirm="Are you sure you want to archive this manager?" class="text-yellow-600 hover:underline mr-2"><i class="fas fa-archive"></i> Archive</a>
                                <a href="?delete_manager=<?php echo $manager['user_id']; ?>" data-confirm="Are you sure you want to delete this manager?" class="text-red-500 hover:underline"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <!-- Equipments Table -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Add Equipment</h3>
                <form method="POST" class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="name" placeholder="Equipment Name" required class="border rounded px-3 py-2">
                    <select name="status" class="border rounded px-3 py-2" required>
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="out_of_service">Out of Service</option>
                    </select>
                    <input type="date" name="last_maintenance_date" class="border rounded px-3 py-2" placeholder="Last Maintenance">
                    <input type="date" name="next_maintenance_date" class="border rounded px-3 py-2" placeholder="Next Maintenance">
                    <button type="submit" name="add_equipment" class="bg-green-500 text-white rounded px-4 py-2 col-span-1 md:col-span-4">Add Equipment</button>
                </form>
                <h3 class="text-lg font-semibold mb-4">All Equipments</h3>
                <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Maint.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Maint.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($equipments as $equipment): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['name'] ?? ''); ?></td>
                            <td class="px-6 py-4">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="equipment_id" value="<?php echo $equipment['equipment_id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                        <option value="available" <?php if (($equipment['status'] ?? '') === 'available') echo 'selected'; ?>>Available</option>
                                        <option value="maintenance" <?php if (($equipment['status'] ?? '') === 'maintenance') echo 'selected'; ?>>Maintenance</option>
                                        <option value="out_of_service" <?php if (($equipment['status'] ?? '') === 'out_of_service') echo 'selected'; ?>>Out of Service</option>
                                    </select>
                                    <input type="hidden" name="change_status" value="1">
                                </form>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['last_maintenance_date'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['next_maintenance_date'] ?? ''); ?></td>
                            <td class="px-6 py-4">
                                <a href="?archive_equipment=<?php echo $equipment['equipment_id']; ?>" data-confirm="Are you sure you want to archive this equipment?" class="text-yellow-600 hover:underline mr-2"><i class="fas fa-archive"></i> Archive</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
      <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md text-center">
        <div class="flex flex-col items-center">
          <div class="bg-yellow-100 rounded-full p-4 mb-4">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl"></i>
          </div>
          <h2 class="text-xl font-bold mb-2">Are you sure?</h2>
          <p class="mb-6" id="confirmModalMsg">Do you want to proceed?</p>
          <div class="flex justify-center gap-4">
            <button id="confirmYes" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded font-semibold flex items-center"><i class="fas fa-check mr-2"></i> Yes</button>
            <button id="confirmNo" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded font-semibold flex items-center"><i class="fas fa-times mr-2"></i> No</button>
          </div>
        </div>
      </div>
    </div>
    <script>
    // Custom confirmation modal logic
    let confirmAction = null;
    function showConfirmModal(message, action) {
      document.getElementById('confirmModalMsg').textContent = message;
      document.getElementById('confirmModal').classList.remove('hidden');
      confirmAction = action;
    }
    document.getElementById('confirmYes').onclick = function() {
      document.getElementById('confirmModal').classList.add('hidden');
      if (typeof confirmAction === 'function') confirmAction();
    };
    document.getElementById('confirmNo').onclick = function() {
      document.getElementById('confirmModal').classList.add('hidden');
      confirmAction = null;
    };
    // Attach modal to archive/delete buttons
    window.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('a[data-confirm]').forEach(function(link) {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const href = this.getAttribute('href');
          const msg = this.getAttribute('data-confirm');
          showConfirmModal(msg, function() { window.location.href = href; });
        });
      });
    });
    </script>
</body>
</html>
