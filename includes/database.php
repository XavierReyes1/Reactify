<?php
try {
$db = new PDO("mysql: host=localhost; dbname=reactify",
    "root", 
   "root"
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Error: No se pudo conectar a la base de datos.";
    echo "Código de error: " . $e->getCode();
    echo "Mensaje: " . $e->getMessage();
    exit;
}
