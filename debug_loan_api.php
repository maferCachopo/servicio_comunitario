<?php

// Simple debug script to test loan API functionality
echo "=== Testing Loan API Functionality ===\n\n";

// Test 1: Check if controller methods exist
echo "1. Testing Controller Methods:\n";
$controllerFile = file_get_contents('app/Http/Controllers/LoanUserController.php');
$methods = ['partiturasDisponibles', 'solicitarPrestamo', 'misPrestamos'];

foreach ($methods as $method) {
    echo "   - $method: ";
    if (strpos($controllerFile, "public function $method") !== false) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 2: Check route registration
echo "\n2. Testing Route Registration:\n";
$routeFile = file_get_contents('routes/web.php');
$routes = [
    'api/partituras-disponibles',
    'api/solicitar-prestamo',
    'api/mis-prestamos'
];

foreach ($routes as $route) {
    echo "   - $route: ";
    if (strpos($routeFile, $route) !== false) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 3: Check for common issues
echo "\n3. Checking for Common Issues:\n";

// Check for proper imports
$imports = ['Partitura', 'Prestamo', 'Inventario', 'Auth'];
foreach ($imports as $import) {
    echo "   - Import $import: ";
    if (strpos($controllerFile, "use App\\Models\\$import") !== false || strpos($controllerFile, "use Illuminate\\Support\\Facades\\$import") !== false) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 4: Check model relationships
echo "\n4. Testing Model Relationships:\n";
$partituraFile = file_get_contents('app/Models/Partitura.php');
$prestamoFile = file_get_contents('app/Models/Prestamo.php');

echo "   - Partitura has autor relationship: ";
if (strpos($partituraFile, 'public function autor') !== false) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
}

echo "   - Partitura has inventarios relationship: ";
if (strpos($partituraFile, 'public function inventarios') !== false) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
}

echo "   - Prestamo has partitura relationship: ";
if (strpos($prestamoFile, 'public function partitura') !== false) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
}

echo "\n=== Debug Summary ===\n";
echo "If all tests show ✅, the API should be working correctly.\n";
echo "The 'Unauthenticated' response is expected when testing without login.\n";
echo "To test with authentication, log in first and then test the endpoints.\n";

?>