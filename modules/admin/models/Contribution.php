<?php

namespace app\modules\admin\models;

use app\models\value\Lob;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "Contributions".
 *
 * @property string $lob_cd
 * @property string $contrib_type
 * @property int $wage_pct
 * @property float $factor
 * @property string|null $operand
 *
 * @property FeeType $feeType
 * @property Lob $lob
 * @property array $contribOptions
 * @property array $operandOptions
 * @property string $operandText
 */
class Contribution extends ActiveRecord
{
    CONST OPERAND_HOURS = 'G';
    CONST OPERAND_WAGES = 'H';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Contributions';
    }

    /**
     * @param $lob_cd
     * @param $contrib_type
     * @param $wage_pct
     * @return Contribution|null
     * @throws NotFoundHttpException
     */
    public static function findByKey($lob_cd, $contrib_type, $wage_pct)
    {
        if (($model = Contribution::findOne([
                'lob_cd' => $lob_cd,
                'contrib_type' => $contrib_type,
                'wage_pct' => $wage_pct,
            ])) !== null)
            return $model;
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lob_cd', 'contrib_type', 'wage_pct', 'factor', 'operand'], 'required'],
            [['wage_pct'], 'integer'],
            [['factor'], 'number'],
            [['operand'], 'string'],
            [['lob_cd'], 'string', 'max' => 4],
            [['contrib_type'], 'string', 'max' => 2],
            [['lob_cd', 'contrib_type', 'wage_pct'], 'unique', 'targetAttribute' => ['lob_cd', 'contrib_type', 'wage_pct']],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
            [['contrib_type'], 'in', 'range' => FeeType::contributionTypes()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'lob_cd' => 'Trade',
            'contrib_type' => 'Type',
            'wage_pct' => 'Wage Pct',
            'factor' => 'Factor',
            'operand' => 'Operand',
        ];
    }

    /**
     * Gets query for [[ContribType]].
     *
     * @return ActiveQuery
     */
    public function getFeeType()
    {
        return $this->hasOne(FeeType::className(), ['fee_type' => 'contrib_type']);
    }

    /**
     * Gets query for [[LobCd]].
     *
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    public function getLobOptions()
    {
        return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'lob_cd');
    }

    public function getOperandOptions()
    {
        return [
            self::OPERAND_HOURS => 'Hours Worked',
            self::OPERAND_WAGES => 'Wage Percentage',
        ];
    }

    public function getOperandText()
    {
        return isset($this->operandOptions[$this->operand]) ? $this->operandOptions[$this->operand] : 'Unkown';
    }

    public function getContribOptions()
    {
        return ArrayHelper::map(FeeType::find()->where(['in', 'fee_type', FeeType::contributionTypes()])->all(), 'fee_type', 'descrip');
    }
}
