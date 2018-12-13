<?php

namespace app\models\rbac;

use Yii;

/**
 * This is the model class for table "AuthItems".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItemChild[] $authItemChilds
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 * @property AuthRule $ruleName
 **/
class AuthItem extends \yii\db\ActiveRecord
{
    private $_descendants = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'AuthItems';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChilds()
    {
        return $this->hasMany(AuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('AuthItemChilds', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('AuthItemChilds', ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @param $parent
     * @return array
     * @throws \yii\db\Exception
     */
    public function getDescendants($parent=null)
    {
        if (is_null($parent)) {
            $parent = $this->name;
            $this->_descendants = [];
        }

        $sql = <<<SQL
          SELECT child FROM AuthItemChilds AS C JOIN AuthItems AS AI ON C.child = AI.name AND AI.type = 1
            WHERE C.parent = :parent;
SQL;

        $db = yii::$app->db;
        $children = $db->createCommand($sql)
            ->bindValue(':parent', $parent)
            ->queryColumn();

        if (count($children) > 0)
            $this->_descendants = array_unique(array_merge($this->_descendants, $children));

        foreach ($children as $child)
            $this->getDescendants ($child);

        return $this->_descendants;

    }

}
