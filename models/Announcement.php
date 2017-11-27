<?php

namespace app\models;

use Yii;
use app\models\base\BaseNote;
use app\models\user\User;

/**
 * This is the model class for table "Announcements".
 *
 * @property integer $id
 * @property string $note
 * @property integer $created_at
 * @property integer $created_by
 */
class Announcement extends BaseNote
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Announcements';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
    	return parent::rules();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return parent::attributeLabels();
    }
        
}
