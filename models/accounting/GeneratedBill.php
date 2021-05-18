<?php

namespace app\models\accounting;

use app\models\contractor\Contractor;
use app\models\value\Lob;
use app\models\user\User;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "GeneratedBills".
 *
 * @property integer $id
 * @property string $license_nbr
 * @property string $lob_cd
 * @property integer $employees
 * @property integer $created_at
 * @property integer $created_by
 * @property string $remarks
 *
 * @property Contractor $contractor
 * @property Lob $lob
 * @property User $creator
 */
class GeneratedBill extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'GeneratedBills';
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::className(), 'updatedAtAttribute' => false],
            ['class' => BlameableBehavior::className(), 'updatedByAttribute' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_nbr', 'lob_cd'], 'required'],
            [['employees', 'created_at', 'created_by'], 'integer'],
            [['remarks'], 'string'],
            [['license_nbr'], 'string', 'max' => 11],
            [['lob_cd'], 'string', 'max' => 4],
            [['license_nbr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['license_nbr' => 'license_nbr']],
            [['lob_cd'], 'exist', 'skipOnError' => true, 'targetClass' => Lob::className(), 'targetAttribute' => ['lob_cd' => 'lob_cd']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'license_nbr' => 'License Nbr',
            'lob_cd' => 'Trade',
            'employees' => 'Employees',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function deleteImage()
    {
        return true;
    }

    public function getBillPayments()
    {
        return $this->hasMany(BillPayment::className(), ['receipt_id' => 'id']);
    }
}
