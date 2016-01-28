<?php namespace Intisana\Encuesta\Pregunta;

use Intisana\Encuesta\Contracts\Pregunta;
use PHPExcel_Chart;
use PHPExcel_Chart_DataSeries as DataSeries;
use PHPExcel_Chart_DataSeriesValues as DataSeriesValues;
use PHPExcel_Chart_Layout as Layout;
use PHPExcel_Chart_Legend as Legend;
use PHPExcel_Chart_PlotArea as PlotArea;
use PHPExcel_Chart_Title;
use PHPExcel_Worksheet;

class Simple extends Pregunta
{
	protected $datos = array();
	protected $respaldo = array();
	protected $tipo = null;
	protected $template = 'simple.twig';

	public function setRespuesta($respuesta)
	{
		if (empty($respuesta) || $respuesta == ' ' || $respuesta == '')
		{
			return;
		}
		if (array_key_exists($respuesta, $this->datos))
		{
			$this->datos[$respuesta] += 1;
		}
		else
		{
			$this->datos[$respuesta] = 1;
			$this->respaldo[] = $respuesta;
		}
	}

	/**
	 * @return null
	 */
	public function getTipo()
	{
		return $this->tipo;
	}

	/**
	 * @param null $tipo
	 */
	public function setTipo($tipo)
	{
		$this->tipo = $tipo;
	}


	public function renderReporte()
	{
		$datos = array(
			'data' => array(
				array(
					'type' => 'pie',
					'showInLegend' => true,
					'legendText' => '{label}',
					'dataPoints' => array_map(function ($valor, $key, $num)
					{
						return array('label' => ($num + 1), 'y' => $valor, 'name' => utf8_encode($key));
					}, $this->datos, array_keys($this->datos), array_keys($this->respaldo))
				)
			)
		);
		return $this->view->render('reportes/simple.twig', array('pregunta' => $this, 'options' => json_encode($datos)));
	}

	public function reset()
	{
		$this->respaldo = array();
		$this->datos = array();
	}

	public function updateOption($params)
	{
		return true;
	}

	public function exportExcel(PHPExcel_Worksheet $worksheet, array $style_h2)
	{
		$last_row = $worksheet->getHighestDataRow();
		$last_row += 2;
		$max_col = $worksheet->getHighestDataColumn();

		$worksheet->mergeCells("A{$last_row}:{$max_col}{$last_row}");
		$worksheet->setCellValue("A{$last_row}", utf8_encode($this->getTitulo()));
		$worksheet->getStyle("A{$last_row}:{$max_col}{$last_row}")->applyFromArray($style_h2);
		$worksheet->getRowDimension($last_row)->setRowHeight(20);

		$last_row += 2;
		$worksheet->setCellValue("C{$last_row}", 'Respuestas');
		$worksheet->setCellValue("D{$last_row}", 'Votos');

		$first_row = $last_row;
		$last_row += 1;
		$count = 1;

		foreach ($this->getDatos() as $texto => $contador)
		{
			$worksheet->setCellValue("B{$last_row}", $count);
			$worksheet->setCellValue("C{$last_row}", utf8_encode($texto));
			if (mb_strlen($texto) > 45)
			{
				$worksheet->getRowDimension($last_row)->setRowHeight(27);
			}
			$worksheet->setCellValue("D{$last_row}", $contador);
			$last_row++;
			$count++;
		}

		$last_row -= 1;
		$worksheet->getStyle("C{$first_row}:D{$last_row}")->applyFromArray($this->getEstiloTabla('center', true));
		$first_row++;
		$worksheet->getStyle("B{$first_row}:D{$last_row}")->applyFromArray($this->getEstiloTabla());

		$first_row -= 1;
		$top_chart = $first_row - 1;
		$bottom_chart = $first_row + 12;

		$chart1 = $this->getChart($first_row, $last_row, $top_chart, $bottom_chart);

		$worksheet->addChart($chart1);
		$worksheet->setCellValue("A{$bottom_chart}", "");

		return $worksheet;
	}

	/**
	 * @param $first_row
	 * @param $last_row
	 * @param $top_chart
	 * @param $bottom_chart
	 * @return PHPExcel_Chart
	 */
	protected function getChart($first_row, $last_row, $top_chart, $bottom_chart)
	{
		$labels = array(
			new DataSeriesValues('String', 'Encuesta!$D$' . $first_row, null, 1) // votos
		);
		$first_row += 1;
		$xLabels = array(
			new DataSeriesValues('String', 'Encuesta!$B$' . $first_row . ':$B$' . $last_row, null, count($this->datos)) // Opciones
		);
		$yValues = array(
			new DataSeriesValues('Number', 'Encuesta!$D$' . $first_row . ':$D$' . $last_row, null, count($this->datos)) // Opciones
		);
		$series1 = new DataSeries(DataSeries::TYPE_PIECHART, null, range(0, count($yValues) - 1), $labels, $xLabels, $yValues);
		$layout1 = new Layout();
		$layout1->setShowVal(true);
		$layout1->setShowPercent(true);
		$plotarea1 = new PlotArea($layout1, array($series1));
		$legend1 = new Legend(Legend::POSITION_RIGHT, null, false);
		$titulo = new PHPExcel_Chart_Title(utf8_encode("Gráfico - " . $this->getOrden()));
		$chart1 = new PHPExcel_Chart('chart' . $this->getSerial(), $titulo, $legend1, $plotarea1, true, 0, null, null);

		$chart1->setTopLeftPosition("F" . $top_chart);
		$chart1->setBottomRightPosition("J" . $bottom_chart);

		return $chart1;
	}

}