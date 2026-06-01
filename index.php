<?php
require_once __DIR__ . '/config/database.php';

// Fetch Active Banners
$bannersStmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY position ASC LIMIT 5");
$banners = $bannersStmt->fetchAll();

// Fetch Live Rates
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Complex query to get today's rates and compare with yesterday's to show movement
$ratesQuery = "
    SELECT t1.metal_type, t1.purity, t1.rate_per_gram as today_rate,
           COALESCE(t2.rate_per_gram, t1.rate_per_gram) as yesterday_rate,
           (t1.rate_per_gram - COALESCE(t2.rate_per_gram, t1.rate_per_gram)) as rate_change
    FROM scheme_gold_rates t1
    LEFT JOIN scheme_gold_rates t2 ON t1.metal_type = t2.metal_type AND t1.purity = t2.purity AND t2.date = ?
    WHERE t1.date = ?
    ORDER BY FIELD(t1.metal_type, 'gold', 'silver', 'platinum')
";
$stmtRates = $pdo->prepare($ratesQuery);
$stmtRates->execute([$yesterday, $today]);
$liveRates = $stmtRates->fetchAll();

// Fetch Active Schemes
$schemesStmt = $pdo->query("SELECT * FROM savings_schemes WHERE is_active = 1 ORDER BY duration_months ASC LIMIT 3");
$activeSchemes = $schemesStmt->fetchAll();

// Fetch Featured Products (Randomizing for dynamic feel, normally would use a featured flag)
$productsStmt = $pdo->query("
    SELECT p.id, p.name, p.slug, p.base_price, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = TRUE LIMIT 1) as primary_image
    FROM products p
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE p.is_active = TRUE
    ORDER BY p.created_at DESC
    LIMIT 4
");
$featuredProducts = $productsStmt->fetchAll();

// Fetch Testimonials
$testimonialsStmt = $pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3");
$testimonials = $testimonialsStmt->fetchAll();
if(empty($testimonials)) {
    // Inject mock testimonials for premium feel if DB is empty
    $testimonials = [
        ['customer_name' => 'Aisha Sharma', 'rating' => 5, 'review_text' => 'The craftsmanship is absolutely unparalleled. I invested in the Swarna scheme and the redemption process was flawless.'],
        ['customer_name' => 'Rohan Mehta', 'rating' => 5, 'review_text' => 'Narayan Jewelers offers the most transparent pricing and live rate tracking. My go-to for all family investments.'],
        ['customer_name' => 'Priya Patel', 'rating' => 5, 'review_text' => 'Their bridal collection took my breath away. The customer service and secure delivery made the entire experience premium.']
    ];
}

$page_title = "Narayan Jewelers | Premium Diamond & Gold Collections";
$page_description = "Experience the pinnacle of craftsmanship with Narayan Jewelers. Explore premium diamond rings, gold necklaces, and invest smartly with our Fintech Gold Savings ecosystem.";

include_once __DIR__ . '/assets/includes/header.php';
?>

<style>
    .marquee-container {
        overflow: hidden;
        white-space: nowrap;
        display: flex;
        width: 100%;
    }
    .marquee-content {
        display: flex;
        animation: marquee 30s linear infinite;
    }
    /* Duplicate content to create a seamless loop */
    .marquee-content::after {
        content: attr(data-text);
        display: flex;
    }
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); } /* Translate by -50% because the content is doubled */
    }
    /* Pause animation on hover for better UX */
    .marquee-container:hover .marquee-content {
        animation-play-state: paused;
    }
</style>

