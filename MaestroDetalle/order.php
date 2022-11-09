<?php
include_once './config.php';
include_once './conexion.php';
include_once './comandoDML.php';

// parametros
$op;
$orderId;

// variables
$order;
$order_detail;
$metodo = filter_input(INPUT_SERVER, "REQUEST_METHOD");

$conn = new conexion($servidor, $usuario, $password, $basedatos);
$lista_clientes = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("customers", "customer_id, company_name"));
$lista_empleados = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("employees", "employee_id, CONCAT(first_name,' ',last_name) AS employee_name"));
$lista_transportistas = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("shippers", "shipper_id, company_name"));

if ($metodo == "GET") {
    $op = Modo(filter_input(INPUT_GET, 'mod'));
    $orderId = filter_input(INPUT_GET, 'id');

    if (isset($op) && isset($orderId)) {
        switch ($op) {
            case 0:
                // Ver registro
                $order = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("order_master", "*", "order_id = $orderId"), 1);
                $order_detail = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("order_detail", "*", "order_id = $orderId"));
                break;
            case 1:
                if ($orderId == -1) {
                    // Nuevo registro
                    $order = $conn->obtener_campos("orders");
                    $order_detail[0] = $conn->obtener_campos("order_detail");
                } else {
                    // Registro existente
                    $order = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("orders", "*", "order_id = $orderId"), 1);
                    $order_detail = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("order_detail", "*", "order_id = $orderId"));
                }
                break;
            case 2:
                // Borrar registro
                $insert_order_details = comandoDML::GenerarSentenciaDelete("order_details", "order_id = $orderId");
                $conn->ejecutar_consulta($insert_order_details);

                $insert_order = comandoDML::GenerarSentenciaDelete("orders", "order_id = $orderId");
                $conn->ejecutar_consulta($insert_order);

                header("Location: index.php");
                break;
        }
    }
}

