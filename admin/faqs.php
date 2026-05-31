<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Helpers.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');

        if ($question && $answer) {
            $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)");
            $stmt->execute([$question, $answer, $category]);
            $success = "FAQ added successfully.";
        } else {
            $error = "Question and Answer are required.";
        }
    } elseif ($action === 'delete') {
        $faq_id = filter_input(INPUT_POST, 'faq_id', FILTER_VALIDATE_INT);
        if ($faq_id) {
            $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$faq_id]);
            $success = "FAQ deleted.";
        }
    }
}

$faqs = $pdo->query("SELECT * FROM faqs ORDER BY category ASC, created_at DESC")->fetchAll();
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">FAQ Management</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage the Frequently Asked Questions content.</p>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
        <i class="fas fa-check-circle mr-2"></i> <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-6 border border-red-200">
        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-bold text-gray-800">Existing FAQs</h2>
            </div>
            <div class="p-6 space-y-4">
                <?php if(empty($faqs)): ?>
                    <p class="text-center text-gray-500">No FAQs added yet.</p>
                <?php else: foreach($faqs as $faq): ?>
                    <div class="border border-gray-200 rounded-lg p-4 group relative">
                        <span class="inline-block bg-brand-cultured text-brand-wine text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded mb-2">
                            <?php echo htmlspecialchars($faq['category']); ?>
                        </span>
                        <h3 class="font-bold text-gray-800 mb-2 pr-8"><?php echo htmlspecialchars($faq['question']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>

                        <form method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Delete this FAQ?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h2 class="font-bold text-gray-800 mb-4">Add New FAQ</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <input type="text" name="category" placeholder="e.g. Shipping, Returns" value="General" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                    <input type="text" name="question" required class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                    <textarea name="answer" required rows="4" class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-1 focus:ring-brand-gold"></textarea>
                </div>

                <button type="submit" class="w-full bg-brand-wine text-white py-2.5 rounded-lg font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                    Save FAQ
                </button>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>