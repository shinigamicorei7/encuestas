<?php namespace Intisana\Encuesta;

use Intisana\DI\Contracts\Injectable;
use Intisana\Encuesta\Contracts\Pregunta;

class BackUp extends Injectable
{
	/** @var  integer */
	protected $serial_enc;
	/** @var  string */
	protected $titulo_enc;
	/** @var integer */
	protected $numero_personas = 0;
	/** @var Pregunta[] */
	protected $preguntas_enc;

	public function setPregunta(Pregunta $pregunta)
	{
		$this->preguntas_enc[] = $pregunta;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSerial()
	{
		return $this->serial_enc;
	}

	/**
	 * @param int $serial_enc
	 * @return BackUp
	 */
	public function setSerial($serial_enc)
	{
		$this->serial_enc = $serial_enc;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitulo()
	{
		return $this->titulo_enc;
	}

	/**
	 * @param string $titulo_enc
	 * @return BackUp
	 */
	public function setTitulo($titulo_enc)
	{
		$this->titulo_enc = $titulo_enc;
		return $this;
	}

	/**
	 * @return Contracts\Pregunta[]
	 */
	public function getPreguntas()
	{
		$di = $this->di;
		return array_map(function (Pregunta $enc) use ($di)
		{
			return $enc->setDi($di)->setCheckout(true);
		}, $this->preguntas_enc);
	}

	/**
	 * @return mixed
	 */
	public function getNumeroPersonas()
	{
		return $this->numero_personas;
	}

	/**
	 * @param integer $numero_personas
	 * @return $this
	 */
	public function setNumeroPersonas($numero_personas)
	{
		$this->numero_personas = $numero_personas;
		return $this;
	}

	public function save()
	{
		$serialized = serialize($this);

		$slug = str_replace(' ', '_', utf8_encode($this->getTitulo()));
		$date = date('Y-m-d_H:i:s');
		$dir = __DIR__ . '/../../storage/backup/';
		$extension = 'dat';

		$file = $dir . implode('.', array($date, $slug, $extension));
		@file_put_contents($file, $serialized, null);
	}

	public function restore_backup($file)
	{
		$serialized = file_get_contents($file);
		var_dump(unserialize($serialized));
	}
}