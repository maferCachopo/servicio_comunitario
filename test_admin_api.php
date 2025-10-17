<?php

// Test script to verify the admin API endpoints are working

echo "Testing Admin API endpoints...\n\n";

// Test the partituras data endpoint
$ch = curl_init('http://127.0.0.1:8000/inventory/partituras-data?draw=1&columns%5B0%5D%5Bdata%5D=titulo&start=0&length=10');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'Cookie: laravel_session=' . uniqid() // Simulate session
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. Testing /inventory/partituras-data:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test database directly to see current state
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=sistema_inventario', 'root', '');
    
    // Check obras with partituras and inventarios
    $stmt = $pdo->query("
        SELECT o.id, o.titulo, o.anio, COUNT(p.id) as partituras_count, COUNT(i.id) as inventarios_count
        FROM obras o
        LEFT JOIN partituras p ON o.id = p.obra_id
        LEFT JOIN inventarios i ON p.id = i.partitura_id
        GROUP BY o.id
        ORDER BY o.id
        LIMIT 5
    ");
    $obras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "2. Obras con sus partituras e inventarios:\n";
    foreach ($obras as $obra) {
        echo "ID: {$obra['id']}, Título: {$obra['titulo']}, Año: {$obra['anio']}, Partituras: {$obra['partituras_count']}, Inventarios: {$obra['inventarios_count']}\n";
    }
    
    echo "\n3. Inventarios con instrumentos:\n";
    $stmt = $pdo->query("SELECT id, partitura_id, instrumento, cantidad, cantidad_disponible FROM inventarios WHERE instrumento IS NOT NULL AND instrumento != ''");
    $inventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($inventarios as $inv) {
        echo "ID: {$inv['id']}, Partitura: {$inv['partitura_id']}, Instrumento: {$inv['instrumento']}, Total: {$inv['cantidad']}, Disponible: {$inv['cantidad_disponible']}\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";