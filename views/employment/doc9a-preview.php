<?php

use app\models\contractor\Contractor;

/* @var $contractorModel Contractor */

?>

<h4 class="sm-print">9a Cards for: <?= $contractorModel->license_nbr . ' ' . $contractorModel->contractor ?></h4>

<?php

$recd_cnt = 0;
$page_cnt = 1;
foreach ($contractorModel->doc9aCards as $card) {
    $recd_cnt++;
    echo $this->render('_document', ['model' => $card]);
    if ($recd_cnt % 3 == 0) {
        echo '<br /><h4 class="sm-print">Page ' . $page_cnt . '</h4>';
        echo '<p class="page-break"></p><hr> ';
        $page_cnt++;
    }
}
echo '<br /><h4 class="sm-print">Page ' . $page_cnt . ' (' . $recd_cnt . ' records printed)</h4>';