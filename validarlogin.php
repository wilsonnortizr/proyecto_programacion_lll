<?php
include("db.php");

$usuarioI = $_POST["nombre"];
$claveI   = $_POST["clave"];

$usuarioI = mysqli_real_escape_string($conexion, $usuarioI);

$resultado = mysqli_query($conexion, "SELECT * FROM usuario WHERE usuario = '$usuarioI' LIMIT 1");

if ($resultado && mysqli_num_rows($resultado) == 1) {
    $fila = mysqli_fetch_assoc($resultado);
    if (password_verify($claveI, $fila['clave'])) {
        echo "<script>
            alert('¡Bienvenido al sistema!');
            window.location.href = 'panel.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Contraseña incorrecta'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Usuario incorrecto'); window.location.href='index.php';</script>";
}
?>
