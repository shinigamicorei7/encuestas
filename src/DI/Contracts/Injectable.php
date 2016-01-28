<?php namespace Intisana\DI\Contracts;

use Intisana\DI\Container;

/**
 * Class Injectable
 * @property \PDO $db
 * @property \Twig_Environment $view
 */
class Injectable
{
	/**
	 * @var Container
	 */
	protected $di;

	/**
	 * Injectable constructor.
	 * @param Container $di
	 * @return $this
	 */
	public function setDi($di)
	{
		$this->di = $di;
		return $this;
	}


	public function __get($name)
	{
		return $this->di[$name];
	}
}