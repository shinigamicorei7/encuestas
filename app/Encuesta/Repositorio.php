<?php namespace Intisana\Encuesta;

use Exception;
use Intisana\DI\Contracts\Construible;
use Intisana\Encuesta\Contracts\Pregunta;
use Intisana\Encuesta\Pregunta\Composed;
use Intisana\Encuesta\Pregunta\HasMany;
use Intisana\Encuesta\Pregunta\HasOne;
use Intisana\Encuesta\Pregunta\Simple;
use Intisana\Encuesta\Pregunta\YesOrNot;
use PDO;

class Repositorio extends Construible
{
	/**
	 * @param $id
	 * @return Encuesta
	 * @throws Exception
	 */
	public function find($id)
	{
		$ps = $this->db->prepare('SELECT serial_enc,objeto_serializado_enc FROM encuesta WHERE serial_enc = ?');
		$ps->bindParam(1, $id, PDO::PARAM_INT);

		if ($ps->execute() AND ($rs = $ps->fetch(PDO::FETCH_OBJ)))
		{
			/** @var Encuesta $encuesta */
			$encuesta = unserialize($rs->objeto_serializado_enc);

			return $encuesta->setDi($this->di)->setSerial($rs->serial_enc);
		}

		throw new Exception('La encuesta solicita no existe');
	}

	/**
	 * @return Encuesta
	 * @throws Exception
	 */
	public function first()
	{
		$ps = $this->db->query('SELECT serial_enc,titulo_enc,objeto_serializado_enc FROM encuesta LIMIT 1');

		if ($rs = $ps->fetch(PDO::FETCH_OBJ))
		{
			/** @var Encuesta $encuesta */
			$encuesta = unserialize($rs->objeto_serializado_enc);

			return $encuesta->setTitulo($rs->titulo_enc)
							->setDi($this->di)
							->setSerial($rs->serial_enc);
		}

		throw new Exception('La encuesta solicita no existe');
	}

	public function create($params)
	{
		/** @var Encuesta $encuesta */
		$encuesta = new Encuesta();
		$encuesta->setTitulo(utf8_decode($params->titulo))
				 ->setDetalles($params->detalles);
		$serialized = serialize($encuesta);
		$ps = $this->db->prepare('INSERT INTO encuesta (titulo_enc,objeto_serializado_enc) VALUES (:titulo,:encuesta)');
		$datos = array(':titulo' => $encuesta->getTitulo(), ':encuesta' => $serialized);
		if ($ps->execute($datos))
		{
			echo sprintf('<h1>Se creó la encuesta: "%s" </h1>', $encuesta->getTitulo());
			$preguntas = $params->preguntas;
			$encuesta->setDi($this->di);
			foreach ($preguntas->has_one as $key => $detalles)
			{
				$pregunta = new HasOne(utf8_decode($key));
				$pregunta->setOrden($detalles->orden);
				foreach ($detalles->opciones as $option)
				{
					$pregunta->setOption(utf8_decode($option));
				}
				$encuesta->addPregunta($pregunta);
				echo sprintf('<p>Se agrega la pregunta: "%s"</p>', $pregunta->getTitulo());
				echo sprintf('<p>De tipo: "%s"</p>', 'Selección Simple');
			}

			foreach ($preguntas->has_many as $key => $detalles)
			{
				$pregunta = new HasMany(utf8_decode($key));
				$pregunta->setOrden($detalles->orden);
				foreach ($detalles->opciones as $option)
				{
					$pregunta->setOption(utf8_decode($option));
				}
				$encuesta->addPregunta($pregunta);
				echo sprintf('<p>Se agrega la pregunta: "%s"</p>', $pregunta->getTitulo());
				echo sprintf('<p>De tipo: "%s"</p>', 'Selección Multiple');
			}

			foreach ($preguntas->simple as $key => $detalles)
			{
				$pregunta = new Simple(utf8_decode($key));
				$pregunta->setOrden($detalles->orden);
				$encuesta->addPregunta($pregunta);
				echo sprintf('<p>Se agrega la pregunta: "%s"</p>', $pregunta->getTitulo());
				echo sprintf('<p>De tipo: "%s"</p>', 'Simple');
			}

			foreach ($preguntas->yes_or_not as $key => $detalles)
			{
				$pregunta = new YesOrNot(utf8_decode($key));
				$pregunta->setOrden($detalles->orden);
				$encuesta->addPregunta($pregunta);
				echo sprintf('<p>Se agrega la pregunta: "%s"</p>', $pregunta->getTitulo());
				echo sprintf('<p>De tipo: "%s"</p>', 'Sí o No');
			}

			foreach ($preguntas->composed as $composed)
			{
				$pregunta = new Composed(utf8_decode($composed->titulo));
				$pregunta->setTipo($composed->tipo);
				$pregunta->setOrden($composed->orden);
				$pregunta->setSubPregunta(utf8_decode($composed->sub_pregunta));
				foreach ($composed->opciones as $option)
				{
					$pregunta->setOption(utf8_decode($option[0]), $option[1]);
				}
				$encuesta->addPregunta($pregunta);
				echo sprintf('<p>Se agrega la pregunta: "%s", "%s"</p>', $pregunta->getTitulo(), $pregunta->getSubPregunta());
				echo sprintf('<p>De tipo: "%s"</p>', 'Compuesta');
			}
		}
	}

	public function update(Encuesta $encuesta)
	{
		$encuesta->setDi(null);
		$serial_enc = $encuesta->getSerial();
		$serialized = serialize($encuesta);
		$ps = $this->db->prepare('UPDATE encuesta SET objeto_serializado_enc = ? WHERE serial_enc = ?');
		$ps->bindParam(1, $serialized, PDO::PARAM_LOB);
		$ps->bindParam(2, $serial_enc, PDO::PARAM_INT);
		if (!$ps->execute())
		{
			throw new Exception('No se pudo actualizar la encuesta [' . $encuesta->getTitulo() . ']');
		}
	}

	public function updatePregunta(Pregunta $pregunta)
	{
		$pregunta->setDi(null);
		$serial_encpre = $pregunta->getSerial();
		$titulo = $pregunta->getTitulo();
		$serialized = serialize($pregunta);
		$ps = $this->db->prepare('UPDATE encuesta_preguntas SET titulo_encpre = ? ,objeto_serializado_encpre = ? WHERE serial_encpre = ?');
		$ps->bindParam(1, $titulo, PDO::PARAM_STR);
		$ps->bindParam(2, $serialized, PDO::PARAM_LOB);
		$ps->bindParam(3, $serial_encpre, PDO::PARAM_INT);
		if (!$ps->execute())
		{
			throw new \Exception('No se pudo actualizar la pregunta [' . $pregunta->getTitulo() . ']');
		}
	}
}