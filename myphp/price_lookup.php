<?php
// price_lookup.php
session_start();
include('db_connection.php');

// Si llega una búsqueda por AJAX
if (isset($_GET['q'])) {
    $query = trim($_GET['q']);

    // Preparar SQL para buscar productos que coincidan
    $stmt = $pdo->prepare("SELECT nombre_producto, precio 
                           FROM productos 
                           WHERE nombre_producto LIKE :query 
                           ORDER BY nombre_producto ASC 
                           LIMIT 50"); // limitar a 50 resultados
    $likeQuery = "%$query%";
    $stmt->bindParam(':query', $likeQuery);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lector de Precios</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f7f7f7; }
h1 { text-align: center; }
input[type=text] { width: 100%; padding: 12px; font-size: 16px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #007BFF; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
</style>
</head>
<body>
<h1>Lector de Precios</h1>
<input type="text" id="search" placeholder="Escribe el nombre del producto...">
<table id="results">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
        </tr>
    </thead>
    <tbody>
        <!-- Resultados aparecerán aquí -->
    </tbody>
</table>

<script>
const searchInput = document.getElementById('search');
const resultsBody = document.querySelector('#results tbody');

searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim();

    if (q.length === 0) {
        resultsBody.innerHTML = '';
        return;
    }

    fetch(`price_lookup.php?q=${encodeURIComponent(q)}`)
        .then(response => response.json())
        .then(data => {
            resultsBody.innerHTML = '';
            if (data.length === 0) {
                resultsBody.innerHTML = '<tr><td colspan="2">No se encontraron resultados</td></tr>';
                return;
            }
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${item.nombre_producto}</td><td>${item.precio}</td>`;
                resultsBody.appendChild(row);
            });
        })
        .catch(err => {
            console.error(err);
        });
});
</script>
</body>
</html>