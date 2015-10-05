<?php                                       

$date = getdate(time() - 60);  
$hours = (int) $date['hours'];
if($hours >= 2 AND $hours < 5) {
	echo "Cancel import" . PHP_EOL;
	exit;
}

include(realpath(__DIR__) . '/import.class.php');      

$import = new Import(DOCROOT . 'files/');    
$import->updateImportData();
$import->stocks();