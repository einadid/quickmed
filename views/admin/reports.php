<?php
/**
 * Admin Reports - Overview
 */
require_once __DIR__ . '/../../config.php';
requireLogin();
requireRole('admin');

$pageTitle = 'System Reports';
include __DIR__ . '/../../includes/header.php';
?>

<section class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-deep-green mb-8">📊 System Reports</h1>
    
    <div class="grid md:grid-cols-2 gap-6">
        <a href="analytics.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center">
            <h2 class="text-2xl font-bold">📈 Sales Analytics</h2>
            <p>View detailed sales charts</p>
        </a>
        <a href="profit-report.php" class="card bg-white border-4 border-deep-green hover:bg-light-green p-8 text-center">
            <h2 class="text-2xl font-bold">💰 Profit Report</h2>
            <p>Check net profit/loss</p>
        </a>
    </div>
</section>

<?php include __DIR__ . '/../../includes/footer.php'; ?>