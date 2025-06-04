<?php
session_start();
require_once('../config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit(); }

// Default settings
$settings = [
    'site_title' => 'EliteFit',
    'admin_email' => 'admin@elitefit.com',
    'logo_url' => '',
    'theme_color' => 'blue',
    'enable_notifications' => true,
    'maintenance_mode' => false,
    'registration_enabled' => true,
    'session_timeout' => 30,
    'backup_frequency' => 'weekly',
    'backup_retention' => 30,
    'currency' => 'USD'
];

// Load settings from database or file if available
// For this example, we'll use the default settings

// Handle save settings
$success = false;
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Safely get and validate form data with default values
    if (isset($_POST['site_title'])) $settings['site_title'] = trim($_POST['site_title']);
    if (isset($_POST['admin_email'])) $settings['admin_email'] = trim($_POST['admin_email']);
    if (isset($_POST['logo_url'])) $settings['logo_url'] = trim($_POST['logo_url']);
    if (isset($_POST['theme_color'])) $settings['theme_color'] = $_POST['theme_color'];
    if (isset($_POST['enable_notifications'])) $settings['enable_notifications'] = true;
    if (isset($_POST['maintenance_mode'])) $settings['maintenance_mode'] = true;
    if (isset($_POST['registration_enabled'])) $settings['registration_enabled'] = true;
    if (isset($_POST['session_timeout'])) $settings['session_timeout'] = intval($_POST['session_timeout']);
    if (isset($_POST['backup_frequency'])) $settings['backup_frequency'] = $_POST['backup_frequency'];
    if (isset($_POST['currency'])) $settings['currency'] = $_POST['currency'];
    
    // Example: Save settings to a file or database
    // file_put_contents('../settings.json', json_encode($settings));
    
    $success = true;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // In a real application, you would verify the current password and update it
    if ($new_password === $confirm_password) {
        // Example: Update password in database
        // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        // $stmt->bind_param('si', $hashed_password, $_SESSION['user_id']);
        // $stmt->execute();
        // $stmt->close();
        
        $success = true;
    } else {
        $error = "New passwords do not match";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EliteFit Admin</title>
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
                <a href="#" class="flex items-center px-4 py-3 bg-gray-900 hover:bg-gray-700 transition-colors">
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
                <h2 class="text-xl font-semibold">System Settings</h2>
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
                <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                    <div class="flex items-center">
                        <div class="py-1">
                            <i class="fas fa-check-circle mr-2"></i>
                        </div>
                        <div>
                            <p class="font-bold">Success</p>
                            <p class="text-sm">Your changes have been saved successfully.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                    <div class="flex items-center">
                        <div class="py-1">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                        </div>
                        <div>
                            <p class="font-bold">Error</p>
                            <p class="text-sm"><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button id="generalTab" class="tab-btn py-4 px-6 border-b-2 border-blue-500 font-medium text-sm text-blue-600 focus:outline-none">
                                General
                            </button>
                            <button id="securityTab" class="tab-btn py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Security
                            </button>
                            <button id="notificationsTab" class="tab-btn py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Notifications
                            </button>
                            <button id="backupTab" class="tab-btn py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none">
                                Backup & Restore
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- General Settings -->
                <div id="generalSection" class="tab-content">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">General Settings</h3>
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Title</label>
                                    <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                                    <input type="email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                                    <input type="text" name="logo_url" value="<?php echo htmlspecialchars($settings['logo_url']); ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Theme Color</label>
                                    <select name="theme_color" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="blue" <?php if ($settings['theme_color'] === 'blue') echo 'selected'; ?>>Blue</option>
                                        <option value="green" <?php if ($settings['theme_color'] === 'green') echo 'selected'; ?>>Green</option>
                                        <option value="purple" <?php if ($settings['theme_color'] === 'purple') echo 'selected'; ?>>Purple</option>
                                        <option value="red" <?php if ($settings['theme_color'] === 'red') echo 'selected'; ?>>Red</option>
                                        <option value="orange" <?php if ($settings['theme_color'] === 'orange') echo 'selected'; ?>>Orange</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                    <select name="currency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="USD" <?php if ($settings['currency'] === 'USD') echo 'selected'; ?>>USD ($)</option>
                                        <option value="EUR" <?php if ($settings['currency'] === 'EUR') echo 'selected'; ?>>EUR (€)</option>
                                        <option value="GBP" <?php if ($settings['currency'] === 'GBP') echo 'selected'; ?>>GBP (£)</option>
                                        <option value="JPY" <?php if ($settings['currency'] === 'JPY') echo 'selected'; ?>>JPY (¥)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" id="registration_enabled" name="registration_enabled" <?php if ($settings['registration_enabled']) echo 'checked'; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="registration_enabled" class="ml-2 block text-sm text-gray-900">Enable Member Registration</label>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="save_settings" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Settings -->
                <div id="securitySection" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Security Settings</h3>
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" value="<?php echo htmlspecialchars($settings['session_timeout']); ?>" min="5" max="120" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Maintenance Mode</label>
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php if ($settings['maintenance_mode']) echo 'checked'; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">Enable Maintenance Mode</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="text-md font-medium mb-4">Change Admin Password</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                        <input type="password" name="current_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <input type="password" name="new_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="change_password" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Update Security Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div id="notificationsSection" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Notification Settings</h3>
                        <form method="POST" class="space-y-6">
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="enable_notifications" name="enable_notifications" <?php if ($settings['enable_notifications']) echo 'checked'; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="enable_notifications" class="ml-2 block text-sm text-gray-900">Enable Email Notifications</label>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-md font-medium mb-2">Notification Events</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="notify_new_member" name="notify_new_member" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="notify_new_member" class="ml-2 block text-sm text-gray-900">New Member Registration</label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" id="notify_equipment_maintenance" name="notify_equipment_maintenance" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="notify_equipment_maintenance" class="ml-2 block text-sm text-gray-900">Equipment Maintenance Due</label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" id="notify_payment" name="notify_payment" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="notify_payment" class="ml-2 block text-sm text-gray-900">Payment Received</label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" id="notify_session_booking" name="notify_session_booking" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="notify_session_booking" class="ml-2 block text-sm text-gray-900">New Session Booking</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="save_settings" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Save Notification Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Backup & Restore Settings -->
                <div id="backupSection" class="tab-content hidden">
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4">Backup & Restore</h3>
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Automatic Backup Frequency</label>
                                    <select name="backup_frequency" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="daily" <?php if ($settings['backup_frequency'] === 'daily') echo 'selected'; ?>>Daily</option>
                                        <option value="weekly" <?php if ($settings['backup_frequency'] === 'weekly') echo 'selected'; ?>>Weekly</option>
                                        <option value="monthly" <?php if ($settings['backup_frequency'] === 'monthly') echo 'selected'; ?>>Monthly</option>
                                        <option value="never" <?php if ($settings['backup_frequency'] === 'never') echo 'selected'; ?>>Never</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Backup Retention (days)</label>
                                    <input type="number" name="backup_retention" value="30" min="1" max="365" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-6 space-y-4">
                                <div>
                                    <h4 class="text-md font-medium mb-2">Manual Backup</h4>
                                    <p class="text-sm text-gray-600 mb-4">Create a backup of your database and settings.</p>
                                    <button type="button" id="manualBackupBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                        <i class="fas fa-download mr-2"></i> Create Backup Now
                                    </button>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <h4 class="text-md font-medium mb-2">Restore from Backup</h4>
                                    <p class="text-sm text-gray-600 mb-4">Upload a previous backup file to restore your system.</p>
                                    <div class="flex items-center">
                                        <input type="file" id="backupFile" name="backupFile" class="hidden">
                                        <label for="backupFile" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg cursor-pointer transition-colors">
                                            <i class="fas fa-upload mr-2"></i> Select Backup File
                                        </label>
                                        <span id="selectedFile" class="ml-3 text-sm text-gray-600">No file selected</span>
                                    </div>
                                    <button type="button" id="restoreBackupBtn" class="mt-4 bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors flex items-center" disabled>
                                        <i class="fas fa-undo mr-2"></i> Restore from Backup
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="save_settings" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Save Backup Settings</button>
                            </div>
                        </form>
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
        // Tab switching
        document.getElementById('generalTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('generalSection').classList.remove('hidden');
        });

        document.getElementById('securityTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('securitySection').classList.remove('hidden');
        });

        document.getElementById('notificationsTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('notificationsSection').classList.remove('hidden');
        });

        document.getElementById('backupTab').addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('backupSection').classList.remove('hidden');
        });

        // File upload handling
        document.getElementById('backupFile').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file selected';
            document.getElementById('selectedFile').textContent = fileName;
            document.getElementById('restoreBackupBtn').disabled = !this.files[0];
        });

        // Manual backup button
        document.getElementById('manualBackupBtn').addEventListener('click', function() {
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating Backup...';
    this.disabled = true;
    
    // Make an AJAX request to the backup script
    fetch('create-backup.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            // Create a download link for the backup file
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'elitefit_backup_' + new Date().toISOString().slice(0, 10) + '.sql';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            // Update button state
            this.innerHTML = '<i class="fas fa-check mr-2"></i> Backup Created!';
            
            // Reset after 2 seconds
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-download mr-2"></i> Create Backup Now';
                this.disabled = false;
            }, 2000);
        })
        .catch(error => {
            console.error('Error creating backup:', error);
            this.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Backup Failed';
            
            // Reset after 2 seconds
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-download mr-2"></i> Create Backup Now';
                this.disabled = false;
            }, 2000);
        });
});

// Update the restore backup button functionality
document.getElementById('restoreBackupBtn').addEventListener('click', function() {
    // Check if a file is selected
    const fileInput = document.getElementById('backupFile');
    if (!fileInput.files[0]) {
        alert('Please select a backup file first');
        return;
    }
    
    // Confirm restoration
    if (!confirm('WARNING: Restoring from backup will overwrite your current database. This action cannot be undone. Are you sure you want to continue?')) {
        return;
    }
    
    // Create a form data object
    const formData = new FormData();
    formData.append('backupFile', fileInput.files[0]);
    
    // Update button state
    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Restoring...';
    this.disabled = true;
    
    // Submit the form data to the restore script
    fetch('restore-backup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        // Update button state
        this.innerHTML = '<i class="fas fa-check mr-2"></i> Restore Complete!';
        
        // Reload the page after 2 seconds
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Error restoring backup:', error);
        this.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Restore Failed';
        
        // Reset after 2 seconds
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-undo mr-2"></i> Restore from Backup';
            this.disabled = false;
        }, 2000);
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
