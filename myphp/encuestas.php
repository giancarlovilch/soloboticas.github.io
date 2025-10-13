<?php
// Cargar el contenido del JSON
$jsonFile = 'json/encuesta.json';
$data = json_decode(file_get_contents($jsonFile), true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo $data['titulo']; ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f8fa;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #0b5394;
    }
    p.description {
      text-align: center;
      color: #444;
      margin-bottom: 30px;
    }
    .question {
      margin-bottom: 25px;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    .question h3 {
      font-size: 16px;
      color: #222;
    }
    label {
      display: block;
      margin-left: 20px;
      margin-bottom: 5px;
      color: #333;
    }
    button {
      display: block;
      margin: 30px auto;
      padding: 10px 30px;
      background-color: #0b5394;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover {
      background-color: #083d70;
    }
  </style>
</head>
<body>

<div class="container">
  <h1><?php echo $data['titulo']; ?></h1>
  <p class="description"><?php echo $data['descripcion']; ?></p>

  <form method="POST" action="#">
    <?php foreach ($data['preguntas'] as $pregunta): ?>
      <div class="question">
        <h3><?php echo $pregunta['id'] . '. ' . $pregunta['texto']; ?></h3>
        <?php foreach ($pregunta['opciones'] as $index => $opcion): ?>
          <label>
            <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" value="<?php echo chr(65 + $index); ?>">
            <?php echo chr(65 + $index) . ') ' . $opcion; ?>
          </label>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <button type="submit">Enviar respuestas</button>
  </form>
</div>

</body>
</html>
