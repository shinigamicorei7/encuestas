<?php
session_start();

/** @var \Intisana\Encuesta\Repositorio $repositorio */
$repositorio = require_once 'bootstrap/repository.php';

$encuesta = $repositorio->first();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$pregunta = $encuesta->getPregunta($_POST['serial']);
	if (isset($_POST['options']))
	{
		$pregunta->updateOption($_POST['options']);
	}
	$pregunta->setTitulo($_POST['titulo']);
	$repositorio->updatePregunta($pregunta);
}

$encuesta->renderReporte();