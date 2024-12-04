<?php
session_start();
include('db_connection.php');
include('session_manager.php');

// Verificar si el usuario está logeado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['nickname'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión activa
    exit();
}

// Verificar si el nickname es "giancarlovilch"
if ($_SESSION['nickname'] !== 'GIANCARLOVILCH') {
    header('Location: dashboard.php'); // Redirigir al login si el nickname no es el correcto
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width initial-scale=1">
    <link rel="preload" href="../css/seguridad.css" as="style">
    <link rel="stylesheet" href="../css/seguridad.css">
    <link href="./Solo Boticas (Security)_files/css2" rel="stylesheet" type="text/css">    
    <title>Solo Boticas (Security)</title>
</head>

<body class="typora-export os-windows">
    <div class="typora-export-content">
        <div id="write" class="">
            <h1 id="solo-boticas-security"><span>Solo Boticas (Security)</span></h1>
            <h3 id="gps"><span>GPS</span></h3>
            <p><a href="https://www.sinotrack.com/" target="_blank" class="url">https://www.sinotrack.com/</a></p>
            <figure>
                <table>
                    <thead>
                        <tr>
                            <th><span>Vehículo (*)</span></th>
                            <th><span>ID CODE</span></th>
                            <th><span>LOGIN MODE SERVER</span></th>
                            <th><span>Password</span></th>
                            <th><span>Phone Number</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span>MOTO</span></td>
                            <td><span>9117904898</span></td>
                            <td><span>VIP SinoTrack</span></td>
                            <td><span>123456</span></td>
                            <td><span>(+51) 965 491 485</span></td>
                        </tr>
                        <tr>
                            <td><span>AUTO</span></td>
                            <td><span>7028632297</span></td>
                            <td><span>SinoTrack Pro</span></td>
                            <td><span>123456</span></td>
                            <td>(+51) 960 492 303</td>
                        </tr>
                        <tr>
                            <td><span>SB2</span></td>
                            <td><span>ALARM02</span></td>
                            <td><span>6666</span></td>
                            <td><span>1234</span></td>
                            <td>(+51) ##########</td>
                        </tr>
                        <tr>
                            <td><span>SB3</span></td>
                            <td><span>ALARM03</span></td>
                            <td><span>6666</span></td>
                            <td><span>1234</span></td>
                            <td>(+51) ##########</td>
                        </tr>
                        <tr>
                            <td><span>SB4</span></td>
                            <td><span>ALARM04</span></td>
                            <td><span>6666</span></td>
                            <td><span>1234</span></td>
                            <td>(+51) ##########</td>
                        </tr>
                        <tr>
                            <td><span>CAMIÓN</span></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </figure>
            <blockquote>                
                <p><span>CONTACTO HIJO MAYOR: CIRO CANO CHAVEZ - TELÉFONO: 916678116</span></p>
                <p><span>CONTACTO MAMÁ: CHAVEZ VILLAVICENCIO MARINA - TELÉFONO: 947996894</span></p>
            </blockquote>
            <h3 id="onvif---vms"><span>ONVIF - VMS</span></h3>
            <figure>
                <table>
                    <thead>
                        <tr>
                            <th><span>SB#</span></th>
                            <th><span>CloudID</span></th>
                            <th><span>UserName</span></th>
                            <th><span>Password</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>2ebf120e06ba85e2bmmg</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>6fc24c8dec5138c8</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>43491f43166218d8</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>11a1724529659809</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>036ba69440610481</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1</span></td>
                            <td><span>34c2349611d5f5bb</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB1(*)</span></td>
                            <td><span>334067b343de7ceb</span></td>
                            <td><span>admin</span></td>
                            <td><span>soloboticas</span></td>
                        </tr>
                        <tr>
                            <td><span>SB2</span></td>
                            <td><span>fa0d5cb3afe9bbcf</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB2</span></td>
                            <td><span>b625859176e5b291</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB2</span></td>
                            <td><span>b7b1bcd06dd41d71</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB2</span></td>
                            <td><span>653a3ce321ba2e7c</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB2(*)</span></td>
                            <td><span>15e10b0296699998</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB3</span></td>
                            <td><span>b4461e4c12dd8b87</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB3</span></td>
                            <td><span>fc5be5f428d328aa</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB3(*)</span></td>
                            <td><span>821245f2065da757</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                        <tr>
                            <td><span>SB4</span></td>
                            <td><span>c222aef928aa36cf</span></td>
                            <td><span>admin</span></td>
                            <td><span>null</span></td>
                        </tr>
                    </tbody>
                </table>
            </figure>
        </div>
    </div>

</body>

</html>