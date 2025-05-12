<?php 


use Model\ActiveRecord;
require __DIR__ . '/../vendor/autoload.php';


require 'database.php';

// Conectarnos a la base de datos

ActiveRecord::setDB($db);