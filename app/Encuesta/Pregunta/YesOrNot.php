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

class YesOrNot extends Pregunta
{
	protected $template = 'yes_or_not.twig';

	protected $yes = 0;
	protected $not = 0;

	public function setRespuesta($respuesta)
	{
		switch ($respuesta)
		{
			case 's':
				$this->yes++;
				break;
			case 'n':
				$this->not++;
				break;
		}
	}

	public function renderReporte()
	{
		$datos = array(
			'legend' => array(
				'maxWidth' => 450,
				'itemWidth' => 150
			),
			'axisY' => array(
				'interval' => 1,
				'includeZero' => false
			),
			'data' => array(
				array(
					'type' => 'bar',
					'showInLegend' => true,
					'legendText' => '{label}',
					'dataPoints' => array(
						array('label' => utf8_encode('Sí'), 'y' => $this->yes),
						array('label' => utf8_encode('No'), 'y' => $this->not),
					)
				)
			)
		);
		return $this->view->render('reportes/yes_or_not.twig', array('pregunta' => $this, 'options' => json_encode($datos)));
	}

	/**
	 * @return int
	 */
	public function getYes()
	{
		return $this->yes;
	}

	/**
	 * @return int
	 */
	public function getNot()
	{
		return $this->not;
	}

	public function reset()
	{
		$this->yes = 0;
		$this->not = 0;
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
		$worksheet->setCellValue("C{$last_row}", utf8_encode('Opción'));
		$worksheet->setCellValue("D{$last_row}", 'Votos');

		$first_row = $last_row;
		$last_row += 1;

		$worksheet->setCellValue("B{$last_row}", 1);
		$worksheet->setCellValue("C{$last_row}", utf8_encode('Sí'));
		$worksheet->setCellValue("D{$last_row}", $this->yes);
		$last_row++;
		$worksheet->setCellValue("B{$last_row}", 2);
		$worksheet->setCellValue("C{$last_row}", 'No');
		$worksheet->setCellValue("D{$last_row}", $this->not);

		$worksheet->getStyle("C{$first_row}:D{$last_row}")->applyFromArray($this->getEstiloTabla('center', true));
		$first_row++;
		$worksheet->getStyle("B{$first_row}:D{$last_row}")->applyFromArray($this->getEstiloTabla());

		$first_row -= 1;
		$top_chart = $first_row - 1;
		$bottom_chart = $first_row + 12;
		$chart = $this->getChart($first_row, $last_row, $top_chart, $bottom_chart);

		$chart1 = $this->getChart($first_row, $last_row, $top_chart, $bottom_chart);

		$worksheet->addChart($chart1);
		$worksheet->setCellValue("A{$bottom_chart}", "");

		return $worksheet;
	}

	protected function getChart($first_row, $last_row, $top_chart, $bottom_chart)
	{
		$first_row += 1;
		$labels = array(
			new DataSeriesValues('String', 'Encuesta!$C$' . $first_row, null, 1), // si
			new DataSeriesValues('String', 'Encuesta!$C$' . $last_row, null, 1) // no
		);
		$xLabels = array(
			new DataSeriesValues('String', 'Encuesta!$C$' . $first_row, null, 1), // Sí y no
			new DataSeriesValues('String', 'Encuesta!$C$' . $last_row, null, 1) // Sí y no
		);
		$yValues = array(
			new DataSeriesValues('Number', 'Encuesta!$D$' . $first_row, null, 1), // Opciones
			new DataSeriesValues('Number', 'Encuesta!$D$' . $last_row, null, 1) // Opciones
		);

		$series1 = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($yValues) - 1), $labels, $xLabels, $yValues);
		$series1->setPlotDirection(DataSeries::DIRECTION_BAR);
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