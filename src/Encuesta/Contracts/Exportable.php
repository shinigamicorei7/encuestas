<?php namespace Intisana\Encuesta\Contracts;

use PHPExcel_Worksheet;

interface Exportable
{
	/**
	 * @param PHPExcel_Worksheet $worksheet
	 * @param array $style_h2
	 * @return PHPExcel_Worksheet
	 */
	public function exportExcel(PHPExcel_Worksheet $worksheet, array $style_h2);
}