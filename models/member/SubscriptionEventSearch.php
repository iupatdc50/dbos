<?php

namespace app\models\member;

use app\helpers\CriteriaHelper;
use yii\data\ActiveDataProvider;

class SubscriptionEventSearch extends SubscriptionEvent
{
    public $fullName;
    public $created_dt;
    public $status;

    public function rules()
    {
        return [
            [['fullName', 'created_dt', 'status'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = SubscriptionEvent::find();

        $query->joinWith([
            'member', 'receipt'
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => [
                'created_dt'=>SORT_DESC,
                'fullName'=> SORT_ASC,
//                Member::tableName() . '.last_nm'=>SORT_ASC,
//                Member::tableName() . '.first_nm' => SORT_ASC,
            ]]
        ]);
        $dataProvider->sort->attributes['fullName'] = [
            'asc' => [Member::tableName() . '.last_nm' => SORT_ASC, Member::tableName() . '.first_nm' => SORT_ASC],
            'desc' => [Member::tableName() . '.last_nm' => SORT_DESC, Member::tableName() . '.first_nm' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['status'] = ['asc' => ['status' => SORT_ASC], 'desc' => ['status' => SORT_DESC]];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'or',
            ['like', Member::tableName() . '.last_nm', $this->fullName],
            ['like', Member::tableName() . '.first_nm', $this->fullName],
            ['like', Member::tableName() . '.nick_nm', $this->fullName],
        ])
        ->andFilterWhere(['status' => $this->status]);
        $criteria = CriteriaHelper::parseMixed('created_dt', $this->created_dt, true);
        $query->andFilterWhere($criteria);

        return $dataProvider;
    }
}