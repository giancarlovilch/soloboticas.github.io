<?php
session_start();
include('db_connection.php');
include('session_manager.php');
// Verificar si el usuario está logeado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['nickname'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión activa
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo Boticas Información</title>
    <link rel="icon" type="image/x-icon" href="../img/img_1/SB007.ico">    
    <link rel="preload" href="../css/infograma.css" as="style">
    <link rel="stylesheet" href="../css/infograma.css">
    <meta property="og:description" content="Salud y cuidado de familia a familia. ¡Bienvenidos a SoloBoticas!"/>
</head>
<body>
    <div class="container">
        <h3>BCP Cuentas</h3>
        <table>
            <thead>
                <tr>
                    <th>SB#</th>
                    <th>N. Cuenta Corriente</th>
                    <th>Internet</th>
                    <th>Luz</th>
                    <th>Agua</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Casa</td>
                    <td>&nbsp;</td>
                    <td>0255635 (WIN)</td>
                    <td>1732831</td>
                    <td>5197733</td>
                </tr>
                <tr>
                    <td>SB1</td>
                    <td>1911987150021</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>SB2</td>
                    <td>1919412206086</td>
                    <td>016078323 (Claro)</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>SB3</td>
                    <td>1919284055031</td>
                    <td>016275223 (Claro)</td>
                    <td>0966235</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>SB4</td>
                    <td>1919392112016</td>
                    <td>47238914 (FiberPro)</td>
                    <td>3073910</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>SB5</td>
                    <td>1919981454065</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>SB6</td>
                    <td>1911476402050</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <h3>RRHH</h3>
        <table>
            <thead>
                <tr>
                    <th>(*)</th>
                    <th>N. Cuenta Personal</th>
                    <th>N. Cuenta Corriente</th>
                    <th>Contacto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Grupo KGyR</td>
                    <td>&nbsp;</td>
                    <td>1919078016029</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Sra. Marina</td>
                    <td>19191095796053</td>
                    <td>&nbsp;</td>
                    <td>947996894 (Bitel)</td>
                </tr>
                <tr>
                    <td>Sr. Roy</td>
                    <td>19140391537031</td>
                    <td>1912172027065</td>
                    <td>999443808 (Bitel)</td>
                </tr>
                <tr>
                    <td>Jv. Gian</td>
                    <td>19305710880064</td>
                    <td>&nbsp;</td>
                    <td>935812267 (Bitel)</td>
                </tr>
                <tr>
                    <td>Sta. Kristhel</td>
                    <td>19138031414069</td>
                    <td>&nbsp;</td>
                    <td>964211004 (Claro)</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Químicos</th>
                    <th>N. Cuenta Personal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CERIN SOTO YESMIN ROSA</td>
                    <td>19196481864026</td>
                </tr>
                <tr>
                    <td>OROPEZA MOLINA STEPHANIE FLOR</td>
                    <td>19330034927009</td>
                </tr>
                <tr>
                    <td>BOCANEGRA CACHAY CLARA IVONNE</td>
                    <td>19123516317054</td>
                </tr>
            </tbody>
        </table>

        <h3>Información</h3>
        <table>
            <thead>
                <tr>
                    <th>SB#</th>
                    <th>Dirección</th>
                    <th>RUC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>SB1</td>
                    <td>AV.CANTO GRANDE NRO.3714 INT.A A.H.JESUS OROPEZA CHONTA -SJL-LIMA-LIMA-PERU</td>
                    <td>10456279487</td>
                </tr>
                <tr>
                    <td>SB2</td>
                    <td>AV.SAN MARTIN DE PORRES ESTE NRO.111 INT.B A.H.JESUS OROPEZA CHONTA -SJL-LIMA-LIMA-PERU</td>
                    <td>20607821004</td>
                </tr>
                <tr>
                    <td>SB3</td>
                    <td>AV.CANTO GRANDE NRO.3718 INT.A-B A.H.JESUS OROPEZA CHONTA -SJL-LIMA-LIMA-PERU</td>
                    <td>20607821004</td>
                </tr>
                <tr>
                    <td>SB4</td>
                    <td>AV.CANTO GRANDE NRO.2796 INT.A URB. GANIMEDES -SJL-LIMA-LIMA-PERU</td>
                    <td>20607821004</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
