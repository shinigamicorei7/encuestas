<?php

/** @var \Intisana\Encuesta\Repositorio $repositorio */
$repositorio = require_once 'bootstrap/repository.php';

$encuesta = $repositorio->first();

$excel = new PHPExcel();
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
$worksheet = $excel->getActiveSheet();
$worksheet->setShowGridlines(false);
$worksheet->setTitle('Encuesta');

$style_h1 = array(
	'font' => array(
		'bold' => true,
		'size' => 20
	),
	'alignment' => array(
		'horizontal' => 'center',
		'vertical' => 'center'
	)
);

$style_h2 = array_merge($style_h1, array('font' => array('size' => 16, 'color' => array('rgb' => '1B7D5A'))));

$titulo = utf8_encode($encuesta->getTitulo());

$column_width = (mb_strlen($titulo) / 5);

$worksheet->mergeCells('A1:J1');
$worksheet->getStyle('A1:J1')->applyFromArray($style_h1);
$worksheet->setCellValue('A1', $titulo);

$worksheet->getColumnDimension('A')->setWidth(4);
$worksheet->getColumnDimension('B')->setWidth(4);
$worksheet->getColumnDimension('C')->setWidth(45);
$worksheet->getColumnDimension('D')->setWidth(6);
$worksheet->getColumnDimension('E')->setWidth(8);
$worksheet->getColumnDimension('F')->setWidth(8);
$worksheet->getColumnDimension('G')->setWidth(10);

foreach (range('H', 'J') as $column)
{
	$worksheet->getColumnDimension($column)->setWidth($column_width);
}

$worksheet->getRowDimension(1)->setRowHeight(24);

/** @var \Intisana\Encuesta\Contracts\Pregunta $pregunta */
foreach ($encuesta->getPreguntas() as $pregunta)
{
	$worksheet = $pregunta->exportExcel($worksheet, $style_h2);
}

$factory = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$factory->setIncludeCharts(true);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Encuesta.xlsx"');
$factory->save('php://output');