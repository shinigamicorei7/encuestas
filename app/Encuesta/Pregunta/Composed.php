<?php namespace Intisana\Encuesta\Pregunta;

use Intisana\Encuesta\Contracts\Pregunta;
use PHPExcel_Worksheet;

class Composed extends Pregunta
{
	protected $template = 'composed.twig';
	protected $tipo;
	protected $tipos;
	protected $sub_pregunta;

	public function setOption($value, $tipo)
	{
		$this->datos[] = array($value, 'tipo' => $tipo, 'contador' => 0, 'detalle' => array());
	}

	public function setRespuesta($respuestas)
	{
		switch ($this->tipo)
		{
			case 'has_many_with_details':
				foreach ($respuestas as $respuesta)
				{
					$datos = array_map(function ($row) use ($respuesta)
					{

						if ($row[0] === $respuesta[0])
						{
							$row['detalle'][] = isset($respuesta[1]) ? $respuesta[1] : 0;
							$row['contador'] = ($row['contador'] + 1);
						}
						return $row;
					}, $this->datos);
					$this->datos = $datos;
				}
				break;
			case 'has_one_with_details':
				$datos = array_map(function ($row) use ($respuestas)
				{
					if ($row[0] === $respuestas[0])
					{
						$row['detalle'][] = $respuestas[1];
						$row['contador'] = ($row['contador'] + 1);
					}

					return $row;
				}, $this->datos);
				$this->datos = $datos;
				break;
			default:
				$this->datos = $this->datos;
				break;
		}
	}

	/**
	 * @return mixed
	 */
	public function getTipo()
	{
		return $this->tipo;
	}

	/**
	 * @param mixed $tipo
	 */
	public function setTipo($tipo)
	{
		$this->tipo = $tipo;
	}

	/**
	 * @return mixed
	 */
	public function getSubPregunta()
	{
		return $this->sub_pregunta;
	}

	/**
	 * @param mixed $sub_pregunta
	 */
	public function setSubPregunta($sub_pregunta)
	{
		$this->sub_pregunta = $sub_pregunta;
	}

	public function renderReporte()
	{
		$datos_temp = array_filter($this->datos, array($this, 'excluirCeros'));
		$datos = array(
			'axisY' => array(
				'title' => utf8_encode($this->titulo),
				'includeZero' => false,
				'titleFontSize' => 12,
				'interval' => 1,
				'includeZero' => false
			),
			'axisY2' => array(
				'title' => utf8_encode($this->sub_pregunta) . ' (Rango)',
				'includeZero' => false,
				'titleFontSize' => 12
			),
			'axisX' => array(
				'interval' => 1,
				'includeZero' => false
			),
			'toolTip' => array(
				'shared' => true
			),
			'data' => array(
				array(
					'type' => 'line',
					'showInLegend' => true,
					'legendText' => utf8_encode($this->titulo),
					'dataPoints' => array_map(array($this, 'getDataSeries1'), $datos_temp, array_keys($datos_temp))
				),
				array(
					'type' => 'rangeSplineArea',
					'axisYType' => 'secondary',
					'showInLegend' => true,
					'legendText' => utf8_encode($this->sub_pregunta),
					'dataPoints' => array_map(array($this, 'getDataSeries2'), $datos_temp, array_keys($datos_temp))
				)
			)
		);
		return $this->view->render('reportes/composed.twig', array('pregunta' => $this, 'options' => json_encode($datos)));
	}

	protected function getDataSeries1($row, $key)
	{
		return array('label' => $key + 1, 'y' => $row['contador'], 'name' => utf8_encode($row[0]));
	}

	protected function getDataSeries2($row, $key)
	{
		$min = min_mod($row['detalle']);
		$max = max_mod($row['detalle']);
		return array('label' => $key + 1, 'y' => array($min, $max), 'name' => $row[0] . ' (Rango)');
	}

	protected function excluirCeros($row)
	{
		return $row['contador'] != 0;
	}

	public function reset()
	{
		$this->datos = array_map(function ($row)
		{
			$row['contador'] = 0;
			$row['detalle'] = array();
			return $row;
		}, $this->datos);
	}

	public function updateOption($params)
	{
		$this->datos = array_map(function ($row)
		{

		});
	}

	/**
	 * @param PHPExcel_Worksheet $worksheet
	 * @param array $style_h2
	 * @return PHPExcel_Worksheet
	 */
	public function exportExcel(PHPExcel_Worksheet $worksheet, array $style_h2)
	{
		$last_row = $worksheet->getHighestDataRow();
		$last_row += 2;
		$max_col = $worksheet->getHighestDataColumn();

		$worksheet->mergeCells("A{$last_row}:{$max_col}{$last_row}");
		$worksheet->setCellValue("A{$last_row}", utf8_encode($this->getTitulo()) . ' - ' . utf8_encode($this->getSubPregunta()));
		$worksheet->getStyle("A{$last_row}:{$max_col}{$last_row}")->applyFromArray($style_h2);
		$worksheet->getRowDimension($last_row)->setRowHeight(20);

		$last_row += 2;
		$merge = $last_row + 1;
		$worksheet->mergeCells("C{$last_row}:C{$merge}");
		$worksheet->setCellValue("C{$last_row}", utf8_encode($this->getTitulo()));
		$merge = $last_row + 1;
		$worksheet->mergeCells("D{$last_row}:D{$merge}");
		$worksheet->setCellValue("D{$last_row}", 'Votos');
		$worksheet->mergeCells("E{$last_row}:G{$last_row}");
		$worksheet->setCellValue("E{$last_row}", utf8_encode($this->getSubPregunta()));
		$worksheet->getRowDimension($last_row)->setRowHeight(27);
		$last_row++;
		$worksheet->setCellValue("E{$last_row}", utf8_encode('Mínimo'));
		$worksheet->setCellValue("F{$last_row}", utf8_encode('Máximo'));
		$worksheet->setCellValue("G{$last_row}", 'Promedio');
		$first_row = $last_row - 1;
		$last_row += 1;

		foreach ($this->getDatos() as $key => $dato)
		{
			$worksheet->setCellValue("B{$last_row}", $key + 1);
			$worksheet->setCellValue("C{$last_row}", utf8_encode($dato[0]));
			if (mb_strlen($dato[0]) > 45)
			{
				$worksheet->getRowDimension($last_row)->setRowHeight(25);
			}
			$worksheet->setCellValue("D{$last_row}", $dato['contador']);
			$worksheet->setCellValue("E{$last_row}", min_mod($dato['detalle']));
			$worksheet->setCellValue("F{$last_row}", max_mod($dato['detalle']));
			$worksheet->setCellValue("G{$last_row}", promedio($dato['detalle'], 1));
			$last_row++;
		}

		$merged = $first_row + 1;
		$worksheet->getStyle("C{$first_row}:G{$merged}")->applyFromArray($this->getEstiloTabla('center', true));
		$last_row -= 1;
		$first_row += 2;
		$worksheet->getStyle("B{$first_row}:G{$last_row}")->applyFromArray($this->getEstiloTabla());

		return $worksheet;
	}
}