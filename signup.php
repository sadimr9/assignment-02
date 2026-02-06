<?php

declare(strict_types=1);
$page = "Sign up";
require_once __DIR__ . '/includes/bootstrap.php';

if ($auth->check()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        set_old([
            'name' => $name,
            'email' => $email,
        ]);

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please provide a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password and confirm password do not match.';
        } elseif ($users->findByEmail($email) !== null) {
            $error = 'This email is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $users->create($name, $email, $hashedPassword);

            clear_old();
            flash('success', 'Registration successful. Please login.');
            redirect('login.php');
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
        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Create Account</h1>
        <p class="text-gray-600 mt-2">Join Profile Management app</p>
      </div>

      <div class="bg-white rounded-2xl shadow-xl p-8">
        <?php if ($error !== ''): ?>
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <?= csrf_field() ?>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
            <input type="text" name="name" value="<?= e(old('name')) ?>" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="John Doe" required>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
            <input type="email" name="email" value="<?= e(old('email')) ?>" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="you@example.com" required>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input type="password" name="password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="At least 8 characters" required>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
            <input type="password" name="confirm_password" class="w-full px-3 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Repeat password" required>
          </div>

          <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700">Create Account</button>
        </form>
      </div>

      <p class="text-center text-sm text-gray-600">
        Already have an account?
        <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in</a>
      </p>
    </div>
  </body>
</html>
