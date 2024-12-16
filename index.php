<?php
include("../login/sesion.php");
include("../conexion.php");

$con = conectar();

$sql = "SELECT idProducto, producto FROM productos";
$resultadoProducto = $con->query($sql);

$sql = "SELECT idEquipo, equipo FROM equipos WHERE tipoEquipo = 99";
$resultadoEquipo = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Estadístico de Envasado</title>
    <link rel="stylesheet" type="text/css" href="css/stylesIndex.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>
    <iframe src="../encabezado.php" class="miClaseIframe"></iframe>
    <br><br>
    <div class="container"><br>
        <h1 style="text-align: center;">CONTROL ESTADÍSTICO DE ENVASADO</h1>

        <?php
            $fechaHoy = date('Y-m-d');
            $hora = date('H:i:s');
        ?>


        <button id="enviarBtn">FINALIZAR</button>
        <button id="salirBtn" onclick="location.href='menuValidacionCEE.php'">VOLVER A MENÚ</button>
        <br><br>

        <div class="form-container">
            <div class="column1">
                <label for="producto">PRODUCTO:</label>
                <br><br>
                <input type="text" id="searchProducto" class="producto" placeholder="Buscar producto...">
                <br><br>
                <select name="producto" class="producto" id="productoSelect" required autofocus>
                    <option value="">Selecciona un producto</option>
                    <?php
                    if ($resultadoProducto->num_rows > 0) {
                        while ($fila = $resultadoProducto->fetch_assoc()) {
                            echo "<option value='" . $fila["idProducto"] . "'>" . $fila["producto"] . "</option>";
                        }
                    }
                    ?>
                </select>
                <br><br>
                <label for="envasadora">ENVASADORA:</label>
                <br>
                <select name="envasadora" class="envasadora" id="envasadoraSelect" autofocus>
                    <option value="">Selecciona Envasadora</option>
                    <?php
                    if ($resultadoEquipo->num_rows > 0) {
                        while ($fila = $resultadoEquipo->fetch_assoc()) {
                            echo "<option value='" . $fila["idEquipo"] . "'>" . $fila["equipo"] . "</option>";
                        }
                    }
                    ?>
                </select>
                <br><br>
                <label>CÓDIGO: </label>
                <br>
                <input type="text" name="codigo" value="">
                <br><br>
                <label>CADUCIDAD: 
                <br>  
                <input type="date" name="caducidad"  value="<?php echo date('Y-m-d', strtotime('+1 year'));?>"></label>
                <br><br>
                <label for="li">LI: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="li" id="li" oninput="actualizarLI()">
                <br><br>
                <label for="ls">LS: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="ls" id="ls" oninput="actualizarPromedio()">
                <br><br>
                <label for="x">X: </label>
                <br>
                <input type="number" step=".50" value="00.00" name="x" id="x" oninput="actualizarValorDesdeX()">
                <br><br>
            </div>

            <!-- Segunda columna -->
            <div class="column2">
                <label for="real-time">Hora:</label>
                <input class="Hora" type="time" id="real-time" placeholder="Hora" required>
                
                <label class="lvalor" for="valor">Valor:</label>
                <input class="Valor" type="number" step=".50" id="valor" value="" placeholder="Valor" required>
                <br><br>

                <!-- Botón Guardar -->
                <button id="guardarBtn">Guardar</button>

                <!-- Tabla para mostrar los valores ingresados -->
                <h2>Valores Ingresados</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Valor</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaValores">
                        <!-- Aquí se agregarán los valores dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

        <script>
let valControlEstadistico = [];

// Validar y guardar valores
document.getElementById('guardarBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario

    const hora = document.getElementById('real-time').value;
    const valor = parseFloat(document.getElementById('valor').value);
    const li = parseFloat(document.getElementById('li').value);
    const ls = parseFloat(document.getElementById('ls').value);

    // Validar que LS sea mayor que LI
    if (ls <= li) {
        Swal.fire('Error', 'El valor de LS debe ser mayor que LI.', 'error');
        return;
    }

    // Validar valores
    if (isNaN(valor) || valor < li || valor > ls) {
        Swal.fire('Error', `El valor ingresado debe estar entre ${li} y ${ls}.`, 'error');
        return;
    }

    // Agregar a la tabla y al array
    if (hora && !isNaN(valor)) {
        valControlEstadistico.push({ hora, valor });
        actualizarTabla(); // Actualizar la tabla automáticamente
    }
});

