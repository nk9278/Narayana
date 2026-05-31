<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';

$category_slug = $_GET['c'] ?? '';

$category = null;
if ($category_slug) {
    $stmt = $pdo->prepare("SELECT id, name, description FROM categories WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$category_slug]);
    $category = $stmt->fetch();
}

$page_title = $category ? $category['name'] : 'All Jewelry';
$page_desc = $category ? $category['description'] : 'Explore our exclusive collection of masterful creations.';

// Dynamic query building based on category
$query = "SELECT p.id, p.name, p.slug, p.base_price, c.name as category_name,
                 (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as primary_image
          FROM products p
          JOIN subcategories s ON p.subcategory_id = s.id
          JOIN categories c ON s.category_id = c.id
          WHERE p.is_active = TRUE";
$params = [];

if ($category) {
    $query .= " AND c.id = ?";
    $params[] = $category['id'];
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-10 md:mb-16 text-center reveal active">
            <h1 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4"><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="text-brand-wine/70 max-w-2xl mx-auto font-light"><?php echo htmlspecialchars($page_desc); ?></p>
        </div>

        <!-- Filters & Sorting Controls Base -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-brand-gold/20 flex flex-col md:flex-row justify-between items-center mb-10 reveal active">
            <div class="flex items-center space-x-4 mb-4 md:mb-0">
                <button class="text-brand-wine text-sm font-medium flex items-center hover:text-brand-gold transition-colors">
                    <i class="fas fa-filter mr-2"></i> Filters
                </button>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-brand-wine/60"><?php echo count($products); ?> Products</span>
            </div>

            <div class="flex items-center">
                <label for="sort" class="text-sm text-brand-wine/70 mr-3">Sort By:</label>
                <select id="sort" class="bg-brand-cultured border-none text-sm text-brand-wine rounded-lg focus:ring-0 cursor-pointer">
                    <option value="newest">Newest Arrivals</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php if (empty($products)): ?>
                <div class="col-span-full text-center py-20">
                    <p class="text-brand-wine/60 text-lg">No products found in this category.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $index => $product): ?>
                    <?php
                        $delay = ($index % 4) * 100;
                        $image = $product['primary_image'] ?? 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=600&auto=format&fit=crop';
                    ?>
                    <a href="product.php?slug=<?php echo urlencode($product['slug']); ?>" class="block group reveal active" style="transition-delay: <?php echo $delay; ?>ms;">
                        <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/10 shadow-sm h-full flex flex-col">
                            <div class="relative h-72 overflow-hidden bg-brand-cultured flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($image); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">

                                <button class="absolute top-4 right-4 w-10 h-10 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center text-brand-wine/50 hover:text-red-500 hover:bg-white transition-all shadow-sm z-10" onclick="event.preventDefault(); /* Add to wishlist logic */">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <div class="p-6 flex flex-col flex-grow relative">
                                <span class="text-xs font-semibold text-brand-gold tracking-wider uppercase mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <h3 class="text-lg font-semibold text-brand-wine mb-2 group-hover:text-brand-burgundy transition-colors line-clamp-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="mt-auto pt-4 flex items-center justify-between border-t border-brand-gold/10">
                                    <span class="font-bold text-brand-wine">₹<?php echo number_format($product['base_price'], 2); ?></span>
                                    <span class="w-8 h-8 rounded-full bg-brand-wine/5 flex items-center justify-center text-brand-wine group-hover:bg-brand-gold group-hover:text-white transition-colors">
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>