<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            // In a real application, you would send an email here with a link containing the token
            // e.g., sendResetEmail($email, $token);
        }

        // For security, always display a success message to prevent email enumeration.
        $success = true;
    }
}
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="min-h-screen pt-24 pb-16 flex items-center justify-center bg-brand-cultured">
    <div class="max-w-md w-full mx-auto px-4 reveal active">
        <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-brand-gold/20 floating-card">
            <div class="bg-brand-wine px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute top-[20%] -right-[30%] w-[150%] h-[150%] rounded-full bg-brand-gold/5 blur-3xl"></div>
                <h2 class="text-3xl font-bold text-brand-gold mb-2 relative z-10">Reset Password</h2>
                <p class="text-brand-cultured/80 text-sm font-light relative z-10">We'll send you instructions to reset it</p>
            </div>

            <div class="px-8 py-10">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm mb-6 border border-red-100 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 text-green-700 px-6 py-8 rounded-2xl text-center mb-6 border border-green-200">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Check your email</h3>
                        <p class="text-sm">If an account exists for <strong><?php echo htmlspecialchars($email); ?></strong>, we have sent a password reset link.</p>
                        <a href="login.php" class="inline-block mt-6 px-6 py-2 bg-white text-brand-wine border border-brand-gold/30 rounded-full text-sm font-medium hover:bg-brand-cultured transition-colors">Return to Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="forgot-password.php" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div>
                            <label for="email" class="block text-sm font-medium text-brand-wine mb-2">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full bg-brand-cultured/50 border border-brand-gold/30 rounded-xl py-3 px-4 text-brand-wine focus:bg-white focus:border-brand-gold focus:ring-1 focus:ring-brand-gold/50 outline-none transition-all duration-300"
                                   placeholder="Enter your registered email">
                        </div>

                        <button type="submit" class="w-full bg-brand-wine text-brand-cultured py-3.5 rounded-xl font-medium shadow-lg hover:bg-brand-burgundy hover:shadow-brand-wine/30 hover:-translate-y-0.5 transition-all duration-300">
                            Send Reset Link
                        </button>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-brand-wine/70">
                            Remember your password?
                            <a href="login.php" class="text-brand-gold font-semibold hover:text-brand-wine transition-colors ml-1">Sign In</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>