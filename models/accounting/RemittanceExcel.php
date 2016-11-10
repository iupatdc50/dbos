<?php

namespace app\models\accounting;

use Yii;
use yii\base\Model;
use yii\base\InvalidConfigException;

class RemittanceExcel extends Model
{
	/**
	 * @var string	Path to the Excel spreadsheet.  Must be injected on Create.
	 */
	public $xlsx_file;
	public $sheet;
	/**
	 * @var string Excel columns start with A, B, C, etc.
	 */
	public $maxColA;
	public $maxCol;
	public $maxRow;
	private $_feeColumns = [];
	
	public function init()
	{
		if(!isset($this->xlsx_file))
			throw new InvalidConfigException('Must inject an Excel spreadsheet');
		
		try {
			$inputFileType = \PHPExcel_IOFactory::identify($this->xlsx_file);
			$objReader = \PHPExcel_IOFactory::createReader($inputFileType);
			$objExcel = $objReader->load($this->xlsx_file);
		} catch (Exception $e) {
			throw new Exception('Unable to stage Excel spreadsheet: ' . $e);
		}
		
		$this->sheet = $objExcel->getSheet(0);
		$this->maxColA = $this->sheet->getHighestColumn();
		$this->maxCol = \PHPExcel_Cell::columnIndexFromString($this->maxColA);
		$this->maxRow = $this->sheet->getHighestRow();
	}
	
	public function setFeeColumns($fee_types = [])
	{
		$headerRow = $this->sheet->rangeToArray('A1:' . $this->maxColA . '1', null, true, false);
		if (!($headerRow[0][0] == 'Last Name'))
			throw new \Exception('Spreadsheet is not the right format (no header row)');
		
		$this->_feeColumns = [];
		
		// assume $headerRow columns are zero based (see remit-template for # of base columns 
		for ($col = 5; $col < $this->maxCol; $col++) {
			$fee_type = substr($headerRow[0][$col], 0, 2);
			if(in_array($fee_type, $fee_types))
				$this->_feeColumns[$fee_type] = $col;
		}
		
		return $this;
		
	}
	
	public function getFeeColumns()
	{
		return $this->_feeColumns;
	}
	
	public function getAllocsArray()
	{
		if(empty($this->_feeColumns))
			throw new InvalidConfigException('Fee columns is empty.  Call setFeeColumns() before executing.');
		
		$allocs = [];
		
		for ($row = 2; $row <= $this->maxRow; $row++) {
			
			$dataRow = $this->sheet->rangeToArray('A'. $row . ':' . $this->maxColA . $row, null, true, false);
			$alloc = [];
			$alloc['last_nm'] = $dataRow[0][0];
			$alloc['first_nm'] = $dataRow[0][1];
			$alloc['report_id'] = $dataRow[0][2];
			
			// assume $dataRow columns are zero based
			foreach ($this->_feeColumns as $fee_type => $col) {
				$alloc[$fee_type] = $dataRow[0][$col];
			}
			
			$allocs[] = $alloc;
			
		}
		return $allocs;
	}
	
}