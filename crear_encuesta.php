<?php

header('Content-Type: text/html');
use Intisana\Encuesta\Repositorio;

require 'vendor/autoload.php';

$di = require 'bootstrap/servicios.php';

$repositorio = new Repositorio($di);
$params = json_decode(file_get_contents("storage/preguntas.json"));
$repositorio->create($params);