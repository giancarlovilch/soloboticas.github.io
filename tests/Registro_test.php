<?php
/**
 * TEST: Registro ciego por DNI
 * Objetivo: Validar que el sistema cree el perfil completo solo con números.
 */

require_once __DIR__ . '/../src/Models/Registro.php';

echo "<h2>🧪 Test: Registro por DNI (Sin Nombres)</h2>";

$modelo = new \Models\RegistroPostulante();

// Simulamos lo que enviará el formulario de puros desplegables
$data = [
    'dni'       => '88776655', // Cambia este número para cada test
    'genero_id' => 2,          // Femenino (por ejemplo)
    'situacion_id' => 1,       // Propia
    'puesto_id' => 1           // ID del puesto elegido del combo
];

$res = $modelo->registrarNuevo($data);

if ($res['success']) {
    echo "<p style='color:green;'>✅ Éxito: Postulante ".$data['dni']." registrado y puesto en cola 'Pendiente'.</p>";
} else {
    echo "<p style='color:red;'>❌ Error: " . $res['error'] . "</p>";
}