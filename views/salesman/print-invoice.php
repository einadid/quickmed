<?php
/**
 * Compact POS Invoice - Thermal Printer Friendly
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    echo "Invalid Order ID";
    exit;
}

// Get order details
$orderQuery = "SELECT o.*, p.parcel_number, s.name as shop_name, s.location, s.phone as shop_phone
               FROM orders o
               LEFT JOIN parcels p ON o.id = p.order_id
               LEFT JOIN shops s ON p.shop_id = s.id
               WHERE o.id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found";
    exit;
}

// Get items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?= $order['order_number'] ?></title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 58mm; /* Standard Thermal Paper Width */
            margin: 0 auto;
            background: #fff;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 { margin: 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        
        .details { font-size: 10px; margin-bottom: 5px; }
        .details div { display: flex; justify-content: space-between; }
        
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
        td { padding: 2px 0; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals { margin-top: 5px; font-size: 11px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .bold { font-weight: bold; }
        
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
        
        .actions {
            margin-top: 20px;
            text-align: center;
            padding-bottom: 20px;
        }
        button {
            padding: 8px 16px;
            cursor: pointer;
            background: #000;
            color: #fff;
            border: none;
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>QUICKMED</h2>
        <p><?= htmlspecialchars($order['shop_name']) ?></p>
        <p><?= htmlspecialchars($order['location']) ?></p>
        <p>Tel: <?= htmlspecialchars($order['shop_phone']) ?></p>
    </div>

    <div class="divider"></div>

    <div class="details">
        <div><span>Order:</span> <span>#<?= $order['order_number'] ?></span></div>
        <div><span>Date:</span> <span><?= date('d-m-y H:i') ?></span></div>
        <div><span>Customer:</span> <span><?= htmlspecialchars($order['customer_name']) ?></span></div>
        <?php if($order['member_id']): ?>
        <div><span>Member ID:</span> <span><?= htmlspecialchars($order['member_id']) ?></span></div>
        <?php endif; ?>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%">Item</th>
                <th class="text-center" style="width: 15%">Qty</th>
                <th class="text-right" style="width: 35%">Total</th>
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
        <div>
            <span>Subtotal:</span>
            <span><?= number_format($order['subtotal'], 2) ?></span>
        </div>
        
        <?php if ($order['points_discount'] > 0): ?>
        <div>
            <span>Discount:</span>
            <span>-<?= number_format($order['points_discount'], 2) ?></span>
        </div>
        <?php endif; ?>

        <div class="bold" style="font-size: 14px; margin-top: 5px;">
            <span>TOTAL:</span>
            <span><?= number_format($order['total_amount'], 2) ?></span>
        </div>
        
        <div style="margin-top: 5px; font-size: 10px;">
            <span>Paid (Cash):</span>
            <span><?= number_format($order['total_amount'], 2) ?></span>
        </div>
    </div>

    <?php if ($order['points_earned'] > 0): ?>
    <div class="divider"></div>
    <div style="text-align: center; font-weight: bold;">
        ⭐ You earned <?= $order['points_earned'] ?> points!
    </div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>Please visit again.</p>
        <p>www.quickmed.com</p>
    </div>

    <div class="actions no-print">
        <button onclick="window.print()">PRINT</button>
        <button onclick="window.close()" style="background: #ccc; color: #000; margin-left: 10px;">CLOSE</button>
    </div>

</body>
</html>