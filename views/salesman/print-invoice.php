<?php
/**
 * POS - Print Invoice (Standalone Page)
 */

require_once __DIR__ . '/../../config.php';

requireLogin();
requireRole('salesman');

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    $_SESSION['error'] = 'Invalid order';
    redirect('pos.php');
}

// Get order details
$orderQuery = "SELECT o.*, p.parcel_number
               FROM orders o
               LEFT JOIN parcels p ON o.id = p.order_id
               WHERE o.id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = 'Order not found';
    redirect('pos.php');
}

// Get order items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $order['order_number'] ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            border: 4px solid #065f46;
            padding: 20px;
            margin-bottom: 20px;
            background: #ecfccb;
        }
        .invoice-details {
            border: 2px solid #065f46;
            padding: 15px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border: 4px solid #065f46;
        }
        th, td {
            border: 2px solid #065f46;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #065f46;
            color: white;
        }
        .total-row {
            background: #84cc16;
            font-weight: bold;
            font-size: 18px;
        }
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>QuickMed</h1>
        <p>Your Trusted Pharmacy</p>
        <p>📞 09678-100100 | 📧 support@quickmed.com</p>
    </div>

    <div class="invoice-details">
        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Order #:</strong> <?= htmlspecialchars($order['order_number']) ?><br>
                <strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
            </div>
            <div style="text-align: right;">
                <strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?><br>
                <?php if ($order['customer_phone']): ?>
                    <strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">৳<?= number_format($item['price'], 2) ?></td>
                    <td style="text-align: right;"><strong>৳<?= number_format($item['subtotal'], 2) ?></strong></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">GRAND TOTAL:</td>
                <td style="text-align: right;">৳<?= number_format($order['total_amount'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: center; margin-top: 30px; padding: 20px; border-top: 4px solid #065f46;">
        <p><strong>Thank you for shopping with QuickMed!</strong></p>
        <p style="font-size: 12px;">This is a computer-generated invoice</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="background: #065f46; color: white; padding: 15px 30px; border: 4px solid #84cc16; font-weight: bold; font-size: 16px; cursor: pointer;">
            🖨️ PRINT INVOICE
        </button>
        <button onclick="window.close()" style="background: #6b7280; color: white; padding: 15px 30px; border: 4px solid #374151; font-weight: bold; font-size: 16px; cursor: pointer; margin-left: 10px;">
            ✕ CLOSE
        </button>
    </div>
</body>
</html>