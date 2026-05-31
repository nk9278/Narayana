<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/assets/components/ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (!$user['is_active']) {
                $error = "Your account is inactive. Please contact support.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                // Prevent session fixation
                session_regenerate_id(true);
                header("Location: /user-dashboard/index.php");
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="min-h-screen pt-24 pb-16 flex items-center justify-center bg-brand-cultured">
    <div class="max-w-md w-full mx-auto px-4 reveal active">
        <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-brand-gold/20 floating-card">
            <div class="bg-brand-wine px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute -top-[50%] -right-[20%] w-[150%] h-[150%] rounded-full bg-brand-gold/5 blur-3xl"></div>
                <h2 class="text-3xl font-bold text-brand-gold mb-2 relative z-10">Welcome Back</h2>
                <p class="text-brand-cultured/80 text-sm font-light relative z-10">Sign in to your premium account</p>
            </div>

            <div class="px-8 py-10">
                <?php if (isset($error)): ?>
                    <?php echo UI::alert($error, 'error'); ?>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <?php echo UI::input('email', 'Email Address', 'email'); ?>

                    <div class="relative">
                        <div class="absolute right-0 top-0 mt-1">
                            <a href="forgot-password.php" class="text-xs text-brand-gold hover:text-brand-wine transition-colors font-medium">Forgot Password?</a>
                        </div>
                        <?php echo UI::input('password', 'Password', 'password'); ?>
                    </div>

                    <?php echo UI::button('Sign In', 'submit', 'w-full'); ?>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-brand-wine/70">
                        New to Narayan Jewelers?
                        <a href="register.php" class="text-brand-gold font-semibold hover:text-brand-wine transition-colors ml-1">Create an Account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>