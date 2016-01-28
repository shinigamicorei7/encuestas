<?php

if (!function_exists('with'))
{
	function with($object)
	{
		return $object;
	}
}

if (!function_exists('assets'))
{
	function assets($abstract, $secure = null)
	{
		$assetsDir = 'resources/assets/';
		return $assetsDir . $abstract;
	}
}

if (!function_exists('promedio'))
{
	function promedio($conjunto, $round = 0)
	{
		if (is_array($conjunto) and count($conjunto) > 0)
		{
			return round(array_sum($conjunto) / count($conjunto), $round);
		}

		return null;
	}
}

function min_mod()
{
	$args = func_get_args();

	if (!count($args[0]))
	{
		return null;
	}
	else
	{
		$min = false;
		foreach ($args[0] AS $value)
		{
			if (is_numeric($value))
			{
				$curval = floatval($value);
				if ($curval < $min || $min === false) $min = $curval;
			}
		}
	}

	return $min;
}

function max_mod($an_array)
{
	if (!count($an_array))
	{
		return null;
	}

	$largest_number = $an_array[0];
	// Loop to find the largest number
	for ($index = 0; $index < count($an_array); $index++)
	{
		if ($largest_number < $an_array[$index])
		{
			$largest_number = $an_array[$index];
		}
	}

	return intval($largest_number);
}