<?php

include_once './config.php';
include_once './conexion.php';

$product = [];

if (filter_input(INPUT_SERVER, "REQUEST_METHOD")=== 'POST'){
    $productId = filter_input(INPUT_POST, 'id');
    
    $consulta = "SELECT product_id, product_name, quantity_per_unit, unit_price "
            . "FROM products WHERE product_id = $productId;";
    
    $conn = new conexion($servidor, $usuario, $password, $basedatos);
    $product = $conn->ejecutar_consulta($consulta, 1);
    
    $product['success'] = false;
    
    if (count($product) > 0) {
        $product['success'] = true;
    }
}

echo json_encode($product);