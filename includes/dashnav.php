<?php
/**
 * QuickMed — Unified Dashboard Header + Role Tab Navigation (v2.0)
 * ------------------------------------------------------------------
 * Usage (at the top of any dashboard/sub page, AFTER header include):
 *
 *     $dashTitle    = 'Master Control';
 *     $dashSubtitle = 'Admin Panel & System Overview';
 *     $dashIcon     = '👑';
 *     include __DIR__ . '/../../includes/dashnav.php';
 *     // ... then open your own <section class="container ..."> ...
 *
 * The tab links are automatic per role. Active tab is highlighted.
 */

if (!isset($currentUser) || !$currentUser) {
    $currentUser = function_exists('getCurrentUser') ? getCurrentUser() : null;
}
$__role = $currentUser['role_name'] ?? ($_SESSION['role_name'] ?? 'customer');
$__here = basename($_SERVER['PHP_SELF'] ?? '');

$__tabs = [
    'admin' => [
        ['dashboard.php', '📊 Dashboard'],
        ['medicines.php', '💊 Medicines'],
        ['shops.php', '🏪 Shops'],
        ['users.php', '👥 Users'],
        ['codes.php', '🎫 Codes'],
        ['prescriptions.php', '📋 Prescriptions'],
        ['flash-sales.php', '⚡ Flash Sales'],
        ['news.php', '📰 News'],
        ['messages.php', '💬 Messages'],
        ['reviews.php', '⭐ Reviews'],
        ['reports.php', '📑 Reports'],
        ['audit-logs.php', '🛡️ Audit Logs'],
    ],
    'customer' => [
        ['__dash__', '📊 Dashboard'],
        ['__shop__', '🛍️ Shop'],
        ['__orders__', '📦 My Orders'],
        ['__rx__', '📋 Prescriptions'],
        ['__profile__', '👤 Profile'],
    ],
    'doctor' => [
        ['dashboard.php', '📊 Dashboard'],
        ['prescriptions.php', '📋 Prescriptions'],
        ['my-posts.php', '📝 My Posts'],
        ['create-post.php', '➕ New Post'],
        ['add-news.php', '📰 Add News'],
    ],
    'salesman' => [
        ['dashboard.php', '📊 Dashboard'],
        ['pos.php', '🧾 POS Sale'],
        ['parcels.php', '📦 Parcels'],
        ['online-orders.php', '🌐 Online Orders'],
        ['prescriptions.php', '📋 Prescriptions'],
        ['reports.php', '📑 Reports'],
    ],
    'shop_manager' => [
        ['dashboard.php', '📊 Dashboard'],
        ['inventory.php', '📦 Inventory'],
        ['parcels.php', '🧾 Parcels'],
        ['online-orders.php', '🌐 Online Orders'],
        ['stock-alert.php', '⚠️ Stock Alert'],
        ['reports.php', '📑 Reports'],
    ],
];

$__list = $__tabs[$__role] ?? $__tabs['customer'];

function __dash_url($role, $file) {
    if ($file === '__dash__')    return SITE_URL . '/views/customer/dashboard.php';
    if ($file === '__shop__')    return SITE_URL . '/shop.php';
    if ($file === '__orders__')  return SITE_URL . '/my-orders.php';
    if ($file === '__rx__')      return SITE_URL . '/prescription-upload.php';
    if ($file === '__profile__') return SITE_URL . '/profile.php';
    return SITE_URL . '/views/' . $role . '/' . $file;
}

$__title = $dashTitle ?? 'Dashboard';
$__sub = $dashSubtitle ?? '';
$__icon = $dashIcon ?? '📊';
?>
<section class="page-hero">
    <div class="page-hero-inner" style="padding-top:2.25rem;padding-bottom:1.5rem">
        <?php if ($__role !== '' && $__role !== 'customer'): ?>
            <span class="hero-kicker"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $__role))) ?> Panel</span>
        <?php endif; ?>
        <h1 style="font-size:clamp(1.7rem,4vw,2.6rem)"><?= $__icon ?> <?= htmlspecialchars($__title) ?></h1>
        <?php if ($__sub !== ''): ?><p><?= htmlspecialchars($__sub) ?></p><?php endif; ?>
    </div>
</section>

<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="container mx-auto px-4 py-3 flex gap-2 overflow-x-auto whitespace-nowrap" style="scrollbar-width:thin">
        <?php foreach ($__list as [$__file, $__label]):
            $__url = __dash_url($__role, $__file);
            $__active = ($__file === $__here) ? ' active' : '';
            // Map customer special pages to active state
            if ($__role === 'customer') {
                $map = ['__dash__' => 'dashboard.php', '__shop__' => 'shop.php', '__orders__' => 'my-orders.php', '__rx__' => 'prescription-upload.php', '__profile__' => 'profile.php'];
                $__active = (($map[$__file] ?? '') === $__here) ? ' active' : '';
            }
        ?>
            <a href="<?= htmlspecialchars($__url) ?>" class="dash-tab<?= $__active ?>"><?= htmlspecialchars($__label) ?></a>
        <?php endforeach; ?>
    </div>
</div>
