<?php

declare(strict_types=1);
$page = "Dashboard";
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

$initials = strtoupper(substr($user['name'], 0, 1));
$success = get_flash('success') ?? '';
?>
<!doctype html>
<html lang="en">

  <?=new Header()->header($page);?>

  <body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">

      <?=new Sidebar()->sidebar($page);?>

      <main class="flex-1 p-6 lg:p-8">
        <h2 class="text-2xl font-bold text-gray-800">Welcome, <?= e($user['name']) ?></h2>
        <p class="text-gray-600 mb-6">This is your profile dashboard.</p>

        <?php if ($success !== ''): ?>
          <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="max-w-3xl bg-white rounded-xl shadow overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2"></div>
          <div class="p-6">
            <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-700 mb-4"><?= e($initials) ?></div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Full Name</p>
                <p class="font-semibold text-gray-800"><?= e($user['name']) ?></p>
              </div>
              <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-semibold text-gray-800"><?= e($user['email']) ?></p>
              </div>
              <div class="border rounded-lg p-4 md:col-span-2">
                <p class="text-sm text-gray-500">Registered At</p>
                <p class="font-semibold text-gray-800"><?= e($user['created_at']) ?></p>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </body>
</html>