<main>
    <!-- Dynamic Hero Section -->
    <section class="relative overflow-hidden bg-brand-cultured min-h-[85vh] flex items-center pt-24 pb-16 md:py-24">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-brand-gold/10 blur-[100px] mix-blend-multiply animate-pulse"></div>
            <div class="absolute top-[20%] -right-[20%] w-[50%] h-[70%] rounded-full bg-brand-wine/5 blur-[120px] mix-blend-multiply animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col md:flex-row items-center relative z-10">
            <div class="w-full md:w-1/2 pr-0 md:pr-12 text-center md:text-left reveal active" style="transition-delay: 100ms;">
                <span class="inline-block py-1.5 px-4 rounded-full bg-white/60 backdrop-blur-sm text-brand-wine text-xs font-bold tracking-widest uppercase mb-6 shadow-sm border border-brand-gold/20">
                    The Pinnacle of Craftsmanship
                </span>
                <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold tracking-tight mb-6 text-brand-wine leading-[1.05]">
                    Timeless, <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-gold to-brand-wine">Brilliance.</span>
                </h1>
                <p class="text-base md:text-lg text-brand-wine/70 mb-10 max-w-lg mx-auto md:mx-0 font-light leading-relaxed">
                    Discover our exclusive collection of masterful creations. Immerse yourself in uncompromising premium quality, or start securing your future with our <strong class="font-medium text-brand-wine">Gold Savings Ecosystem</strong>.
                </p>
                <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-5">
                    <a href="category.php" class="bg-brand-wine text-brand-cultured px-8 py-4 rounded-full font-medium shadow-xl hover:bg-brand-burgundy hover:shadow-brand-wine/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center">
                        Explore Collection <i class="fas fa-arrow-right ml-3 text-sm text-brand-gold"></i>
                    </a>
                    <a href="savings-schemes/enroll.php" class="bg-white border border-brand-gold/40 text-brand-wine px-8 py-4 rounded-full font-medium shadow-lg hover:bg-brand-cultured hover:border-brand-gold hover:-translate-y-1 transition-all duration-300 flex items-center justify-center">
                        Start Saving <i class="fas fa-piggy-bank ml-3 text-sm text-brand-gold"></i>
                    </a>
                </div>
            </div>

            <!-- Dynamic CMS Banner Slider (Fallback to static if none) -->
            <div class="w-full md:w-1/2 mt-16 md:mt-0 relative group reveal active" style="transition-delay: 300ms;">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-gold/40 to-brand-wine/10 rounded-[2.5rem] transform rotate-3 scale-105 group-hover:rotate-6 transition-transform duration-700 ease-out z-0 blur-sm"></div>
                <div class="relative z-10 rounded-[2.5rem] shadow-2xl w-full h-[350px] sm:h-[450px] md:h-[550px] overflow-hidden border border-white/60 floating-card bg-brand-cultured">
                    <?php if(!empty($banners)): ?>
                        <!-- Minimal CSS-only slider for the first CMS banner for simplicity -->
                        <a href="<?php echo htmlspecialchars($banners[0]['link_url'] ?: '#'); ?>" class="block w-full h-full relative">
                            <img src="<?php echo htmlspecialchars($banners[0]['image_url']); ?>" alt="<?php echo htmlspecialchars($banners[0]['title']); ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-brand-wine/80 to-transparent p-6 pt-12">
                                <h3 class="text-white font-bold text-xl"><?php echo htmlspecialchars($banners[0]['title']); ?></h3>
                            </div>
                        </a>
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=2000&auto=format&fit=crop" alt="Premium Jewelry" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Updated Background Color to New Primary Dark -->
    <div class="bg-[#65202F] border-y border-brand-gold/30 py-3 relative z-20 shadow-inner">
        <div class="marquee-container w-full overflow-hidden">
            <div class="marquee-content flex items-center text-xs md:text-sm tracking-widest uppercase font-medium"
                 data-text="
                    <span class='mx-4 md:mx-8 text-white'>100% Certified Jewelry</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Free Insured Shipping</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Lifetime Exchange Policy</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Handcrafted Perfection</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>100% Certified Jewelry</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Free Insured Shipping</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Lifetime Exchange Policy</span>
                    <span class='text-brand-gold'>♦</span>
                    <span class='mx-4 md:mx-8 text-white'>Handcrafted Perfection</span>
                    <span class='text-brand-gold'>♦</span>
                 ">
                 <span class='mx-4 md:mx-8 text-white'>100% Certified Jewelry</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Free Insured Shipping</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Lifetime Exchange Policy</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Handcrafted Perfection</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>100% Certified Jewelry</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Free Insured Shipping</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Lifetime Exchange Policy</span>
                 <span class='text-brand-gold'>♦</span>
                 <span class='mx-4 md:mx-8 text-white'>Handcrafted Perfection</span>
                 <span class='text-brand-gold'>♦</span>
            </div>
        </div>
    </div>

    <!-- Live Metal Rates Widget -->
    <section class="py-12 bg-white border-b border-brand-gold/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 reveal active">
                <div class="text-center md:text-left">
                    <h2 class="text-xl md:text-2xl font-bold text-brand-wine">Live Market Rates</h2>
                    <p class="text-xs text-brand-wine/60 font-medium tracking-wider uppercase mt-1">Updated: <?php echo date('d M Y'); ?></p>
                </div>

                <div class="flex flex-wrap justify-center md:justify-end gap-4 sm:gap-6 w-full md:w-auto">
                    <?php foreach($liveRates as $rate): ?>
                    <div class="bg-brand-cultured px-5 py-3 rounded-2xl border border-brand-gold/20 min-w-[140px] text-center shadow-sm">
                        <span class="block text-xs font-bold text-brand-gold uppercase tracking-wider mb-1"><?php echo htmlspecialchars($rate['metal_type'] . ' ' . $rate['purity']); ?></span>
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-lg font-bold text-brand-wine">₹<?php echo number_format($rate['today_rate']); ?></span>
                            <?php if($rate['rate_change'] > 0): ?>
                                <span class="text-[10px] text-green-600 bg-green-100 px-1.5 py-0.5 rounded font-bold"><i class="fas fa-arrow-up"></i></span>
                            <?php elseif($rate['rate_change'] < 0): ?>
                                <span class="text-[10px] text-red-600 bg-red-100 px-1.5 py-0.5 rounded font-bold"><i class="fas fa-arrow-down"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Fintech Savings Scheme Promo -->
    <?php if(!empty($activeSchemes)): ?>
    <section class="py-20 md:py-28 bg-gradient-to-b from-brand-wine to-brand-burgundy relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-20">
            <div class="absolute top-[10%] right-[10%] w-96 h-96 rounded-full bg-brand-gold blur-[100px]"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-12 md:mb-16 reveal active">
                <span class="text-brand-gold text-xs font-bold tracking-widest uppercase mb-3 block">Smart Investment</span>
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">Secure Your Future in Gold</h2>
                <p class="text-white/70 max-w-2xl mx-auto font-light leading-relaxed">Lock in today's gold rate with flexible monthly installments. Watch your wealth grow and redeem against stunning jewelry pieces.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                <?php foreach($activeSchemes as $idx => $scheme): ?>
                <div class="bg-white/10 backdrop-blur-md rounded-[2rem] p-8 border border-brand-gold/30 hover:-translate-y-2 transition-transform duration-500 reveal active" style="transition-delay: <?php echo $idx * 150; ?>ms;">
                    <h3 class="text-xl font-bold text-brand-lightgold mb-1"><?php echo htmlspecialchars($scheme['name']); ?></h3>
                    <p class="text-white/80 text-sm font-light mb-6"><?php echo $scheme['duration_months']; ?> Months Duration</p>

                    <div class="mb-8">
                        <span class="text-xs text-white/50 uppercase tracking-widest block mb-1">Monthly Installment</span>
                        <span class="text-4xl font-bold text-white">₹<?php echo number_format($scheme['monthly_amount']); ?></span>
                    </div>

                    <ul class="space-y-3 mb-8 text-sm text-white/80 font-light">
                        <li class="flex items-start"><i class="fas fa-check text-brand-gold mt-1 mr-3"></i> Reserve gold at live rates</li>
                        <li class="flex items-start"><i class="fas fa-check text-brand-gold mt-1 mr-3"></i> 100% secure storage</li>
                        <li class="flex items-start"><i class="fas fa-check text-brand-gold mt-1 mr-3"></i> Exclusive maturity discounts</li>
                    </ul>

                    <a href="/savings-schemes/enroll.php" class="block w-full bg-brand-gold text-brand-wine text-center py-3.5 rounded-xl font-bold hover:bg-brand-lightgold transition-colors shadow-lg">
                        Start Plan
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-16 md:py-32 bg-brand-cultured">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-20 reveal active">
                <h2 class="text-3xl md:text-4xl font-bold text-brand-wine mb-3 md:mb-4 tracking-tight">Curated For You</h2>
                <p class="text-brand-wine/70 font-light text-sm md:text-base">Explore our most distinguished selections.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <?php if(empty($featuredProducts)): ?>
                    <p class="col-span-full text-center text-brand-wine/50">No products available at the moment.</p>
                <?php else: foreach($featuredProducts as $idx => $prod): ?>
                    <a href="product.php?slug=<?php echo urlencode($prod['slug']); ?>" class="block group reveal active" style="transition-delay: <?php echo $idx * 150; ?>ms;">
                        <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/10 shadow-sm h-full flex flex-col">
                            <div class="h-64 md:h-72 overflow-hidden relative bg-gray-50 flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($prod['primary_image'] ?: 'https://images.unsplash.com/photo-1605100804763-247f67b2548e?q=80&w=800&auto=format&fit=crop'); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                <button class="absolute top-4 right-4 w-10 h-10 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center text-brand-wine/50 hover:text-red-500 hover:bg-white transition-all shadow-sm z-10" onclick="event.preventDefault(); addToWishlist(<?php echo $prod['id']; ?>)">
                                    <i id="wishlist-icon-<?php echo $prod['id']; ?>" class="far fa-heart"></i>
                                </button>
                            </div>
                            <div class="p-6 flex flex-col flex-grow relative z-20">
                                <span class="text-xs font-semibold text-brand-gold tracking-wider uppercase mb-2"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                                <h3 class="text-lg font-semibold text-brand-wine group-hover:text-brand-burgundy transition-colors line-clamp-2"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                <div class="mt-auto pt-4 flex items-center justify-between border-t border-brand-gold/10">
                                    <span class="font-bold text-brand-wine">₹<?php echo number_format($prod['base_price'], 2); ?></span>
                                    <span class="w-8 h-8 rounded-full bg-brand-wine/5 flex items-center justify-center text-brand-wine group-hover:bg-brand-gold group-hover:text-white transition-colors">
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <div class="text-center mt-12">
                <a href="category.php" class="inline-flex items-center justify-center px-8 py-3.5 border-2 border-brand-wine text-brand-wine rounded-full font-bold hover:bg-brand-wine hover:text-white transition-colors">
                    View All Products
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 md:py-28 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16 reveal active">
                <span class="text-brand-gold text-xs font-bold tracking-widest uppercase mb-3 block">Testimonials</span>
                <h2 class="text-3xl md:text-5xl font-bold text-brand-wine mb-4">A Legacy of Trust</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach($testimonials as $idx => $review): ?>
                <div class="bg-brand-cultured/50 rounded-3xl p-8 border border-brand-gold/10 reveal active" style="transition-delay: <?php echo $idx * 150; ?>ms;">
                    <div class="flex text-brand-gold mb-4 text-sm">
                        <?php for($i=0; $i<$review['rating']; $i++) echo '<i class="fas fa-star"></i>'; ?>
                    </div>
                    <p class="text-brand-wine/80 font-light italic mb-6 leading-relaxed">"<?php echo htmlspecialchars($review['review_text']); ?>"</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-brand-wine text-brand-gold flex items-center justify-center font-bold mr-3">
                            <?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?>
                        </div>
                        <span class="font-bold text-brand-wine text-sm"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<script>
// Reusable wishlist toggle for frontend grids
function addToWishlist(productId) {
    const icon = document.getElementById('wishlist-icon-' + productId);
    // Note: requires a csrf token meta tag or global variable in production
    const csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>';

    fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=toggle&product_id=${productId}&csrf_token=${encodeURIComponent(csrfToken)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.status === 'added') {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-red-500');
            } else {
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far');
            }
        } else {
            window.location.href = 'login.php';
        }
    });
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Find the marquee content div
        const marquee = document.querySelector('.marquee-content');
        if(marquee) {
            // Append the raw HTML stored in data-text to create the infinite loop effect
            marquee.innerHTML += marquee.getAttribute('data-text');
        }
    });
</script>

<?php include_once __DIR__ . '/assets/includes/footer.php'; ?>