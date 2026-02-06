<?php

declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

session_start();

require_once __DIR__ . '/includes/helpers.php';

if (is_installed()) {
    redirect('login.php');
}

$error = '';
$success = '';
$step = 'select';

function createUsersTable(PDO $pdo, string $driver): void
{
    if ($driver === 'sqlite') {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                created_at DATETIME NOT NULL
            )'
        );

        return;
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function writeConfig(array $config): void
{
    $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";
    file_put_contents(__DIR__ . '/config.php', $content);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'select_driver') {
                $driver = $_POST['driver'] ?? '';

                if (!in_array($driver, ['sqlite', 'mysql'], true)) {
                    throw new RuntimeException('Please choose a valid database type.');
                }

                if ($driver === 'sqlite') {
                    $dbPath = __DIR__ . '/storage/profile_management.db';
                    $pdo = new PDO('sqlite:' . $dbPath);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    createUsersTable($pdo, 'sqlite');

                    $config = [
                        'app_name' => 'Profile Management',
                        'driver' => 'sqlite',
                        'sqlite' => [
                            'path' => $dbPath,
                        ],
                        'mysql' => [
                            'host' => '',
                            'port' => '3306',
                            'database' => '',
                            'username' => '',
                            'password' => '',
                        ],
                    ];

                    writeConfig($config);
                    file_put_contents(__DIR__ . '/storage/install.lock', date('c'));
                    @rename(__FILE__, __DIR__ . '/install.disabled.php');
                    $_SESSION['flash']['success'] = 'Installation completed with SQLite. Please login.';
                    redirect('login.php');
                }

                $step = 'mysql';
            }

            if ($action === 'setup_mysql') {
                $step = 'mysql';

                $host = trim((string) ($_POST['host'] ?? ''));
                $port = trim((string) ($_POST['port'] ?? '3306'));
                $database = trim((string) ($_POST['database'] ?? ''));
                $username = trim((string) ($_POST['username'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');

                if ($host === '' || $database === '' || $username === '') {
                    throw new RuntimeException('Host, database name, and username are required for MySQL.');
                }

                if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
                    throw new RuntimeException('Database name can only include letters, numbers, and underscore.');
                }

                if (!preg_match('/^[0-9]{2,5}$/', $port)) {
                    throw new RuntimeException('Port must be a valid number.');
                }

                $serverPdo = new PDO(
                    sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
                    $username,
                    $password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                $dbPdo = new PDO(
                    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database),
                    $username,
                    $password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                createUsersTable($dbPdo, 'mysql');

                $config = [
                    'app_name' => 'Profile Management',
                    'driver' => 'mysql',
                    'sqlite' => [
                        'path' => __DIR__ . '/storage/profile_management.db',
                    ],
                    'mysql' => [
                        'host' => $host,
                        'port' => $port,
                        'database' => $database,
                        'username' => $username,
                        'password' => $password,
                    ],
                ];

                writeConfig($config);
                file_put_contents(__DIR__ . '/storage/install.lock', date('c'));
                @rename(__FILE__, __DIR__ . '/install.disabled.php');
                $_SESSION['flash']['success'] = 'Installation completed with MySQL. Please login.';
                redirect('login.php');
            }
        } catch (Throwable $th) {
            $error = $th->getMessage();
        }
    }
}

$oldHost = e((string) ($_POST['host'] ?? '127.0.0.1'));
$oldPort = e((string) ($_POST['port'] ?? '3306'));
$oldDb = e((string) ($_POST['database'] ?? 'profile_management'));
$oldUser = e((string) ($_POST['username'] ?? 'root'));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Install | Profile Management | Sadi Chowdhury</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gradient-to-br from-indigo-50 via-white to-cyan-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-2xl p-8">
      <h1 class="text-3xl font-bold text-indigo-700 mb-2">Profile Management Installer</h1>
      <p class="text-gray-600 mb-8">Choose a database engine and complete setup.</p>

      <?php if ($error !== ''): ?>
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if ($success !== ''): ?>
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($step === 'select'): ?>
        <form method="post" class="space-y-6">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="select_driver">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="cursor-pointer">
              <input type="radio" name="driver" value="sqlite" class="peer sr-only" checked>
              <div class="border-2 border-gray-200 rounded-xl p-6 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                <span class="block text-xl font-semibold text-gray-800">SQLite</span>
                <span class="block text-gray-600 mt-2">Auto-creates <code>storage/profile_management.db</code>.</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="driver" value="mysql" class="peer sr-only">
              <div class="border-2 border-gray-200 rounded-xl p-6 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                <span class="block text-xl font-semibold text-gray-800">MySQL</span>
                <span class="block text-gray-600 mt-2">Provide host, DB, user, and password.</span>
              </div>
            </label>
          </div>

          <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700">Continue</button>
        </form>
      <?php else: ?>
        <form method="post" class="space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="setup_mysql">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
              <input type="text" name="host" value="<?= $oldHost ?>" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
              <input type="text" name="port" value="<?= $oldPort ?>" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
              <input type="text" name="database" value="<?= $oldDb ?>" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
              <input type="text" name="username" value="<?= $oldUser ?>" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input type="password" name="password" class="w-full border rounded-lg px-3 py-2">
            </div>
          </div>

          <div class="flex gap-3">
            <a href="install.php" class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-50">Back</a>
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700">Finish Installation</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </body>
</html>
