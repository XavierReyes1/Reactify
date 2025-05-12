<?php
namespace Model;

use PDO;
use PDOException;

class ActiveRecord {

    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    public $id;
    public $imagen_actual;
    protected static $alertas = [];

    public static function setDB($database) {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    public static function consultarSQL($query, $params = []) {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $objetos = [];
        foreach($resultados as $registro) {
            $objetos[] = static::crearObjeto($registro);
        }

        return $objetos;
    }

    protected static function crearObjeto($registro) {
        $objeto = new static;
        foreach($registro as $key => $value) {
            if(property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos() {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sanitizarAtributos() {
        // En PDO los valores se manejan con parámetros preparados
        return $this->atributos();
    }

    public function sincronizar($args = []) {
        foreach($args as $key => $value) {
            if(property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    public function guardar() {
        return isset($this->id) ? $this->actualizar() : $this->crear();
    }

    public static function all() {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC";
        return self::consultarSQL($query);
    }

    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = :id LIMIT 1";
        $resultados = self::consultarSQL($query, ['id' => $id]);
        return array_shift($resultados);
    }

    public static function get($limite) {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY id DESC LIMIT :limite";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $objetos = [];
        foreach($resultados as $registro) {
            $objetos[] = static::crearObjeto($registro);
        }
        return $objetos;
    }

    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE $columna = :valor LIMIT 1";
        $resultados = self::consultarSQL($query, ['valor' => $valor]);
        return array_shift($resultados);
    }

    public function crear() {
        $atributos = $this->sanitizarAtributos();

        $columnas = array_keys($atributos);
        $valoresMarcadores = array_map(fn($col) => ':' . $col, $columnas);

        $query = "INSERT INTO " . static::$tabla . " (" . join(', ', $columnas) . ") ";
        $query .= "VALUES (" . join(', ', $valoresMarcadores) . ")";

        $stmt = self::$db->prepare($query);
        foreach($atributos as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $resultado = $stmt->execute();
        if ($resultado) {
            $this->id = self::$db->lastInsertId();
        }

        return [
            'resultado' => $resultado,
            'id' => $this->id
        ];
    }

    public function actualizar() {
        $atributos = $this->sanitizarAtributos();

        $valores = [];
        foreach($atributos as $key => $value) {
            $valores[] = "$key = :$key";
        }

        $query = "UPDATE " . static::$tabla . " SET ";
        $query .= join(', ', $valores);
        $query .= " WHERE id = :id LIMIT 1";

        $stmt = self::$db->prepare($query);
        foreach($atributos as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function eliminar() {
        $query = "DELETE FROM " . static::$tabla . " WHERE id = :id LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
