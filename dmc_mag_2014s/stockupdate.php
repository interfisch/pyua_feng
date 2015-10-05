<?php 

// stock update by zebrasolutions

ini_set("display_errors", 1);
error_reporting(E_ALL);

// a little helper for print_r ;-)
function p_r($var) {
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

// dummy product sku: 201106

require_once ('/home/magento/www/app/Mage.php');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


$sku = "01059-001";
$qty = 0;

$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku); 
if ($product) {
	$productId = $product->getIdBySku($sku);
	$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	$stockItemId = $stockItem->getId();
	$stock = array();
	
	if (empty($qty)) {
		$qty = 0;
		$in_stock = 1;
		$backorders = 0;
	}
	else {
		$in_stock = 1;
		$backorders = 0;
	}
	
	if (!$stockItemId) {
		$stockItem->setData('product_id', $product->getId());
		$stockItem->setData('stock_id', 1);
		$stockItem->setData('qty', $qty);
		$stockItem->setData('is_in_stock', $in_stock);
		$stockItem->setData('backorders', $backorders);
		
		echo $sku . " created.\n";
		
	} else {
		$stockItem->setData('qty', $qty);
		$stockItem->setData('is_in_stock', $in_stock);
		$stockItem->setData('backorders', $backorders);
		$stockItem->save();
		
		echo $sku . " updated.\n";
		
		p_r($stockItem->getData());
	}
	
	unset($stockItem);
	unset($product);
	unset($sku);
	unset($qty);
}


/*

define('MAGENTO', realpath(dirname(__FILE__)));
 require_once MAGENTO . '/app/Mage.php';
 
 umask(0);
 Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 $count = 0;
 
 $file = fopen(MAGENTO . '/var/import/updateStockLevels.csv', 'r');
 while (($line = fgetcsv($file)) !== FALSE) { 
 
 if ($count == 0) {
 foreach ($line as $key=>$value) {
 $cols[$value] = $key;
 } 
 } 
 
 $count++;
 
 if ($count == 1) continue;
 
 #Convert the lines to cols 
 if ($count > 0) { 
 foreach($cols as $col=>$value) {
 unset(${$col});
 ${$col} = $line[$value];
 } 
 }
 
 // Check if SKU exists
 $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
 
 if ( $product ) {
 
 $productId = $product->getIdBySku($sku);
 $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
 $stockItemId = $stockItem->getId();
 $stock = array();
 
 if (!$stockItemId) {
 $stockItem->setData('product_id', $product->getId());
 $stockItem->setData('stock_id', 1); 
 } else {
 $stock = $stockItem->getData();
 }
 
 foreach($cols as $col=>$value) {
 $stock[$col] = $line[$value];
 } 
 
 foreach($stock as $field => $value) {
 $stockItem->setData($field, $value?$value:0);
 }
 
 
 
 $stockItem->save();
 
 unset($stockItem);
 unset($product);
 }
 
 echo "<br />Stock updated $sku";
 
 }
 fclose($file);


*/

?>


