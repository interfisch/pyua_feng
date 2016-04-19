<?php

$proxyUrl = 'http://localhost/pyua/index.php/api/v2_soap/?wsdl'; // TODO : change url

$proxy = new SoapClient($proxyUrl);
$sessionId = $proxy->login('feng', '556656');

$result = $proxy->salesOrderInfo($sessionId, '100001163');
print_r($result);
?>

