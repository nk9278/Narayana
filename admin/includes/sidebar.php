<!-- Sidebar Sidebar -->
<aside id="sidebar" class="bg-brand-wine w-64 h-screen flex-shrink-0 border-r border-brand-gold/20 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 absolute md:relative z-20">
    <div class="h-16 flex items-center justify-between px-6 border-b border-brand-gold/20">
        <span class="text-brand-gold font-bold tracking-wider uppercase text-sm">Admin Panel</span>
        <button id="close-sidebar" class="md:hidden text-brand-gold/70 hover:text-brand-gold">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <a href="/admin/index.php" class="bg-brand-burgundy text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg border border-brand-gold/10">
                <i class="fas fa-home w-6 text-center mr-2"></i> Dashboard
            </a>
            <a href="/admin/categories.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-tags w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Categories
            </a>
            <a href="/admin/products.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-box w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Products
            </a>
            <a href="/admin/orders.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-shopping-cart w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Orders
            </a>
            <a href="/admin/coupons.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-ticket-alt w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Coupons
            </a>
            <a href="/admin/schemes.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-piggy-bank w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Savings Schemes
            </a>
            <a href="/admin/gold-rates.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-chart-line w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Live Gold Rates
            </a>
            <a href="/admin/customers.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-users w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Customers
            </a>
            <a href="/admin/settings.php" class="text-white hover:bg-brand-burgundy hover:text-brand-gold group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-cog w-6 text-center mr-2 text-brand-gold/70 group-hover:text-brand-gold"></i> Settings
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-brand-gold/20">
        <a href="/admin/logout.php" class="flex items-center text-white hover:text-brand-gold transition-colors text-sm font-medium px-3 py-2">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>
</aside>

<!-- Main Content Area Wrapper -->
<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <!-- Top Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 z-10 shadow-sm">
        <button id="open-sidebar" class="md:hidden text-gray-500 hover:text-brand-wine focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <div class="flex-1 px-4 flex justify-end">
            <div class="flex items-center space-x-4">
                <button class="text-gray-400 hover:text-brand-wine relative">
                    <i class="far fa-bell text-xl"></i>
                    <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                </button>
                <div class="flex items-center">
                    <div class="h-8 w-8 rounded-full bg-brand-wine text-white flex items-center justify-center font-bold text-sm">A</div>
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">Admin</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Flow Starts Here -->
    <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">