if ($metodo == "POST") {
    $op = Modo(filter_input(INPUT_POST, 'mod'));
    $orderId = filter_input(INPUT_POST, 'id');

    // Obtener datos para tabla Master
    $order = $conn->obtener_campos("orders");
    $order['customer_id'] = htmlspecialchars(filter_input(INPUT_POST, "customer_id"));
    $order['employee_id'] = htmlspecialchars(filter_input(INPUT_POST, "employee_id"));
    $order['order_date'] = htmlspecialchars(filter_input(INPUT_POST, "order_date"));
    $order['required_date'] = htmlspecialchars(filter_input(INPUT_POST, "required_date"));
    $order['shipped_date'] = htmlspecialchars(filter_input(INPUT_POST, "shipped_date"));
    $order['ship_via'] = htmlspecialchars(filter_input(INPUT_POST, "ship_via"));
    $order['freight'] = htmlspecialchars(filter_input(INPUT_POST, "freight"));
    $order['ship_name'] = htmlspecialchars(filter_input(INPUT_POST, "ship_name"));
    $order['ship_address'] = htmlspecialchars(filter_input(INPUT_POST, "ship_address"));
    $order['ship_city'] = htmlspecialchars(filter_input(INPUT_POST, "ship_city"));
    $order['ship_region'] = htmlspecialchars(filter_input(INPUT_POST, "ship_region"));
    $order['ship_postal_code'] = htmlspecialchars(filter_input(INPUT_POST, "ship_postal_code"));
    $order['ship_country'] = htmlspecialchars(filter_input(INPUT_POST, "ship_country"));

    if ($order['customer_id'] != "" && $order['employee_id'] != "" && $order['ship_via'] != "") {

        $comandoSQL = '';

        if ($orderId == -1) {
            $orderId = $conn->obtenerSiguienteIndice("orders", "order_id");
            $order['order_id'] = $orderId;

            $comandoSQL = comandoDML::GenerarSentenciaInsert("orders", $order);
        } else {
            $comandoSQL = comandoDML::GenerarSentenciaUpdate("orders", $order, "order_id = $orderId");
        }

        if ($comandoSQL != '') {
            $conn->ejecutar_consulta($comandoSQL);

            // Obtener datos para tabla Detalle
            $detail = filter_input(INPUT_POST, "detail", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            if (isset($detail) && is_array($detail)) {
                procesarDetalle($orderId, $detail, $conn);
            }

            // Redireccionando 
            header("Location: order.php?mod=V&id=$orderId");
        }
    } else {
        header("Location: index.php");
    }
}

function procesarDetalle($orderId, $order_detail, $conexion) {

    $sublista = reOrganizar($order_detail);

    foreach ($sublista as $lista) {
        $accion = $lista['accion'];
        unset($lista['accion']);

        $comandoSQL = "";
        switch ($accion) {
            case "ins":
                $lista['order_id'] = $orderId;
                $comandoSQL = comandoDML::GenerarSentenciaInsert("order_details", $lista);
                break;
            case "upd":
                $comandoSQL = comandoDML::GenerarSentenciaUpdate("order_details", $lista, "order_id = $orderId AND product_id=$lista[product_id]");
                break;
            case "del":
                $comandoSQL = comandoDML::GenerarSentenciaDelete("order_details", "order_id = $orderId AND product_id=$lista[product_id]");
                break;
        }
        $conexion->ejecutar_consulta($comandoSQL);
    }
}

function reOrganizar($matriz) {
    $registros = [];
    $campos = array_keys($matriz);
    $valores = array_values($matriz);
    $f = 0;
    while ($f < count($valores[0])) {
        $fila = [];
        for ($i = 0; $i < count($valores); $i++) {
            $valor = $valores[$i][$f];
            $fila[$campos[$i]] = $valor;
        }
        $registros[$f] = $fila;
        $f++;
    }
    return $registros;
}

function Modo($mod) {
    switch ($mod) {
        case 'V';
            return 0; // Ver
        case 'E':
            return 1; // Edicion
        case 'D':
            return 2; // Borrar
    }
}
?>
<!doctype html>
<html class="no-js" lang="es" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maestro - Detalle</title>
        <link rel="stylesheet" href="css/foundation.css">
        <link rel="stylesheet" href="css/app.css">
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css' >
        <link rel='stylesheet' href='https://cdn.datatables.net/1.12.1/css/dataTables.foundation.min.css' >
        <script src="https://code.jquery.com/jquery-3.6.1.js"></script>
        <script src="js/vendor/what-input.js"></script>
        <script src="js/vendor/foundation.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.foundation.min.js"></script>
        <script src="js/jquery-functions.js"></script>
        <script src="js/app.js"></script>
    </head>
    <body>

        <div class="grid-container">
            <div class="top-bar">
                <div class="top-bar-left">
                    <ul class="dropdown menu" data-dropdown-menu>
                        <li class="menu-text">Órden de Venta</li>
                    </ul>
                </div>
            </div>

            <div class="grid-x grid-padding-x">
                <div class="large-12 cell">
                    <h1>Orden #<?php echo $orderId == -1 ? "" : $orderId; ?></h1>
                </div>
            </div>

            <div class="grid-x grid-padding-x">
                <div class="large-12 cell">
                    <div class="callout">
                        <!-- formulario -->
                        <form method="POST" action="<?php echo htmlspecialchars(filter_input(INPUT_SERVER, 'PHP_SELF')); ?>">
                            <!-- menu -->
                            <div class="top-bar" id="responsive-menu">
                                <div class="top-bar-left">
                                    <ul class="dropdown menu" data-dropdown-menu>
                                        <?php
                                        if ($op == 1) {
                                            echo "<li><input type='submit' class='button' value='Guardar'></li>";
                                            echo "<li><a href='index.php' class='secondary button'>Cancelar</a></li>";
                                        } else {
                                            echo "<li><a href='index.php' class='secondary button'>Cerrar</a></li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <?php
                                if ($orderId > -1) {
                                    echo "<div class='top-bar-right'>";
                                    echo "<ul class='menu'>";
                                    echo "<li>";
                                    echo "<input type='button' class='alert button' value='Borrar' onclick='borrarRegistro($orderId)'>";
                                    echo "</li>";
                                    echo "</ul>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                            <!-- Lína 1 -->
                            <div class="grid-x grid-padding-x">
                                <input type="hidden" name="mod" value="V" />
                                <input type="hidden" name="id" value="<?php echo $orderId; ?>" />
                            </div>
                            <!-- Lína 2 -->
                            <div class="grid-x grid-padding-x">
                                <div class="large-6 medium-4 cell">
                                    <label>Cliente</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<select name='customer_id'>";
                                        echo "<option value=''></option>";
                                        foreach ($lista_clientes as $cliente) {
                                            $selected = "";
                                            if (isset($order) && ($order['customer_id'] == $cliente['customer_id'])) {
                                                $selected = "selected";
                                            }
                                            echo "<option value='$cliente[customer_id]' $selected>$cliente[company_name]</option>";
                                        }
                                        echo "</select>";
                                    } else {
                                        echo "<p>$order[customer_name]</p>";
                                    }
                                    ?>
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Fecha Orden</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='date' name='order_date' value='$order[order_date]' />";
                                    } else {
                                        echo "<p>$order[order_date]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Fecha Requerida</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='date' name='required_date' value='$order[required_date]' />";
                                    } else {
                                        echo "<p>$order[required_date]</p>";
                                    }
                                    ?> 
                                </div>
                            </div>
                            <!-- Lína 2 -->
                            <hr/>
                            <div class="grid-x grid-padding-x">
                                <div class="large-4 medium-4 cell">
                                    <label>Nombre de Envío</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_name' value='$order[ship_name]' />";
                                    } else {
                                        echo "<p>$order[ship_name]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-2 medium-4 cell">
                                    <label>Cargo por Envío</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='number' min='0' step='any' placeholder='0.0' name='freight' value='$order[freight]' />";
                                    } else {
                                        echo "<p>$order[freight]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Fecha de Envío</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='date' name='shipped_date' value='$order[shipped_date]' />";
                                    } else {
                                        echo "<p>$order[shipped_date]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Transportista</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<select name='ship_via'>";
                                        echo "<option value=''></option>";
                                        foreach ($lista_transportistas as $trans) {
                                            $selected = "";
                                            if (isset($order) && ($order['ship_via'] == $trans['shipper_id'])) {
                                                $selected = "selected";
                                            }
                                            echo "<option value='$trans[shipper_id]' $selected>$trans[company_name]</option>";
                                        }
                                        echo "</select>";
                                    } else {
                                        echo "<p>$order[shipper_name]</p>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <!-- Lína 3 -->
                            <div class="grid-x grid-padding-x">
                                <div class="large-9 medium-4 cell">
                                    <label>Direccion</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_address' value='$order[ship_address]' />";
                                    } else {
                                        echo "<p>$order[ship_address]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Ciudad</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_city' value='$order[ship_city]' />";
                                    } else {
                                        echo "<p>$order[ship_city]</p>";
                                    }
                                    ?> 
                                </div>
                            </div>
                            <!-- Lína 3 -->
                            <div class="grid-x grid-padding-x">
                                <div class="large-4 medium-4 cell">
                                    <label>Region/Estado</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_region' value='$order[ship_region]' />";
                                    } else {
                                        echo "<p>$order[ship_region]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-2 medium-4 cell">
                                    <label>Codigo Postal</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_postal_code' value='$order[ship_postal_code]' />";
                                    } else {
                                        echo "<p>$order[ship_postal_code]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Pais</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<input type='text' name='ship_country' value='$order[ship_country]' />";
                                    } else {
                                        echo "<p>$order[ship_country]</p>";
                                    }
                                    ?> 
                                </div>
                                <div class="large-3 medium-4 cell">
                                    <label>Empleado</label>
                                    <?php
                                    if ($op == 1) {
                                        echo "<select name='employee_id'>";
                                        echo "<option value=''></option>";
                                        foreach ($lista_empleados as $empleado) {
                                            $selected = "";
                                            if (isset($order) && ($order['employee_id'] == $empleado['employee_id'])) {
                                                $selected = "selected";
                                            }
                                            echo "<option value='$empleado[employee_id]' $selected>$empleado[employee_name]</option>";
                                        }
                                        echo "</select>";
                                    } else {
                                        echo "<p>$order[employee_name]</p>";
                                    }
                                    ?> 
                                </div>
                            </div>
                            <!-- Lína 4 -->
                            <hr/>
                            <div class="grid-x grid-padding-x">
                                <div class="large-12 medium-4 cell">
                                    <table id="detalle" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Opciones</th>
                                                <th>Orden</th>
                                                <th>Codigo</th>
                                                <th>Producto</th>
                                                <th>Unidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Cantidad</th>
                                                <th>Descuento</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detalle-lista">
                                            <?php
                                            if ($op == 1) {
                                                echo "<tr>";
                                                echo "<th></th>";
                                                echo "<th><p>$orderId</p></th>";
                                                echo "<th><input type='text' placeholder='Codigo' id='productid'/></th>";
                                                echo "<th><input type='text' placeholder='Producto' id='product_name' readonly></th>";
                                                echo "<th><input type='text' placeholder='Unidad' id='unit' readonly></th>";
                                                echo "<th><input type='number' min='0' step='any' placeholder='Precio' id='unit_price'/></th>";
                                                echo "<th><input type='number' min='0' step='any' placeholder='Cantidad' id='quantity'/></th>";
                                                echo "<th><input type='number' min='0' step='any' placeholder='Descuento' id='discount'/></th>";
                                                echo "</tr>";
                                            }
                                            if (count($order_detail) > 0 && $orderId != -1) {
                                                foreach ($order_detail as $detail) {
                                                    if ($op == 1) {
                                                        echo "<tr>";
                                                        echo "<td>"
                                                        . "<a href='javascript:void(0);' id='delete_line'>Eliminar</a>"
                                                        . "<input type='hidden' name='detail[accion][]' value='upd'>"
                                                        . "</td>";
                                                        echo "<td><input type='text' name='detail[order_id][]' value='$detail[order_id]' readonly></td>";
                                                        echo "<td><input type='text' name='detail[product_id][]' value='$detail[product_id]' readonly></td>";
                                                        echo "<td><input type='text' value='$detail[product_name]' readonly></td>";
                                                        echo "<td><input type='text' value='$detail[quantity_per_unit]' readonly></td>";
                                                        echo "<td><input type='number' min='0' step='any' name='detail[unit_price][]' value='$detail[unit_price]'></td>";
                                                        echo "<td><input type='number' min='0' step='any' name='detail[quantity][]' value='$detail[quantity]'></td>";
                                                        echo "<td><input type='number' min='0' step='any' name='detail[discount][]' value='$detail[discount]'></td>";
                                                        echo "</tr>";
                                                    } else {
                                                        echo "<tr>";
                                                        echo "<td></td>";
                                                        echo "<td>$detail[order_id]</td>";
                                                        echo "<td>$detail[product_id]</td>";
                                                        echo "<td>$detail[product_name]</td>";
                                                        echo "<td>$detail[quantity_per_unit]</td>";
                                                        echo "<td>$detail[unit_price]</td>";
                                                        echo "<td>$detail[quantity]</td>";
                                                        echo "<td>$detail[discount]</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
