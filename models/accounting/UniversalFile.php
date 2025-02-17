<?php

namespace app\models\accounting;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "UniversalFile".
 *
 * @property string $acct_month
 * @property string $A
 * @property string $B
 * @property string $C
 * @property string|null $D
 * @property string|null $E
 * @property string|null $F
 * @property string|null $G
 * @property float|null $H
 * @property string $I
 * @property float|null $J
 * @property string $K
 * @property string $L
 * @property string|null $M
 * @property string $N
 * @property string $O
 * @property string $P
 * @property string $Q
 * @property string $R
 * @property string|null $S
 * @property string|null $T
 * @property string|null $U
 * @property float|null $V
 * @property string|null $W
 * @property string|null $X
 * @property float|null $Y
 * @property string|null $Z
 * @property string|null $AA
 * @property string|null $AB
 * @property float|null $AC
 * @property string|null $AD
 * @property string|null $AE
 * @property float|null $AF
 * @property string|null $AG
 * @property string|null $AH
 * @property string|null $AI
 * @property float|null $AJ
 * @property string|null $AK
 * @property float|null $AL
 * @property string|null $AM
 * @property string|null $AN
 * @property string|null $AO
 * @property string|null $AP
 * @property string|null $AQ
 * @property string|null $AR
 * @property float|null $AS
 * @property string|null $AT
 * @property string|null $AU
 * @property string|null $AV
 * @property string|null $AW
 */
class UniversalFile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UniversalFile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acct_month', 'B', 'C', 'K', 'N', 'O'], 'required'],
            [['D', 'F', 'S', 'T', 'U', 'W', 'X', 'Z', 'AA', 'AB', 'AD', 'AE', 'AG', 'AH', 'AI', 'AK', 'AM', 'AN', 'AO', 'AP', 'AR', 'AT', 'AU', 'AV', 'AW'], 'string'],
            [['H', 'J', 'V', 'Y', 'AC', 'AF', 'AJ', 'AL', 'AS', 'AX'], 'number'],
            [['M'], 'safe'],
            [['B', 'P', 'Q', 'R'], 'string'],
            [['acct_month'], 'string', 'max' => 6],
            [['A', 'I', 'L'], 'string', 'max' => 1],
            [['C'], 'string', 'max' => 11],
            [['E'], 'string', 'max' => 10],
            [['G', 'AQ'], 'string', 'max' => 14],
            [['K'], 'string', 'max' => 60],
            [['N', 'O'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'acct_month' => 'Acct Month',
            'A' => 'A - H/D',
            'B' => 'B - Local',
            'C' => 'C - Employer',
            'D' => 'D - CNTRC/',
            'E' => 'E - Wk_Mnth',
            'F' => 'F - Tot_PD/Job_Cat',
            'G' => 'G - Tot_Hrs/Skl_Lvl',
            'H' => 'H - None/Hrs_Paid',
            'I' => 'I - Bill_Cycle/Grss_Wgs_Paid',
            'J' => 'J - DT_Rcvd/Hrs_Wkd',
            'K' => 'K - ER_Name/Grss_Wgs_Wkd',
            'L' => 'L - ER_Federal_ID/PAT',
            'M' => 'M - Effective_Date/Calc_Amnt',
            'N' => 'N - ER_Address/Employee_Last_Name',
            'O' => 'O - ER_City/Employee_First_Name',
            'P' => 'P - ER_State/Province',
            'Q' => 'Q - ER_Zip',
            'R' => 'R - ER_Country',
            'S' => 'H&W Hours',
            'T' => 'Pension Rate',
            'U' => 'Annuity Rate',
            'V' => 'LMCI Rate',
            'W' => 'LMP Rate',
            'X' => 'FTI Rate',
            'Y' => 'PAT Rate',
            'Z' => 'Future Per Cap Rate?',
            'AA' => 'Pension Amount Paid',
            'AB' => 'Annuity Amount Paid',
            'AC' => 'LMCI Amount Paid',
            'AD' => 'LMP Amount Paid',
            'AE' => 'FTI Amount Paid',
            'AF' => 'PAT Amount Paid',
            'AG' => 'Future Per Cap Amount Paid?',
            'AH' => 'SIN Indicator',
            'AI' => 'Total Package (if available)',
            'AJ' => 'Dues Check Off Paid',
            'AK' => 'Flat Fee Paid',
            'AL' => 'APF Paid',
            'AM' => 'Building Fund Paid',
            'AN' => 'Death Benefit Paid',
            'AO' => 'Market Recovery Paid',
            'AP' => 'Death Fund Paid',
            'AQ' => 'Dues Check Off Paid Rate?',
            'AR' => 'Flat Fee Paid Rate',
            'AS' => 'APF Paid Rate?',
            'AT' => 'Building Fund Paid Rate',
            'AU' => 'Death Benefit Paid Rate',
            'AV' => 'Market Recovery Paid Rate?',
            'AW' => 'Death Fund Paid Rate',
            'AX' => 'Admin Fees Paid',
        ];
    }
}
