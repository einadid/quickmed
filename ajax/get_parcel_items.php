<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) exit;

$parcelId = intval($_GET['id']);
$query = "SELECT medicine_id, medicine_name as name, price, quantity FROM order_items WHERE parcel_id = $parcelId";
$result = $conn->query($query);

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
?>