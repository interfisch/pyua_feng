<?php
//header('Content-Type: text/plain');


error_reporting(E_ALL ^ E_NOTICE);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('MAGENTO', realpath(DOCROOT . '../'));

define('PIMCORE_BP', realpath(DOCROOT . '/../../pimcore'));

require_once PIMCORE_BP . "/pimcore/config/startup.php";

require_once MAGENTO . '/app/Mage.php';


class StoresData {

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
}


$args = $_SERVER['argv'];
unset($args[0]);
$action = current($args);
$force = next($args);

$storesData = new StoresData();

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
	default:
		echo "No action selected!";
	break;
}
