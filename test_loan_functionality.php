<?php

// Test script to verify loan functionality
echo "=== Testing Loan Request Functionality ===\n\n";

// Test 1: Check if all required routes exist
echo "1. Testing Routes:\n";
$routes = [
    'GET /api/partituras-disponibles',
    'POST /api/solicitar-prestamo',
    'GET /api/mis-prestamos',
    'GET /api/prestamos-pendientes',
    'POST /api/procesar-prestamo/{id}'
];

foreach ($routes as $route) {
    echo "   - $route: ";
    // Check if route exists in routes/web.php
    $routeFile = file_get_contents('routes/web.php');
    $routePath = str_replace(['GET ', 'POST '], '', $route);
    
    // Extract the path part before any parameters
    $basePath = explode('{', $routePath)[0];
    $basePath = rtrim($basePath, '/');
    
    // Check for different variations of the route
    $routeExists = false;
    if (strpos($routeFile, "'" . $basePath . "'") !== false) {
        $routeExists = true;
    } elseif (strpos($routeFile, '"' . $basePath . '"') !== false) {
        $routeExists = true;
    } elseif (strpos($routeFile, $basePath) !== false) {
        $routeExists = true;
    }
    
    if ($routeExists) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 2: Check if controllers exist
echo "\n2. Testing Controllers:\n";
$controllers = [
    'app/Http/Controllers/LoanUserController.php',
    'app/Http/Controllers/InventoryController.php'
];

foreach ($controllers as $controller) {
    echo "   - $controller: ";
    if (file_exists($controller)) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 3: Check if models exist
echo "\n3. Testing Models:\n";
$models = [
    'app/Models/Prestamo.php',
    'app/Models/Partitura.php',
    'app/Models/Inventario.php'
];

foreach ($models as $model) {
    echo "   - $model: ";
    if (file_exists($model)) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 4: Check if views exist
echo "\n4. Testing Views:\n";
$views = [
    'resources/views/profile/show.blade.php',
    'resources/views/profile/partials/loan_request_modal.blade.php',
    'resources/views/inventory/partials/prestamos_table.blade.php'
];

foreach ($views as $view) {
    echo "   - $view: ";
    if (file_exists($view)) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 5: Check if JavaScript file exists
echo "\n5. Testing JavaScript:\n";
$jsFile = 'resources/js/loan-request.js';
echo "   - $jsFile: ";
if (file_exists($jsFile)) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
}

// Test 6: Check database migrations
echo "\n6. Testing Database Migrations:\n";
$migrations = [
    '2025_10_01_140222_create_prestamos_table.php',
    '2025_10_01_142700_update_prestamos_table_use_users.php'
];

foreach ($migrations as $migration) {
    echo "   - $migration: ";
    $files = glob('database/migrations/*' . $migration);
    if (count($files) > 0) {
        echo "✅ EXISTS\n";
    } else {
        echo "❌ MISSING\n";
    }
}

// Test 7: Check role-based access in profile view
echo "\n7. Testing Role-Based Access:\n";
$profileView = file_get_contents('resources/views/profile/show.blade.php');
$accessChecks = [
    'mafer2@example.com' => strpos($profileView, 'mafer2@example.com') !== false,
    'loan_user' => strpos($profileView, 'loan_user') !== false,
    'loan_user' => strpos($profileView, 'loan_user') !== false,
    'admin check' => strpos($profileView, 'Auth::user()->role != \'admin\'') !== false
];

foreach ($accessChecks as $check => $exists) {
    echo "   - $check: " . ($exists ? "✅ EXISTS\n" : "❌ MISSING\n");
}

// Test 8: Check API endpoints in controllers
echo "\n8. Testing API Endpoints:\n";
$loanController = file_get_contents('app/Http/Controllers/LoanUserController.php');
$inventoryController = file_get_contents('app/Http/Controllers/InventoryController.php');

$endpoints = [
    'partiturasDisponibles' => strpos($loanController, 'partiturasDisponibles') !== false,
    'solicitarPrestamo' => strpos($loanController, 'solicitarPrestamo') !== false,
    'misPrestamos' => strpos($loanController, 'misPrestamos') !== false,
    'prestamosPendientes' => strpos($inventoryController, 'prestamosPendientes') !== false,
    'procesarPrestamo' => strpos($inventoryController, 'procesarPrestamo') !== false
];

foreach ($endpoints as $endpoint => $exists) {
    echo "   - $endpoint: " . ($exists ? "✅ EXISTS\n" : "❌ MISSING\n");
}

echo "\n=== Test Summary ===\n";
echo "If all tests show ✅, the loan functionality is properly implemented!\n";
echo "If any show ❌, those components need to be created or fixed.\n";

?>