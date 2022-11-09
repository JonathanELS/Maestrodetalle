<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class conexion {

    private $mysqli;
    private $resultado_consulta;
    private $resultado;

    function __construct($servidor, $usuario, $password, $basedatos) {
        $this->mysqli = new mysqli($servidor, $usuario, $password, $basedatos);
    }

    function ejecutar_consulta($consulta, $limite = 0) {
        if (str_starts_with(strtoupper($consulta), 'SELECT')) {
            $this->resultado = [];
            $this->resultado_consulta = $this->mysqli->query($consulta);
            if ($this->resultado_consulta) {
                $this->resultado = $this->resultado_consulta->fetch_all(MYSQLI_BOTH);
                $this->resultado_consulta->free_result();
            }
            if ($limite > 0 && count($this->resultado) > 0 && $limite <= count($this->resultado)) {
                return ($limite == 1) ? array_slice($this->resultado, 0, $limite)[0] : array_slice($this->resultado, 0, $limite);
            }
            return $this->resultado;
        } else {
            return $this->mysqli->real_query($consulta);
        }
    }

    function obtener_campos($tabla) {
        $campos = [];
        $consulta = "SELECT group_concat(COLUMN_NAME) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tabla';";
        $this->resultado = $this->ejecutar_consulta($consulta, 1);
        if (count($this->resultado) > 0) {
            $columnas = explode(",", $this->resultado[0]);
            foreach ($columnas as $col) {
                $campos[$col] = "";
            }
        }
        return $campos;
    }
    
    function obtenerSiguienteIndice($tabla, $llave_primaria){
        $consulta = "SELECT MAX($llave_primaria) + 1 AS id FROM $tabla;";
        $this->resultado = $this->ejecutar_consulta($consulta, 1);
        return $this->resultado['id'];
    }

    function __destruct() {
        $this->mysqli->close();
    }

}
