<?php

declare(strict_types=1);
$page = "Change Password";
require_once __DIR__ . '/includes/bootstrap.php';

if (!$auth->check()) {
    flash('error', 'Please login first.');
    redirect('login.php');
}

$user = $auth->user();
if ($user === null) {
    $auth->logout();
    session_start();
    flash('error', 'Session expired. Please login again.');
    redirect('login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'All password fields are required.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } else {
            $users->updatePassword((int) $user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
            $success = 'Password updated successfully.';
            $user = $auth->user();
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <?=new Header()->header($page);?>
  <body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">
     
     <?=new Sidebar()->sidebar($page);?>


      <main class="flex-1 p-6 lg:p-8">
        <h2 class="text-2xl font-bold text-gray-800">Change Password</h2>
        <p class="text-gray-600 mb-6">Keep your account secure.</p>

        <div class="max-w-3xl bg-white rounded-xl shadow overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2"></div>
          <div class="p-6">
            <?php if ($error !== ''): ?>
              <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?= e($error) ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
              <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700"><?= e($success) ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
              <?= csrf_field() ?>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                <input type="password" name="current_password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                <input type="password" name="new_password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" minlength="8" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" minlength="8" required>
              </div>
              <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">Update Password</button>
            </form>
          </div>
        </div>
      </main>
    </div>
  </body>
</html>
