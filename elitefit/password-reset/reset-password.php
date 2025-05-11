<?php
session_start();
// Use correct path for config.php
include_once __DIR__ . '/../config.php';

// If reset_email is not set, redirect to login (prevents showing form or popup for wrong session)
if (!isset($_SESSION['reset_email'])) {
    $_SESSION['error'] = "Password reset session expired.";
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .success-popup {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.25);
        animation: fadeInBg 0.3s;
      }
      .success-popup-content {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        padding: 2.5rem 2rem;
        max-width: 350px;
        width: 100%;
        text-align: center;
        animation: popIn 0.35s cubic-bezier(.39,.575,.565,1) both;
      }
      .success-popup .icon {
        color: #22c55e;
        font-size: 3rem;
        margin-bottom: 1rem;
        animation: bounce 1s infinite alternate;
      }
      @keyframes popIn {
        0% { opacity: 0; transform: scale(0.85) translateY(40px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
      }
      @keyframes fadeInBg {
        0% { background: rgba(0,0,0,0); }
        100% { background: rgba(0,0,0,0.25); }
      }
      @keyframes bounce {
        0% { transform: translateY(0); }
        100% { transform: translateY(-10px); }
      }
      .success-popup .btn {
        margin-top: 1.5rem;
        padding: 0.7rem 1.5rem;
        border-radius: 0.5rem;
        background: #22c55e;
        color: #fff;
        font-weight: bold;
        border: none;
        transition: background 0.2s;
        font-size: 1rem;
      }
      .success-popup .btn:hover {
        background: #16a34a;
      }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['success']) && !isset($_SESSION['reset_email'])): ?>
      <div class="success-popup" id="successPopup">
        <div class="success-popup-content">
          <div class="icon"><i class="fas fa-check-circle"></i></div>
          <h4 class="mb-2 text-2xl font-bold text-green-700">Success!</h4>
          <div class="mb-2 text-gray-700"><?= $_SESSION['success']; ?></div>
          <button class="btn" onclick="window.location.href='../index.php'">Continue to Login</button>
        </div>
      </div>
      <script>
        setTimeout(function() {
          var popup = document.getElementById('successPopup');
          if (popup) popup.style.display = 'none';
        }, 7000);
      </script>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Create New Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="process-reset.php">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email']) ?>">
                            <input type="hidden" name="role" value="<?= htmlspecialchars($_SESSION['reset_role']) ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>