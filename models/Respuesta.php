<?php

namespace Model;

class Respuesta extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'fecha', 'areas_id', 'nivel_satisfaccion', 'comentario'];

    public $id;
    public $fecha;
    public $areas_id;
    public $nivel_satisfaccion;
    public $comentario;
   

    
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->fecha = $args['fecha'] ?? '';
        $this->areas_id = $args['areas_id'] ?? '';
        $this->nivel_satisfaccion = $args['nivel_satisfaccion'] ?? '';
        $this->comentario = $args['comentario'] ?? '';
        
    }
}
