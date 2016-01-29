<?php namespace Intisana\Encuesta\Pregunta;

use Intisana\Encuesta\Contracts\Pregunta;
use PHPExcel_Chart;
use PHPExcel_Chart_DataSeries as DataSeries;
use PHPExcel_Chart_DataSeriesValues as DataSeriesValues;
use PHPExcel_Chart_Layout as Layout;
use PHPExcel_Chart_Legend as Legend;
use PHPExcel_Chart_PlotArea as PlotArea;
use PHPExcel_Chart_Title;
use PHPExcel_Worksheet as Worksheet;

class HasOne extends Pregunta
{
	protected $template = 'has_one.twig';

	public function setOption($value, $default = 0)
	{
		$this->datos[] = array($value, $default);
	}

	public function setRespuesta($respuesta)
	{
		$datos = array_map(function ($row) use ($respuesta)
		{
			if ($row[0] === $respuesta)
			{
				return array($respuesta, ($row[1] + 1));
			}
			else
			{
				return $row;
			}
		}, $this->datos);

		$this->datos = $datos;
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
		return $this->view->render('reportes/has_one.twig', array('pregunta' => $this, 'options' => json_encode($datos)));
	}

	protected function excluirCeros($row)
	{
		return $row[1] != 0;
	}

	public function reset()
	{
		$this->datos = array_map(function ($row)
		{
			$row[1] = 0;
			return $row;
		}, $this->datos);
	}

	public function updateOption($params)
	{
		foreach ($params as $key => $param)
		{
			$actual = $this->datos[$key][0];
			if ($actual !== $param)
			{
				$this->datos[$key][0] = $param;
			}
		}
	}

	/**
	 * @param Worksheet $worksheet
	 * @param array $style_h2
	 * @return Worksheet
	 * @throws \PHPExcel_Exception
	 */
	public function exportExcel(Worksheet $worksheet, array $style_h2)
	{
		$last_row = $worksheet->getHighestDataRow();
		$last_row += 2;
		$max_col = $worksheet->getHighestDataColumn();
		$worksheet->mergeCells("A{$last_row}:{$max_col}{$last_row}");
		$worksheet->setCellValue("A{$last_row}", utf8_encode($this->getTitulo()));
		$worksheet->getStyle("A{$last_row}:{$max_col}{$last_row}")->applyFromArray($style_h2);
		$worksheet->getRowDimension($last_row)->setRowHeight(20);

		$last_row += 2;
		$worksheet->setCellValue("C{$last_row}", utf8_encode('Opción'));
		$worksheet->setCellValue("D{$last_row}", 'Votos');

		$first_row = $last_row;
		$last_row += 1;

		foreach ($this->getDatos() as $key => $dato)
		{
			$worksheet->setCellValue("B{$last_row}", $key + 1);
			$worksheet->setCellValue("C{$last_row}", utf8_encode($dato[0]));
			if (mb_strlen($dato[0]) > 45)
			{
				$worksheet->getRowDimension($last_row)->setRowHeight(27);
			}
			$worksheet->setCellValue("D{$last_row}", $dato[1]);
			$last_row++;
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