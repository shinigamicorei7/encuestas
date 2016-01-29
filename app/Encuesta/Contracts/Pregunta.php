<?php namespace Intisana\Encuesta\Contracts;

use Intisana\DI\Contracts\Injectable;

abstract class Pregunta extends Injectable implements Exportable
{
	protected $titulo;
	protected $template;
	protected $datos;
	protected $orden;
	protected $serial;
	protected $checkout = false;

	/**
	 * Pregunta constructor.
	 * @param $titulo
	 */
	public function __construct($titulo)
	{
		$this->titulo = $titulo;
	}

	public function render()
	{
		echo $this->view->render($this->template, array('pregunta' => $this));
	}

	public function setTitulo($titulo)
	{
		$this->titulo = $titulo;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitulo()
	{
		return $this->titulo;
	}

	/**
	 * @return mixed
	 */
	public function getDatos()
	{
		return $this->datos;
	}

	public function setOrden($orden)
	{
		$this->orden = $orden;
		return $this;
	}

	public function getOrden()
	{
		return $this->orden;
	}

	/**
	 * @return mixed
	 */
	public function getSerial()
	{
		return $this->serial;
	}

	/**
	 * @param mixed $serial
	 * @return $this
	 */
	public function setSerial($serial)
	{
		$this->serial = $serial;
		return $this;
	}

	abstract public function setRespuesta($respuesta);

	abstract public function renderReporte();

	abstract public function reset();

	abstract public function updateOption($params);

	/**
	 * @param boolean $checkout
	 * @return Pregunta
	 */
	public function setCheckout($checkout)
	{
		$this->checkout = $checkout;
		return $this;
	}

	protected function getEstiloTabla($horizontal_alignment = 'center', $bold = false)
	{
		return array(
			'font' => array(
				'bold' => $bold
			),
			'alignment' => array(
				'vertical' => 'center',
				'horizontal' => $horizontal_alignment,
				'wrap' => true
			),
			'borders' => array(
				'allborders' => array(
					'style' => 'thin',
				),
			)
		);
	}

	public function isCheckout()
	{
		return $this->checkout;
	}
}