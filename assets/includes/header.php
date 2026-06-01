<?php
// Hardened Security: Default Headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com;");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'Narayan Jewelers | Premium Collection'); ?></title>
    <?php if (isset($page_description)): ?>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title ?? 'Narayan Jewelers'); ?>">
    <meta property="og:type" content="website">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            cultured: '#F2F4F3',
                            wine: '#65202F',
                            burgundy: '#49111C',
                            gold: '#D6B36A',
                            lightgold: '#E3C98B'
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body { font-family: 'Inter', sans-serif; background-color: #F2F4F3; color: #65202F; }
        .glass-dark { background: rgba(101, 32, 47, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid rgba(214, 179, 106, 0.2); }
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.9s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .floating-card { transition: transform 0.4s ease, box-shadow 0.4s ease; }
        .floating-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -10px rgba(101, 32, 47, 0.15), 0 10px 15px -5px rgba(101, 32, 47, 0.1); }
    </style>
</head>
<body class="antialiased selection:bg-brand-gold selection:text-brand-wine">

    <header class="fixed w-full top-0 z-50 glass-dark transition-all duration-300">
        <div class="px-4 sm:px-6 lg:px-8 py-2.5 md:py-3 flex items-center justify-between relative">

            <button id="mobile-menu-btn" class="lg:hidden text-brand-gold hover:text-brand-cultured transition-colors mr-2 sm:mr-4 focus:outline-none">
                <i class="fas fa-bars text-lg sm:text-xl"></i>
            </button>

            <a href="/" class="flex-shrink-0 flex items-center lg:mr-auto">
                <img src="/assets/logo.png" alt="Narayan Jewelers" class="h-6 md:h-8 w-auto object-contain drop-shadow-md">
            </a>

            <nav class="hidden lg:flex items-center space-x-6 xl:space-x-8 text-[13px] xl:text-sm font-medium text-brand-gold/90">
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Rings</a>
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Earrings</a>
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Bracelets</a>
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Necklaces</a>
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Solitaires</a>
                <a href="#" class="hover:text-brand-cultured hover:-translate-y-0.5 transition-all">Mangalsutras</a>
            </nav>

            <div class="hidden md:flex flex-1 max-w-[220px] lg:max-w-[260px] ml-auto relative group mr-4 lg:mr-5 xl:mr-8">
                <input type="text" placeholder="Search..."
                       class="w-full bg-white/10 border border-transparent rounded-full py-1.5 px-5 pl-10 text-sm text-brand-cultured focus:bg-white/20 focus:border-brand-gold focus:outline-none focus:ring-2 focus:ring-brand-gold/50 shadow-sm transition-all duration-300 placeholder-brand-cultured/60">
                <i class="fas fa-search absolute left-4 top-2 text-brand-gold group-focus-within:text-brand-cultured transition-colors text-sm"></i>
            </div>

            <div class="flex-shrink-0 flex items-center space-x-3 sm:space-x-4 lg:space-x-5 text-brand-gold ml-auto lg:ml-0">
                <button class="hover:text-brand-cultured transition-colors"><i class="far fa-user text-[17px] md:text-lg"></i></button>
                <button class="hover:text-brand-cultured transition-colors hidden sm:block"><i class="far fa-heart text-[17px] md:text-lg"></i></button>
                <button class="hover:text-brand-cultured transition-colors relative">
                    <i class="fas fa-shopping-bag text-[17px] md:text-lg"></i>
                    <span class="absolute -top-1.5 -right-2 bg-brand-burgundy text-white text-[9px] md:text-[10px] font-bold h-3.5 w-3.5 md:h-4 md:w-4 rounded-full flex items-center justify-center ring-2 ring-brand-wine">0</span>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden bg-brand-wine border-t border-brand-gold/20 absolute w-full left-0 top-full shadow-2xl transition-all duration-300 origin-top h-screen overflow-y-auto pb-32 z-50">
            <div class="px-4 py-6 space-y-5">
                <div class="md:hidden relative w-full mb-4">
                    <input type="text" placeholder="Search collections..."
                           class="w-full bg-white/10 border border-brand-gold/30 rounded-full py-3 px-5 pl-11 text-sm text-white focus:bg-white/20 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold/50 shadow-sm transition-all duration-300 placeholder-white/70">
                    <i class="fas fa-search absolute left-4 top-3.5 text-brand-gold text-sm"></i>
                </div>

                <nav class="flex flex-col space-y-4 text-[15px] font-medium text-white">
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Rings <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Earrings <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Bracelets <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Necklaces <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Solitaires <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>
                    <a href="#" class="hover:text-brand-gold hover:pl-2 transition-all flex items-center justify-between border-b border-brand-gold/10 pb-3">Mangalsutras <i class="fas fa-chevron-right text-[10px] opacity-50"></i></a>

                    <a href="#" class="sm:hidden hover:text-brand-gold hover:pl-2 transition-all flex items-center gap-3 pt-2">
                        <i class="far fa-heart"></i> My Wishlist
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <div class="h-[60px] md:h-[72px]"></div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const btn = document.getElementById("mobile-menu-btn");
            const menu = document.getElementById("mobile-menu");
            const icon = btn.querySelector("i");

            btn.addEventListener("click", () => {
                menu.classList.toggle("hidden");
                // Toggle body scrolling to prevent moving the background while the menu is open
                if (menu.classList.contains("hidden")) {
                    icon.classList.replace("fa-times", "fa-bars");
                    document.body.style.overflow = '';
                } else {
                    icon.classList.replace("fa-bars", "fa-times");
                    document.body.style.overflow = 'hidden';
                }
            });
        });
    </script>