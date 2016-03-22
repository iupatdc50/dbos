<?php

namespace app\models\accounting;

use Yii;

class ReceiptMember extends Receipt
{
	protected $_remit_filter = 'member_remittable';
}