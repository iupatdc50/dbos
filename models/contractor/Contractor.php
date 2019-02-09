<?php

namespace app\models\contractor;

use yii\base\InvalidParamException;
use yii\db\Query;
use app\models\base\iNotableInterface;
use app\models\member\Member;
use app\helpers\OptionHelper;
use yii\helpers\ArrayHelper;
use app\models\project\BaseRegistration;
use app\models\value\Lob;
use yii\web\UploadedFile;

/**
 * This is the model class for table "Contractors".
 *
 * @property string $license_nbr
 * @property string $contractor
 * @property string $contact_nm
 * @property string $email
 * @property string $url
 * @property string deducts_dues
 * @property string $is_active
 * @property UnionContractor $currentSignatory
 * @property Address[] $addresses
 * @property Phone[] $phones
 * @property Email[] $emails;
 * @property Signatory[] $signatories
 * @property integer $employeeCount
 * @property Member[] $employees
 * @property Note[] $notes
 */
class Contractor extends \yii\db\ActiveRecord  implements iNotableInterface
{
	CONST STATUS_ACTIVE = 'T';
	CONST STATUS_INACTIVE = 'F';

    CONST CONTRACTOR_OTHER_PAYOR = 'XX-10008';

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
     *                               key will be a like search
     * @return array
     * @throws \yii\db\Exception
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
        	[['is_active', 'deducts_dues'], 'in', 'range' => OptionHelper::getAllowedTF()],
        	[['is_active', 'deducts_dues'], 'default', 'value' => 'F'],
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
            'emailTexts' => 'Email(s)',
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
    public function getAddressDefault()
    {
    	return $this->hasOne(AddressDefault::className(), ['license_nbr' => 'license_nbr']);
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
    public function getPhoneDefault()
    {
    	return $this->hasOne(PhoneDefault::className(), ['license_nbr' => 'license_nbr']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmails()
    {
        return $this->hasMany(Email::className(), ['license_nbr' => 'license_nbr']);
    }

    public function getContactEmail()
    {
        return $this->hasOne(Email::className(), ['license_nbr' => 'license_nbr'])
            ->andOnCondition(['email_type' => Email::TYPE_CONTACT]);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSignatories()
    {
    	return $this->hasMany(Signatory::className(), ['license_nbr' => 'license_nbr']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
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
        if ($this->license_nbr == self::CONTRACTOR_OTHER_PAYOR) {
            $records = Lob::find()->orderBy('lob_cd')->all();
        } else {
            if (!isset($this->currentSignatory))
                return null;
            $lobs = explode(', ', $this->currentSignatory->lobs);
            $records = Lob::find()->where(['lob_cd' => $lobs])->orderBy('lob_cd')->all();
        }
    	$options = [];
		foreach($records as $record) {
			$options[] = ['id' => $record['lob_cd'], 'name' => $record['short_descrip']];
		}
   		return $options;
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

    public function getEmailTexts()
    {
        $texts = [];
        foreach ($this->emails as $email) {
            $texts[] = $email->getEmailText(true);
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
    
    public function getDeductsDuesText()
    {
    	return OptionHelper::getTFText($this->deducts_dues);
    }

    /**
     * Status is held in the $this->is_active column, based on whether there
     * is at least one active signatory
     * @param null $override
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
   	
   	/**
   	 * Adds a journal note to this contractor
   	 *
   	 * @param Note $note
   	 * @throws \BadMethodCallException
   	 * @return boolean
   	 */
   	public function addNote($note)
   	{
   		if (!($note instanceof Note))
   			throw new \BadMethodCallException('Not an instance of ContractorNote');
   		$note->license_nbr = $this->license_nbr;
        /** @noinspection PhpUndefinedMethodInspection */
        $image = $note->uploadImage();
        /* @var $image UploadedFile */
   		if ($note->save()) {
   			if ($image !== false) {
   				$path = $note->imagePath;
   				$image->saveAs($path);
   			}
   			return true;
   		}
   		return false;
   	}
   	
   	/**
   	 * @return \yii\db\ActiveQuery
   	 */
   	public function getNotes()
   	{
   		return $this->hasMany(Note::className(), ['license_nbr' => 'license_nbr'])->orderBy(['created_at' => SORT_DESC]);
   	}
   	
   	public function getNoteCount()
   	{
   		return $this->hasMany(Note::className(), ['license_nbr' => 'license_nbr'])->count();
   	}
   	
}
