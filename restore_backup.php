<?php
require_once('vendor/autoload.php');
$di = require_once('bootstrap/servicios.php');

$respaldos = array();
foreach (glob('./storage/backup/*.dat') as $file)
{
	$datos = explode('.', basename($file));
	$fecha = str_replace('_', ' ', $datos[0]);
	$nombre = utf8_decode(str_replace('_', ' ', $datos[1]));

	$respaldos[$nombre][] = array('fecha' => $fecha, 'archivo' => $file);
}
echo $di['view']->render('backups.twig', compact('respaldos'));
