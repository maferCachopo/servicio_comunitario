<?php

// Test script to verify the API endpoints are working correctly

echo "Testing API endpoints...\n\n";

// Test 1: Check if server is running
$ch = curl_init('http://127.0.0.1:8000/api/partituras-disponibles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. Testing /api/partituras-disponibles (without auth):\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test 2: Check database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sistema_inventario', 'root', '');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventarios WHERE instrumento IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "2. Database test:\n";
    echo "Inventarios with instrumento: " . $result['count'] . "\n\n";
    
    // Test 3: Check inventarios data
    $stmt = $pdo->query("SELECT id, partitura_id, instrumento, cantidad_disponible FROM inventarios LIMIT 5");
    $inventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "3. Sample inventarios data:\n";
    foreach ($inventarios as $inv) {
        echo "ID: {$inv['id']}, Partitura: {$inv['partitura_id']}, Instrumento: {$inv['instrumento']}, Disponible: {$inv['cantidad_disponible']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";