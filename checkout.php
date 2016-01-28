<?php

require_once('vendor/autoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$di = require_once('bootstrap/servicios.php');
	/** @var \Intisana\Encuesta\BackUp $encuesta */
	$encuesta = unserialize(file_get_contents($_POST['file']));
	$encuesta->setDi($di);
	$mensaje = array('<b>Esta es una vista previa de una encuesta respaldada</b>');
	$isCheckout = true;
	echo $di['view']->render('reportes/encuestas.twig', compact('encuesta', 'mensaje', 'isCheckout'));
}
