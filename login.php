<?php

declare(strict_types=1);
$page = "Login";
require_once __DIR__ . '/includes/bootstrap.php';

if ($auth->check()) {
    redirect('dashboard.php');
}

$error = get_flash('error') ?? '';
$success = get_flash('success') ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        set_old(['email' => $email]);

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!$auth->attempt(strtolower($email), $password)) {
            $error = 'Invalid login credentials.';
        } else {
            clear_old();
            redirect('dashboard.php');
        }
    }
}
?>
<!doctype html>
<html lang="en">
  <?=new Header()->header($page);?>
  <body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Welcome Back</h1>
        <p class="text-gray-600 mt-2">Sign in to continue</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl p-8">
        <?php if ($success !== ''): ?>
          <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <?= csrf_field() ?>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <input type="email" name="email" value="<?= e(old('email')) ?>" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@example.com" required>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input type="password" name="password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••" required>
          </div>

          <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">Sign In</button>
        </form>
      </div>

      <p class="text-center text-sm text-gray-600">
        Don't have an account?
        <a href="signup.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign up</a>
      </p>
    </div>
  </body>
</html>
