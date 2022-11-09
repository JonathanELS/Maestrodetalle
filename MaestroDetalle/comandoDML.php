<?php

class comandoDML {

    public static function GenerarSentenciaSelect($tabla, $campos, $filtro = "") {
        $cadena_campos = is_array($campos) ? implode(",", array_keys($campos)) : $campos;

        $comando = "SELECT $cadena_campos FROM $tabla";
        if (isset($filtro) && $filtro != "") {
            $comando .= " WHERE $filtro";
        }
        $comando .= ";";

        return $comando;
    }

    public static function GenerarSentenciaInsert($tabla, $arreglo = []) {
        $datos = comandoDML::eliminarVacios($arreglo);

        $campos = $datos['campos'];
        $valores = $datos['valores'];

        $cadena_campos = implode(",", array_values($campos));
        $cadena_valores = "";

        foreach ($valores as $valor) {
            if (is_bool($valor)) {
                $cadena_valores .= ($valor) ? 1 : 0 . ',';
            } else if (is_numeric($valor) || is_float($valor) || is_int($valor)) {
                $cadena_valores .= $valor . ',';
            } else if (comandoDML::ValidarFecha($valor, 'Y-m-d')) {
                $cadena_valores .= "STR_TO_DATE('$valor', '%Y-%m-%d'),";
            } else {
                $cadena_valores .= "'$valor',";
            }
        }

        return "INSERT INTO $tabla ($cadena_campos) VALUES (" . substr($cadena_valores, 0, -1) . ");";
    }

    public static function GenerarSentenciaUpdate($tabla, $arreglo = [], $filtro = "") {
        $datos = comandoDML::eliminarVacios($arreglo);
        
        $campos = $datos['campos'];
        $valores = $datos['valores'];
        
        $seteos = "";

        foreach ($valores as $i => $valor) {
            if (is_bool($valor)) {
                $seteos .= $campos[$i] . "=" . ($valor) ? 1 : 0 . ',';
            } else if (is_numeric($valor) || is_float($valor) || is_int($valor)) {
                $seteos .= $campos[$i] . "=" . $valor . ',';
            } else if (comandoDML::ValidarFecha($valor, 'Y-m-d')) {
                $seteos .= $campos[$i] . "=" . "STR_TO_DATE('$valor', '%Y-%m-%d'),";
            } else {
                $seteos .= $campos[$i] . "=" . "'" . $valor . "',";
            }
        }

        $comando = "UPDATE $tabla SET " . substr($seteos, 0, -1);
        if (isset($filtro) && $filtro != "") {
            $comando .= " WHERE $filtro";
        }
        $comando .= ";";

        return $comando;
    }

    public static function GenerarSentenciaDelete($tabla, $filtro = "") {
        $comando = "DELETE FROM $tabla";
        if (isset($filtro) && $filtro != "") {
            $comando .= " WHERE $filtro";
        }
        $comando .= ";";
        return $comando;
    }

    public static function ValidarFecha($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    private static function eliminarVacios($datos) {
        $campos = array_keys($datos);
        $valores = array_values($datos);
        foreach ($valores as $i => $valor) {
            if (is_null($valor) || !isset($valor) || $valor == '') {
                unset($campos[$i]);
                unset($valores[$i]);
            }
        }
        return array("campos" => $campos, "valores" => $valores);
    }

}
