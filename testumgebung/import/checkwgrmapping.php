<?php
include(realpath(__DIR__) . '/import.class.php');   
$import = new Import(DOCROOT . 'files/');
$import->checkWgrMapping();