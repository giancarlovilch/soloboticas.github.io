<?php
// Cargar el archivo JSON con respuestas
$jsonFile = 'json/respuestas.json';
$data = json_decode(file_get_contents($jsonFile), true);
$respuestas = $data['respuestas'];

// Inicializar conteos
$conteo = [];
$totalEncuestados = count($respuestas);

// Contar respuestas por pregunta
foreach ($respuestas as $persona) {
  foreach ($persona['respuestas'] as $index => $respuesta) {
    $pregunta = $index + 1;
    if (!isset($conteo[$pregunta])) {
      $conteo[$pregunta] = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
    }
    $conteo[$pregunta][$respuesta]++;
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resultados Estadísticos - Encuesta SOLO BOTICAS</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f8fa;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #0b5394;
    }
    table {
      border-collapse: collapse;
      width: 90%;
      margin: 20px auto;
      background: white;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #ddd;
      text-align: center;
      padding: 10px;
    }
    th {
      background-color: #0b5394;
      color: white;
    }
    tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    .highlight {
      background-color: #dff0d8;
    }
  </style>
</head>
<body>
  <h1>Resultados Estadísticos de la Encuesta - SOLO BOTICAS</h1>
  <p style="text-align:center;">Total de encuestados: <strong><?php echo $totalEncuestados; ?></strong></p>

  <table>
    <tr>
      <th>Pregunta</th>
      <th>A (%)</th>
      <th>B (%)</th>
      <th>C (%)</th>
      <th>D (%)</th>
      <th>Opción más frecuente</th>
    </tr>

    <?php foreach ($conteo as $pregunta => $opciones): 
      $total = array_sum($opciones);
      $porcentajes = [];
      foreach ($opciones as $letra => $valor) {
        $porcentajes[$letra] = $total > 0 ? round(($valor / $total) * 100, 1) : 0;
      }
      $masFrecuente = array_search(max($opciones), $opciones);
    ?>
      <tr class="highlight">
        <td><?php echo $pregunta; ?></td>
        <td><?php echo $porcentajes['A']; ?>%</td>
        <td><?php echo $porcentajes['B']; ?>%</td>
        <td><?php echo $porcentajes['C']; ?>%</td>
        <td><?php echo $porcentajes['D']; ?>%</td>
        <td><strong><?php echo $masFrecuente; ?></strong></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
