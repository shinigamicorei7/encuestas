<?php

$di = new \Intisana\DI\Container();

$di['config'] = array(
	'database' => array(
		'dsn' => 'mysql:host=localhost;dbname=test',
		'user' => 'root',
		'password' => 'mysql'
	),
	'view' => array(
		'paths' => array(
			__DIR__ . '/../resources/views'
		),
		'options' => array(
			'debug' => true,
			'charset' => 'iso-8859-1'
		)
	)
);

$di['db'] = function ($di)
{
	$config = $di['config']['database'];

	return new PDO($config['dsn'], $config['user'], $config['password']);
};

$di['view.loader'] = function ($di)
{
	$config = $di['config']['view'];

	return new Twig_Loader_Filesystem($config['paths']);
};

$di['view'] = function ($di)
{
	$config = $di['config']['view'];

	$twig = new Twig_Environment($di['view.loader'], $config['options']);

	if ($config['options']['debug'] === true)
	{
		$twig->addExtension(new Twig_Extension_Debug());
	}

	$twig->addFunction(new Twig_SimpleFunction('assets', 'assets'));
	$twig->addFilter(new Twig_SimpleFilter('promedio', 'promedio'));
	$twig->addFilter(new Twig_SimpleFilter('min_mod', 'min_mod'));
	$twig->addFilter(new Twig_SimpleFilter('max_mod', 'max_mod'));

	return $twig;
};

return $di;