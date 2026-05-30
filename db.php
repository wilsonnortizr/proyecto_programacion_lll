<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "proyecto_2";
$port = 3307;

$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
