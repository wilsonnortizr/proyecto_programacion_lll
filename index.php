<!DOCTYPE html>
<html lang="en">
<head>
    <title>Proyecto 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <form method="post" action="validarlogin.php">
        <div class="mb-3">
            <label for="nombre" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese su nombre">
        </div>
        <div class="mb-3">
            <label for="clave" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="clave" name="clave" placeholder="Ingrese su contraseña">
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</body>
</html>
