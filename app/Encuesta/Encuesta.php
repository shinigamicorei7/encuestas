<?php namespace Intisana\Encuesta;

use Intisana\DI\Contracts\Injectable;
use Intisana\Encuesta\Pregunta;
use PDO;

class Encuesta extends Injectable
{
	/**
	 * @var integer
	 */
	protected $tipo;
	/**
	 * @var  string
	 */
	protected $titulo;
	/**
	 * @var Pregunta[]
	 */
	protected $preguntas;
	/**
	 * @var integer
	 */
	protected $serial_enc = null;

	protected $detalles;
	protected $numero_personas = 0;

	public function addPregunta(Pregunta $pregunta)
	{
		$serial = $this->getSerial();
		$titulo = $pregunta->getTitulo();
		$orden = $pregunta->getOrden();
		$serialized = serialize($pregunta);
		$ps = $this->db->prepare('INSERT INTO encuesta_preguntas (serial_enc_encpre, titulo_encpre, objeto_serializado_encpre, orden_encpre) VALUES (?, ?, ?, ?)');
		$ps->bindParam(1, $serial, PDO::PARAM_INT);
		$ps->bindParam(2, $titulo, PDO::PARAM_STR);
		$ps->bindParam(3, $serialized);
		$ps->bindParam(4, $orden, PDO::PARAM_INT);
		if (!$ps->execute())
		{
			throw new \Exception('No se pudo almacenar la pregunta [' . $pregunta->getTitulo() . print_r($ps->errorInfo()) . ']');
		}
	}

	/**
	 * @return Pregunta[]
	 */
	public function getPreguntas()
	{
		$serial = $this->getSerial();
		$ps = $this->db->prepare('SELECT serial_encpre,titulo_encpre,objeto_serializado_encpre,orden_encpre FROM encuesta_preguntas WHERE serial_enc_encpre = ? ORDER BY orden_encpre');
		$ps->bindParam(1, $serial, PDO::PARAM_INT);
		if ($ps->execute() AND ($rows = $ps->fetchAll(PDO::FETCH_OBJ)))
		{
			foreach ($rows as $row)
			{
				/** @var Pregunta $pregunta */
				$pregunta = unserialize($row->objeto_serializado_encpre);
				$this->preguntas[$row->serial_encpre] = $pregunta->setTitulo($row->titulo_encpre)
																 ->setDi($this->di)
																 ->setSerial($row->serial_encpre);
			}
		}
		return $this->preguntas;
	}

	public function setDetalles($detalles)
	{
		$this->detalles = is_array($detalles) ? array_map('utf8_decode', $detalles) : utf8_decode($detalles);
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDetalles()
	{
		return $this->detalles;
	}

	/**
	 * @param string $titulo
	 * @return $this
	 */
	public function setTitulo($titulo)
	{
		$this->titulo = $titulo;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitulo()
	{
		return $this->titulo;
	}

	public function setSerial($serial_enc)
	{
		$this->serial_enc = $serial_enc;
		return $this;
	}

	/**
	 * @param int $numero_personas
	 * @return Encuesta
	 */
	public function setNumeroPersonas($numero_personas)
	{
		$this->numero_personas = $numero_personas;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getNumeroPersonas()
	{
		return $this->numero_personas;
	}

	/**
	 * @return Encuesta
	 */
	public function aumentaNumeroPersonas()
	{
		$this->numero_personas++;
		return $this;
	}

	public function updatePregunta($serial, $respuesta)
	{
		$pregunta = $this->getPregunta($serial);
		$pregunta->setRespuesta($respuesta);
		$pregunta->setDi(null);
		$serial_enc = $this->getSerial();
		$serialized = serialize($pregunta);
		$ps = $this->db->prepare('UPDATE encuesta_preguntas SET objeto_serializado_encpre = ? WHERE serial_encpre = ? AND serial_enc_encpre = ?');
		$ps->bindParam(1, $serialized, PDO::PARAM_LOB);
		$ps->bindParam(2, $serial, PDO::PARAM_INT);
		$ps->bindParam(3, $serial_enc, PDO::PARAM_INT);

		if (!$ps->execute())
		{
			trigger_error('No se pudo actualizar la pregunta [' . $serial . '].', E_USER_ERROR);
			die;
		}
	}

	public function getSerial()
	{
		if (is_null($this->serial_enc))
		{
			$titulo = $this->titulo;
			$ps = $this->db->prepare('SELECT serial_enc FROM encuesta WHERE titulo_enc = ?');
			$ps->bindParam(1, $titulo, PDO::PARAM_STR);
			if ($ps->execute() AND ($datos = $ps->fetch(PDO::FETCH_OBJ)))
			{
				return $this->serial_enc = $datos->serial_enc;
			}
			else
			{
				trigger_error('La encuesta [' . $this->titulo . '] no se creo con éxito.', E_USER_ERROR);
				die;
			}
		}

		return $this->serial_enc;
	}

	/**
	 * @param $serial
	 * @return \Intisana\Encuesta\Pregunta
	 * @throws \Exception
	 */
	public function getPregunta($serial)
	{
		$serial_enc = $this->getSerial();
		$ps = $this->db->prepare('SELECT serial_encpre,titulo_encpre,objeto_serializado_encpre FROM encuesta_preguntas WHERE serial_encpre = ? AND serial_enc_encpre = ?');
		$ps->bindParam(1, $serial, PDO::PARAM_INT);
		$ps->bindParam(2, $serial_enc, PDO::PARAM_INT);
		if ($ps->execute() AND ($rs = $ps->fetch(PDO::FETCH_OBJ)))
		{
			/** @var Pregunta $obj */
			$obj = unserialize($rs->objeto_serializado_encpre);


			return $obj->setTitulo($rs->titulo_encpre)
					   ->setDi($this->di)
					   ->setSerial($rs->serial_encpre);;
		}

		throw new \Exception('La pregunta con el serial [' . $serial . '] no existe, o no esta relacionada con la encuesta actual.');
	}

	public function render()
	{
		echo $this->view->render('encuesta.twig', array('encuesta' => $this));
	}

	public function renderReporte()
	{
		$mensaje = false;
		if (isset($_SESSION['mensaje_enc']))
		{
			$mensaje = $_SESSION['mensaje_enc'];
			unset($_SESSION['mensaje_enc']);
		}
		echo $this->view->render('reportes/encuestas.twig', array('encuesta' => $this, 'mensaje' => $mensaje));
	}
}
