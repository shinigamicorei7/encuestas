<?php

/** @var \Intisana\Encuesta\Repositorio $repositorio */
$repositorio = require_once 'bootstrap/repository.php';

$encuesta = $repositorio->first();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	foreach ($_POST['preguntas'] as $serial => $respuesta)
	{
		$encuesta->updatePregunta($serial, $respuesta);
	}
	$encuesta->aumentaNumeroPersonas();
	$repositorio->update($encuesta);
	echo $di['view']->render('tanks.twig');
	die();
}

$encuesta->render();