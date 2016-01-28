<?php namespace Intisana\DI\Contracts;
use Intisana\DI\Container;

/**
 * Class Construible
 * @property \PDO $db
 * @property \Twig_Environment $view
 */
class Construible
{
	/**
	 * @var Container
	 */
	protected $di;

	/**
	 * Injectable constructor.
	 * @param Container $di
	 */
	public function __construct(Container $di)
	{
		$this->di = $di;
	}


	public function __get($name)
	{
		return $this->di[$name];
	}
}