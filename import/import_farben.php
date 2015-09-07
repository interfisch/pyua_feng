<?php
include(realpath(__DIR__) . '/import.class.php');   
$import = new Import(DOCROOT . 'files/');
$import->updateImportData();   
$import->colors();  