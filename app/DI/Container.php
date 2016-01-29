<?php namespace Intisana\DI;

use Closure;

class Container implements \ArrayAccess
{

	protected $compiled;
	protected $saved;

	public function offsetExists($offset)
	{
		return isset($this->compiled[$offset]) || isset($this->saved[$offset]);
	}

	public function offsetGet($offset)
	{
		if (isset($this->compiled[$offset]))
		{
			return $this->compiled[$offset];
		}

		$object = $this->saved[$offset]($this);
		return $this->compiled[$offset] = $object;
	}

	public function offsetSet($offset, $value)
	{
		if (!$value instanceof Closure)
		{
			$value = function () use ($value)
			{
				return $value;
			};
		}

		$this->saved[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		// TODO: Implement offsetUnset() method.
	}

	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	public function __set($name, $value)
	{
		$this->offsetSet($name, $value);
	}

	/**
	 * @param  \Closure $closure
	 * @return \Closure
	 */
	public function share(Closure $closure)
	{
		return function ($container) use ($closure)
		{
			static $object;
			if (is_null($object))
			{
				$object = $closure($container);
			}
			return $object;
		};
	}
}