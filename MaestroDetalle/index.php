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
    </head>
    <body>

        <div class="grid-container">
            <div class="top-bar">
                <div class="top-bar-left">
                    <ul class="dropdown menu" data-dropdown-menu>
                        <li class="menu-text">
                            <h3>Lista de Órdenes</h3>
                        </li>                     
                    </ul>
                </div>
            </div>

            <div class="top-bar">
                <div class="top-bar-left">
                    <ul class="menu">
                        <li>
                            <input type="button" class="button" value="Nuevo" onclick="window.location = 'order.php?mod=E&id=-1'">
                        </li>
                        <li>
                            <input type="button" class="button" value="Home" onclick="window.location = 'home.php?mod=E&id=-1'">
                        </li>
                    </ul>
                </div>
            </div>

            <div class="callout">
                <div class="grid-x grid-padding-x">
                    <div class="large-12 medium-4 cell">
                        <table id="lista" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Opción</th>
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Método Envío</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include_once './config.php';
                                include_once './conexion.php';
                                include_once './comandoDML.php';

                                $conn = new conexion($servidor, $usuario, $password, $basedatos);
                                $filas = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("order_master", "*"));

                                if (count($filas) > 0) {
                                    foreach ($filas as $fila) {
                                        echo "<tr class='table-expand-row' data-open-details>"
                                        . "<td>"
                                        . "<a href='order.php?mod=E&id=$fila[order_id]'>Editar</a> | "
                                        . "<a href='order.php?mod=V&id=$fila[order_id]'>Ver</a>"
                                        . "</td>"
                                        . "<td>$fila[order_id]</td>"
                                        . "<td>$fila[customer_name]</td>"
                                        . "<td>$fila[order_date]</td>"
                                        . "<td>$fila[shipper_name]</td>"
                                        . "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=5 ></td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(() => {
                /**
                 * Foundation framework
                 */
                $(document).foundation();
                
                /**
                 * Renderiza DataTable 
                 */
                let table = $('#lista').DataTable({"pageLength": 25});
                table.column('1:visible').order('desc').draw();
            });
        </script>
    </body>
</html>
