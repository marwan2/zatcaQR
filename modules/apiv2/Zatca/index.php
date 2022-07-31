<?php

require('../../vendor/autoload.php');

use API\Zatca\ZatcaQR;

if(isset($_POST) && !empty($_POST)) {
	$zatca = new ZatcaQR('', '', '', null, null, null);
	$zatca->getZatcaCodeAPI();
}