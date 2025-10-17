<?php

// Script to add test data for multiple instrumentations
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Inventario;

echo "=== Adding Test Data for Multiple Instrumentations ===\n\n";

try {
    // Add multiple instrumentations for existing scores
    // For partitura 1 (Sinfonía No. 9) - add Piano arrangement
    $pianoArrangement = Inventario::create([
        'partitura_id' => 1,
        'estante_id' => 1,
        'instrumento' => 'Piano',
        'cantidad' => 2,
        'cantidad_disponible' => 2,
        'estado' => 'disponible',
        'notas' => 'Arreglo para piano'
    ]);
    echo "✅ Added Piano arrangement for Sinfonía No. 9 (ID: {$pianoArrangement->id})\n";

    // For partitura 1 (Sinfonía No. 9) - add Violin arrangement  
    $violinArrangement = Inventario::create([
        'partitura_id' => 1,
        'estante_id' => 1,
        'instrumento' => 'Violín',
        'cantidad' => 3,
        'cantidad_disponible' => 3,
        'estado' => 'disponible',
        'notas' => 'Parte de violín'
    ]);
    echo "✅ Added Violín arrangement for Sinfonía No. 9 (ID: {$violinArrangement->id})\n";

    // For partitura 2 (Concierto para Piano) - add Orchestra score
    $orchestraScore = Inventario::create([
        'partitura_id' => 2,
        'estante_id' => 4,
        'instrumento' => 'Orquesta',
        'cantidad' => 1,
        'cantidad_disponible' => 1,
        'estado' => 'disponible',
        'notas' => 'Partitura orquestal completa'
    ]);
    echo "✅ Added Orquesta score for Concierto para Piano (ID: {$orchestraScore->id})\n";

    echo "\n=== Test Data Created Successfully! ===\n";
    echo "Now you should see multiple instrumentations for the same musical works:\n";
    echo "- Sinfonía No. 9: Orquesta, Piano, Violín\n";
    echo "- Concierto para Piano: Piano, Orquesta\n";

} catch (Exception $e) {
    echo "❌ Error creating test data: " . $e->getMessage() . "\n";
}