<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/assets/components/ui.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$cart_id = null;
$cartItems = [];
$subtotal = 0;

if ($user_id) {
    // Get user's cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart = $stmt->fetch();

    if ($cart) {
        $cart_id = $cart['id'];
        // Fetch cart items
        $stmt = $pdo->prepare("
            SELECT ci.id as cart_item_id, ci.quantity, v.id as variant_id, v.weight_grams, v.purity, v.additional_price,
                   p.name, p.slug, p.base_price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as image_url,
                   c.name as category_name
            FROM cart_items ci
            JOIN product_variants v ON ci.variant_id = v.id
            JOIN products p ON v.product_id = p.id
            JOIN subcategories s ON p.subcategory_id = s.id
            JOIN categories c ON s.category_id = c.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart_id]);
        $cartItems = $stmt->fetchAll();
    }
} else {
    // Session Cart for Guest Users
    if (isset($_SESSION['guest_cart']) && !empty($_SESSION['guest_cart'])) {
        $variantIds = array_keys($_SESSION['guest_cart']);
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));

        $stmt = $pdo->prepare("
            SELECT v.id as variant_id, v.weight_grams, v.purity, v.additional_price,
                   p.name, p.slug, p.base_price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as image_url,
                   c.name as category_name
            FROM product_variants v
            JOIN products p ON v.product_id = p.id
            JOIN subcategories s ON p.subcategory_id = s.id
            JOIN categories c ON s.category_id = c.id
            WHERE v.id IN ($placeholders)
        ");
        $stmt->execute($variantIds);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $p['cart_item_id'] = $p['variant_id']; // For guest cart, map cart_item_id to variant_id for updates/removes
            $p['quantity'] = $_SESSION['guest_cart'][$p['variant_id']];
            $cartItems[] = $p;
        }
    }
}

foreach ($cartItems as $item) {
    $itemPrice = $item['base_price'] + $item['additional_price'];
    $subtotal += ($itemPrice * $item['quantity']);
}

$gst = $subtotal * 0.03; // 3% GST on jewelry
$shipping = $subtotal > 0 && $subtotal < 50000 ? 500 : 0; // Free shipping over 50,000
$total = $subtotal + $gst + $shipping;

?>
<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

