<?php
session_start();
require_once('../config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit(); }

// Example: Save site settings (add your own settings logic as needed)
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Example: Save settings to a file or database
    // file_put_contents('../settings.json', json_encode($_POST));
    $success = true;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EliteFit Admin</title>
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
                <a href="#" class="block px-4 py-2 bg-gray-900 hover:bg-gray-700"><i class="fas fa-cog mr-2"></i> Settings</a>
                <a href="archive.php" class="block px-4 py-2 hover:bg-gray-700"><i class="fas fa-archive mr-2"></i> Archive</a>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 overflow-x-hidden overflow-y-auto p-8 ml-64">
            <h2 class="text-2xl font-semibold mb-6">Admin Settings</h2>
            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">Settings saved successfully!</div>
            <?php endif; ?>
            <form method="POST" class="bg-white rounded-lg shadow-md p-6 max-w-xl mx-auto">
                <div class="mb-4">
                    <label class="block mb-2 font-semibold">Site Title</label>
                    <input type="text" name="site_title" class="w-full border rounded px-3 py-2" placeholder="EliteFit" value="EliteFit">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 font-semibold">Admin Email</label>
                    <input type="email" name="admin_email" class="w-full border rounded px-3 py-2" placeholder="admin@elitefit.com" value="admin@elitefit.com">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 font-semibold">Dashboard Logo URL</label>
                    <input type="text" name="logo_url" class="w-full border rounded px-3 py-2" placeholder="https://...">
                </div>
                <button type="submit" name="save_settings" class="bg-blue-500 text-white rounded px-4 py-2">Save Settings</button>
            </form>
        </div>
    </div>
</body>
</html>
