<?php include_once __DIR__ . '/assets/includes/header.php'; ?>

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
    <section class="relative overflow-hidden bg-brand-cultured min-h-[75vh] flex items-center pt-12 pb-16 md:py-20">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-brand-gold/10 blur-3xl mix-blend-multiply animate-pulse"></div>
            <div class="absolute top-[30%] -right-[10%] w-[40%] h-[60%] rounded-full bg-brand-burgundy/5 blur-3xl mix-blend-multiply animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col md:flex-row items-center reveal">
            <div class="w-full md:w-1/2 pr-0 md:pr-12 text-center md:text-left z-10">
                <span class="inline-block py-1 px-3 rounded-full bg-brand-gold/20 text-brand-wine text-xs font-bold tracking-wider uppercase mb-4 md:mb-6 border border-brand-gold/30">
                    The Pinnacle of Craftsmanship
                </span>
                <h1 class="text-4xl sm:text-5xl md:text-7xl font-bold tracking-tight mb-4 md:mb-6 text-brand-wine leading-[1.1]">
                    Timeless, <br>
                    <span class="text-brand-gold">Brilliance.</span>
                </h1>
                <p class="text-base md:text-lg text-brand-wine/80 mb-6 md:mb-8 max-w-lg mx-auto md:mx-0 font-light leading-relaxed">
                    Discover our exclusive collection of masterful creations. At Narayan Jewellers, we specialize strictly in uncompromising, premium quality for those who seek the extraordinary.
                </p>
                <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="#" class="bg-brand-wine text-brand-cultured px-8 py-3.5 rounded-full font-medium shadow-lg hover:bg-brand-wine/90 hover:shadow-brand-wine/30 hover:-translate-y-1 transition-all duration-300 flex items-center justify-center">
                        Explore Collection <i class="fas fa-arrow-right ml-2 text-sm text-brand-gold"></i>
                    </a>
                </div>
            </div>

            <div class="w-full md:w-1/2 mt-12 md:mt-0 relative group">
                <div class="absolute inset-0 bg-brand-gold rounded-[2.5rem] transform rotate-3 scale-105 opacity-20 group-hover:rotate-6 transition-transform duration-700 ease-out z-0"></div>
                <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=2000&auto=format&fit=crop"
                     alt="Premium Jewelry"
                     class="relative z-10 rounded-[2.5rem] shadow-2xl w-full h-[300px] sm:h-[400px] md:h-[500px] object-cover border border-white/60 floating-card">
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

    <section class="py-16 md:py-32 bg-brand-cultured">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-20 reveal">
                <h2 class="text-3xl md:text-4xl font-bold text-brand-wine mb-3 md:mb-4 tracking-tight">Curated For You</h2>
                <p class="text-brand-wine/70 font-light text-sm md:text-base">Explore our most distinguished selections.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-10">
                <a href="#" class="block group reveal" style="transition-delay: 0ms;">
                    <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/20 shadow-sm">
                        <div class="h-64 md:h-80 overflow-hidden relative">
                             <div class="absolute inset-0 bg-gradient-to-t from-brand-wine/60 to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-500 z-10"></div>
                            <img src="https://images.unsplash.com/photo-1605100804763-247f67b2548e?q=80&w=800&auto=format&fit=crop" alt="Rings" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        </div>
                        <div class="p-6 md:p-8 text-center bg-white relative z-20">
                            <h3 class="text-lg md:text-xl font-semibold text-brand-wine group-hover:text-brand-burgundy transition-colors">Diamond Rings</h3>
                            <p class="text-xs md:text-sm text-brand-gold mt-2 font-medium">Starting at ₹15,000</p>
                        </div>
                    </div>
                </a>

                <a href="#" class="block group reveal" style="transition-delay: 150ms;">
                    <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/20 shadow-sm">
                        <div class="h-64 md:h-80 overflow-hidden relative">
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-wine/60 to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-500 z-10"></div>
                            <img src="https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=800&auto=format&fit=crop" alt="Earrings" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        </div>
                        <div class="p-6 md:p-8 text-center bg-white relative z-20">
                            <h3 class="text-lg md:text-xl font-semibold text-brand-wine group-hover:text-brand-burgundy transition-colors">Statement Earrings</h3>
                            <p class="text-xs md:text-sm text-brand-gold mt-2 font-medium">Starting at ₹12,500</p>
                        </div>
                    </div>
                </a>

                <a href="#" class="block group reveal sm:col-span-2 md:col-span-1" style="transition-delay: 300ms;">
                    <div class="bg-white rounded-3xl overflow-hidden floating-card border border-brand-gold/20 shadow-sm">
                        <div class="h-64 md:h-80 overflow-hidden relative">
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-wine/60 to-transparent opacity-60 group-hover:opacity-40 transition-opacity duration-500 z-10"></div>
                            <img src="https://images.unsplash.com/photo-1599643478514-4a4e06d56d1f?q=80&w=800&auto=format&fit=crop" alt="Necklaces" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        </div>
                        <div class="p-6 md:p-8 text-center bg-white relative z-20">
                            <h3 class="text-lg md:text-xl font-semibold text-brand-wine group-hover:text-brand-burgundy transition-colors">Bridal Necklaces</h3>
                            <p class="text-xs md:text-sm text-brand-gold mt-2 font-medium">Starting at ₹45,000</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

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