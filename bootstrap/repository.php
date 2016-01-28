<?php

use Intisana\Encuesta\Repositorio;

require_once(__DIR__ . '/../vendor/autoload.php');

$di = require(__DIR__ . '/servicios.php');

return new Repositorio($di);