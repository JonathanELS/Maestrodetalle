<?php
include 'header.php';
?>
    <body>

        <div class="grid-container">
            <div class="top-bar">
                <div class="top-bar-left">
                    <ul class="dropdown menu" data-dropdown-menu>
                        <li class="menu-text">
                            <h3>Lista de Productos</h3>
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
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Supplier name</th>
                                    <th>Category ID</th>
                                    <th>Quantity per Unit</th>
                                    <th>Unit price</th>
                                    <th>Unit in stock</th>
                                    <th>units on order</th>
                                    <th>Discontinued</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include_once './config.php';
                                include_once './conexion.php';
                                include_once './comandoDML.php';

                                $conn = new conexion($servidor, $usuario, $password, $basedatos);
                                $filas = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("products", "*"));

                                if (count($filas) > 0) {
                                    foreach ($filas as $fila) {
                                        echo "<tr class='table-expand-row' data-open-details>"
                                        . "<td>"
                                        . "<a href='order.php?mod=E&id=$fila[product_id]'>Editar</a> | "
                                        . "<a href='order.php?mod=V&id=$fila[product_id]'>Ver</a>"
                                        . "</td>"
                                        . "<td>$fila[product_id]</td>"
                                        . "<td>$fila[product_name]</td>"
                                        . "<td>$fila[supplier_id]</td>"
                                        . "<td>$fila[category_id]</td>"
                                        . "<td>$fila[quantity_per_unit]</td>"
                                        . "<td>$fila[unit_price]</td>"
                                        . "<td>$fila[units_in_stock]</td>"
                                        . "<td>$fila[units_on_order]</td>"
                                        . "<td>$fila[reorder_level]</td>"
                                        . "<td>$fila[discontinued]</td>"
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
