<!-- User Dashboard Sidebar -->
<aside class="w-full md:w-64 bg-white border-r border-brand-gold/20 flex flex-col h-full flex-shrink-0">
    <div class="p-6 border-b border-brand-gold/10 flex items-center justify-between">
        <h2 class="text-xl font-bold text-brand-wine">My Account</h2>
        <button class="md:hidden text-brand-wine focus:outline-none">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-4">
        <a href="/user-dashboard/index.php" class="flex items-center px-4 py-3 bg-brand-wine text-white rounded-xl shadow-md transition-colors font-medium">
            <i class="fas fa-th-large w-6 text-brand-gold"></i> Dashboard
        </a>
        <a href="#" class="flex items-center px-4 py-3 text-brand-wine hover:bg-brand-cultured rounded-xl transition-colors font-medium">
            <i class="fas fa-shopping-bag w-6 text-brand-gold/70"></i> My Orders
        </a>
        <a href="#" class="flex items-center px-4 py-3 text-brand-wine hover:bg-brand-cultured rounded-xl transition-colors font-medium">
            <i class="far fa-heart w-6 text-brand-gold/70"></i> Wishlist
        </a>
        <a href="#" class="flex items-center px-4 py-3 text-brand-wine hover:bg-brand-cultured rounded-xl transition-colors font-medium">
            <i class="fas fa-piggy-bank w-6 text-brand-gold/70"></i> Savings Schemes
        </a>
        <a href="#" class="flex items-center px-4 py-3 text-brand-wine hover:bg-brand-cultured rounded-xl transition-colors font-medium">
            <i class="far fa-address-card w-6 text-brand-gold/70"></i> Addresses
        </a>
        <a href="#" class="flex items-center px-4 py-3 text-brand-wine hover:bg-brand-cultured rounded-xl transition-colors font-medium">
            <i class="far fa-user w-6 text-brand-gold/70"></i> Profile Details
        </a>
    </nav>

    <div class="p-4 border-t border-brand-gold/10">
        <a href="/logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors font-medium">
            <i class="fas fa-sign-out-alt w-6"></i> Logout
        </a>
    </div>
</aside>
