<?php

namespace app\models\accounting;

use Exception;
use PHPExcel_Cell;
use PHPExcel_Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use PHPExcel_Worksheet;
use yii\base\Model;
use yii\base\InvalidConfigException;

class RemittanceExcel extends Model
{
    const FIRST_COLHEAD = 'Class';
    const FIRST_DATACOL = 6; // assume zero based
    const LAST_NM_COL = 1;
    const FIRST_NM_COL = 2;
    const REPORT_ID_COL = 4;
	/**
	 * @var string	Path to the Excel spreadsheet.  Must be injected on Create.
	 */
	public $xlsx_file;
    /**
     * @var PHPExcel_Worksheet
     */
	public $sheet;
	/**
	 * @var string Excel columns start with A, B, C, etc.
	 */
	public $maxColA;
	public $maxCol;
	public $maxRow;
	private $_feeColumns = [];

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
	public function init()
	{
		if(!isset($this->xlsx_file))
			throw new InvalidConfigException('Must inject an Excel spreadsheet');
		
		try {
			$inputFileType = PHPExcel_IOFactory::identify($this->xlsx_file);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objExcel = $objReader->load($this->xlsx_file);
		} catch (PHPExcel_Reader_Exception $e) {
            throw new Exception('Unable to stage Excel spreadsheet: ' . $e);
		}

        try {
            $this->sheet = $objExcel->getSheet();
            $this->maxColA = $this->sheet->getHighestColumn();
            $this->maxCol = PHPExcel_Cell::columnIndexFromString($this->maxColA);
            $this->maxRow = $this->sheet->getHighestRow();
        } catch (PHPExcel_Exception $e) {
            throw new Exception('Unable to access staged Excel spreadsheet: ' . $e);
		}
	}

    /**
     * @param array $fee_types
     * @return $this
     * @throws Exception
     */
	public function setFeeColumns($fee_types = [])
	{
		$headerRow = $this->sheet->rangeToArray('A1:' . $this->maxColA . '1', null, true, false);
		if (!($headerRow[0][0] == self::FIRST_COLHEAD))
			throw new Exception('Spreadsheet is not the right format (no header row)');
		
		$this->_feeColumns = [];
		
		// assume $headerRow columns are zero based (see remit-template for # of base columns 
		for ($col = self::FIRST_DATACOL; $col < $this->maxCol; $col++) {
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

    /**
     * @return array
     * @throws InvalidConfigException
     */
	public function getAllocsArray()
	{
		if(empty($this->_feeColumns))
			throw new InvalidConfigException('Fee columns is empty.  Call setFeeColumns() before executing.');
		
		$allocs = [];
		
		for ($row = 2; $row <= $this->maxRow; $row++) {

		    $dataRow = $this->sheet->rangeToArray('A'. $row . ':' . $this->maxColA . $row, null, true, false);

		    if (strlen($dataRow[0][1]) < 1)
		        break;

			$alloc = [];
			$alloc['last_nm'] = $dataRow[0][self::LAST_NM_COL];
			$alloc['first_nm'] = $dataRow[0][self::FIRST_NM_COL];
			$alloc['report_id'] = $dataRow[0][self::REPORT_ID_COL];
			
			// assume $dataRow columns are zero based
			foreach ($this->_feeColumns as $fee_type => $col) {
				$alloc[$fee_type] = $dataRow[0][$col];
			}
			
			$allocs[] = $alloc;
			
		}
		return $allocs;
	}

	public function getDocNumber()
    {
        return $this->sheet->getParent()->getProperties()->getCustomPropertyValue('Document number');
    }
	
}