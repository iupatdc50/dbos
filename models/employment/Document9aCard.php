<?php

namespace app\models\employment;

use app\models\contractor\Contractor;
use app\models\member\Member;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Document9aCards".
 *
 * @property string|null $employer
 * @property string $member_id
 * @property string $record_id
 * @property string $doc_id
 *
 * @property Member $member
 * @property Contractor $contractor
 * @property Document $baseDocument
 *
 *
 */
class Document9aCard extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Document9aCards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employer'], 'string', 'max' => 8],
            [['member_id'], 'string', 'max' => 11],
            [['record_id'], 'string', 'max' => 11],
            [['doc_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'employer' => 'Employer',
            'member_id' => 'Member ID',
            'doc_id' => 'Doc ID',
            'record_id' => 'Record ID',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'employer']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBaseDocument()
    {
        return $this->hasOne(Document::className(), ['id' => 'record_id']);
    }

}
