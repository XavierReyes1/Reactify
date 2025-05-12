<?php

namespace Model;

class Areas extends ActiveRecord {
    protected static $tabla = 'areas';
    protected static $columnasDB = ['id',  'nombre'];

    public $id;
    public $nombre;


    
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
      
        
    }
}
