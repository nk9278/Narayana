<?php
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/sidebar.php';

$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();
?>

<main class="flex-1 overflow-y-auto bg-brand-cultured p-6 md:p-10">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-brand-wine">Saved Addresses</h1>
            <p class="text-brand-wine/70 mt-1 font-light">Manage your shipping and billing addresses.</p>
        </div>
        <button class="bg-brand-wine text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-brand-burgundy transition-colors shadow-md">
            <i class="fas fa-plus mr-2"></i>Add New Address
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($addresses)): ?>
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-brand-gold/20 shadow-sm">
                <div class="w-16 h-16 bg-brand-cultured rounded-full flex items-center justify-center mx-auto mb-4 text-brand-gold/50">
                    <i class="fas fa-map-marker-alt text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-brand-wine mb-2">No addresses saved</h3>
                <p class="text-brand-wine/70 text-sm font-light">Add an address for a faster checkout experience.</p>
            </div>
        <?php else: foreach ($addresses as $address): ?>
            <div class="bg-white rounded-2xl p-6 border <?php echo $address['is_default'] ? 'border-brand-gold shadow-md relative overflow-hidden' : 'border-gray-200 shadow-sm'; ?> flex flex-col h-full">
                <?php if ($address['is_default']): ?>
                    <div class="absolute top-0 right-0 bg-brand-gold text-brand-wine text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-bl-lg">
                        Default
                    </div>
                <?php endif; ?>

                <h3 class="font-bold text-brand-wine text-lg mb-2"><?php echo htmlspecialchars($address['city']); ?> Address</h3>

                <div class="text-sm text-brand-wine/70 font-light flex-1 space-y-1 mb-6">
                    <p><?php echo htmlspecialchars($address['address_line1']); ?></p>
                    <?php if (!empty($address['address_line2'])): ?>
                        <p><?php echo htmlspecialchars($address['address_line2']); ?></p>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['pincode']); ?></p>
                    <p><?php echo htmlspecialchars($address['country']); ?></p>
                </div>

                <div class="flex gap-3 mt-auto pt-4 border-t border-gray-100">
                    <button class="flex-1 text-center py-2 border border-brand-gold/30 rounded-lg text-sm font-medium text-brand-wine hover:bg-brand-cultured transition-colors">
                        Edit
                    </button>
                    <button class="w-10 flex items-center justify-center border border-red-200 rounded-lg text-red-500 hover:bg-red-50 transition-colors">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>