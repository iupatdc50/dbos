<?php
namespace app\models\training;

use app\models\value\Lob;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class ArchiveTimesheetForm extends Model
{
    public $member_id;
    public $member_nm;
    public $lob_cd;
    public $lob_descrip;
    public $is_mh;

    public function rules()
    {
        return [
            [['member_id', 'lob_cd'], 'required'],
            [['is_mh', 'member_nm', 'lob_descrip'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'member_id' => 'Member',
            'lob_cd' => 'Trade',
            'is_mh' => "Material Hdlr",
        ];
    }

    public function getLobOptions()
    {
        return ArrayHelper::map(Lob::find()->orderBy('lob_cd')->all(), 'lob_cd', 'short_descrip');
    }

}