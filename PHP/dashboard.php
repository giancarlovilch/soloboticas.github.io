<?php
session_start();
include('db_connection.php');
include('session_manager.php');

// Verificar si el usuario está logeado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['nickname'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión activa
    exit();
}

// Obtener el nickname del usuario logeado
$nickname = $_SESSION['nickname'];

// Consultar el nombre completo del usuario
$stmt = $pdo->prepare("
    SELECT ip.nombre_completo 
    FROM usuarios u
    LEFT JOIN informacion_personal ip ON u.nickname = ip.nickname
    WHERE u.nickname = :nickname
");
$stmt->bindParam(':nickname', $nickname);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Establecer el nombre completo si está disponible
$nombreCompleto = $userInfo ? $userInfo['nombre_completo'] : "Información no disponible";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRUPO KGyR S.A.C</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Krub:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preload" href="../../css/normalize.css" as="style">
    <link href="../../css/normalize.css" rel="stylesheet">
    <link rel="preload" href="../css/dashboard.css" as="style">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="icon" type="image/x-icon" href="../../img_1/SB007.ico">
    <meta property="og:description" content="Salud y cuidado de familia a familia. ¡Bienvenidos a SoloBoticas!" />
</head>

<body>
    <header>
        <h1 class="titulo">Grupo KGyR S.A.C
            <br>
            <span>Solo Boticas</span>
        </h1>
        <p class="user-info">
            Bienvenido, <?php echo htmlspecialchars($nickname); ?><br>
            <?php echo htmlspecialchars($nombreCompleto); ?>
        </p>        
    </header>
    <div class="contenedor contenedor-grid">
        <nav class="nav">
            <ul class="list">
                <!-- Aquí van las opciones del menú -->
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/homemedicine.svg" class="list__img">
                        <a href="../index.php" class="nav__link">Home</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/schedulee.svg" class="list__img">
                        <a href="#" class="nav__link">Horarios</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/win-svgrepo-com.svg" class="list__img">
                        <a href="#" class="nav__link">Ranking Ventas</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/reportt.svg" class="list__img">
                        <a href="#" class="nav__link">Ranking Cajeras</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/resumegeneral.svg" class="list__img">
                        <a href="#" class="nav__link">Resumen x Año</a>
                    </div>
                </li>                
                <li class="list__item list__item--click">
                    <div class="list__button list__button--click">
                        <img src="../css/assets/sales.svg" class="list__img">
                        <a href="#" class="nav__link">Ventas</a>
                        <img src="../css/assets/arrow.svg" class="list__arrow">
                    </div>

                    <ul class="list__show">                       
                        <li class="list__inside">
                            <a href="#" class="nav__link nav__link--inside">Control
                                de Ventas</a>
                        </li>

                    </ul>

                </li>
                <li class="list__item list__item--click">
                    <div class="list__button list__button--click">
                        <img src="../css/assets/bankk.svg" class="list__img">
                        <a href="#" class="nav__link">Caja</a>
                        <img src="../css/assets/arrow.svg" class="list__arrow">
                    </div>
                    <ul class="list__show">
                        <li class="list__inside">
                            <a href="#" class="nav__link nav__link--inside">Agente
                                BCP</a>
                        </li>
                    </ul>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/staff.svg" class="list__img">
                        <a href="https://zfrmz.com/UN7izVUCr6VJU1XvB6PY" class="nav__link">Registro de Trabajadores</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/manual.svg" class="list__img">
                        <a href="https://mega.nz/folder/2YIzVawS#2KvgBy7oW9nuoF0ZMLy8bA" class="nav__link">Manuales y
                            Formatos</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/security.svg" class="list__img">
                        <a href="seguridad.php" class="nav__link">Seguridad</a>
                    </div>
                </li>
                <li class="list__item">
                    <div class="list__button">
                        <img src="../css/assets/message.svg" class="list__img">
                        <a href="../php/infograma.php" class="nav__link">Información</a>
                    </div>
                </li>             
            </ul>
        </nav>
        <aside class="sidebar-1">
            <img src="/img/img_1/SB006.jpg" alt="">
        </aside>
    </div>
    <div class="user-info">
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    <script src="../js/dashboard.js"></script>
    
</body>

</html>