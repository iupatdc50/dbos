<?php

namespace app\models\training;


/**
 * This is the model class for table "CurrentMemberCredentials".
 *
 * @property string $catg
 * @property int $display_seq [int(11)]
 * @property string $show_on_cert [enum('T', 'F')]
 *
 */
class CurrentMemberCredential extends MemberCredential
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CurrentMemberCredentials';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

}
