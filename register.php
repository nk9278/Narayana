<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/assets/components/ui.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (empty($errors)) {
        // Check if email or phone already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $errors[] = "Email or phone number already registered.";
        } else {
            $password_hash = password_hash($password, PASSWORD_ARGON2ID);

            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$first_name, $last_name, $email, $phone, $password_hash]);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                session_regenerate_id(true);
                header("Location: /user-dashboard/index.php");
                exit;
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again later.";
            }
        }
    }
}
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="min-h-screen pt-24 pb-16 flex items-center justify-center bg-brand-cultured">
    <div class="max-w-xl w-full mx-auto px-4 reveal active">
        <div class="bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-brand-gold/20 floating-card">
            <div class="bg-brand-wine px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute -top-[50%] -left-[20%] w-[150%] h-[150%] rounded-full bg-brand-gold/5 blur-3xl"></div>
                <h2 class="text-3xl font-bold text-brand-gold mb-2 relative z-10">Join Our Club</h2>
                <p class="text-brand-cultured/80 text-sm font-light relative z-10">Experience the pinnacle of craftsmanship</p>
            </div>

            <div class="px-8 py-10">
                <?php if (!empty($errors)): ?>
                    <?php
                        $errorMsg = implode("<br>", array_map('htmlspecialchars', $errors));
                        echo UI::alert($errorMsg, 'error');
                    ?>
                <?php endif; ?>

                <form method="POST" action="register.php" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php echo UI::input('first_name', 'First Name', 'text', $_POST['first_name'] ?? ''); ?>
                        <?php echo UI::input('last_name', 'Last Name', 'text', $_POST['last_name'] ?? ''); ?>
                    </div>

                    <?php echo UI::input('email', 'Email Address', 'email', $_POST['email'] ?? ''); ?>
                    <?php echo UI::input('phone', 'Phone Number', 'tel', $_POST['phone'] ?? ''); ?>

                    <div>
                        <?php echo UI::input('password', 'Password', 'password'); ?>
                        <p class="text-xs text-brand-wine/60 -mt-2 font-light">Must be at least 8 characters long.</p>
                    </div>

                    <?php echo UI::button('Create Account', 'submit', 'w-full'); ?>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm text-brand-wine/70">
                        Already have an account?
                        <a href="login.php" class="text-brand-gold font-semibold hover:text-brand-wine transition-colors ml-1">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>