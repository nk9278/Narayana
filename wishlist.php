<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/assets/components/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check and update database schema for wishlist if needed
try {
    $pdo->query("SELECT 1 FROM wishlist_items LIMIT 1");
} catch (PDOException $e) {
    // Create the table dynamically if it doesn't exist
    $pdo->exec("CREATE TABLE wishlist_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )");
}

// Remove Item Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    Helpers::verifyCsrfToken($_POST['csrf_token'] ?? '');
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($product_id) {
        $stmt = $pdo->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    }
    header("Location: wishlist.php");
    exit;
}

// Fetch Wishlist Items
$stmt = $pdo->prepare("
    SELECT w.product_id, p.name, p.slug, p.base_price,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as image_url,
           c.name as category_name
    FROM wishlist_items w
    JOIN products p ON w.product_id = p.id
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlistItems = $stmt->fetchAll();
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-10 text-center reveal active">
            <h1 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4">My Wishlist</h1>
            <p class="text-brand-wine/70 font-light">Curated selections saved for later.</p>
        </div>

        <?php if (empty($wishlistItems)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-brand-gold/20 shadow-sm reveal active">
                <div class="w-20 h-20 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-6 text-brand-gold/50">
                    <i class="far fa-heart text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-wine mb-2">Your wishlist is empty</h3>
                <p class="text-brand-wine/70 mb-8 font-light">Save items you love here to review them later or add them to your cart.</p>
                <a href="category.php" class="inline-flex bg-brand-wine text-white py-3 px-8 rounded-full font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                    Explore Collections
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach ($wishlistItems as $index => $item): ?>
                    <?php $delay = ($index % 4) * 100; ?>
                    <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/10 shadow-sm h-full flex flex-col reveal active" style="transition-delay: <?php echo $delay; ?>ms;">
                        <a href="product.php?slug=<?php echo urlencode($item['slug']); ?>" class="relative h-64 overflow-hidden bg-brand-cultured block group">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://images.unsplash.com/photo-1599643478514-4a4e06d56d1f?q=80&w=600&auto=format&fit=crop'); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        </a>

                        <div class="p-5 flex flex-col flex-grow">
                            <span class="text-xs font-semibold text-brand-gold tracking-wider uppercase mb-1"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            <a href="product.php?slug=<?php echo urlencode($item['slug']); ?>" class="text-lg font-semibold text-brand-wine mb-2 hover:text-brand-burgundy transition-colors line-clamp-2">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                            <div class="text-lg font-bold text-brand-wine mb-4 mt-auto">
                                ₹<?php echo number_format($item['base_price'], 2); ?>
                            </div>

                            <div class="flex gap-2 border-t border-brand-gold/10 pt-4 mt-auto">
                                <a href="product.php?slug=<?php echo urlencode($item['slug']); ?>" class="flex-1 bg-brand-cultured text-brand-wine py-2.5 rounded-lg text-sm font-medium hover:bg-brand-gold hover:text-white transition-colors text-center">
                                    View Options
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirm('Remove this item from wishlist?');">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="w-10 h-10 border border-brand-gold/30 text-brand-wine/50 rounded-lg flex items-center justify-center hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-colors">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>