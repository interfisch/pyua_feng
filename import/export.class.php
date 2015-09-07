<?php
error_reporting(E_ALL ^ E_NOTICE);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('MAGENTO', realpath(DOCROOT . '../'));

define('PIMCORE_BP', realpath(DOCROOT . '/../../pimcore'));

require_once PIMCORE_BP . "/pimcore/config/startup.php";

require_once MAGENTO . '/app/Mage.php';


class Export {

	public function deleteAllStores() {
		$allStores = new Object_Store_List();

		foreach($allStores as $store) {
			try {
				$store->delete();
				echo "SUCCESS: deleted the Store: " . $store->getElklineid() . " (" . $store->getId(). ") - " . $store->getName() . PHP_EOL;
			} catch (Exception $e) {
				echo "ERROR: couldn't delete the Store: " . $store->getElklineid() . " (" . $store->getId(). ") - " . $store->getName() . PHP_EOL;
				echo $e->getMessage() . PHP_EOL;
			}
		}

		echo "All Stores deleted!" . PHP_EOL;
	}

	public function activateAllStores() {
		$allStores = new Object_Store_List();

		foreach ($allStores as $store) {
			try {
				$store->setPublished(true);
				$store->save();

				echo "SUCCESS: activated the Store: " . $store->getElklineid() . " (" . $store->getId(). ") - " . $store->getName() . PHP_EOL;
			} catch (Exception $e) {
				echo "ERROR: couldn't activated the Store: " . $store->getElklineid() . " (" . $store->getId(). ") - " . $store->getName() . PHP_EOL;
				echo $e->getMessage() . PHP_EOL;
			}

		}

		echo "All Stores activated!" . PHP_EOL;
	}

	public function exportSpecialPrices() {
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

		$collection = Mage::getResourceModel('catalog/product_collection');
//		$collection->addAttributeToFilter('status',1); //only enabled product
		$collection->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
					->addAttributeToFilter('special_to_date', array('or'=> array(
						0 => array('date' => true, 'to' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left');
		$fields = array('sku','status','special_from_date','special_to_date','price','special_price');

		$collection->addAttributeToSelect($fields); //add product attribute to be fetched

		array_walk($fields,function(&$item) {
			$item = '"' . $item . '"';
		});

		echo implode(';', $fields) . PHP_EOL;
		foreach ($collection as $product) {
			$datas = array(
				$product->getSku(),
				$product->getStatus(),
				$product->getSpecialFromDate(),
				$product->getSpecialToDate(),
				$product->getPrice(),
				$product->getSpecialPrice()
			);
			array_walk($datas,function(&$item) {
				$item = '"' . addslashes($item ). '"';
			});

			echo implode(';', $datas) . PHP_EOL;
		}
	}
}


$args = $_SERVER['argv'];
unset($args[0]);
$action = current($args);
$force = next($args);

$export = new Export();

$methods = get_class_methods($export);

$storesData = new Export();

switch ($action) {
	case "delete":
		if ($force == '--force-deletion') {
			$storesData->deleteAllStores();
		} else {
			echo "You need to add \"--force-deletion\" to confirm the deletion!" . PHP_EOL;
		}
		break;
	case "activate":
		if ($force == '--force-activation') {
			$storesData->activateAllStores();
		} else {
			echo "You need to add \"--force-activation\" to confirm the activation!" . PHP_EOL;
		}
		break;
	case "specialprices":
		$storesData->exportSpecialPrices();
		break;
	default:
		echo "No action selected!";
		break;
}
