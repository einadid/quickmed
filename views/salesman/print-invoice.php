<?php
/**
 * Professional POS Invoice - Fixed Address Logic
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    die("Invalid Order ID");
}

// Get Order Details (Updated Query to fetch User Address)
$orderQuery = "SELECT o.*, 
               u.member_id, u.points as current_points, u.address as member_address, u.phone as member_phone,
               s.name as shop_name, s.location, s.phone as shop_phone
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
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");

// VAT & Totals
$vatRate = 0.05; // Default fallback
// Calculate based on saved totals to be accurate
$subtotal = $order['subtotal'];
$discount = $order['points_discount'];
$grandTotal = $order['total_amount'];
$vatAmount = $grandTotal - ($subtotal - $discount); // Back-calculate VAT

// --- ADDRESS LOGIC ---
$finalAddress = '';
$finalPhone = $order['customer_phone'];

// 1. Try Order Address first (if not generic 'POS Sale')
if (!empty($order['customer_address']) && $order['customer_address'] !== 'POS Sale') {
    $finalAddress = $order['customer_address'];
} 
// 2. If empty or 'POS Sale', try Member Address
elseif (!empty($order['member_address'])) {
    $finalAddress = $order['member_address'];
    // If order phone is empty, use member phone
    if (empty($finalPhone)) {
        $finalPhone = $order['member_phone'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $order['order_number'] ?></title>
    <style>
        @media print { 
            @page { margin: 0; }
            body { margin: 5px; } 
            .no-print { display: none !important; } 
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-width: 78mm;
            margin: 20px auto;
            color: #000;
            background: #fff;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .divider-solid { border-top: 1px solid #000; margin: 5px 0; }
        
        .info { font-size: 11px; }
        .info div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        
        .address-box {
            margin: 5px 0;
            padding: 5px;
            border: 1px solid #000;
            font-size: 11px;
            font-weight: bold;
            background: #eee; /* Visible on screen, usually ignored by thermal printers */
        }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin: 5px 0; }
        th { text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
        td { padding: 2px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals { margin-top: 5px; font-size: 11px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 5px; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        .barcode { font-family: 'Libre Barcode 39', cursive; font-size: 24px; margin-top: 5px; }
        
        .btn { padding: 8px 15px; background: #000; color: #fff; border: none; cursor: pointer; margin: 5px; border-radius: 4px; }
        .btn-outline { background: #fff; color: #000; border: 1px solid #000; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body onload="window.print()">

    <div class="header">
        <h2><?= htmlspecialchars($order['shop_name'] ?? 'QuickMed Pharmacy') ?></h2>
        <p><?= htmlspecialchars($order['location']) ?></p>
        <p>Phone: <?= htmlspecialchars($order['shop_phone']) ?></p>
    </div>

    <div class="divider-solid"></div>

    <div class="info">
        <div><span>Invoice:</span> <strong>#<?= $order['order_number'] ?></strong></div>
        <div><span>Date:</span> <span><?= date('d-M-y h:i A', strtotime($order['created_at'])) ?></span></div>
        <div><span>Customer:</span> <span><?= htmlspecialchars($order['customer_name']) ?></span></div>
        
        <?php if (!empty($order['member_id'])): ?>
        <div><span>Member ID:</span> <strong><?= htmlspecialchars($order['member_id']) ?></strong></div>
        <?php endif; ?>
    </div>

    <!-- ADDRESS SECTION -->
    <?php if (!empty($finalAddress)): ?>
    <div class="address-box">
        ADDRESS:<br>
        <?= nl2br(htmlspecialchars($finalAddress)) ?>
        <?php if (!empty($finalPhone)): ?>
        <br>Phone: <?= htmlspecialchars($finalPhone) ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%">Item</th>
                <th class="text-center" style="width: 15%">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right"><?= number_format($item['subtotal'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <div><span>Subtotal:</span> <span><?= number_format($subtotal, 2) ?></span></div>
        
        <?php if ($discount > 0): ?>
        <div><span>Discount:</span> <span>-<?= number_format($discount, 2) ?></span></div>
        <?php endif; ?>
        
        <div><span>VAT:</span> <span><?= number_format($vatAmount, 2) ?></span></div>

        <div class="grand-total">
            <div><span>TOTAL:</span> <span>৳<?= number_format($grandTotal, 2) ?></span></div>
        </div>
        
        <div style="text-align: center; margin-top: 5px;">
            (Paid via Cash)
        </div>
    </div>

    <?php if ($order['points_earned'] > 0): ?>
    <div class="divider"></div>
    <div style="text-align: center; font-weight: bold; font-size: 11px;">
        ⭐ Points Earned: +<?= $order['points_earned'] ?>
        <?php if(!empty($order['current_points'])): ?>
        <br>Current Balance: <?= $order['current_points'] ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Thank you!</p>
        <div class="barcode">*<?= $order['order_number'] ?>*</div>
    </div>

    <div style="text-align: center; margin-top: 20px;" class="no-print">
        <button onclick="window.print()" class="btn">🖨️ PRINT</button>
        <button onclick="window.close()" class="btn btn-outline">❌ CLOSE</button>
    </div>

</body>
</html>