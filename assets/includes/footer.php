<footer class="bg-brand-wine text-brand-cultured pt-16 md:pt-24 pb-8 md:pb-12 border-t-[6px] border-brand-gold relative overflow-hidden reveal">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Reduced gap and bottom margin on mobile -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-12 mb-10 md:mb-16">

                <!-- Brand Section -->
                <div class="col-span-1 md:col-span-5 text-center md:text-left">
                    <div class="mb-4 md:mb-6 flex justify-center md:justify-start">
                        <!-- Added logo to footer layout here -->
                        <img src="/assets/logo.png" alt="Narayan Jewelers" class="h-10 md:h-12 w-auto object-contain drop-shadow-md">
                    </div>
                    <p class="text-brand-cultured/70 max-w-sm mx-auto md:mx-0 mb-6 md:mb-8 leading-relaxed font-light text-sm md:text-base">
                        Crafting timeless beauty. Our commitment to quality and contemporary design ensures that every piece is a masterpiece, designed exclusively for you.
                    </p>
                    <div class="flex justify-center md:justify-start space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-brand-gold/10 border border-brand-gold/20 flex items-center justify-center text-brand-gold hover:bg-brand-lightgold hover:text-brand-wine hover:border-brand-lightgold hover:-translate-y-1 transition-all duration-300 shadow-lg"><i class="fab fa-instagram text-sm"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-brand-gold/10 border border-brand-gold/20 flex items-center justify-center text-brand-gold hover:bg-brand-lightgold hover:text-brand-wine hover:border-brand-lightgold hover:-translate-y-1 transition-all duration-300 shadow-lg"><i class="fab fa-facebook-f text-sm"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-brand-gold/10 border border-brand-gold/20 flex items-center justify-center text-brand-gold hover:bg-brand-lightgold hover:text-brand-wine hover:border-brand-lightgold hover:-translate-y-1 transition-all duration-300 shadow-lg"><i class="fab fa-twitter text-sm"></i></a>
                    </div>
                </div>

                <!-- Quick Links Section -->
                <div class="col-span-1 md:col-span-3 text-center md:text-left mt-4 md:mt-0">
                    <h3 class="text-sm font-bold tracking-wider uppercase text-brand-gold mb-4 md:mb-6">Quick Links</h3>
                    <ul class="space-y-3 md:space-y-4 text-sm text-brand-cultured/80 font-light">
                        <li><a href="#" class="hover:text-brand-lightgold transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-brand-lightgold transition-colors">Our Collections</a></li>
                        <li><a href="#" class="hover:text-brand-lightgold transition-colors">Store Locator</a></li>
                        <li><a href="#" class="hover:text-brand-lightgold transition-colors">Exchange & Refund Policy</a></li>
                    </ul>
                </div>

                <!-- Newsletter Section -->
                <div class="col-span-1 md:col-span-4 text-center md:text-left mt-4 md:mt-0">
                    <h3 class="text-sm font-bold tracking-wider uppercase text-brand-gold mb-4 md:mb-6">Join the Club</h3>
                    <p class="text-sm text-brand-cultured/70 mb-4 md:mb-6 font-light">Subscribe for exclusive offers, early access to new collections, and styling tips.</p>

                    <form class="flex relative group max-w-md mx-auto md:mx-0" method="POST" action="/subscribe">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="email" placeholder="Email Address" required
                               class="w-full bg-brand-cultured/10 border border-brand-gold/30 rounded-full py-3 px-6 pr-14 text-sm text-brand-cultured focus:outline-none focus:border-brand-lightgold focus:bg-brand-cultured/20 transition-all duration-300 placeholder-brand-cultured/50">
                        <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 w-10 bg-brand-gold rounded-full flex items-center justify-center hover:bg-brand-lightgold transition-colors shadow-md">
                            <i class="fas fa-arrow-right text-xs text-brand-wine"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bottom Footer: Centered content on mobile -->
            <div class="border-t border-brand-gold/20 pt-6 md:pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-brand-cultured/60 font-light text-center md:text-left">
                <p class="mb-4 md:mb-0">&copy; <?php echo date("Y"); ?> Narayan Jewelers. All rights reserved.</p>
                <div class="flex space-x-4 md:space-x-6 justify-center">
                    <a href="#" class="hover:text-brand-lightgold transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-brand-lightgold transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const reveals = document.querySelectorAll(".reveal");
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("active");
                    }
                });
            }, {
                threshold: 0.15,
                rootMargin: "0px 0px -50px 0px"
            });

            reveals.forEach((element) => observer.observe(element));
        });
    </script>
</body>
</html>