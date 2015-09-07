<?php
include(realpath(__DIR__) . '/import.class.php');
$import = new Import(DOCROOT . 'files/');
$import->createShoppingXML();
$import->reIndex();
#$import->solrindex();
$import->clear_cache();