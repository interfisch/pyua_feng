<?php
ini_set('memory_limit', '2048M');
include(realpath(__DIR__) . '/import.class.php');
$import = new Import(DOCROOT . 'files/');
#$import->updateImportData();
$import->stores();  
