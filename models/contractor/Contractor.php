<?php

namespace app\models\contractor;

use Yii;
use yii\db\Query;
use app\models\member\Member;
use app\helpers\OptionHelper;
use yii\helpers\ArrayHelper;
use app\models\project\BaseRegistration;
use app\models\member\Employment;
use app\models\value\Lob;

/**
 * This is the model class for table "Contractors".
 *
 * @property string $license_nbr
 * @property string $contractor
 * @property string $contact_nm
 * @property string $email
 * @property string $url
 * @property string $is_active
 * @property UnionContractor $currentSignatory
 * @property Address[] $addresses
 * @property Phone[] $phones
 * @property Signatory[] $signatories
 * @property integer $employeeCount
 * @property Member[] $employees
 */
class Contractor extends \yii\db\ActiveRecord
{
	CONST STATUS_ACTIVE = 'T';
	CONST STATUS_INACTIVE = 'F';
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Contractors';
    }
    
    /**
     * Returns a set of contractors for Select2 picklist. Contractor name
     * is returned as text (id, text are required columns for Select2)
     * 
     * @param string|array $search Criteria used for partial contractor list. If an array, then contractor
     * 							   key will be a like search
     */
    public static function listAll($search)
    {
    	/* @var Query $query */
    	$query = new Query;
    	$query->select('license_nbr as id, contractor as text')
    		->from('ContractorPickList')
    		->limit(10)
    		->distinct();
    	if (ArrayHelper::isAssociative($search)) { 
    		if (isset($search['contractor'])) {
    			$query->where(['like', 'contractor', $search['contractor']]);
    			unset($search['contractor']);
    		}
    		$query->andWhere($search);
    	} elseif (!is_null($search)) 
    		$query->where(['like', 'contractor', $search]);
    	$command = $query->createCommand();
    	return $command->queryAll();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_nbr', 'contractor'], 'required'],
            [['license_nbr'], 'string', 'max' => 8],
            [['contractor'], 'string', 'max' => 60],
            [['contact_nm'], 'string', 'max' => 30],
            [['email'], 'email'],
        	[['url'], 'url'],
            [['email', 'url'], 'default'],
        	[['is_active'], 'in', 'range' => OptionHelper::getAllowedTF()],
        	[['is_active'], 'default', 'value' => 'F'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'license_nbr' => 'License',
            'contractor' => 'Contractor',
            'contact_nm' => 'Contact Name',
            'addressTexts' => 'Address(es)',
        	'phoneTexts' => 'Phone(s)',
        	'email' => 'Email',
            'url' => 'Website',
        	'is_active' => 'Status',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
    	return $this->hasMany(Address::className(), ['license_nbr' => 'license_nbr']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
    	return $this->hasMany(Phone::className(), ['license_nbr' => 'license_nbr']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSignatories()
    {
    	return $this->hasMany(Signatory::className(), ['license_nbr' => 'license_nbr']);
    }
    
    public function getCurrentSignatory()
    {
    	return $this->hasOne(UnionContractor::className(), ['license_nbr' => 'license_nbr']);
    }
    
    /**
     * Builds a dependent LOB picklist for a DepDrop widget
     * 
     * @return NULL|array Array in ['id' => x, 'name' => y] format
     */
    public function getCurrentLobOptions()
    {
    	$lobs = explode(', ', $this->currentSignatory->lobs);
    	if (empty($lobs))
    		return null;
    	$records = Lob::find()->where(['lob_cd' => $lobs])->orderBy('lob_cd')->all();
    	$options = [];
		foreach($records as $record) {
			$options[] = ['id' => $record['lob_cd'], 'name' => $record['short_descrip']];
		}
   		return empty($lobs) ? null : $options;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegistrations()
    {
    	return $this->hasMany(BaseRegistration::className(), ['bidder' => 'license_nbr']);
    }
    
    public function getAddressTexts()
    {
    	$texts = [];
    	foreach ($this->addresses as $address) {
    		$texts[] = $address->getAddressText(true);
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    public function getPhoneTexts()
    {
    	$texts = [];
    	foreach ($this->phones as $phone) {
    		$texts[] = $phone->phoneText;
    	}
    	return (sizeof($texts) > 0) ? implode(PHP_EOL, $texts) : null;
    }
    
    /**
     * Serves as a getter for ContractorSearch::employeeCount
     */
    public function getEmployeeCount()
    {
    	return $this->hasMany(Member::className(), ['member_id' => 'member_id'])
    			->viaTable('CurrentEmployees', ['employer' => 'license_nbr'])
    			->count()
    	; 
    }
    
    public function getEmployees()
    {
    	return $this->hasMany(Member::className(), ['member_id' => 'member_id'])
    			->viaTable('CurrentEmployees', ['employer' => 'license_nbr']);
    }
    
    /**
     * Status is held in the $this->is_active column, based on whether there
     * is at least one active signatory
     */
    public function setStatus($override = NULL)
    {
    	if (isset($override)) {
    		$options = $this->statusOptions;
    		if (!isset($options[$override])) {
    			throw new InvalidParamException("Invalid override `{$override}` passed in parameter"); 
    		}
    		$this->is_active = $override;
    	} else {
    		$this->is_active = (isset($this->currentSignatory)) ? 'T' : 'F';
    	}
    }
    
   	public function getStatusOptions()
   	{
   		return [
   				self::STATUS_ACTIVE => 'Active', 
   				self::STATUS_INACTIVE => 'Inactive'
   		];
   	} 
   
   	public function getStatusText()
   	{
   		$options = $this->statusOptions;
   		return (isset($options[$this->is_active])) ? $options[$this->is_active] : 'Unknown active status ' . $this->is_active;
   	}
}
