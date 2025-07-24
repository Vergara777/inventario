<?php
$conexion = mysqli_connect("localhost:3306", "FILANTROPO", "qwertyuiop777", "inventariodb");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
