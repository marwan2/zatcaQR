<?php

require('../../vendor/autoload.php');

use API\Zatca\ZatcaQR;

header('Content-Type: application/json');

if(isset($_POST) && !empty($_POST)) {
	$zatca = new ZatcaQR('', '', '', null, null, null);
	print($zatca->getZatcaCodeAPI());


}
