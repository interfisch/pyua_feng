<?php

$proxyUrl = 'http://localhost/pyua/index.php/api/v2_soap/?wsdl'; // TODO : change url

$proxy = new SoapClient($proxyUrl);
$sessionId = $proxy->login('feng', 'admin2015');
$result = $proxy->catalogProductAttributeInfo($sessionId, 'manufacturer');
var_dump($result);
?>

