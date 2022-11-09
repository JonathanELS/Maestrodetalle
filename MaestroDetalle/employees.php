<?php
include 'header.php';
?>
    <body>

        <div class="grid-container">
            <div class="top-bar">
                <div class="top-bar-left">
                    <ul class="dropdown menu" data-dropdown-menu>
                        <li class="menu-text">
                            <h3>Lista de empleados</h3>
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
                                    <th>Company name</th>
                                    <th>Contact Name</th>
                                    <th>Contact title</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Region</th>
                                    <th>Postal Code</th>
                                    <th>Country</th>
                                    <th>phone</th>
                                    <th>fax</th>
                                    <th>Homepage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include_once './config.php';
                                include_once './conexion.php';
                                include_once './comandoDML.php';

                                $conn = new conexion($servidor, $usuario, $password, $basedatos);
                                $filas = $conn->ejecutar_consulta(comandoDML::GenerarSentenciaSelect("suppliers", "*"));

                                if (count($filas) > 0) {
                                    foreach ($filas as $fila) {
                                        echo "<tr class='table-expand-row' data-open-details>"
                                        . "<td>"
                                        . "<a href='order.php?mod=E&id=$fila[supplier_id]'>Editar</a> | "
                                        . "<a href='order.php?mod=V&id=$fila[supplier_id]'>Ver</a>"
                                        . "</td>"
                                        . "<td>$fila[0]</td>"
                                        . "<td>$fila[1]</td>"
                                        . "<td>$fila[2]</td>"
                                        . "<td>$fila[3]</td>"
                                        . "<td>$fila[4]</td>"
                                        . "<td>$fila[5]</td>"
                                        . "<td>$fila[6]</td>"
                                        . "<td>$fila[7]</td>"
                                        . "<td>$fila[8]</td>"
                                        . "<td>$fila[9]</td>"
                                        . "<td>$fila[10]</td>"
                                        . "<td>$fila[11]</td>"
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