<?php

namespace app\models\training;

use app\components\behaviors\OpImageBehavior;
use app\components\utilities\OpDate;
use app\helpers\OptionHelper;
use app\models\member\Member;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "MemberCredentials".
 *
 * @property integer $id
 * @property string $member_id
 * @property integer $credential_id
 * @property string $complete_dt
 * @property string $expire_dt
 * @property string $doc_id [varchar(20)]
 *
 * @property Credential $credential
 *
 * @property string $imagePath
 * @method uploadImage()
 * @method deleteImage()
 *
 */
class MemberCredential extends ActiveRecord
{
    /* @var Member $member Injected Member object, used for creating new entries */
    public $member;

    public $catg;
    public $unrestricted;

    /**
     * @var mixed	Stages document to be uploaded
     */
    public $doc_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MemberCredentials';
    }

    public static function instantiate($row)
    {
        if ($row['credential_id'] == Credential::RESP_FIT)
            return new MemberCredRespFit();
        elseif ($row['credential_id'] == Credential::DRUG)
            return new MemberCredDrug();
        return new static;
    }

    /**
     * Handles all the document attachment processing functions for the model
     *
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
        return [
            OpImageBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credential_id', 'complete_dt'], 'required'],
            [['credential_id'], 'integer'],
            [['expire_dt'], 'safe'],
            [['member_id'], 'string', 'max' => 11],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            [['credential_id'], 'exist', 'skipOnError' => true, 'targetClass' => Credential::className(), 'targetAttribute' => ['credential_id' => 'id']],
            [['doc_id'], 'string', 'max' => 20],
            [['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png, jpg, jpeg'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'credential_id' => 'Credential',
            'complete_dt' => 'Completed',
            'expire_dt' => 'Expires',
            'doc_id' => 'Doc',
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                if (!(isset($this->member) && ($this->member instanceof Member)))
                    throw new InvalidConfigException('No member object injected');
                $this->member_id = $this->member->member_id;
            }
            if ($this->isAttributeChanged('complete_dt') && isset($this->credential->duration)) {
                $expire_dt = new OpDate();
                $expire_dt->setFromMySql($this->complete_dt)->modify("+{$this->credential->duration} month");
                $this->expire_dt = $expire_dt->getMySqlDate(false);
            }
            return true;
        }
        return false;

    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $periods = $this->getScheduled();
            /* @var MemberScheduled $period */
            foreach ($periods as $period)
                $period->delete();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return ActiveQuery
     */
    public function getCredential()
    {
        return $this->hasOne(Credential::className(), ['id' => 'credential_id']);
    }

    /**
     * @param $catg
     * @return array
     * @throws InvalidConfigException
     * @noinspection DuplicatedCode
     */
    public function getCredentialOptions($catg)
    {
        if (!isset($this->member))
            throw new InvalidConfigException('Member object not injected');
        $query = Credential::find()
            ->where(['catg' => $catg])
            ->andWhere(['or',
                ['lob_cd' => $this->member->currentStatus->lob_cd],
                ['lob_cd' => null],
            ])

            ;
        if (Yii::$app->user->can('editLimitedCredentials'))
            $query->andWhere(['unrestricted' => OptionHelper::TF_TRUE]);
        $query->orderBy('display_seq');
        return ArrayHelper::map($query->all(), 'id', 'credential');
    }

    public function getScheduled()
    {
        return $this->hasMany(MemberScheduled::className(), ['member_id' => 'member_id', 'credential_id' => 'credential_id']);
    }

}
