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
        <div class="bg-gray-800 text-white w-64 py-4 flex-shrink-0 h-screen fixed top-0 left-0 z-30">
            <div class="px-4 mt-4"><h1 class="text-2xl font-bold mb-8">EliteFit Admin</h1></div>
            <nav class="mt-8">
                <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                <a href="members.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-users mr-2"></i> Members</a>
                <a href="trainers.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-dumbbell mr-2"></i> Trainers</a>
                <a href="equipment.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-cogs mr-2"></i> Equipment</a>
                <a href="settings.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-cog mr-2"></i> Settings</a>
                <a href="#" class="block px-4 py-2 bg-gray-900 hover:bg-gray-700"><i class="fas fa-archive mr-2"></i> Archive</a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-8 ml-64">
            <h2 class="text-2xl font-semibold mb-6">Archive</h2>
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
            <!-- Archived Users -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Archived Users</h3>
                <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Archived At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($archived_users as $user): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['role'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($user['archived_at'] ?? ''); ?></td>
                            <td class="px-6 py-4">
                                <a href="?restore_user=<?php echo $user['user_id']; ?>" data-confirm="Are you sure you want to restore this user?" class="text-green-600 hover:underline mr-2"><i class="fas fa-undo"></i> Restore</a>
                                <a href="?permadelete_user=<?php echo $user['user_id']; ?>" data-confirm="Are you sure you want to permanently delete this user?" class="text-red-500 hover:underline"><i class="fas fa-trash"></i> Delete Permanently</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <!-- Archived Equipment -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Archived Equipment</h3>
                <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Archived At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($archived_equipment as $equipment): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['name'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['status'] ?? ''); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($equipment['archived_at'] ?? ''); ?></td>
                            <td class="px-6 py-4">
                                <a href="?restore_equipment=<?php echo $equipment['equipment_id']; ?>" data-confirm="Are you sure you want to restore this equipment?" class="text-green-600 hover:underline mr-2"><i class="fas fa-undo"></i> Restore</a>
                                <a href="?permadelete_equipment=<?php echo $equipment['equipment_id']; ?>" data-confirm="Are you sure you want to permanently delete this equipment?" class="text-red-500 hover:underline"><i class="fas fa-trash"></i> Delete Permanently</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
