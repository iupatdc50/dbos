<?php
namespace app\components\utilities;

use Exception;
use PHPExcel_Exception;
use PHPExcel_Reader_Exception;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Exception;
use PHPExcel_Writer_IWriter;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQueryInterface;
use yii\grid\ActionColumn;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use \PHPExcel;
use \PHPExcel_IOFactory;
use \PHPExcel_Style_NumberFormat;

class ExcelGrid extends GridView
{
	public $columns_array;
	public $properties;
	public $filename='excel';
	public $extension='xlsx';
	public $summaryCols;
	/* @var $_provider ActiveDataProvider */
	private $_provider;
	private $_visibleColumns;
	private $_beginRow = 1;
	private $_endRow;
	private $_endCol;
	private $_objPHPExcel;
	/* @var $_objPHPExcelSheet PHPExcel_Worksheet */
	private $_objPHPExcelSheet;
    /* @var $_objPHPExcelWriter PHPExcel_Writer_IWriter */
	private $_objPHPExcelWriter;

    /**
     * @throws InvalidConfigException
     */
	public function init(){
		parent::init();
	}

    /**
     * @return string|void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
	public function run(){
		//$this->test();
        $oldEncoding = null;
		if (function_exists('mb_internal_encoding')) {
			$oldEncoding=mb_internal_encoding();
			mb_internal_encoding('utf8');
		}
		ob_start();
		$this->init_provider();
		$this->init_excel_sheet();
		$this->initPHPExcelWriter('Excel2007');
		$this->generateHeader();
		$row = $this->generateBody();
		$this->generateTotals($row);
		$writer = $this->_objPHPExcelWriter;
		$this->setHttpHeaders();
		ob_end_clean();
		$writer->save('php://output');
		if (function_exists('mb_internal_encoding'))
			mb_internal_encoding($oldEncoding);
		exit;
		//Yii::$app->end();
		//$writer->save('test.xlsx');
		//parent::run();
	}

	public function init_provider(){
		$this->_provider = clone($this->dataProvider);
	}

    /**
     * @throws PHPExcel_Exception
     */
	public function init_excel_sheet(){
		$this->_objPHPExcel=new PHPExcel();
		$creator = '';
		$title = '';
		$subject = '';
		$description = 'Excel Grid';
		$category = '';
		$keywords = '';
		$manager = '';
		$created = date("Y-m-d H:i:s");
		$lastModifiedBy = '';
		extract($this->properties);
		$this->_objPHPExcel->getProperties()
		->setCreator($creator)
		->setTitle($title)
		->setSubject($subject)
		->setDescription($description)
		->setCategory($category)
		->setKeywords($keywords)
		->setManager($manager)
		//->setCompany($company)
		->setCreated($created)
		->setLastModifiedBy($lastModifiedBy);
		$this->_objPHPExcelSheet = $this->_objPHPExcel->getActiveSheet();
		if(isset($this->properties['sheetTitle']))
			$this->_objPHPExcelSheet->setTitle($this->properties['sheetTitle']);
	}

    /**
     * @param $writer
     * @throws PHPExcel_Reader_Exception
     */
	public function initPHPExcelWriter($writer)
	{
		$this->_objPHPExcelWriter = PHPExcel_IOFactory::createWriter(
				$this->_objPHPExcel,
				$writer
		);
	}

    /**
     * @throws PHPExcel_Exception
     */
	public function generateHeader(){
		$this->setVisibleColumns();
		$sheet = $this->_objPHPExcelSheet;
		$colFirst = self::columnName(1);
		$this->_endCol = 0;
		foreach ($this->_visibleColumns as $column) {
			$this->_endCol++;
			$head = ($column instanceof DataColumn) ? $this->getColumnHeader($column) : $column->header;
			$sheet->setCellValue(self::columnName($this->_endCol) . $this->_beginRow, $head, true);
		}
		$sheet->freezePane($colFirst . ($this->_beginRow + 1));
	}