// Ordenar las filas por hora y actualizar la tabla
function actualizarTabla() {
    valControlEstadistico.sort((a, b) => (a.hora > b.hora ? -1 : 1));

    const tabla = document.getElementById('cuerpoTablaValores');
    tabla.innerHTML = ''; // Limpiar tabla

    valControlEstadistico.forEach(entry => {
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>${entry.hora}</td>
            <td>${entry.valor.toFixed(2)}</td>
            <td><button class="eliminarBtn">Eliminar</button></td>
        `;
        tabla.appendChild(nuevaFila);

        // Agregar evento para eliminar fila
        nuevaFila.querySelector('.eliminarBtn').addEventListener('click', function() {
            valControlEstadistico = valControlEstadistico.filter(e => e !== entry);
            actualizarTabla();
        });
    });
}

// Colocar hora actual al cargar la página
function setCurrentTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('real-time').value = `${hours}:${minutes}`;
}

// Llamar a la función al cargar la página
window.onload = setCurrentTime;

// Validar formato del campo "Valor"
document.getElementById('valor').addEventListener('input', function() {
    let value = this.value.replace(/[^0-9.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    const parsedValue = parseFloat(value);
    this.value = isNaN(parsedValue) ? '' : parsedValue.toFixed(2);
});

// Buscar productos
document.getElementById('searchProducto').addEventListener('input', function() {
    const filtro = this.value.toLowerCase();
    const opciones = document.getElementById('productoSelect').options;

    for (const opcion of opciones) {
        const texto = opcion.text.toLowerCase();
        opcion.style.display = texto.includes(filtro) ? '' : 'none';
    }
});

function actualizarLI() {
    const liInput = document.getElementById('li'); // Campo LI
    const lsInput = document.getElementById('ls'); // Campo LS
    const xInput = document.getElementById('x');  // Campo X
    const valorInput = document.getElementById('valor'); // Campo Valor

    // Obtener el valor de LI
    const liValue = parseFloat(liInput.value) || 0;

    // Actualizar LS con LI + 10
    const lsValue = liValue + 10;
    lsInput.value = lsValue.toFixed(2);

    // Calcular el promedio (X)
    const promedio = (liValue + lsValue) / 2;
    xInput.value = promedio.toFixed(2);

    // Pasar el promedio (X) al campo Valor
    valorInput.value = promedio.toFixed(2);
}

// Actualizar el promedio si se modifica LS manualmente
function actualizarPromedio() {
    const liInput = document.getElementById('li'); // Campo LI
    const lsInput = document.getElementById('ls'); // Campo LS
    const xInput = document.getElementById('x');  // Campo X
    const valorInput = document.getElementById('valor'); // Campo Valor

    // Obtener valores de LI y LS
    const liValue = parseFloat(liInput.value) || 0;
    const lsValue = parseFloat(lsInput.value) || 0;

    // Calcular el promedio solo si LS > LI
    if (lsValue > liValue) {
        const promedio = (liValue + lsValue) / 2;
        xInput.value = promedio.toFixed(2);

        // Pasar el promedio (X) al campo Valor
        valorInput.value = promedio.toFixed(2);
    } else {
        // Limpiar X y Valor si LS no es válido
        xInput.value = '';
        valorInput.value = '';
    }
}

// Actualizar el campo Valor al modificar X
function actualizarValorDesdeX() {
    const xInput = document.getElementById('x'); // Campo X
    const valorInput = document.getElementById('valor'); // Campo Valor

    // Obtener el valor de X
    const xValue = parseFloat(xInput.value) || 0;

    // Copiar X al campo Valor
    valorInput.value = xValue.toFixed(2);
}

document.getElementById('enviarBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario


    const data = {
        producto: document.querySelector('select[name="producto"]').value,
        envasadora: document.querySelector('select[name="envasadora"]').value,
        ls: document.querySelector('input[name="ls"]').value,
        li: document.querySelector('input[name="li"]').value,
        codigo: document.querySelector('input[name="codigo"]').value,
        caducidad: document.querySelector('input[name="caducidad"]').value,
        valores: valControlEstadistico // Aquí se envían los valores
    };

    fetch('procesarEstadistica.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.message, 'success');
            // Limpiar los campos si es necesario
            document.getElementById('cuerpoTablaValores').innerHTML = ''; // Limpiar la tabla
            valControlEstadistico = []; // Resetear el array
        } else {
            Swal.fire('Error', data.message || 'No se pudo completar la operación', 'error');
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al enviar los datos: ' + error.message, 'error');
    });
});
        </script>
    </div>
</body>
</html>
