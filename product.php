<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header("Location: category.php");
    exit;
}

// Fetch Product Details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, s.name as subcategory_name
    FROM products p
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE p.slug = ? AND p.is_active = TRUE
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    // 404 handling
    header("HTTP/1.0 404 Not Found");
    echo "Product not found.";
    exit;
}

// Fetch Images
$stmt = $pdo->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmt->execute([$product['id']]);
$images = $stmt->fetchAll();

// Fallback image
if (empty($images)) {
    $images = [['image_url' => 'https://images.unsplash.com/photo-1599643478514-4a4e06d56d1f?q=80&w=800&auto=format&fit=crop', 'is_primary' => 1]];
}

// Fetch Variants
$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$stmt->execute([$product['id']]);
$variants = $stmt->fetchAll();
?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-brand-wine/60 mb-8 font-light reveal active">
            <a href="/" class="hover:text-brand-gold transition-colors">Home</a>
            <span class="mx-2">/</span>
            <a href="category.php" class="hover:text-brand-gold transition-colors"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span class="mx-2">/</span>
            <span class="text-brand-wine font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 xl:gap-16">

            <!-- Image Gallery -->
            <div class="space-y-4 reveal active">
                <div class="bg-white rounded-3xl overflow-hidden border border-brand-gold/20 relative group h-[500px] xl:h-[600px]">
                    <img id="main-image" src="<?php echo htmlspecialchars($images[0]['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover transition-transform duration-700 ease-in-out">
                </div>

                <?php if (count($images) > 1): ?>
                <div class="grid grid-cols-4 gap-4">
                    <?php foreach ($images as $img): ?>
                        <div class="bg-white rounded-xl overflow-hidden border border-brand-gold/10 cursor-pointer h-24 hover:border-brand-gold/50 transition-colors" onclick="document.getElementById('main-image').src='<?php echo htmlspecialchars($img['image_url']); ?>'">
                            <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="flex flex-col reveal active" style="transition-delay: 200ms;">
                <span class="text-xs font-bold tracking-widest text-brand-gold uppercase mb-2"><?php echo htmlspecialchars($product['subcategory_name']); ?></span>
                <h1 class="text-3xl md:text-4xl font-bold text-brand-wine mb-4 leading-tight"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="flex items-end gap-4 mb-6">
                    <span class="text-3xl font-bold text-brand-wine">₹<?php echo number_format($product['base_price'], 2); ?></span>
                    <span class="text-sm text-brand-wine/50 font-light pb-1">(Price may vary based on live gold rates)</span>
                </div>

                <div class="prose prose-sm text-brand-wine/80 font-light mb-8 max-w-none">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <form method="POST" action="cart.php" class="space-y-8 mt-auto">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                    <?php if (!empty($variants)): ?>
                    <div>
                        <label class="block text-sm font-medium text-brand-wine mb-3">Select Specification</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <?php foreach ($variants as $index => $variant): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="variant_id" value="<?php echo $variant['id']; ?>" class="peer sr-only" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                    <div class="text-center py-3 px-4 rounded-xl border border-brand-gold/30 text-brand-wine text-sm hover:bg-brand-cultured peer-checked:border-brand-wine peer-checked:bg-brand-wine peer-checked:text-white transition-all">
                                        <?php echo htmlspecialchars($variant['weight_grams']); ?>g <br>
                                        <span class="text-xs opacity-70"><?php echo htmlspecialchars($variant['purity']); ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="flex gap-4 pt-6 border-t border-brand-gold/20">
                        <button type="submit" class="flex-1 bg-brand-wine text-white py-4 rounded-xl font-medium shadow-lg hover:bg-brand-burgundy hover:shadow-brand-wine/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-shopping-bag mr-2 text-brand-gold"></i> Add to Cart
                        </button>
                        <button type="button" class="w-14 h-14 bg-white border border-brand-gold/30 text-brand-wine rounded-xl flex items-center justify-center hover:bg-brand-cultured hover:text-red-500 hover:border-red-200 transition-all shadow-sm">
                            <i class="far fa-heart text-xl"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-8 grid grid-cols-2 gap-4 text-xs font-medium text-brand-wine/70 border border-brand-gold/10 bg-white rounded-2xl p-4">
                    <div class="flex items-center"><i class="fas fa-certificate text-brand-gold mr-3 text-lg"></i> 100% Certified</div>
                    <div class="flex items-center"><i class="fas fa-shipping-fast text-brand-gold mr-3 text-lg"></i> Free Insured Shipping</div>
                    <div class="flex items-center"><i class="fas fa-undo-alt text-brand-gold mr-3 text-lg"></i> Lifetime Exchange</div>
                    <div class="flex items-center"><i class="fas fa-box-open text-brand-gold mr-3 text-lg"></i> Premium Packaging</div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>