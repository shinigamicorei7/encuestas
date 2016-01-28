<?php
session_start();

/** @var \Intisana\Encuesta\Repositorio $repositorio */
$repositorio = require_once 'bootstrap/repository.php';

$encuesta = $repositorio->first();

$backup = new \Intisana\Encuesta\BackUp();

$backup->setSerial($encuesta->getSerial())
	   ->setTitulo($encuesta->getTitulo())
	   ->setNumeroPersonas($encuesta->getNumeroPersonas());

$encuesta->setNumeroPersonas(0);

$repositorio->update($encuesta);

$_SESSION['mensaje_enc'][] = '<ul>';
$_SESSION['mensaje_enc'][] = '<li>El contador de la encuesta a sido reiniciado &crarr;</li>';

$encuesta->setDi($di);

foreach ($encuesta->getPreguntas() as $pregunta)
{
	$backup->setPregunta($pregunta);
	$pregunta->reset();
	$repositorio->updatePregunta($pregunta);
	$_SESSION['mensaje_enc'][] = sprintf('<li>Los contadores de la pregunta [%s] han sido reiniciados</li>', $pregunta->getTitulo());
}

$_SESSION['mensaje_enc'][] = '</ul>';

$backup->save();

header('Location: reporte.php');
