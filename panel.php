<?php
// Incluimos la conexión a la base de datos
include("db.php");

// Variables para el resultado
$distancia_total = null;
$itinerario = [];
$origen_seleccionado = "";
$destino_seleccionado = "";

// PROCESAMIENTO DEL FORMULARIO CUANDO SE DA CLICK EN "BUSCAR RUTA MÁS CORTA"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buscar'])) {
    $origen_id = intval($_POST['origen']);
    $destino_id = intval($_POST['destino']);
    
    $origen_seleccionado = $origen_id;
    $destino_seleccionado = $destino_id;

    if ($origen_id == $destino_id) {
        $distancia_total = 0;
        // Si es el mismo lugar, obtenemos su nombre
        $res = mysqli_query($conexion, "SELECT nombre FROM departamento WHERE id_departamento = $origen_id");
        $reg = mysqli_fetch_assoc($res);
        $itinerario[] = $reg['nombre'];
    } else {
        // ========================================================
        // ALGORITMO DIJKSTRA (PROGRAMACIÓN ESTRUCTURADA)
        // ========================================================
        
        // 1. Cargar todos los departamentos y las rutas de la BD a arreglos en memoria
        $res_deps = mysqli_query($conexion, "SELECT id_departamento, nombre FROM departamento");
        $nombres_deps = [];
        $distancias = [];
        $previos = [];
        $visitados = [];
        $grafo = [];

        while ($dep = mysqli_fetch_assoc($res_deps)) {
            $id = $dep['id_departamento'];
            $nombres_deps[$id] = $dep['nombre'];
            $distancias[$id] = INF; // Inicializar distancias como infinitas
            $previos[$id] = null;   // No hay camino previo aún
            $visitados[$id] = false; // Ninguno ha sido visitado
            $grafo[$id] = [];        // Inicializar lista de adyacencia
        }

        // Cargar las aristas (rutas)
        $res_rutas = mysqli_query($conexion, "SELECT origen_id, destino_id, distancia_km FROM ruta");
        while ($ruta = mysqli_fetch_assoc($res_rutas)) {
            $grafo[$ruta['origen_id']][$ruta['destino_id']] = intval($ruta['distancia_km']);
        }

        // La distancia al nodo origen es 0
        $distancias[$origen_id] = 0;

        // Bucle principal del algoritmo
        while (true) {
            $u = null;
            $min_dist = INF;

            // Encontrar el nodo no visitado con la distancia más pequeña
            foreach ($distancias as $nodo => $dist) {
                if (!$visitados[$nodo] && $dist < $min_dist) {
                    $min_dist = $dist;
                    $u = $nodo;
                }
            }

            // Si no encontramos un nodo accesible o llegamos al destino, terminamos
            if ($u === null || $u == $destino_id) {
                break;
            }

            $visitados[$u] = true;

            // Actualizar las distancias de los vecinos del nodo actual
            if (isset($grafo[$u])) {
                foreach ($grafo[$u] as $vecino => $peso) {
                    $alt = $distancias[$u] + $peso;
                    if ($alt < $distancias[$vecino]) {
                        $distancias[$vecino] = $alt;
                        $previos[$vecino] = $u;
                    }
                }
            }
        }

        // 2. Reconstruir el camino si es que se encontró una ruta válida
        if ($distancias[$destino_id] !== INF) {
            $distancia_total = $distancias[$destino_id];
            $camino_inverso = [];
            $actual = $destino_id;

            while ($actual !== null) {
                $camino_inverso[] = $nombres_deps[$actual];
                $actual = $previos[$actual];
            }

            // Invertimos el camino para que vaya desde el Origen hasta el Destino
            $itinerario = array_reverse($camino_inverso);
        }
    }
}

