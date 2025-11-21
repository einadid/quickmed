<?php
/**
 * Live Search for Medicines - FIXED
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Clean the query
$query = clean($query);

// Search medicines with shop info
$searchQuery = "SELECT DISTINCT m.id, m.name, m.generic_name, m.power, m.form, m.image,
                sm.price, sm.stock_quantity, sm.shop_id,
                s.name as shop_name, s.city
                FROM medicines m
                JOIN shop_medicines sm ON m.id = sm.medicine_id
                JOIN shops s ON sm.shop_id = s.id
                WHERE (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand LIKE ?)
                AND sm.stock_quantity > 0
                AND s.is_active = 1
                ORDER BY m.name ASC
                LIMIT 10";

$searchTerm = "%$query%";
$stmt = $conn->prepare($searchQuery);

if ($stmt === false) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$medicines = [];
while ($row = $result->fetch_assoc()) {
    $medicines[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'generic_name' => $row['generic_name'],
        'power' => $row['power'],
        'form' => $row['form'],
        'image' => $row['image'],
        'price' => number_format($row['price'], 2),
        'stock' => (int)$row['stock_quantity'],
        'shop_id' => (int)$row['shop_id'],
        'shop_name' => $row['shop_name'],
        'city' => $row['city']
    ];
}

echo json_encode($medicines);