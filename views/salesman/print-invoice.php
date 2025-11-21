<?php
/**
 * Professional POS Invoice - VAT & Member Info Included
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    die("Invalid Order ID");
}

// Get Order Details
$orderQuery = "SELECT o.*, u.member_id, u.points as current_points, 
               s.name as shop_name, s.location, s.phone as shop_phone, s.email as shop_email
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               LEFT JOIN parcels p ON o.id = p.order_id
               LEFT JOIN shops s ON p.shop_id = s.id
               WHERE o.id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Get Items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result();

// VAT Calculation (5%)
$vatRate = 0.05;
$subtotal = $order['subtotal'];
$discount = $order['points_discount'];
$vatAmount = ($subtotal - $discount) * $vatRate;
$grandTotal = ($subtotal - $discount) + $vatAmount;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['order_number'] ?></title>
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 10px; }
            .no-print { display: none !important; }
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-width: 80mm; /* Standard POS Paper Width */
            margin: 20px auto;
            background: #fff;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; }
        
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .divider-solid { border-top: 1px solid #000; margin: 8px 0; }
        
        .info { font-size: 11px; margin-bottom: 5px; }
        .info div { display: flex; justify-content: space-between; }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { text-align: left; padding: 4px 0; border-bottom: 1px solid #000; }
        td { padding: 4px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals { margin-top: 5px; font-size: 12px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        .barcode { text-align: center; margin-top: 10px; font-family: 'Libre Barcode 39', cursive; font-size: 30px; }
        
        /* Print Button Style */
        .action-bar {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            background: rgba(255,255,255,0.9);
            padding: 10px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            margin: 0 5px;
            border-radius: 4px;
        }
        .btn-outline { background: #fff; color: #000; border: 2px solid #000; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h2><?= htmlspecialchars($order['shop_name'] ?? 'QuickMed Pharmacy') ?></h2>
        <p><?= htmlspecialchars($order['location'] ?? 'Dhaka, Bangladesh') ?></p>
        <p>Phone: <?= htmlspecialchars($order['shop_phone'] ?? '09678-100100') ?></p>
        <p>Email: <?= htmlspecialchars($order['shop_email'] ?? 'support@quickmed.com') ?></p>
    </div>

    <div class="divider-solid"></div>

    <!-- Order Info -->
    <div class="info">
        <div><span>Invoice:</span> <strong>#<?= $order['order_number'] ?></strong></div>
        <div><span>Date:</span> <span><?= date('d-M-Y h:i A', strtotime($order['created_at'])) ?></span></div>
        
        <!-- Customer / Member Info -->
        <div style="margin-top: 5px;">
            <span>Customer:</span> 
            <span><?= htmlspecialchars($order['customer_name']) ?></span>
        </div>
        
        <?php if (!empty($order['member_id'])): ?>
        <div><span>Member ID:</span> <strong><?= htmlspecialchars($order['member_id']) ?></strong></div>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 45%">Item</th>
                <th class="text-center" style="width: 15%">Qty</th>
                <th class="text-right" style="width: 20%">Price</th>
                <th class="text-right" style="width: 20%">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right"><?= number_format($item['price'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Calculations -->
    <div class="totals">
        <div>
            <span>Subtotal:</span>
            <span><?= number_format($subtotal, 2) ?></span>
        </div>
        
        <?php if ($discount > 0): ?>
        <div>
            <span>Points Discount:</span>
            <span>-<?= number_format($discount, 2) ?></span>
        </div>
        <?php endif; ?>

        <div>
            <span>VAT (5%):</span>
            <span><?= number_format($vatAmount, 2) ?></span>
        </div>

        <div class="grand-total">
            <span>GRAND TOTAL:</span>
            <span>৳<?= number_format($grandTotal, 2) ?></span>
        </div>
        
        <div>
            <span>Paid (Cash):</span>
            <span>৳<?= number_format($grandTotal, 2) ?></span>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Member Points Summary -->
    <?php if ($order['points_earned'] > 0 || !empty($order['member_id'])): ?>
    <div class="info" style="text-align: center;">
        <?php if ($order['points_earned'] > 0): ?>
            <p>⭐ Points Earned: <strong>+<?= $order['points_earned'] ?></strong></p>
        <?php endif; ?>
        
        <?php if (!empty($order['current_points'])): ?>
            <p>Current Balance: <strong><?= $order['current_points'] ?> Pts</strong></p>
        <?php endif; ?>
    </div>
    <div class="divider"></div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>Goods sold are not returnable without receipt.</p>
        <p>Thank you for shopping with us!</p>
        <div class="barcode">*<?= $order['order_number'] ?>*</div>
        <p>www.quickmed.com</p>
    </div>

    <!-- Print Buttons -->
    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn">🖨️ PRINT</button>
        <button onclick="window.close()" class="btn btn-outline">CLOSE</button>
    </div>

</body>
</html>