    /**
     * @return int
     * @throws PHPExcel_Exception
     * @throws Exception
     */
	public function generateBody()
	{
		$columns = $this->_visibleColumns;
		$models = array_values($this->_provider->getModels());
		if (count($columns) == 0) {
			$this->_objPHPExcelSheet->setCellValue('A1', $this->emptyText, true);
			reset($models);
			return 0;
		}
		$keys = $this->_provider->getKeys();
		$this->_endRow = 0;
		foreach ($models as $index => $model) {
			$key = $keys[$index];
			$this->generateRow($model, $key, $index);
			$this->_endRow++;
		}
		// Set autofilter on
		$this->_objPHPExcelSheet->setAutoFilter(
				self::columnName(1) .
				$this->_beginRow .
				":" .
				self::columnName($this->_endCol) .
				$this->_endRow
		);
		
		$style = $this->_objPHPExcelSheet->getStyle('A2:' . self::columnName($this->_endCol) . strval($this->_endRow + 1));
		$style->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

		for($col = 1; $col <= $this->_endCol; $col++) {
			$this->_objPHPExcelSheet->getColumnDimension(self::columnName($col))->setAutoSize(true);
		}

		return ($this->_endRow > 0) ? count($models) : 0;
	}

    /**
     * @param $model
     * @param $key
     * @param $index
     * @throws Exception
     */
	public function generateRow($model, $key, $index)
	{
		$cells = [];
		$this->_endCol = 0;
		foreach ($this->_visibleColumns as $column) {
			if ($column instanceof SerialColumn || $column instanceof ActionColumn) {
				continue;
			} else {
                /* @var $column DataColumn */
				$format = $column->format;
				try {
				    if ($column->content === null) {
				        $content = $column->getDataCellValue($model, $key, $index);
				        $value = $this->formatter->format($content, $format);
                    } else {
				        $content = $column->content;
                        $value = call_user_func($content, $model, $key, $index, $column);
                    }
                } catch (Exception $e) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    Yii::error("*** EG010 Problem with encode: " . print_r($content, true));
				    throw $e;
                }
			}
			if (empty($value) && !empty($column->attribute) && $column->attribute !== null) {
				$value =ArrayHelper::getValue($model, $column->attribute, '');
			}
			$this->_endCol++;
			$cells[] = $this->_objPHPExcelSheet->setCellValue(self::columnName($this->_endCol) . ($index + $this->_beginRow + 1),
					strip_tags($value), true);
		}
	}

	public function generateTotals($row)
    {
        $total_row = $row + 3;

        $colA =  self::columnName($this->summaryCols[0] - 1);
        $this->_objPHPExcelSheet->setCellValue($colA . $total_row, '*** TOTALS', true);

        foreach ($this->summaryCols as $col) {
            $colA = self::columnName($col);
            $formula = "=SUM(" . $colA . "1:" . $colA . ($row + 2) . ")";
            $this->_objPHPExcelSheet->setCellValue($colA . $total_row, $formula, true);
        }
    }

	protected function setVisibleColumns()
	{
		$cols = [];
		foreach ($this->columns as $key => $column) {
			if ($column instanceof SerialColumn || $column instanceof ActionColumn) {
				continue;
			}
			$cols[] = $column;
		}
		$this->_visibleColumns = $cols;
	}

	public function getColumnHeader($col)
	{
		if(isset($this->columns_array[$col->attribute]))
			return $this->columns_array[$col->attribute];

		/* @var $model yii\base\Model */
		if ($col->header !== null || ($col->label === null && $col->attribute === null)) {
			return trim($col->header) !== '' ? $col->header : $col->grid->emptyCell;
		}
		$provider = $this->dataProvider;
		if ($col->label === null) {
			if ($provider instanceof ActiveDataProvider && $provider->query instanceof ActiveQueryInterface) {
				$model = new $provider->query->modelClass;
				$label = $model->getAttributeLabel($col->attribute);
			} else {
				$models = $provider->getModels();
				if (($model = reset($models)) instanceof Model) {
					$label = $model->getAttributeLabel($col->attribute);
				} else {
					$label =$col->attribute;
				}
			}
		} else {
			$label = $col->label;
		}
		return $label;
	}
	public static function columnName($index)
	{
		$i = $index - 1;
		if ($i >= 0 && $i < 26) {
			return chr(ord('A') + $i);
		}
		if ($i > 25) {
			return (self::columnName($i / 26)) . (self::columnName($i % 26 + 1));
		}
		return 'A';
	}

	protected function setHttpHeaders()
	{
		header("Cache-Control: no-cache");
		header("Pragma: no-cache");
        header("Content-Type: application/force-download");
        header("Content-Type: application/{$this->extension}; charset=utf-8");
		header("Content-Disposition: attachment; filename={$this->filename}.{$this->extension}");
		header("Expires: 0");
	}
}