<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, is_active FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            if (!$admin['is_active']) {
                $error = "Your account is deactivated.";
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                session_regenerate_id(true);
                header("Location: /admin/index.php");
                exit;
            }
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Narayan Jewelers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            cultured: '#F2F4F3',
                            wine: '#65202F',
                            burgundy: '#49111C',
                            gold: '#D6B36A',
                            lightgold: '#E3C98B'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-brand-gold/30">
            <div class="bg-brand-wine p-8 text-center border-b border-brand-gold">
                <h1 class="text-2xl font-bold text-brand-gold mb-1">Admin Portal</h1>
                <p class="text-brand-cultured/70 text-sm font-light">Secure Access Only</p>
            </div>

            <div class="p-8">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-6 border border-red-100">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                        <input type="email" id="email" name="email" required
                               class="w-full bg-gray-50 border border-gray-300 rounded-lg py-2.5 px-4 text-gray-900 focus:bg-white focus:border-brand-gold focus:ring-1 focus:ring-brand-gold outline-none transition-all">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full bg-gray-50 border border-gray-300 rounded-lg py-2.5 px-4 text-gray-900 focus:bg-white focus:border-brand-gold focus:ring-1 focus:ring-brand-gold outline-none transition-all">
                    </div>

                    <button type="submit" class="w-full bg-brand-wine text-white py-3 rounded-lg font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                        Authenticate
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>