<main class="bg-brand-cultured min-h-screen pt-24 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-10 text-center reveal active">
            <h1 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4">Shopping Cart</h1>
            <p class="text-brand-wine/70 font-light">Review your premium selections.</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-3xl p-12 text-center border border-brand-gold/20 shadow-sm reveal active">
                <div class="w-24 h-24 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-6 text-brand-gold/50">
                    <i class="fas fa-shopping-bag text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-brand-wine mb-2">Your cart is empty</h3>
                <p class="text-brand-wine/70 mb-8 font-light">Looks like you haven't added any masterful creations to your cart yet.</p>
                <a href="category.php" class="inline-flex bg-brand-wine text-white py-3.5 px-8 rounded-full font-medium shadow-md hover:bg-brand-burgundy transition-colors">
                    Return to Shop
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-10 reveal active">

                <!-- Cart Items List -->
                <div class="lg:w-2/3 space-y-6">
                    <?php foreach ($cartItems as $item):
                        $unitPrice = $item['base_price'] + $item['additional_price'];
                        $lineTotal = $unitPrice * $item['quantity'];
                    ?>
                        <div class="bg-white p-4 sm:p-6 rounded-3xl shadow-sm border border-brand-gold/20 flex flex-col sm:flex-row gap-6 relative group">

                            <!-- Remove Button -->
                            <button onclick="removeCartItem(<?php echo $item['cart_item_id']; ?>)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-sm border border-gray-100 group-hover:border-red-200">
                                <i class="fas fa-times"></i>
                            </button>

                            <!-- Product Image -->
                            <div class="w-full sm:w-32 h-32 rounded-2xl overflow-hidden bg-brand-cultured flex-shrink-0 border border-brand-gold/10">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://images.unsplash.com/photo-1599643478514-4a4e06d56d1f?q=80&w=400&auto=format&fit=crop'); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Product Details -->
                            <div class="flex-grow flex flex-col">
                                <span class="text-xs font-semibold text-brand-gold tracking-wider uppercase mb-1"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <a href="product.php?slug=<?php echo urlencode($item['slug']); ?>" class="text-lg font-bold text-brand-wine hover:text-brand-burgundy transition-colors line-clamp-1 mb-1">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                                <p class="text-sm text-brand-wine/60 mb-4 font-light">
                                    Weight: <?php echo htmlspecialchars($item['weight_grams']); ?>g | Purity: <?php echo htmlspecialchars($item['purity']); ?>
                                </p>

                                <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                                    <div class="text-lg font-bold text-brand-wine">
                                        ₹<?php echo number_format($unitPrice, 2); ?>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="flex items-center border border-brand-gold/30 rounded-lg overflow-hidden bg-white">
                                        <button onclick="updateCartQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['quantity'] - 1; ?>)" class="w-8 h-8 flex items-center justify-center text-brand-wine hover:bg-brand-cultured transition-colors" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="w-10 text-center text-sm font-medium text-brand-wine"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateCartQuantity(<?php echo $item['cart_item_id']; ?>, <?php echo $item['quantity'] + 1; ?>)" class="w-8 h-8 flex items-center justify-center text-brand-wine hover:bg-brand-cultured transition-colors">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white p-8 rounded-3xl shadow-lg border-t-4 border-t-brand-gold border-x border-b border-brand-gold/20 sticky top-32">
                        <h3 class="text-xl font-bold text-brand-wine mb-6">Order Summary</h3>

                        <div class="space-y-4 mb-6 text-sm font-light text-brand-wine/80">
                            <div class="flex justify-between items-center">
                                <span>Subtotal</span>
                                <span class="font-medium text-brand-wine">₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Estimated GST (3%)</span>
                                <span class="font-medium text-brand-wine">₹<?php echo number_format($gst, 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Shipping</span>
                                <span class="font-medium text-brand-wine">
                                    <?php echo $shipping == 0 ? '<span class="text-green-600 font-bold">FREE</span>' : '₹' . number_format($shipping, 2); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        <div class="mb-6 pt-4 border-t border-brand-gold/10">
                            <label class="block text-xs font-medium text-brand-wine mb-2 uppercase tracking-wider">Gift Card or Discount Code</label>
                            <div class="flex">
                                <input type="text" placeholder="Enter code" class="flex-grow bg-brand-cultured/50 border border-brand-gold/30 rounded-l-xl py-2.5 px-4 text-sm text-brand-wine focus:bg-white focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold/50 transition-all">
                                <button class="bg-brand-wine text-white px-5 rounded-r-xl text-sm font-medium hover:bg-brand-burgundy transition-colors">Apply</button>
                            </div>
                        </div>

                        <div class="border-t border-brand-gold/20 pt-4 mb-8">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-brand-wine">Total</span>
                                <span class="text-2xl font-bold text-brand-gold">₹<?php echo number_format($total, 2); ?></span>
                            </div>
                            <p class="text-[10px] text-right text-brand-wine/50 mt-1">Inclusive of all taxes</p>
                        </div>

                        <a href="checkout.php" class="block w-full bg-brand-wine text-white text-center py-4 rounded-xl font-medium shadow-xl shadow-brand-wine/20 hover:bg-brand-burgundy hover:shadow-brand-wine/40 hover:-translate-y-1 transition-all duration-300">
                            Proceed to Checkout <i class="fas fa-lock ml-2 text-xs opacity-70"></i>
                        </a>

                        <div class="mt-6 flex items-center justify-center space-x-3 opacity-60">
                            <i class="fab fa-cc-visa text-2xl"></i>
                            <i class="fab fa-cc-mastercard text-2xl"></i>
                            <i class="fas fa-rupee-sign text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<script>
function updateCartQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;
    const csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&cart_item_id=${cartItemId}&quantity=${newQuantity}&csrf_token=${encodeURIComponent(csrfToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error updating quantity');
        }
    });
}

function removeCartItem(cartItemId) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    const csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&cart_item_id=${cartItemId}&csrf_token=${encodeURIComponent(csrfToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error removing item');
        }
    });
}
</script>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>