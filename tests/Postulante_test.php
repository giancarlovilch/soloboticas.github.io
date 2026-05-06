<?php
/**
 * TEST DE MODELO: Postulante
 * Objetivo: Verificar que el Modelo puede extraer datos de la tabla real.
 */

require_once __DIR__ . '/../src/Models/Postulante.php';

echo "<h2>🧪 Test de Lectura: Tabla Postulante</h2>";

try {
    $modelo = new \Models\Postulante();
    $datos = $modelo->listarTodos();

    if (is_array($datos)) {
        echo "<p style='color: green;'>✅ <b>EXITO:</b> El modelo devolvió un arreglo.</p>";
        echo "<b>Registros encontrados:</b> " . count($datos) . "<br><br>";

        if (count($datos) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #eee;'><th>ID</th><th>Nombres</th><th>DNI</th></tr>";
            foreach ($datos as $p) {
                echo "<tr>";
                echo "<td>".$p['id_postulante']."</td>";
                echo "<td>".$p['nombres']." ".$p['apellidos']."</td>";
                echo "<td>".$p['num_documento']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<i>La tabla está vacía, pero la consulta funcionó.</i>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <b>ERROR EN EL MODELO:</b> " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='test_db.php'>Volver al test de DB</a></p>";