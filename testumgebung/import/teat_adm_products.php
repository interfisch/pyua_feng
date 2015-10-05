<?php
include(realpath(__DIR__) . '/import.class.php');
$import = new Import(DOCROOT . 'files/');
$import->updateImportData();

$products = $import->loadProducts();
$found = array();

foreach($products as $p) {
    $product = Mage::getModel('catalog/product');

    $product->setPrice($p->price);
    $product->setSpecialPrice($p->specialprice);
    $product->setSpecialFromDate($p->specialprice_from);
    $product->setSpecialToDate($p->specialprice_to);

    $product->setElkSonderaktion($p->elk_sonderaktion);
    $product->setElkAktionsartikel($p->elk_aktionsartikel);
    $product->setElkTopartikel($p->elk_topartikel);
    $product->setElkBtob($p->elk_btob);
    $product->setElkBtoc($p->elk_btoc);
    $product->setElkInfotext($p->elk_infotext);
    $product->setElkRabattaktiv($p->elk_rabattaktiv);
    $product->setElkSaison($p->elk_saison);
    $product->setElkStoffart($p->elk_stoffart);

    if($product->hasSpecialPrice() and $product->getElkAktionsartikel() and $product->getElkSonderaktion()) {
        echo "Found {$p->sku} {$p->name} [{$p->specialprice_from} to {$p->specialprice_to}]" .PHP_EOL;
    }
}
