<?php

// Constantes de negocio 
const TASA_INTERES   = 0.026; 
const TASA_CASHBACK  = 0.001;
const ARCHIVO_SALIDA = 'estado_cuenta.txt';
const MIN_GASTOS     = 3;

// Arreglo transacciones 
$transacciones = [];

function h($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function dinero($valor)
{
    return '₡' . number_format($valor, 2, '.', ',');
}

function registrarTransaccion($id, $descripcion, $monto)
{
    global $transacciones;

    if ($descripcion === '' || $monto <= 0) {
        return false;
    }
    foreach ($transacciones as $t) {
        if ($t['id'] === $id) { // id único
            return false;
        }
    }

    array_push($transacciones, [
        'id'          => $id,
        'descripcion' => $descripcion,
        'monto'       => $monto,
    ]);
    return true;
}

function generarEstadoDeCuenta()
{
    global $transacciones;

    // Total de contado
    $totalContado = 0;
    foreach ($transacciones as $t) {
        $totalContado += $t['monto'];
    }

    // Interés: 2.6 %
    $totalConInteres = $totalContado * (1 + TASA_INTERES);

    // Cash back: 0.1 %
    $cashback = $totalContado * TASA_CASHBACK;

    // Monto final 
    $montoFinal = $totalConInteres - $cashback;

    // Formato para la factura
    $linea = str_repeat('=', 60);
    $sep   = str_repeat('-', 60);

    $texto  = $linea . PHP_EOL;
    $texto .= "              ESTADO DE CUENTA - TARJETA DE CRÉDITO" . PHP_EOL;
    $texto .= "Fecha: " . date('d/m/Y H:i') . PHP_EOL;
    $texto .= $linea . PHP_EOL;
    $texto .= "TRANSACCIONES REGISTRADAS" . PHP_EOL;
    $texto .= $sep . PHP_EOL;
    $texto .= sprintf("%-6s %-33s %18s", 'ID', 'Descripción', 'Monto') . PHP_EOL;
    $texto .= $sep . PHP_EOL;

    foreach ($transacciones as $t) {
        $texto .= sprintf(
            "%-6s %-33s %18s",
            $t['id'],
            mb_substr($t['descripcion'], 0, 33),
            dinero($t['monto'])
        ) . PHP_EOL;
    }

    $texto .= $sep . PHP_EOL;
    $texto .= sprintf("%-40s %18s", 'Monto total de contado:', dinero($totalContado)) . PHP_EOL;
    $texto .= sprintf("%-40s %18s", 'Monto total con interés (2.6%):', dinero($totalConInteres)) . PHP_EOL;
    $texto .= sprintf("%-40s %18s", 'Cash back (0.1% del contado):', '-' . dinero($cashback)) . PHP_EOL;
    $texto .= $linea . PHP_EOL;
    $texto .= sprintf("%-40s %18s", 'MONTO FINAL A PAGAR:', dinero($montoFinal)) . PHP_EOL;
    $texto .= $linea . PHP_EOL;

    echo '<pre>' . h($texto) . '</pre>';

    // Archivo
    if (file_put_contents(ARCHIVO_SALIDA, $texto) !== false) {
        echo '<p>Archivo <strong>' . ARCHIVO_SALIDA . '</strong> generado correctamente.</p>';
    } else {
        echo '<p style="color:red">Error: no se pudo escribir ' . ARCHIVO_SALIDA . '.</p>';
    }
}

// Leer los gastos del usuario
$error = '';
$mostrarEstado = false;

// Valores por si hay error 
$descripciones = array_fill(0, MIN_GASTOS, '');
$montos        = array_fill(0, MIN_GASTOS, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripciones = $_POST['descripcion'] ?? [];
    $montos        = $_POST['monto'] ?? [];

    $id = 1;
    foreach ($descripciones as $i => $desc) {
        $desc  = trim($desc);
        $monto = str_replace(',', '.', trim($montos[$i] ?? ''));

        if ($desc !== '' && is_numeric($monto)) {
            if (registrarTransaccion($id, mb_substr($desc, 0, 60), (float)$monto)) {
                $id++;
            }
        }
    }

    if (count($transacciones) >= MIN_GASTOS) {
        $mostrarEstado = true;
    } else {
        $error = 'Debes ingresar al menos ' . MIN_GASTOS
               . ' gastos válidos (descripción y monto mayor a 0). Válidos: '
               . count($transacciones) . '.';
        $transacciones = []; 
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de cuenta - Tarjeta de crédito</title>
</head>
<body>

<?php if ($mostrarEstado): ?>

    <?php generarEstadoDeCuenta(); ?>
    <p><a href="<?= h($_SERVER['PHP_SELF']) ?>">Registrar otros gastos</a></p>

<?php else: ?>

    <h1>Registro de gastos</h1>
    <p>Digita dónde gastaste y cuánto (mínimo <?= MIN_GASTOS ?> gastos).</p>

    <?php if ($error): ?>
        <p style="color:red"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <table id="tabla-gastos" cellpadding="6">
            <tr>
                <th>|</th>
                <th>¿Dónde pagó?</th>
                <th>¿Cuánto? (₡)</th>
            </tr>
            <?php
            $filas = max(MIN_GASTOS, count($descripciones));
            for ($i = 0; $i < $filas; $i++):
            ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <input type="text" name="descripcion[]" maxlength="60"
                               value="<?= h($descripciones[$i] ?? '') ?>"
                               <?= $i < MIN_GASTOS ? 'required' : '' ?>>
                    </td>
                    <td>
                        <input type="number" name="monto[]" min="0.01" step="0.01"
                               value="<?= h($montos[$i] ?? '') ?>"
                               <?= $i < MIN_GASTOS ? 'required' : '' ?>>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>

        <p>
            <button type="button" onclick="agregarFila()">+ Agregar otro gasto</button>
            <button type="submit">Generar estado de cuenta</button>
        </p>
    </form>

    <script>
        function agregarFila() {
            var tabla = document.getElementById('tabla-gastos');
            var n = tabla.rows.length; 
            var fila = tabla.insertRow();
            fila.innerHTML =
                '<td>' + n + '</td>' +
                '<td><input type="text" name="descripcion[]" maxlength="60"></td>' +
                '<td><input type="number" name="monto[]" min="0.01" step="0.01"></td>';
        }
    </script>

<?php endif; ?>

</body>
</html>