// Obtener la lista completa de departamentos para llenar los selectores <select>
$resultado_departamentos = mysqli_query($conexion, "SELECT id_departamento, nombre FROM departamento ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Logística EduXela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Barra superior oscura */
        .navbar-custom {
            background-color: #1a252f;
            color: #ffffff;
            padding: 15px 20px;
            font-size: 1.4rem;
            font-weight: 500;
        }
        /* Contenedor de las tarjetas */
        .card-custom {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: #ffffff;
            overflow: hidden;
            margin-bottom: 20px;
        }
        /* Encabezado Azul de Calcular Trayectoria */
        .card-header-blue {
            background-color: #0d6efd;
            color: white;
            padding: 12px 20px;
            font-size: 1.15rem;
            font-weight: 500;
        }
        /* Encabezado Oscuro de Resultado */
        .card-header-dark {
            background-color: #212529;
            color: white;
            padding: 12px 20px;
            font-size: 1.15rem;
            font-weight: 500;
        }
        .card-body-custom {
            padding: 25px;
        }
        /* Botón verde personalizado */
        .btn-search {
            background-color: #198754;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .btn-search:hover {
            background-color: #157347;
        }
        .result-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px 20px;
            border-radius: 4px;
        }
        .route-step {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            margin: 3px;
            font-size: 0.95rem;
        }
        .route-arrow {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0 2px;
        }
    </style>
</head>
<body>

    <!-- Barra superior -->
    <div class="navbar-custom">
        Sistema de Logística EduXela
    </div>

    <div class="container mt-4" style="max-width: 750px;">

        <!-- Tarjeta: Calcular Trayectoria -->
        <div class="card-custom">
            <div class="card-header-blue">Calcular Trayectoria</div>
            <div class="card-body-custom">
                <form method="POST" action="panel.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Origen</label>
                        <select name="origen" class="form-select" required>
                            <option value="">-- Seleccione origen --</option>
                            <?php
                            if ($resultado_departamentos) {
                                mysqli_data_seek($resultado_departamentos, 0);
                                while ($dep = mysqli_fetch_assoc($resultado_departamentos)) {
                                    $sel = ($dep['id_departamento'] == $origen_seleccionado) ? 'selected' : '';
                                    echo "<option value='{$dep['id_departamento']}' $sel>{$dep['nombre']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Destino</label>
                        <select name="destino" class="form-select" required>
                            <option value="">-- Seleccione destino --</option>
                            <?php
                            if ($resultado_departamentos) {
                                mysqli_data_seek($resultado_departamentos, 0);
                                while ($dep = mysqli_fetch_assoc($resultado_departamentos)) {
                                    $sel = ($dep['id_departamento'] == $destino_seleccionado) ? 'selected' : '';
                                    echo "<option value='{$dep['id_departamento']}' $sel>{$dep['nombre']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="buscar" class="btn-search">Buscar Ruta Más Corta</button>
                </form>
            </div>
        </div>

        <!-- Tarjeta: Resultado -->
        <div class="card-custom">
            <div class="card-header-dark">Resultado</div>
            <div class="card-body-custom">
                <?php if ($distancia_total === null): ?>
                    <p class="text-muted mb-0">Seleccione un origen y destino para calcular la ruta.</p>
                <?php elseif ($distancia_total === 0): ?>
                    <div class="result-box">
                        <p class="mb-1"><strong>Distancia total:</strong> 0 km</p>
                        <p class="mb-0"><strong>Ruta:</strong> <span class="route-step"><?= htmlspecialchars($itinerario[0]) ?></span></p>
                    </div>
                <?php elseif (count($itinerario) > 0): ?>
                    <div class="result-box">
                        <p class="mb-2"><strong>Distancia total:</strong> <?= $distancia_total ?> km</p>
                        <p class="mb-1"><strong>Itinerario:</strong></p>
                        <div>
                            <?php foreach ($itinerario as $i => $lugar): ?>
                                <span class="route-step"><?= htmlspecialchars($lugar) ?></span>
                                <?php if ($i < count($itinerario) - 1): ?>
                                    <span class="route-arrow">→</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">No se encontró una ruta entre los puntos seleccionados.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
