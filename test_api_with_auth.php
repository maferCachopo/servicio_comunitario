<?php

// Test script to verify the API endpoints work with authentication

echo "Testing API with authentication...\n\n";

// Create a simple test user session (simulate login)
$testUserId = 1; // Assuming user with ID 1 exists

// Test the API endpoint with a simulated authenticated request
$ch = curl_init('http://127.0.0.1:8000/api/partituras-disponibles');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Cookie: laravel_session=' . uniqid() // Simulate session
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. Testing /api/partituras-disponibles (with simulated auth):\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test database directly
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sistema_inventario', 'root', '');
    
    // Check inventarios with instrumento data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inventarios WHERE instrumento IS NOT NULL AND instrumento != ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "2. Inventarios with instrumento data: " . $result['count'] . "\n\n";
    
    // Show sample data
    $stmt = $pdo->query("SELECT id, partitura_id, instrumento, cantidad_disponible FROM inventarios WHERE instrumento IS NOT NULL AND instrumento != '' LIMIT 5");
    $inventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "3. Sample inventarios with instruments:\n";
    foreach ($inventarios as $inv) {
        echo "ID: {$inv['id']}, Partitura: {$inv['partitura_id']}, Instrumento: {$inv['instrumento']}, Disponible: {$inv['cantidad_disponible']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";