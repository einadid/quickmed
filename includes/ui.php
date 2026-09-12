<?php
/**
 * QuickMed — Shared UI Helpers (v2.0)
 * -------------------------------------------------
 * Small echo-helpers so EVERY page (public + dashboards)
 * renders the same hero / badges / empty states.
 * Auto-loaded from config.php. All functions are guarded
 * with function_exists() so double-includes are safe.
 */

if (!function_exists('qm_hero')) {
    /**
     * Consistent page hero band.
     * Usage: qm_hero('Shop Medicines', 'Genuine products...', 'SHOP', '🛍️');
     */
    function qm_hero($title, $subtitle = '', $kicker = '', $icon = '') {
        $iconHtml = $icon !== '' ? '<span class="mr-2">' . $icon . '</span>' : '';
        $kickerHtml = $kicker !== '' ? '<span class="hero-kicker">' . htmlspecialchars($kicker) . '</span>' : '';
        $subHtml = $subtitle !== '' ? '<p>' . htmlspecialchars($subtitle) . '</p>' : '';
        echo '<section class="page-hero"><div class="page-hero-inner">'
            . $kickerHtml
            . '<h1>' . $iconHtml . htmlspecialchars($title) . '</h1>'
            . $subHtml
            . '</div></section>';
    }
}

if (!function_exists('qm_section_head')) {
    /**
     * Centered section heading used on homepage & content pages.
     */
    function qm_section_head($title, $subtitle = '', $kicker = '') {
        $kickerHtml = $kicker !== '' ? '<span class="kicker">' . htmlspecialchars($kicker) . '</span>' : '';
        $subHtml = $subtitle !== '' ? '<p>' . htmlspecialchars($subtitle) . '</p>' : '';
        echo '<div class="section-head" data-aos="fade-up">'
            . $kickerHtml
            . '<h2>' . htmlspecialchars($title) . '</h2>'
            . $subHtml
            . '</div>';
    }
}

if (!function_exists('qm_badge')) {
    /**
     * Unified status badge. Returns HTML string.
     * Covers: parcel/order/rx/review/news status values.
     */
    function qm_badge($status) {
        $s = strtolower(trim((string)$status));
        $map = [
            // success
            'delivered' => ['badge-success', 'Delivered'],
            'approved' => ['badge-success', 'Approved'],
            'paid' => ['badge-success', 'Paid'],
            'published' => ['badge-success', 'Published'],
            'active' => ['badge-success', 'Active'],
            'in stock' => ['badge-success', 'In Stock'],
            'completed' => ['badge-success', 'Completed'],
            // warning
            'processing' => ['badge-warning', 'Processing'],
            'pending' => ['badge-warning', 'Pending'],
            'packed' => ['badge-warning', 'Packed'],
            'reviewed' => ['badge-info', 'Reviewed'],
            // danger
            'returned' => ['badge-danger', 'Returned'],
            'cancelled' => ['badge-danger', 'Cancelled'],
            'canceled' => ['badge-danger', 'Cancelled'],
            'rejected' => ['badge-danger', 'Rejected'],
            'out of stock' => ['badge-danger', 'Out of Stock'],
            'banned' => ['badge-danger', 'Banned'],
            // info
            'out_for_delivery' => ['badge-info', 'Out for Delivery'],
            'ready' => ['badge-info', 'Ready'],
            'shipped' => ['badge-info', 'Shipped'],
        ];
        if (isset($map[$s])) {
            return '<span class="badge ' . $map[$s][0] . '">' . $map[$s][1] . '</span>';
        }
        $label = ucwords(str_replace('_', ' ', $s));
        return '<span class="badge badge-neutral">' . htmlspecialchars($label) . '</span>';
    }
}

if (!function_exists('qm_empty')) {
    /**
     * Consistent empty-state block.
     */
    function qm_empty($title, $text = '', $btnLabel = '', $btnUrl = '') {
        $textHtml = $text !== '' ? '<p>' . htmlspecialchars($text) . '</p>' : '';
        $btnHtml = ($btnLabel !== '' && $btnUrl !== '')
            ? '<a href="' . htmlspecialchars($btnUrl) . '" class="btn btn-primary">' . htmlspecialchars($btnLabel) . '</a>'
            : '';
        echo '<div class="empty-state" data-aos="zoom-in">'
            . '<div class="empty-icon">📦</div>'
            . '<h3>' . htmlspecialchars($title) . '</h3>'
            . $textHtml . $btnHtml
            . '</div>';
    }
}

if (!function_exists('qm_back')) {
    /**
     * Consistent "back" button.
     */
    function qm_back($url, $label = '← Back') {
        return '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline btn-sm">' . htmlspecialchars($label) . '</a>';
    }
}

if (!function_exists('qm_money')) {
    /**
     * Consistent money formatting: ৳1,250.00
     */
    function qm_money($amount) {
        return '৳' . number_format((float)$amount, 2);
    }
}

if (!function_exists('qm_avatar')) {
    /**
     * Profile avatar image (or initial-letter fallback).
     */
    function qm_avatar($user, $size = 'w-16 h-16') {
        $name = $user['full_name'] ?? 'U';
        $initial = strtoupper(substr(trim($name) !== '' ? trim($name) : 'U', 0, 1));
        if (!empty($user['profile_image'])) {
            $src = SITE_URL . '/uploads/profiles/' . $user['profile_image'];
            return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($name) . '" class="' . $size . ' rounded-full object-cover border-4 border-white shadow-lg">';
        }
        return '<div class="' . $size . ' rounded-full bg-[#065f46] text-white flex items-center justify-center font-bold text-2xl border-4 border-white shadow-lg">' . $initial . '</div>';
    }
}

if (!function_exists('qm_stat')) {
    /**
     * Dashboard stat card.
     * Usage: qm_stat('📦', '12', 'Total Orders', 'lime');
     */
    function qm_stat($icon, $value, $label, $color = '') {
        echo '<div class="stat-card ' . htmlspecialchars($color) . '">'
            . '<div class="stat-icon">' . $icon . '</div>'
            . '<div><div class="stat-value">' . $value . '</div>'
            . '<div class="stat-label">' . htmlspecialchars($label) . '</div></div>'
            . '</div>';
    }
}
