(($) => {
    $(() => {
        /**
         * Foundation framework
         */
        $(document).foundation();
        
        /**
         * Renderiza DataTable 
         */
        $('#detalle').DataTable();

        /**
         * Captura tecleo de Tab o Enter
         */
        $('#productid').on('keypress', (e) => {
            if (e.keyCode === 9 || e.keyCode === 13) {
                e.preventDefault();

                let productId = $('#productid').val();
                let product = getProduct(productId);

                if (product) {
                    $('#product_name').val(product['product_name']);
                    $('#unit').val(product['quantity_per_unit']);
                    $('#unit_price').val(product['unit_price']);
                    $('#discount').val(0);

                    $('#unit_price').focus();
                } else {
                    alert('Codigo de producto no encontrado');
                }
            }
        });

        /**
         * Captura tecleo de Tab o Enter
         */
        $('#unit_price').on('keypress', (e) => {
            if (e.keyCode === 9 || e.keyCode === 13) {
                e.preventDefault();
                $('#quantity').focus();
            }
        });

        /**
         * Captura tecleo de Tab o Enter
         */
        $('#quantity').on('keypress', (e) => {
            if (e.keyCode === 9 || e.keyCode === 13) {
                e.preventDefault();
                $('#discount').focus();
            }
        });

        /**
         * Captura tecleo de Tab o Enter
         */
        $('#discount').on('keypress', (e) => {
            if (e.keyCode === 9 || e.keyCode === 13) {
                e.preventDefault();

                let product_name = '';
                let unit = '';
                let productId = $('#productid').val();
                let unitPrice = $('#unit_price').val();
                let quantity = $('#quantity').val();
                let discount = $('#discount').val();

                let product = getProduct(productId);

                if (product) {
                    product_name = product['product_name'];
                    unit = product['quantity_per_unit'];
                }

                let linea = ``;
                linea += `<tr>`;
                linea += `<td><a href='javascript:void(0);' id='delete_line'>Eliminar</a><input type='hidden' name='detail[accion][]' value='ins'></td>`;
                linea += `<td><input type='text' name='detail[order_id][]' value='<?php echo $orderId; ?>' readonly></td>`;
                linea += `<td><input type='text' name='detail[product_id][]' value='${productId}' readonly></td>`;
                linea += `<td><input type='text' value='${product_name}' readonly></td>`;
                linea += `<td><input type='text' value='${unit}' readonly></td>`;
                linea += `<td><input type='number' min='0' step='any' name='detail[unit_price][]' value='${unitPrice}'></td>`;
                linea += `<td><input type='number' min='0' step='any' name='detail[quantity][]' value='${quantity}'></td>`;
                linea += `<td><input type='number' min='0' step='any' name='detail[discount][]' value='${discount}'></td>`;
                linea += `</tr>`;

                $('#detalle-lista').append(linea);
                clear();
            }
        });

        /**
         * Funcion que oculta linea marcada para eliminar
         */
        $("tbody#detalle-lista").on("click", "#delete_line", () => {
            $(this).parent().parent().find('input[name="detail[accion][]"]').val('del');
            $(this).parent().parent().hide();
        });

        /**
         * Valida campos requeridos antes de guardar
         */
        $(document).on('click', 'form input[type=submit]', (e) => {
            let customer_id = $("form select[name='customer_id']").val();
            let employee_id = $("form select[name='employee_id']").val();
            let ship_via = $("form select[name='ship_via']").val();
            let order_date = $("form input[name='order_date']").val();

            if (customer_id === "" || order_date === "" || employee_id === "" || ship_via === "") {
                e.preventDefault();
                alert("Ingrese campos requeridos");
            }
        });

        /**
         * Llama products.php para recuperar registro de products por Id 
         * @param {string} id
         * @returns {response}                 
         */
        const getProduct = (id) => {
            let product = null;
            $.ajax({
                async: false,
                method: "POST",
                url: "products.php",
                dataType: 'JSON',
                data: {id: id},
                success: (response) => {
                    if (typeof response === "object" && response.success) {
                        product = response;
                    }
                },
                error: (error) => {
                    alert(error.responseText);
                }
            });
            return product;
        };

        /**
         * Limpia los campos de captura de producto               
         */
        const clear = () => {
            $('#product_name').val('');
            $('#unit').val('');
            $('#unit_price').val('');
            $('#quantity').val('');
            $('#discount').val('');
            $('#productid').val('').focus();
        };
    });
})(jQuery);
