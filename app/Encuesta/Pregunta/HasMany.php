<?php namespace Intisana\Encuesta\Pregunta;

class HasMany extends HasOne
{
	protected $template = 'has_many.twig';

	public function setRespuesta($respuestas)
	{
		foreach ($respuestas as $respuesta)
		{
			parent::setRespuesta($respuesta);
		}
	}

	public function renderReporte()
	{
		$datos_temp = array_filter($this->datos, array($this, 'excluirCeros'));
		$datos = array(
			'data' => array(
				array(
					'type' => 'pie',
					'showInLegend' => true,
					'legendText' => '{label}',
					'dataPoints' => array_map(function ($row, $key)
					{
						return array('label' => ($key + 1), 'y' => $row[1], 'name' => utf8_encode($row[0]));
					}, $datos_temp, array_keys($datos_temp))
				)
			)
		);
		return $this->view->render('reportes/has_many.twig', array('pregunta' => $this, 'options' => json_encode($datos)));
	}


}