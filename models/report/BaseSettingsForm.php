<?php

namespace app\models\report;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;
use app\models\value\Lob;
use app\models\value\Island;

class BaseSettingsForm extends Model
{
	CONST OUTPUT_EXCEL = 'excel';
	CONST OUTPUT_PRINT = 'print';
	
	public $show_islands = true;
	
	public $lob_cd;
	public $island;
	public $output_to;
	
	public function rules()
	{
		return [
				[['lob_cd', 'island'], 'safe'],
				[['output_to'], 'required'],
				['lob_cd', 'exist', 'targetClass' => '\app\models\value\Lob'],
        		[['island'], 'exist', 'targetClass' => 'app\models\value\Island'],
				[['output_to'], 'in', 'range' => self::getAllowedOutputTo()],
		];
	}
	
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'lob_cd' => 'Local',
        ];
    }
    
	public static function getAllowedOutputTo()
	{
		return [
				self::OUTPUT_EXCEL,
				self::OUTPUT_PRINT,
		];
	}
	
	public function getLobOptions()
	{
		return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
	}
	
	public function getIslandOptions()
	{
		return ArrayHelper::map(Island::find()->orderBy('island_cd')->all(), 'island', 'island');
	}
	
	public function getOutputToOptions()
	{
		return [
				self::OUTPUT_EXCEL => 'Excel Spreadsheet',
				self::OUTPUT_PRINT => 'Printable Format',
		];
	}
	
}