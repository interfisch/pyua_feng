<?php
//header('Content-Type: text/plain');


error_reporting(E_ALL ^ E_NOTICE);

define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

define('MAGENTO', realpath(DOCROOT . '../'));

define('PIMCORE_BP', realpath(DOCROOT . '/../../pimcore'));

require_once PIMCORE_BP . "/pimcore/config/startup.php";

require_once MAGENTO . '/app/Mage.php';


class Import_Exception {
	public static function error ($e) {
		mail('bschultz@rcgmbh.com', 'elkline import exception', $e->getMessage() . "\n\n" . (string) $e, "From: import@elkline.de\nContent-Type: text/plain; charset=\"utf-8\"");
		mail('mfroese@uandi.com', 'elkline import exception', $e->getMessage() . "\n\n" . (string) $e, "From: import@elkline.de\nContent-Type: text/plain; charset=\"utf-8\"");
	}

	public static $throwNoErrors = array(
		E_WARNING,
		E_NOTICE
	);

	public static function error_handler($code, $message, $file, $line) {
		if (!in_array($code, self::$throwNoErrors)) {
			$e = new Extended_Exception('ERROR: ' . $code . ': ' . $message);
			$e->setFile($file);
			$e->setLine($line);

			self::error($e);
		}
	}

	// for uncatchable errors
	public static function captureShutdown() {
		$error = error_get_last();

		if( $error ) {
			$type = $error['type'];
			self::error_handler($type, $error['message'], $error['file'], $error['line']);
		} else {
			return true;
		}
	}
}

class Extended_Exception extends Exception {
	public function setFile($file) {
		$this->file = $file;
	}

	public function setLine($line) {
		$this->line = $line;
	}
}

set_exception_handler(array('Import_Exception', 'error'));
// set_error_handler(array('Import_Exception', 'error_handler'));
register_shutdown_function(array( 'Import_Exception', 'captureShutdown'));

class Import {
	const BASE_ADDRESS_ID = 9999999;
	public $dir;
	public $time;
	public $doReindex = false;
	public $logFile = false;
	public $errorLog = array();
	public $suppressLogOutput = false;

	/* Setzt das Verzeichnis für die Export Daten
	 * Setzt eine Neue Admin Session
	 */
	public function __construct($dir) {
		$this->logFile = DOCROOT . 'logs/import_' . date('Ymd') . '.log';

		$this->time = time();
		$this->dir = $dir;
		umask(0);
		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		Mage::getSingleton('core/session', array('name'=>'adminhtml'));
		$userModel = Mage::getModel('admin/user');
		$userModel->setUserId(1);
		$session = Mage::getSingleton('admin/session');
		$session->setUser($userModel);
		$session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

		date_default_timezone_set('Europe/Berlin');
	}


	public function log($text, $type = 'info') {
		$msg = date('Y-m-d H:i:s') . ' ' . strtoupper($type) . ': ' . $text . PHP_EOL;

		if(!$this->suppressLogOutput) echo $msg;

		if(strtoupper($type) == 'ERROR') {
			$this->errorLog[] = $msg;
		}

		file_put_contents($this->logFile, $msg, FILE_APPEND);
	}

	public function __destruct() {
		if($this->doReindex) {
			$this->reIndex();
		}

		$this->log("Duration " . ( time() - $this->time) . " seconds");

		if(count($this->errorLog)) {
			mail('bschultz@rcgmbh.com', 'elkline import error', implode('', $this->errorLog), "From: import@elkline.de\nContent-Type: text/plain; charset=\"utf-8\"");
		}
	}

	/* Regeneriert die Indizes */
	public function reIndex() {
		$this->disable_realtime_index();
		$indexer = Mage::getSingleton('index/indexer');

		$processes = array();
    	foreach ($indexer->getProcessesCollection() as $process) {
			/*
			if($process->getStatus() !== Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX) {
				$this->log($process->getIndexer()->getName() . " index is up to date");
			} else {
			*/
	      try {
	          $process->reindexEverything();
	          $this->log($process->getIndexer()->getName() . " index was rebuilt successfully");
	      } catch (Mage_Core_Exception $e) {
	          $this->log($e->getMessage(), 'error');
	      } catch (Exception $e) {
	          $this->log($process->getIndexer()->getName() . " index process unknown error:", 'error');
	          $this->log($e, 'error');
	      }
	 		//}
    }
		$this->enable_realtime_index();
	}

 /* Gibt das neuste Verzeichnis mit Export Daten zurück */
	public function getCurrentExportDir() {
		$list = $this->listExportDirs();
		return end($list);
	}

	/* Gibt das vorherige Verzeichnis mit Exportdaten zurück */
	public function getPreviousExportDir() {
		$dirs = $this->listExportDirs();
		return $dirs[count($dirs) - 2];
	}

	/* Gibt eine geordnete Liste mit allen Verzeichnissen mit Exportdaten zurück */
	public function listExportDirs() {
		return glob($this->dir . '*', GLOB_MARK | GLOB_ONLYDIR);
	}

	/* Lädt die Liste der Artikel aus dem aktuellen/vorherigen import Daten */
	public function loadProducts($previous = false) {
		$colors = $this->getOptions('elk_color');
		$sizes = $this->getOptions('elk_size');


		// Pricelist
		$priceCsv = $this->readCsv(($previous ? $this->getPreviousExportDir() : $this->getCurrentExportDir()) . "export_preisliste.txt", array(
			'preisliste_nr',
			'artikel_id',
			'farb_nr',
			'groesse',
			'vk_preis',
			'aktionspreis',
			'von_datum',
			'bis_datum',
			'staffel_1',
			'staffel_2',
			'staffel_3',
			'staffel_4',
			'staffel_5',
			'vk_1',
			'vk_2',
			'vk_3',
			'vk_4',
			'vk_5',
			'rabatt',
			'specialprice',
		));

		$prices = array();

		foreach($priceCsv as $price) {
			if($price->preisliste_nr != 201) {
				continue;
			}

			if(date('Y', strtotime($price->von_datum)) < 2001) {
				$price->von_datum = null;
			}

			if(date('Y', strtotime($price->bis_datum)) < 2001) {
				$price->bis_datum = null;
			}

			$prices[$price->artikel_id] = $price;
		}

		// Array in dem alle Produktdaten gespeichert werden
		$products = array();

		// Sammlung alle verfügbaren Artikelnummern zur späteren Prüfung ob eine Modell Nr. vorhanden ist
		$allProducts = array();

		// CSV vorher zur Prüfung einlesen
		$all = $this->readCsv(($previous ? $this->getPreviousExportDir() : $this->getCurrentExportDir()) . "Export_artikel.txt", array(
			'artikel_id',
			'_artikel_nr',
			'name',
			'sku',
			'elkline_color',
			'elkline_color_name',
			'elkline_size',
			'vk',
			'price',
			'pfad_bild',
			'hwg_nr',
			'hwg',
			'wg_nr',
			'wg',
			'gr_pos',
			'agroesse',
			'Sonderaktion',
			'topartikel',
			'neueingetroffen',
			'b2b',
			'b2c',
			'infotext',
			'rabattaktiv',
			'weight',
			'elkline_saison',
			'store',
			'websites',
			'type',
			'attribute_set',
			'description',
			'short_description',
			'category_ids',
			'status',
			'tax_class_id',
			'qty',
			'is_in_stock',
			'stoffart',
			'Modell_nr',
			'modell_bezeichnung',
			'Aktionsartikel',
			'eigenschaft_1',
			'eigenschaft_2',
			'eigenschaft_3'
		), '|', "'", 1);

		// Alle Artikelnummern sammeln
		foreach($all as $p) {
			array_push($allProducts, $p->_artikel_nr);
		}

		$allProducts = array_unique($allProducts);

		// Alle Produkte durch gehen
		foreach($all as $p) {
			if(!$p->_artikel_nr OR !$p->sku) continue;
			if(!preg_match("/^\d+$/", $p->_artikel_nr) OR !preg_match("/^\d+$/", $p->sku)) continue;

			// Prüfen ob Modell_nr gesetzt ist und es ein Produkt mit der Passenden Artikelnummer gibt
			if ((bool) Mage::getStoreConfig('nachfolge/global/is_active') AND is_numeric($p->Modell_nr) && trim($p->Modell_nr) != '' && in_array($p->Modell_nr, $allProducts)) {
				// Dem Nachfolgeprodukt die Artikelnummer des Vorgängers zuordnen
				$p->_artikel_nr = $p->Modell_nr;
			}

			// Haupt Produkt
			if(!isset($products[$p->_artikel_nr])) {
				$products[$p->_artikel_nr] = (object) array(
					'sku' => $p->_artikel_nr,
					'name' => $p->name,
					'wgr' => $p->wg_nr,
					'options' => array(),
					'description' => $p->description,
					'short_description' => $p->short_description,
					'price' => (float) $p->price,
					'specialprice' => (isset($prices[$p->artikel_id]) ? (float) $prices[$p->artikel_id]->specialprice : null),
					'specialprice_from' => (isset($prices[$p->artikel_id]) ? $prices[$p->artikel_id]->von_datum : null),
					'specialprice_to' => (isset($prices[$p->artikel_id]) ? $prices[$p->artikel_id]->bis_datum : null),
					'elk_artikel_id' => $p->artikel_id,
					'elk_artikel_nr' => $p->_artikel_nr,
					'elk_hwg_nr' => $p->hwg_nr,
					'elk_wg_nr' => $p->wg_nr,
					'elk_sonderaktion' => $p->Sonderaktion == 'Y',
					'elk_aktionsartikel' => $p->Aktionsartikel == 'Y',
					'elk_topartikel' => $p->topartikel == 'Y',
					'elk_btob' => $p->b2b == 'Y',
					'elk_btoc' => $p->b2c == 'Y',
					'elk_infotext' => $p->infotext,
					'elk_rabattaktiv' => $p->rabattaktiv == 'Y',
					'elk_saison' => $p->elkline_saison,
					'elk_stoffart' => $p->stoffart,
					'elk_modell_nr' => $p->Modell_nr,
					'elk_modell_bezeichnung' => $p->modell_bezeichnung,
					'elk_neueingetroffen' => $p->neueingetroffen,
				);


			}

			$products[$p->_artikel_nr]->options[$p->sku] = (object) array(
				'sku' => $p->sku,
				'elkline_color' => array_search($p->elkline_color, $colors),
				'elkline_size' => array_search($p->agroesse, $sizes),
				'price' => (float) $p->price,
				'specialprice' => (isset($prices[$p->artikel_id]) ? (float) $prices[$p->artikel_id]->specialprice : null),
				'specialprice_from' => (isset($prices[$p->artikel_id]) ? $prices[$p->artikel_id]->von_datum : null),
				'specialprice_to' => (isset($prices[$p->artikel_id]) ? $prices[$p->artikel_id]->bis_datum : null),
				'weight' => (float) $p->weight,
				'elk_artikel_id' => $p->artikel_id,
				'elk_artikel_nr' => $p->_artikel_nr,
				'elk_hwg_nr' => $p->hwg_nr,
				'elk_wg_nr' => $p->wg_nr,
				'elk_sonderaktion' => $p->sonderaktion == 'Y',
				'elk_aktionsartikel' => $p->Aktionsartikel == 'Y',
				'elk_topartikel' => $p->topartikel == 'Y',
				'elk_btob' => $p->b2b == 'Y',
				'elk_btoc' => $p->b2c == 'Y',
				'elk_infotext' => $p->infotext,
				'elk_rabattaktiv' => $p->rabattaktiv == 'Y',
				'elk_saison' => $p->elkline_saison,
				'elk_stoffart' => $p->stoffart,
				'elk_modell_nr' => $p->Modell_nr,
				'elk_modell_bezeichnung' => $p->modell_bezeichnung,
				'elk_neueingetroffen' => $p->neueingetroffen,
			);
		}

		return $products;
	}

	/* Liest eine CSV Datei ein */
	public function readCsv($file, $columns = array(), $delimiter = '|', $enclosure = "'", $skip = 0) {
		if(!$handle = fopen($file, 'r')) {
			$this->log("Datei {$file} konnte nicht geöffnet werden", 'error');
			$this->doReindex = false;
			exit;
		}

		$lines = array();
		$count = 0;
		while(($row = fgetcsv($handle, filesize($file) +1, $delimiter, $enclosure)) !== false) {
			$count++;

			if($count <= $skip) continue;
			if(!$columns) {
				$columns = $row;
				continue;
			}

			$item = new stdClass();
			for($i = 0;$i < count($row); $i++) {
                if(isset($columns[$i]) AND $columns[$i])
				$item->$columns[$i] = $row[$i];
			}

			$lines[] = $item;
		}

		return $lines;
	}

	/* Importiert die Elkline Warengruppen */
	public function categories() {
		$csv = $this->readCsv($this->dir . 'wgr_mapping.csv');

		$categories = array();
		foreach($csv as $r) {
			if(!$r->Gruppe OR !$r->Kategorie) {
				continue;
			}

			if(!isset($categories[$r->Gruppe])) {
				$categories[$r->Gruppe] = array();
			}

			if(!isset($categories[$r->Gruppe][$r->Kategorie])) {
				$categories[$r->Gruppe][$r->Kategorie] = array();
			}

			$categories[$r->Gruppe][$r->Kategorie][] = $r->Nr;
		}

		$rootCategory = Mage::getModel('catalog/category')->load(2);

		foreach($categories as $gruppe => $sub) {
			$category = Mage::getModel('catalog/category');
			$category->setName($gruppe);
			$category->setIsActive(1);
			$category->setUrlKey($gruppe);
			$category->setPath($rootCategory->getPath());
			$category->setMetaTitle($gruppe);
			$category->setIncludeInMenu(true);
			$category->setIsAnchor(true);

			try {
				$category->save();
				echo "Added Category {$gruppe}" . PHP_EOL;
			}
			catch (Exception $e) {
				echo "Not Added Category {$gruppe}" . PHP_EOL;
				echo "Exception: {$e}" . PHP_EOL;
			}

			if(count($sub)) {
				foreach($sub as $kategorie => $wgrs) {
					$subcategory = Mage::getModel('catalog/category');
					$subcategory->setName($kategorie);
					$subcategory->setIsActive(1);
					$subcategory->setUrlKey($kategorie);
					$subcategory->setPath($category->getPath());
					$subcategory->setElklineWgr(implode('|', $wgrs));
					$subcategory->setMetaTitle($gruppe .' / ' . $kategorie);
					$subcategory->setIncludeInMenu(true);
					$subcategory->setIsAnchor(false);

					try {
						$subcategory->save();
						echo "Added Subcategory {$gruppe}/{$kategorie}" . PHP_EOL;
					}
					catch (Exception $e) {
						echo "Not Added Subcategory {$kategorie}/{$gruppe}" . PHP_EOL;
						echo "Exception: {$e}" . PHP_EOL;
					}
				}
			}
		}
	}

	public function getOptions($item) {
		$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $item);

		$options = array();

		foreach($attribute->getSource()->getAllOptions(true, true) as $option) {
			$options[$option['value']] = $option['label'];
		}
		return $options;
	}

	public function getAttributeId($item) {
		return $this->getAttribute($item)->getId();
	}

	public function getAttribute($item) {
		return Mage::getModel('eav/config')->getAttribute('catalog_product', $item);
	}

	private function getEntityID_bySKU($db_magento, $sku) {
	    $entity_row = $db_magento->query("SELECT entity_id FROM catalog_product_entity p_e WHERE p_e.sku = '$sku'")->fetchObject();
	    $entity_id  = $entity_row->entity_id;
	    return $entity_id;
	}

	public function stores() {
  	$cols = array(
			'kunden_id',
      		'kunden_nr',
			'firmenname',
			'name',
			'strasse',
			'plz',
			'ort',
			'land',
			'username',
			'passwort',
			'telefon',
			'email',
			'mwst',
			'ansprechpartner',
			'ansprechpartner_geschlecht',
			'ansprechpartner_telefon',
			'ansprechpartner_email',
			'liefer_name1',
			'liefer_name2',
			'liefer_name3',
			'liefer_strasse',
			'liefer_plz',
			'liefer_ort',
			'liefer_land',
			'liefer_telefon',
			'liefer_email',
			null,
			null,
			'intrastat_laender_nr',
			'online_anzeigen',
			'family',
			'kids',
			'adults',
			'accessoires',
			'klein_aber_fein',
			'ausgewählte_artikel',
			'breite_auswahl',
			'add1',
			'add2',
			'url',
			'kundenlimitit',
			'b2c',
		);

		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_kunde.txt', $cols);
    	$stores = array();

    	foreach($csv as $c) {
        	if($c->online_anzeigen == 'J') {
        	    $stores[(int) preg_replace('/\D/', '', $c->kunden_id)] = $c;
        	}
  		}

  		$headerColumn = array(
  			'Anschriften_ID',
  			'KD_ID',
  			'_Kunden_Nr',
  			'Art',
  			'standard_anschrift',
  			'ILN',
  			'Anrede',
  			'Vorname',
  			'Name',
  			'Strasse',
  			'PLZ',
  			'Ort',
  			'PLZ_Postfach',
  			'Postfach',
  			'Telefax',
  			'Telefon',
  			'Hinweis',
  			'Nr',
  			'Land',
  			'Land_id',
  			'Plz_Zeichen',
  			'iln',
  			'filialnummer',
  			'abteilung',
  			'bank',
  			'bic',
  			'iban',
  			'blz',
  			'konto_nr',
  			'ust_id_nr',
  			'geaendert_am',
  			'steuernummer',
  			'e_mail',
  			'mwst_id',
  			'Versandart_ID'
  		);

		//Adressen
		$anschriftCsv = $this->readCsv($this->getCurrentExportDir() . 'Export_anschriften.txt', $headerColumn, '|', '\'', 1);
		$anschriften = array();

		foreach($anschriftCsv as $a) {
			// TODO Prüfen ob Adresse von hier genommen werden soll
			if($a->Art != 'V') continue;

            $_kdId = (int) preg_replace('/\D/', '', $a->KD_ID);

			if (isset($stores[$_kdId])) {
				$stores[$_kdId]->addresses[] = $a;
			}
		}

		// Alte Stores
		$oldIds = array();
		$oldStores = new Object_Store_List();

		foreach($oldStores as $store) {
			$oldIds[$store->getId()] = $store->getElklineid();
		}

		$parent = Object_Folder::getByPath('/stores')->getId();

		foreach($stores as $store) {
			$oldKeyRefreshed = false;

			if (!isset($store->addresses) || !count($store->addresses)) {
				$address = new stdClass();

				$address->Name = $store->name;
				$address->Strasse = $store->strasse;
				$address->PLZ = $store->plz;
				$address->Ort = $store->ort;
				$address->Land = $store->land;
				$address->Telefon = $store->telefon;
				$address->e_mail = $store->email;
				$address->Anschriften_ID = 0;

				$store->addresses[] = $address;
			}
			foreach ($store->addresses as $address) {
				$address_key = strtolower(preg_replace('#[^0-9a-z]+#i', '-', preg_replace('/\D/', '', $store->kunden_id) . '-' . preg_replace('/\D/', '', $address->Anschriften_ID) . '-' . $store->name));

				$pimStore = Store::getByPath('/stores/' . $address_key,1);

				$new = false;
				$changed = false;

				if(!$pimStore OR !$pimStore instanceof Store) {
					if (!$oldKeyRefreshed) {
						$old_address_key = strtolower(preg_replace('#[^0-9a-z]+#i', '-', $store->kunden_id . '-' . $store->name));
						$pimStore = Store::getByPath('/stores/' . $old_address_key,1);

						$oldKeyRefreshed = true;
						if(!$pimStore OR !$pimStore instanceof Store) {
							$new = true;

						} else {
							unset($oldIds[$pimStore->getId()]);
							$pimStore->setKey($address_key);
						}
					} else {
						$new = true;
					}

					if ($new) {
						$pimStore = new Store();
						$pimStore->setCreationDate(time());
						$pimStore->setElklineid(preg_replace('/\D/', '', $store->kunden_id));
						$pimStore->setPublished(false);
						$pimStore->setKey($address_key);
						$pimStore->setStore(false);
						$pimStore->setDealer(true);
						$pimStore->setOpeningtimes('');
						$pimStore->setParentId($parent);
					}
				} else {
					unset($oldIds[$pimStore->getId()]);
				}

				// Falls das Land nicht angegeben ist soll es immer Deutschland sein
				if (empty($address->Land)){
					$address->Land = 'Deutschland';
				}

				$pimStore->setAddressid(preg_replace('/\D/', '', $address->Anschriften_ID));
				$pimStore->setKundennr($store->kunden_nr);
				$pimStore->setName($address->Name);
				$pimStore->setStreet($address->Strasse);
				$pimStore->setZip($address->PLZ);
				$pimStore->setCity($address->Ort);
				$pimStore->setCountry($this->getCountryCode($address->Land));
				$pimStore->setFon($address->Telefon);
				$pimStore->setEmail($address->e_mail);
				$pimStore->setUrl($store->url);

				$location = implode(' ', array($pimStore->getStreet(), $pimStore->getZip(), $pimStore->getCity(),  $pimStore->getCountry()));

				if($cord = $this->getGeoloaction($location)) {
					$point = new Object_Data_Geopoint((float) $cord->long, (float) $cord->lat);
				} else {
					$point = new Object_Data_Geopoint(0, 0);
				}

				$pimStore->setPosition($point);

				try {
					$pimStore->save();
					$this->log(($new ? "Added" : "Updated") . " Store {$pimStore->getKey()} {$pimStore->getName()}");
				}
				catch (Exception $e) {
					$this->log("Not " . ($new ? "Added" : "Updated") . " Store {$pimStore->getKey()} {$pimStore->getName()}", 'error');
					$this->log($e, 'error');
				}
				#$i++;
			}
		}

		if(count($oldIds)) {
			foreach($oldIds as $id => $elklineid) {
				if($pimStore = Store::getById($id, 1)) {
					$pimStore->setPublished(false);
					$pimStore->save();
					$this->log("Disabled Store {$pimStore->getKey()} {$pimStore->getName()}");
				}
			}
		}
	}

	private function getGeoloaction_old($address) {
		$url = "http://maps.google.com/maps/geo?q=" . urlencode($address);
		$url .= '&key=' . Pimcore_Config::getSystemConfig()->services->googlemaps->apikey . '&sensor=false&output=json';
		$content = file_get_contents($url);
		$json = json_decode($content);

		if($json->Status->code !== 200) return false;
		$place = (is_array($json->Placemark)) ? reset($json->Placemark) : $json->Placemark;
		$cord = new stdClass;
		list($cord->long, $cord->lat) = $place->Point->coordinates;
		return $cord;
	}

	private function getGeoloaction($address) {
		$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=" . urlencode($address);
		$content = file_get_contents($url);
		$json = json_decode($content);

		if($json->status !== 'OK') return false;
		if(!isset($json->results) || !is_array($json->results) || !count($json->results)) return false;

		$first_result = reset($json->results);
		if(!isset($first_result->geometry) || !isset($first_result->geometry->location)) return false;
		$location = $first_result->geometry->location;

		$cord = (object) array(
			'long' => $location->lng,
			'lat' => $location->lat
		);

		return $cord;
	}

    public function stores2() {
        $cols = array('kunden_id',
                      'kunden_nr',
                      'firmenname',
        'name',
        'strasse',
        'plz',
        'ort',
        'land',
        'username',
        'passwort',
        'telefon',
        'email',
        'mwst',
        'ansprechpartner',
        'ansprechpartner_geschlecht',
        'ansprechpartner_telefon',
        'ansprechpartner_email',
        'liefer_name1',
        'liefer_name2',
        'liefer_name3',
        'liefer_strasse',
        'liefer_plz',
        'liefer_ort',
        'liefer_land',
        'liefer_telefon',
        'liefer_email',
        null,
        null,
        null,
        'online_anzeigen');

        $csv = $this->readCsv($this->getCurrentExportDir() . 'Export_kunde.txt', $cols);
        $stores = array();

        foreach($csv as $c) {
            if($c->online_anzeigen == 'J') {
                $stores[] = $c;
            }
        }

        $new_count = 0;
        $old_count = 0;

        $db = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach($stores as $c) {
            $result = $db->query("SELECT * FROM elkline_pimcore.object_store_4 WHERE `name` = '{$c->name}'")->fetchObject();

            $address = new stdClass();
            $address->name = $c->name;
            $address->url = '';
            $address->position__longitude = '';
            $address->position__latitude = '';
            $address->openingtimes = '';
            $address->store = '0';
            $address->dealer = '1';

            if(count($adresses = $this->getCustomerAddresses($c->kunden_nr)) > 0) {
                foreach($adresses as $a) {
                    //TODO
                    if($a->Art == '???') {
                        $address->street = '';
                        $address->zip = '';
                        $address->city = '';
                        $address->country = '';
                        $address->fon = '';
                        $address->email = '';
                        break;
                    }
                }

            }
            else {

                $address->street = $c->strasse;
                $address->zip = $c->plz;
                $address->city = $c->ort;
                $address->country = $this->getCountryCode($c->land);
                $address->fon = $c->telefon;
                $address->email = $c->email;
            }

            if($result) {
                $new_count++;
                $id = $result->oo_id;

                $db->query("UPDATE elkline_pimcore.object_store_4
                            SET `name` = '{$c->name}',
                                street = '{$address->street}',
                                zip = '{$address->zip}',
                                city = '{$address->city}',
                                country = '{$address->country}',
                                fon = '{$address->fon}',
                                email = '{$address->email}',
                                url = '{$address->url}',
                                store = '{$address->store}',
                                dealer = '{$address->dealer}'
                            WHERE oo_id = '{$id}'");

                $db->query("UPDATE elkline_pimcore.object_query_4
                            SET `name` = '{$c->name}',
                                street = '{$address->street}',
                                zip = '{$address->zip}',
                                city = '{$address->city}',
                                country = '{$address->country}',
                                fon = '{$address->fon}',
                                email = '{$address->email}',
                                url = '{$address->url}',
                                store = '{$address->store}',
                                dealer = '{$address->dealer}'
                            WHERE oo_id = '{$id}'");
            }
            else {
                $old_count++;
                $o_key = $this->slug($c->name);

                $db->query("INSERT INTO elkline_pimcore.objects
                            SET o_parentId = '245',
                            o_type = 'object',
                            o_key = '{$o_key}',
                            o_path = '/stores/',
                            o_index = null,
                            o_published = '0',
                            o_creationDate = '" . time() . "',
                            o_modificationDate = '" . time() . "',
                            o_userOwner = '1',
                            o_userModification = '1',
                            o_classId = '4',
                            o_className = 'Store'");

                $id = $db->lastInsertId();

                $db->query("INSERT INTO elkline_pimcore.object_store_4
                            SET oo_id = '{$id}',
                                `name` = '{$c->name}',
                                street = '{$address->street}',
                                zip = '{$address->zip}',
                                city = '{$address->city}',
                                country = '{$address->country}',
                                fon = '{$address->fon}',
                                email = '{$address->email}',
                                url = '{$address->url}',
                                store = '{$address->store}',
                                dealer = '{$address->dealer}'");

                $db->query("INSERT INTO elkline_pimcore.object_query_4
                            SET oo_id = '{$id}',
                                `name` = '{$c->name}',
                                street = '{$address->street}',
                                zip = '{$address->zip}',
                                city = '{$address->city}',
                                country = '{$address->country}',
                                fon = '{$address->fon}',
                                email = '{$address->email}',
                                url = '{$address->url}',
                                store = '{$address->store}',
                                dealer = '{$address->dealer}'");
            }
        }
        $this->log("{$new_count} new stores imported. {$old_count} stores updated.", 'warning');
    }

    private function getCountryCode($c) {
        switch(strtolower($c)) {
            case 'deutschland':
               return 'DE';
                break;
            case 'österreich':
               return 'AT';
                break;
            case 'dänemark':
               return 'DK';
                break;
            case 'schweiz':
               return 'CH';
                break;
            case 'niederlande':
               return 'NL';
                break;
            case 'belgien':
               return 'BE';
                break;
            case 'finnland':
               return 'FI';
                break;
            case 'frankreich':
               return 'FR';
                break;
            case 'großbritanien':
               return 'UK';
                break;
            case 'italien':
               return 'IT';
                break;
        }

        if(strtolower($c) == '?sterreich' OR substr(strtolower($c), -9, 9) == 'sterreich') {
        	return 'AT';
        }

        if(strtolower($c) == 'd?nemark' OR substr(strtolower($c), 0, 1) . substr(strtolower($c), 3) == "dnemark") {
        	return 'DK';
        }

        return '';
    }

    private function getCustomerAddresses($customer_nr) {
        $csv = $this->readCsv($this->getCurrentExportDir() . 'Export_anschriften.txt');

        $addresses = array();

        foreach($csv as $c) {
            if($c->_Kunden_Nr == $customer_nr) {
                $addresses[] = $c;
            }
        }

        return $addresses;
    }

	public function stocks() {

		/*
		 * bschultz 2012.02.17
		 * Normale Lagerbestände (kein Blocklager) werden um n verringert
		 * um im normalen Lager in Impuls immer n Artikel für b2b kunden bereit
		 * zu halten.
		 */

		$minStockAmount = 0;
		$cols = array(
			'sku', 'qty', 'elkline_Lager', 'is_in_stock'
		);

		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_lager.txt', $cols, '|', "'", 1);

		$count = count($csv);

		$csvBlockLager = $this->readCsv($this->getCurrentExportDir() . 'Export_lager_197.txt', $cols, '|', "'", 1);
		$blockLager = array();

		if(count($csvBlockLager)) {
			foreach($csvBlockLager as $c) {
				if(!isset($c->sku) OR !isset($c->qty) OR !isset($c->elkline_Lager) OR !isset($c->is_in_stock) OR !$c->sku) {
					continue;
				}
				$blockLager[(string) $c->sku] = (int) $c->qty;
			}
		}

		$i = 1;

		foreach($csv as $c) {

			$c->qty = max(0, $c->qty - $minStockAmount);
			$i++;
			if(!isset($c->sku) OR !isset($c->qty) OR !isset($c->elkline_Lager) OR !isset($c->is_in_stock) OR !$c->sku) {
				$this->log("Wrong entry in stock export on line {$i}", 'warning');
				continue;
			}
			$sku = $c->sku;
			$qty = (int) $c->qty;

			if(isset($blockLager[$sku])) {
				$qty = $qty + $blockLager[$sku];
			}

			if(!$c->is_in_stock) {
				$in_stock = false;
			} else {
				$in_stock = (bool) $qty > 0;
			}

		 	$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);

			if(!$product) {
				$this->log("Produkt {$sku} not found", 'warning');
				continue;
			}

			if(!$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())) {
				$this->log("Stockitem for {$sku} not found", 'warning');
				continue;
			}

			$stockItem->setQty($qty);
			$stockItem->setIsInStock($in_stock);

			if($stockItem->save()) {
				$this->log("Productstock for {$sku} = {$qty} (" . number_format((100 / $count) * $i, 2, ',', '.') . '%)');
			}

			if(isset($blockLager[$sku])) {
				$product->setElkBlocklagerQty($blockLager[$sku]);
				if($product->save()) {
					$this->log("Blocklager Stock for {$sku} = {$blockLager[$sku]}");
				}
			} else {
				if($product->getElkBlocklagerQty() != 0) {
					$product->setElkBlocklagerQty(0);
					if($product->save()) {
						$this->log("Blocklager Stock for {$sku} = 0");
					}
				}
			}
		}

	}



	public function disable_realtime_index() {
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($pCollection as $process) {
			$this->log('Disable Realtime-Index for: ' . $process->getIndexer()->getName());
		  $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
		}
	}

	public function enable_realtime_index() {
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($pCollection as $process) {
			$this->log('Enable Realtime-Index for: ' . $process->getIndexer()->getName());
		  $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
		}
	}

	public function stocks2() {
		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_lager.txt');

		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$i = 1;
		foreach($csv as $c) {
			$i++;
			if(!isset($c->sku) OR !isset($c->qty) OR !isset($c->elkline_Lager) OR !isset($c->is_in_stock) OR !$c->sku) {
				$this->log("Wrong entry in stock export on line {$i}", 'warning');
				continue;
			}
			$sku = $c->sku;
			$qty = (int) $c->qty;

			if(!$c->is_in_stock) {
				$in_stock = false;
			} else {
				$in_stock = (bool) $qty > 0;
			}

			/*

			$sql = "update cataloginventory_stock_item as si INNER JOIN catalog_product_entity as pe ON si.product_id = pe.entity_id
			set si.qty = {$qty}, si.is_in_stock = '" . (int) $in_stock. "', si.manage_stock = 1, si.use_config_manage_stock = 1
			where pe.sku = '{$sku}';";
			*/

			$entity_id = $this->getEntityID_bySKU($db, $sku);

			$sql = "UPDATE cataloginventory_stock_item s_i, cataloginventory_stock_status s_s
			         SET   s_i.qty = '{$qty}', s_i.is_in_stock = IF('{$qty}'>0, 1,0),
			               s_s.qty = '{$qty}', s_s.stock_status = IF('{$qty}'>0, 1,0)
			         WHERE s_i.product_id = '{$entity_id}' AND s_i.product_id = s_s.product_id";

			/*
			$sql = "update cataloginventory_stock_item as si INNER JOIN catalog_product_entity as pe ON si.product_id = pe.entity_id
			set si.qty = {$c->qty}, si.is_in_stock = '" . (int) $in_stock . "'
			where pe.sku = '{$sku}';";
			*/
      try {
				$result = $db->query($sql);
			} catch(Exception $e) {
				$this->log($e, 'error');
				echo $sql . PHP_EOL;
			}

			if(count($result) == 1) {
			 	$this->log("Productstock for {$sku} = {$c->qty}");
			} else {
				$this->log("Failed to Update Productstock for {$sku}", 'error');
			}

			/*
			$product = Mage::getModel('catalog/product');
			if(!$productId = $product->getIdBySku($sku)) {
				echo "Product {$sku} not found<br />\n";
			} else {
				$product->load($productId);
				$stockData = array(
					'qty' => $c->qty,
					'is_in_stock' => $c->is_in_stock,
					'manage_stock' => 1,
					'use_config_manage_stock' => 0,
				);

				$product->setStockData($stockData);

				try{
					$product->save();
					echo "Productstock for {$sku} = {$c->qty}<br />\n";
				}
				catch(Exception $e) {
					echo "Failed to Update Productstock for {$sku}<br />\n{$e}<br />\n";
				}
			}
			*/
		}
	}

	public function getCategoryMapping() {
		$tree = Mage::getResourceModel('catalog/category_tree')->load();
		$ids = $tree->getCollection()->getAllIds();
		$mapping = array();
		foreach($ids as $id) {
			$category = Mage::getModel('catalog/category');
			$category->load($id);
			if($category->getElklineWgr()) {
				$wgrs = explode('|', $category->getElklineWgr());
				foreach($wgrs as $wgr) {
					$mapping[$wgr] = $category;
				}
			}
		}

		return $mapping;

	}

	public function getSalesCategoryMapping() {
		$tree = Mage::getResourceModel('catalog/category_tree')->load();
		$ids = $tree->getCollection()->getAllIds();
		$mapping = array();
		foreach($ids as $id) {
			$category = Mage::getModel('catalog/category');
			$category->load($id);
			if($category->getElklineSalesHwg()) {
				$wgrs = explode('|', $category->getElklineSalesHwg());
				foreach($wgrs as $wgr) {
					$mapping[$wgr] = $category;
				}
			}
		}

		return $mapping;

	}

	public function removeAdmCategory ($catIds) {
		$admCategory = array_search('181', $catIds);

		if ($admCategory) {
			if(isset($catIds[$admCategory])) {
				unset($catIds[$admCategory]);
			}
		}

		return $catIds;
	}

    public function isProductDiscountable($product) {
        // Prüfen ob Specialprice gültig, also ob er vom normalen Preis abweicht und ein zutrffendes Datum hat
        if($product->hasSpecialPrice()) {
            return 0;
        }

//        // Prüfen ob Aktionsartikel
//        if($product->getElkAktionsartikel()) {
//            return 0;
//        }

//        // Prüfen ob Sonderaktionsartikel (ADM)
//        if($product->getElkSonderaktion()) {
//            return 0;
//        }

        return 1;
    }

	public function products() {
		$attr_color = $this->getAttribute('elk_color');
		$attr_size = $this->getAttribute('elk_size');
		$color_id = $this->getAttributeId('elk_color');
		$size_id = $this->getAttributeId('elk_size');
		$url_keys = array();

		$products = $this->loadProducts();

		$oIds = array();

		$oldProducts = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('type_id', array('neq' => 'ugiftcert'));

		foreach($oldProducts as $op) {
			$oIds[$op->getId()] = true;
		}


		$mapping = $this->getCategoryMapping();
		$salesMapping = $this->getSalesCategoryMapping();

    	$i = 1;
		foreach($products as $artNum => $p) {
			// Debug um nur einen Artikel zu Importieren
      		//if($p->elk_artikel_id != 673) continue;


			$this->log("Import Product {$i} of " . count($products));
			$i++;

			$optionIds = array();
			$colorValues = array();
			$sizeValues = array();
			foreach($p->options as $sku => $item) {
				$new = false;
				if(!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)) {
					$product = Mage::getModel('catalog/product');
					$new = true;
				}

				// Neu
				if($new) {
					$product->setTypeId('simple');
					$product->setTaxClassId(2);
					$product->setWebsiteIDs(array(1));
					$product->setStockData(array(
					 'is_in_stock' => 1,
					 'qty' => 0
					));
					$product->setAttributeSetId(4);  // elkline
					$product->setSku($sku);
					$product->setDescription($p->description);
					$product->setShortDescription($p->short_description);
					$product->setVisibility(1); // Nirgends anzeigen
					$product->setCreatedAt(strtotime('now'));
				} else {
					if(!$product->getDescription()) $product->setDescription($p->description);
					if(!$product->getShortDescription()) $product->setShortDescription($p->short_description);

					unset($oIds[$product->getId()]);
				}

				$product->setStatus(1); // Aktiv
				$product->setName($p->name);
				$product->setName($p->name);
				$product->setUrlKey($p->name . '-' . $sku);
				$product->setPrice($item->price);
				$product->setWeight($item->weight);
				$product->setElkColor($item->elkline_color);
				$colorValues[] = $item->elkline_color;
				$product->setElkSize($item->elkline_size);
				$sizeValues[] = $item->elkline_size;

				$product->setElkArtikelId($item->elk_artikel_id);
				$product->setElkArtikelNr($item->elk_artikel_nr);
				$product->setElkHwgNr($item->elk_hwg_nr);
				$product->setElkWgNr($item->elk_wg_nr);
				$product->setElkSonderaktion($item->elk_sonderaktion);
				$product->setElkAktionsartikel($item->elk_aktionsartikel);
				$product->setElkTopartikel($item->elk_topartikel);
				$product->setElkBtob($item->elk_btob);
				$product->setElkBtoc($item->elk_btoc);
				$product->setElkInfotext($item->elk_infotext);
				$product->setElkRabattaktiv($item->elk_rabattaktiv);
				$product->setElkSaison($item->elk_saison);
				$product->setElkStoffart($item->elk_stoffart);
				if($item->modell_nr) $product->setElkModellNr($item->modell_nr);
				$product->setElkModellBezeichnung($item->elk_modell_bezeichnung);
				$product->setElkNeueingetroffen($item->elk_neueingetroffen);

				// SpecialPrice
				$product->setSpecialPrice($item->specialprice);
				$product->setSpecialFromDate($item->specialprice_from);
				$product->setSpecialToDate($item->specialprice_to);

                $product->setElkDiscountable($this->isProductDiscountable($product));

				try {
					$product->save();
					$optionIds[] = $product->getId();
					$this->log(($new ? "Added" : "Updated") . " Product-Option {$p->name} ({$artNum} / {$sku})");
				}
				catch (Exception $e) {
					$this->log("Not " . ($new ? "Added" : "Updated") . " Product-Option {$p->name} ({$artNum} / {$sku})", 'error');
					$this->log($e, 'error');
				}

			}
			// Das konfigurierbare Produkt
			$product = Mage::getModel('catalog/product');

			$new = false;
			if(!$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $artNum)) {
				$product = Mage::getModel('catalog/product');
				$new = true;
			}

			if($new) {
				$product->setStatus(2); // Neu inaktiv
 				$product->setSku($artNum);
				$product->setTypeId('configurable');
				$product->setTaxClassId(2); // none
				$product->setWebsiteIDs(array(1));
				$product->setStockData(array(
				 'is_in_stock' => 1,
				 'qty' => 0
				));
				$product->setAttributeSetId(4);
				$product->setDescription($p->description);
				$product->setShortDescription($p->short_description);
				$product->setVisibility(4); // Catalog und Suche
				$product->setCreatedAt(strtotime('now'));

				/*
				$data = array();

				$data[0] = array(
					'id' => NULL,
					'label' => 'Farbe',
					'use_default' => true,
					'position' => 1,
					'attribute_id' => $color_id,
					'attribute_code' => 'elk_color',
					'frontend_label' => 'Farbe',
					'store_label' => 'Farbe',
					'html_id' => 'config_elk_color',
				);

				$data[1] = array(
					'id' => NULL,
					'label' => 'Größe',
					'use_default' => true,
					'position' => 2,
					'attribute_id' => $size_id,
					'attribute_code' => 'elk_size',
					'frontend_label' => 'Größe',
					'store_label' => 'Größe',
					'html_id' => 'config_elk_size',
				);

				$product->setConfigurableAttributesData($data);
				*/

				$product->setConfigurableAttributesData(array(
					array_merge($attr_color->getData(), array('label' => 'Farbe', 'values' => array_unique($colorValues))),
					array_merge($attr_size->getData(), array('label' => 'Größe', 'values' => array_unique($sizeValues))),
				));

		    $product->setCanSaveConfigurableAttributes(true);
			} else {
				if(!$product->getDescription()) $product->setDescription($p->description);
				if(!$product->getShortDescription()) $product->setShortDescription($p->short_description);
				unset($oIds[$product->getId()]);
			}

			//$product->setStatus(1); // Enabled
			$product->setName($p->name);
			$product->setPrice($p->price);
			$product->setWeight($p->weight);

			// Url Key
			$urlKey = $p->name;
			$url_i = 1;
			while(in_array($urlKey, $url_keys)) {
				$urlKey = $p->name . '-' . $url_i++;
			}

			array_push($url_keys, $urlKey);

			$product->setUrlKey($urlKey);

			$product->setElkArtikelId($p->elk_artikel_id);
			$product->setElkArtikelNr($p->elk_artikel_nr);
			$product->setElkHwgNr($p->elk_hwg_nr);
			$product->setElkWgNr($p->elk_wg_nr);
			$product->setElkSonderaktion($p->elk_sonderaktion);
			$product->setElkAktionsartikel($p->elk_aktionsartikel);
			$product->setElkTopartikel($p->elk_topartikel);
			$product->setElkBtob($p->elk_btob);
			$product->setElkBtoc($p->elk_btoc);
			$product->setElkInfotext($p->elk_infotext);
			$product->setElkRabattaktiv($p->elk_rabattaktiv);
			$product->setElkSaison($p->elk_saison);
			$product->setElkStoffart($p->elk_stoffart);
			if($p->modell_nr) $product->setElkModellNr($p->modell_nr);
			$product->setElkModellBezeichnung($p->elk_modell_bezeichnung);
			$product->setElkNeueingetroffen($p->elk_neueingetroffen);

			// SpecialPrice
			$product->setSpecialPrice($p->specialprice);
			$product->setSpecialFromDate($p->specialprice_from);
			$product->setSpecialToDate($p->specialprice_to);

            $product->setElkDiscountable($this->isProductDiscountable($product));

			$addedToCat = array();

			// Erstmal alle Sales Kategorien raus.
			$catIds = $product->getCategoryIds();

			foreach($catIds as $key => $catId) {
				foreach($salesMapping as $cat) {
					if($cat->getId() == $catId) {
						unset($catIds[$key]);
					}
				}

				// ADM Kat raus
				$catIds = $this->removeAdmCategory($catIds);

				$product->setCategoryIds($catIds);
			}

			// 2011.09.26 Kategoriemappping erhalten
			if(isset($mapping[$p->wgr]) AND ($new OR !count(/* Mage::getResourceSingleton('catalog/product')->getCategoryIds($product) */ $product->getCategoryIds()))) {
				$product->setCategoryIds(array($mapping[$p->wgr]->getId()));
				$addedToCat[] = $mapping[$p->wgr]->getPath();
			}

			// Prüfen ob Specialpreis vorhanden
			if($product->hasSpecialPrice()) {
				// Prüfen on AKtionsartikel
				if($product->getElkAktionsartikel()) {
					//Wenn Artikel nicht Adm ist dann alte Kategorien löschen.
					if(!$product->getElkSonderaktion()) {
						$this->log("Remove Categories from Product {$p->name} ({$artNum})", 'test');
						$product->setCategoryIds(array());
						$addedToCat = array();
					}

					$catIds = array_unique(array_merge($product->getCategoryIds(), array($salesMapping[$p->elk_hwg_nr]->getId())));

					// ADM Kat raus
					$catIds = $this->removeAdmCategory($catIds);

					$product->setCategoryIds($catIds);
					$addedToCat[] = $salesMapping[$p->elk_hwg_nr]->getPath();

					// Angebot des Monats in Kategorie 181 (ADM)
					if($product->getElkSonderaktion()) {
						$cat_ids = (array) $product->getCategoryIds();
						array_push($cat_ids, 181);
						$product->setCategoryIds(array_unique($cat_ids));
						$addedToCat[] = 181;
					};
				}
			}

			$data = array();

			foreach($optionIds as $oId) {
				$data[$oId] = array(
					array('attribute_id' => $color_id, 'label' => 'Farbe'),
					array('attribute_id' => $size_id, 'label' => 'Größe'),
				);
			}

			$product->setConfigurableProductsData($data);

			try {
				$product->save();
				$this->log(($new ? "Added" : "Updated") . " Product {$p->name} ({$artNum}) / " . implode('|', $addedToCat));
			}
			catch (Exception $e) {
				$this->log("Not " . ($new ? "Added" : "Updated") . " Product {$p->name} ({$artNum})", 'error');
				$this->log($e, 'error');
			}
		}

		//UPDATE catalog_product_entity_int SET value = '2' WHERE attribute_id = 84;"
		foreach($oIds as $id => $val) {
			$product = Mage::getModel('catalog/product')->load($id);
			if($product->getStatus() == 2) continue;
			$product->setStatus(2); // disabled
			$product->setUrlKey($product->getName() . '-' . $product->getSku());
			$product->save();
			$this->log("Disabled {$id}");
		}

	}

	public function colors() {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');

		$csv = $this->readCsv($this->getCurrentExportDir() . 'export_farbstaffel.txt', array('nr', 'description', 'rgb', 'rgb2'), '|', "'", 1);

		$importValues = array();

		// Alle Farben aus CSV
		foreach($csv as $c) {
			$importValues[(string) $c->nr] = implode('|', array(trim($c->description), trim($c->rgb), trim($c->rgb2)));
		}
		$attributeId = $this->getAttributeId('elk_color');
		// Alle vorhandenen Farbe
		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attributeId)
			->setStoreFilter(1)
      		->load();

		$optionValues = array();

		foreach($optionCollection as $option) {
			$optionValues[(string) $option->getDefaultValue()] = $option;
		}

		//var_dump($optionValues);
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$count_new = 0;
		$count_update = 0;
		$count_error = 0;
		foreach($importValues as $nr => $value) {
			// Farbe noch nicht vorhanden
			if(!array_key_exists($nr, $optionValues)) {
				$sort = (int) $db->query("SELECT max(sort_order) as sort FROM eav_attribute_option WHERE attribute_id = {$attributeId};")->fetchObject()->sort + 1;
				try {
					// Option anlegen
					$sql = "INSERT INTO eav_attribute_option (option_id, attribute_id, sort_order)  VALUES (null, {$attributeId}, {$sort});\n";
					$db->query($sql);
					$insertId = $db->lastInsertId();

					// Values setzen
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 0, '{$nr}');");
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 1, '{$value}');");

					$this->log("Inserted color option {$nr} -> {$value}");
					$count_new++;
				} catch(Exception $e) {
					$this->log("Failed to insert color option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			// Farbe updaten
			elseif($optionValues[$nr]->getStoreValue() != $value) {
				try {
					$option = $optionValues[$nr];
					$db->query("UPDATE eav_attribute_option_value SET value = '{$value}' WHERE store_id = 1 AND option_id = {$option->getId()} LIMIT 1;");
					$this->log("Updated color option {$nr}");
					$count_update++;
				} catch(Exception $e) {
					$this->log("Failed to update color option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			unset($optionValues[$nr]);
		}

		$this->log("Added {$count_new} new colors, updated {$count_update} colors, {$count_error} errors, " . count($optionValues) . " old colors left");
	}

	public function colors2() {
		$csv = $this->readCsv('export_farbstaffel.txt');
		$sql = '';
		$i = 100;
		$sort = 1;
		foreach($csv as $c) {
			$sql .= "INSERT INTO eav_attribute_option       (option_id,	attribute_id,	sort_order)  VALUES ({$i}, 127, {$sort});\n";
			$sql .= "INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$i}, 0, '{$c->_Nr}');\n";
			$sql .= "INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$i}, 1, '{$c->Bezeichnung}|{$c->RGB}|{$c->RGB}');\n\n";
			$i++;
			$sort++;
		}

		return $sql;
	}

	public function sizes() {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');

		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_groessenstaffel.txt', array('id', 'nr', 'pos', 'bezeichnung', 'hinweis', 'groesse', 'agroesse', 'extra_groesse'), '|', "'", 1);
		$importValues = array();
		//$pos = 1;

		$sizeType = array(
			'none' => 1,
			'onesize' => 1,
			'xs' => 1,
			's' => 2,
			'm' => 3,
			'l' => 4,
			'xl' => 5,
			'xxl' => 6,
			'xxxl' => 7,
			'3xl' => 8,
		);

		// Alle Größen aus CSV
		foreach($csv as $c) {
			$pos = 1;
			$sizeTxt = trim(strtolower($c->agroesse));

			if(array_key_exists($sizeTxt, $sizeType)) {
				$pos = $sizeType[$sizeTxt];
			} elseif($sizeTxt != '') {
				$pos = (int) substr(str_replace(array('/', ' ', 'mm', 'ml', ','), array('', '', '', '', ''), $sizeTxt), 0, 4);
			} else {
				$pos = 1;
			}

			$importValues[(string) $c->agroesse] = array('pos' => $pos, 'value' => $c->agroesse);
		}

		$attributeId = $this->getAttributeId('elk_size');
		// Alle vorhandenen Größen
		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attributeId)
			->setStoreFilter(1)
      ->load();

		$optionValues = array();

		// Nach Duplikaten suchen
		foreach($optionCollection as $option) {
			if(isset($optionValues[(string) $option->getDefaultValue()])) {
				$oldOption = $optionValues[(string) $option->getDefaultValue()];
				$this->log("Remove duplicate size value {$option->getDefaultValue()}");
			}

			$optionValues[(string) $option->getDefaultValue()] = $option;
		}

		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$count_new = 0;
		$count_update = 0;
		$count_error = 0;

		foreach($importValues as $nr => $size) {
			// Größe noch nicht vorhanden
			if(!array_key_exists($nr, $optionValues)) {
				try {
					// Option anlegen
					$sql = "INSERT INTO eav_attribute_option (option_id, attribute_id, sort_order)  VALUES (null, {$attributeId}, {$size['pos']});\n";
					$db->query($sql);
					$insertId = $db->lastInsertId();

					// Values setzen
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 0, '{$nr}');");
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 1, '{$size['value']}');");

					$this->log("Inserted size option {$nr} -> {$size['value']}");
					$count_new++;
				} catch(Exception $e) {
					$this->log("Failed to insert size option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			// Größe updaten, Label passt nicht, order Pos
			elseif($optionValues[$nr]->getStoreValue() != $size['value'] OR $optionValues[$nr]->getSortOrder() != $size['pos']) {
				$option = $optionValues[$nr];
				try {
					$db->query("UPDATE eav_attribute_option_value SET value = '{$size['value']}' WHERE store_id = 1 AND option_id = {$option->getId()} LIMIT 1;");
					$db->query("UPDATE eav_attribute_option SET sort_order = '{$size['pos']}' WHERE option_id = {$option->getId()} LIMIT 1;");
					$this->log("Updated size option {$nr}");
					$count_update++;
				} catch(Exception $e) {
					$this->log("Failed to update size option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			unset($optionValues[$nr]);
		}

		foreach($optionValues as $oldOpt) {
			$this->log("Deleted size option {$oldOpt->getDefaultValue()} -> {$oldOpt->getStoreValue()}");
			$oldOpt->delete();
		}

		$this->log("Added {$count_new} new sizes, updated {$count_update} sizes, {$count_error} errors");
	}

	public function sizes22() {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');

		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_groessenstaffel.txt', array('id', 'nr', 'pos', 'bezeichnung', 'hinweis', 'groesse', 'agroesse', 'extra_groesse'), '|', "'", 1);
		$importValues = array();

		// Alle Größen aus CSV
		foreach($csv as $c) {
			$importValues[(string) $c->groesse] = array('pos' => $c->pos, 'value' => $c->agroesse);
		}

		$attributeId = $this->getAttributeId('elk_size');
		// Alle vorhandenen Größen
		$optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($attributeId)
			->setStoreFilter(1)
      ->load();

		$optionValues = array();

		// Nach Duplikaten suchen
		foreach($optionCollection as $option) {
			if(isset($optionValues[(string) $option->getDefaultValue()])) {
				$oldOption = $optionValues[(string) $option->getDefaultValue()];
				$this->log("Remove duplicate size value {$option->getDefaultValue()}");
			}

			$optionValues[(string) $option->getDefaultValue()] = $option;
		}

		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$count_new = 0;
		$count_update = 0;
		$count_error = 0;

		foreach($importValues as $nr => $size) {
			// Größe noch nicht vorhanden
			if(!array_key_exists($nr, $optionValues)) {
				try {
					// Option anlegen
					$sql = "INSERT INTO eav_attribute_option (option_id, attribute_id, sort_order)  VALUES (null, {$attributeId}, {$size['pos']});\n";
					$db->query($sql);
					$insertId = $db->lastInsertId();

					// Values setzen
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 0, '{$nr}');");
					$db->query("INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$insertId}, 1, '{$size['value']}');");

					$this->log("Inserted size option {$nr} -> {$size['value']}");
					$count_new++;
				} catch(Exception $e) {
					$this->log("Failed to insert size option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			// Größe updaten, Label passt nicht, order Pos
			elseif($optionValues[$nr]->getStoreValue() != $size['value'] OR $optionValues[$nr]->getSortOrder() != $size['pos']) {
				$option = $optionValues[$nr];
				try {
					$db->query("UPDATE eav_attribute_option_value SET value = '{$size['value']}' WHERE store_id = 1 AND option_id = {$option->getId()} LIMIT 1;");
					$db->query("UPDATE eav_attribute_option SET sort_order = '{$size['pos']}' WHERE option_id = {$option->getId()} LIMIT 1;");
					$this->log("Updated size option {$nr}");
					$count_update++;
				} catch(Exception $e) {
					$this->log("Failed to update size option {$nr}", 'error');
					$this->log($e, 'error');
					$count_error++;
				}
			}
			unset($optionValues[$nr]);
		}

		$this->log("Added {$count_new} new sizes, updated {$count_update} sizes, {$count_error} errors, " . count($optionValues) . " old sizes left");
	}

	public function sizes2() {
		$csv = $this->readCsv('Export_groessenstaffel.txt');
		$sql = '';
		$i = 1000;
		$sort = 1;
		foreach($csv as $c) {
			$sql .= "INSERT INTO eav_attribute_option       (option_id,	attribute_id,	sort_order)  VALUES ({$i}, 128, {$c->pos});\n";
			$sql .= "INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$i}, 0, '{$c->groesse}');\n";
			$sql .= "INSERT INTO eav_attribute_option_value (value_id, option_id, store_id, value) VALUES (null, {$i}, 1, '{$c->agroesse}');\n\n";
			$i++;
			$sort++;
		}

		return $sql;
	}

	public function pictures() {

	}

	public function imgsizes() {
		return array(
			'listing',
			'produkt',
			'thumb',
			'warenkorb',
			'zoom',
		);
	}

	public function imgPath() {
		return realpath(DOCROOT . '../imagepool');
	}

	public function picturecheck() {
		$imageSizes = $this->imgsizes();

		$products = $this->loadProducts();

		$productList = array();

		foreach($products as $p) {
			foreach($this->getColorsFromProduct($p) as $cId => $c) {
				foreach($imageSizes as $size) {
					$productList[$p->sku][$c][$size] = 0;
				}
			}
		}


		$images = array();
		$error = array();
    $imgPath = $this->imgPath();

		// Bilder einlesene
		foreach(glob($imgPath . '/src/*.jpg') as $image) {
			$base = basename($image);

			// Bilder idientifizieren
			if(preg_match('/^(\d+?)_(\d+?)_([\w\.]+?)_(\d+?)_web_(.+)\.(jpg|png)$/i', $base, $m)) {

				$image = array();
				list($all, $image['sku'], $image['color'], $image['name'], $image['num'], $image['size'], $image['ext']) = $m;

				//$images[$image['sku']][$image['color']][$image['size']][$image['num']] = (object) $image;
				$images[$base] = (object) $image;
			// Wenn im falschen Format filder aussortieren
			} else {
				$error['wrong_format'][] = $base;
			}
		}

		foreach($images as $base => $image) {
			// Prüfen ob Produkt vorhanden
			if(!isset($productList[$image->sku])) {
				$error['no_product'][$base] = $image;
				unset($images[$base]);
				continue;
			}

			// Prüfen ob Option vorhanden
			if(!isset($productList[$image->sku][$image->color])) {
				$error['no_option'][$base] = $image;
				unset($images[$base]);
				continue;
			}

			$product = $products[$image->sku];

			// Prüfen ob Name richtig
			$name = $this->slug($product->name);

			if($image->name != $name) {
				$error['wrong_name'][$base] = $image;
				unset($images[$base]);
				continue;
			}

			// Prüfen ob größe richtig
			if(!in_array($image->size, $imageSizes)) {
				$error['wrong_size'][$base] = $image;
				unset($images[$base]);
				continue;
			}

			$productList[$image->sku][$image->color][$image->size]++;

		}

		return array(
			'errors' => $error,
			'images_ok' => $images,
			'products' => $productList,
		);
	}

	public function slug($text) {
		return preg_replace( array("/[^A-Za-z0-9\s\s+\.]/", "[ +]"), array("", '_'), str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), mb_strtolower($text, 'UTF-8')));
	}

	public  function getColorsFromProduct($p) {
 		$elk_colors = $this->getOptions('elk_color');
		$colors = array();
  	foreach($p->options as $option) {
			$colors[$option->elkline_color] = $elk_colors[$option->elkline_color];
		}

		return $colors;
	}

	public function images() {
		$check = $this->picturecheck();
		$products = $this->loadProducts();

		$imgPath = $this->imgPath();
		$imgPool = $this->imgPath() . '/src/';
		$targetPath = $imgPath . '/imgimport_' . time() . '/';
		mkdir($targetPath);

		$out_path = $targetPath . 'ok/';
		mkdir($out_path);
		foreach($check['images_ok'] as $base => $image) {
			copy($imgPool . $base, $out_path . $base);
			echo "Image {$base} OK" . PHP_EOL;
		}

		$wrong_name_path = $targetPath . 'wrong_name/';
		mkdir($wrong_name_path);

		foreach($check['errors']['wrong_name'] as $base => $image) {
			if(isset($products[$image->sku]) AND $product = $products[$image->sku]) {
 				$new = "{$image->sku}_{$image->color}_" . $this->slug($product->name) . "_{$image->num}_web_{$image->size}.jpg";
				copy($imgPool . $base, $out_path . $new);
				echo "Image {$base} rename to {$new}" . PHP_EOL;
			} else {
				copy($imgPool . $base, $wrongNamePath . $base);
				echo "!Image {$base} wrong name" . PHP_EOL;
			}
		}

		$wrong_format_path = $targetPath . 'wrong_format/';
		mkdir($wrong_format_path);

		foreach($check['errors']['wrong_format'] as $image) {
			copy($imgPool . $image, $wrong_format_path . $image);
			echo "!Image {$image} wrong name" . PHP_EOL;
		}

		foreach(array('no_product', 'no_option', 'wrong_size') as $action) {
			$akt_path = $targetPath . $action . '/';
			mkdir($akt_path);
      $action_text = str_replace('_' , ' ', $action);

			foreach($check['errors'][$action] as $base => $image) {
				copy($imgPool . $base, $akt_path . $base);
				echo "!Image {$base} {$action_text}" . PHP_EOL;
			}
		}

	}

	public $ftp_host = 'erp.elkline.net';
	public $ftp_user = 'bschultz';
	public $ftp_pass = '*********';
	public $ftp_path = 'webshop_elkline';
	public $ftp_export_files = array(
		'export/Export_artikel.txt',
		'export/export_farbstaffel.txt',
		'export/Export_groessenstaffel.txt',
		'export/Export_kunde.txt',
		'export/Export_anschriften.txt',
		'export_lager/Export_lager.txt',
		'export_lager/Export_lager_197.txt',
		'export/export_preisliste.txt',
	);

	public function updateImportData() {
		$conn_id = ftp_connect($this->ftp_host);

		$login_result = ftp_login($conn_id, $this->ftp_user, $this->ftp_pass);

		if ((!$conn_id) || (!$login_result)) {
			$this->log("Connection to {$this->ftp_host} with user {$this->ftp_user} failed [updateImportData]", 'error');
			exit;
		}

		$path = time() . '/';

		mkdir($this->dir . $path);

		foreach($this->ftp_export_files as $file) {
			$remote_file = $this->ftp_path . '/' . $file;
			$local_file = $path . basename($file);
			if(!ftp_get($conn_id, $this->dir . $local_file, $remote_file, FTP_ASCII)) {
				$this->log("Get {$remote_file} -> {$local_file} Failed", 'warning');
				exit;
			}
		}

		ftp_close($conn_id);
		$this->log("Fetched new data from {$this->ftp_host}");
	}

	public function cleanup() {
		$dirs = $this->listExportDirs();
		// Die letzten 3 Tage behalten
		$time = time() - (60*60*24*20);
		foreach($dirs as $dir) {
			if(filemtime($dir) > $time) {
				$this->log("Keep import files for " . date('d.m.Y H:i:s', (int) basename($dir)));
			} else {
				$this->log("Remove import files for " . date('d.m.Y H:i:s', (int) basename($dir)));
				system("rm -rf " . escapeshellarg($dir));
			}
		}
	}

	public function checkWgrMapping() {
		$collection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToFilter('type_id', 'configurable')
			->addAttributeToSelect('*')
			->load();

		$lines = array(array('sku', 'hwg', 'wg', 'name'));

		foreach($collection as $product) {
			$sku = $product->getSku();
			$hwg = $product->getElkHwgNr();
			$wg = $product->getElkWgNr();

			$ok = true;

			if(substr($wg, 0, 2) != $hwg) {
				$ok = false;
			}

			if(substr($sku, 0, 4) != $wg) {
				$ok = false;
			}

			if(!$ok) {
				$lines[] = array($sku, $hwg, $wg, $product->getName());
			}

		}

		header('Content-type: text/comma-separated-values');
		header('Content-Disposition: attachment; filename="wrong_wgr_products_' . date('Ymd_His') . '.csv"');
		$this->suppressLogOutput = true;
		echo $this->buildCsv($lines);
		exit;

	}

	private function buildCsv(array $data, $seperator = ",", $quote = "\"") {
		$lines = array();
		foreach($data as $line) {
			$lines[] = $quote . implode($quote.$seperator.$quote, $line) . $quote;
		}

		return implode("\n", $lines);
	}

	public function getKundeByNr($customer_id) {
		$kunde = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('customer_id', $customer_id)->getFirstItem();
		return $kunde;
	}

	public function kunden() {
		$kunden = $this->loadKunden();

		foreach($kunden as $k) {
			$new = false;

			if(!$kunde = $this->getKundeByNr($k->nr) OR $kunde->isObjectNew()) {
				$new = true;
				$kunde = Mage::getModel('customer/customer');
				$kunde->setCustomerId($k->nr);
				$kunde->setWebsiteId(1);
				$kunde->setStoreId(0);
			}

			$this->log(($new ? 'Added' : 'Updated') . " Customer {$k->prefix} {$k->firstname} {$k->lastname}");

			$kunde->setEmail($k->email);
			$kunde->setGroupId(4);
			$kunde->setIsActive(1);

			$kunde->setPrefix($k->prefix ? $k->prefix : '');
			$kunde->setFirstname($k->firstname ? $k->firstname : '');
			$kunde->setLastname($k->lastname ? $k->lastname : '');
			$kunde['elk_kreditlimit'] = (int) $k->elk_kreditlimit;

			$adressen = $k->addresses;

			// Bestehende Adressen
			foreach($adressen as $key => $a) {
				foreach($kunde->getAddresses() as $address) {
					if($a->elkline_id == $address->getElklineId()) {
						$address->setFirstname($a->firstname ? $a->firstname : '');
						$address->setLastname($a->lastname ? $a->lastname : '');
						$address->setCity($a->city ? $a->city : '');
						$address->setCountryId($a->country_id ? $a->country_id : '');
						$address->setPostcode($a->postcode ? $a->postcode : '');
						$address->setStreet(array($a->street ? $a->street : ''));
						$address->setTelephone($a->telephone ? $a->telephone : '');
						$address->setIsDefaultBilling($a->billing_address);
						$address->setIsDefaultShipping($a->billing_address);

						unset($adressen[$key]);
						$this->log("- Updated Customer Address {$a->elkline_id}");
					}
				}
			}

			// Neue Adressen
			foreach($adressen as $key => $a) {
				$address = Mage::getModel('customer/address');
				$address->setElklineId($a->elkline_id);
				$address->setFirstname($a->firstname ? $a->firstname : '');
				$address->setLastname($a->lastname ? $a->lastname : '');
				$address->setCity($a->city ? $a->city : '');
				$address->setCountryId($a->country_id ? $a->country_id : '');
				$address->setPostcode($a->postcode ? $a->postcode : '');
				$address->setStreet(array($a->street ? $a->street : ''));
				$address->setTelephone($a->telephone ? $a->telephone : '');
				$address->setIsDefaultBilling($a->billing_address);
				$address->setIsDefaultShipping($a->billing_address);

				$kunde->addAddress($address);
				$this->log("- Added Customer Address {$a->elkline_id}");
			}


			try {
				$kunde->save();
			}
			catch (Exception $e) {
				$type = ($e->getMessage() == 'This customer email already exists') ? 'warning' : 'error';
				$this->log("{$k->elkline_id}:{$k->nr} {$e->getMessage()}", 'warning');
			}
		}
	}

	public function loadKunden() {
  	$kunden_cols = array(
			'kunden_id',
      'kunden_nr',
			'firmenname',
			'name',
			'strasse',
			'plz',
			'ort',
			'land',
			'username',
			'passwort',
			'telefon',
			'email',
			'mwst',
			'ansprechpartner',
			'ansprechpartner_geschlecht',
			'ansprechpartner_telefon',
			'ansprechpartner_email',
			'liefer_name1',
			'liefer_name2',
			'liefer_name3',
			'liefer_strasse',
			'liefer_plz',
			'liefer_ort',
			'liefer_land',
			'liefer_telefon',
			'liefer_email',
			null,
			null,
			'intrastat_laender_nr',
			'online_anzeigen',
			'family',
			'kids',
			'adults',
			'accessoires',
			'klein_aber_fein',
			'ausgewählte_artikel',
			'breite_auswahl',
			'add1',
			'add2',
			'url',
			'kundenlimit',
			'b2c',
		);

		$csv = $this->readCsv($this->getCurrentExportDir() . 'Export_kunde.txt', $kunden_cols);


		// Additional Addresses

		$anschriftCsv = $this->readCsv($this->getCurrentExportDir() . 'Export_anschriften.txt');
		$anschriften = array();
		foreach($anschriftCsv as $a) {
			if(!$a->Land || !$a->elkline_id || !$a->Vorname || !$a->ort || !$a->PLZ || !$a->Strasse) continue;
			$anschriften[$a->_Kunden_Nr][] = $a;
		}

		$nameSplit = array(
			'von',
			'zu',
		);

		$kunden = array();

		foreach($csv as $c) {
			// Name
			$vorname = null;
			$nachname = null;
			$prefix = null;

			if($name = $c->name) {
				$name = trim($name);

				$prefix = substr(strtolower($name), 0, 4);

				if($prefix == 'herr' || $prefix == 'frau') {
					$prefix = ucfirst($prefix);
					$name = trim(substr($name, 4));
				} else {
					$prefix = null;
				}



				foreach($nameSplit as $split) {
					if(($pos = strpos(strtolower($name), $split . ' ')) !== false) {
						$vorname = trim(substr($name, 0, $pos));
						$nachname = trim(substr($name, $pos));
						break;
					}
				}

				if(!$vorname AND !$nachname) {
					if(($pos = strrpos($name, ' ')) !== false) {
						$vorname = trim(substr($name, 0, $pos));
						$nachname = trim(substr($name, $pos));
					}
				}

				if(!$vorname AND !$nachname) {
					$nachname = $name;
				}
			}

			$kunde = new Kunde(array(
				'elkline_id' => $c->kunden_id,
				'nr' => $c->kunden_nr,
				'company' => $c->firmenname,
				'firstname' => $vorname,
				'lastname' => $nachname,
				'prefix' => $prefix,
				'email' => $c->email ? $c->email : "{$c->kunden_nr}_{$c->firmenname}@elkline.de",
				'elk_kreditlimit' => $c->kundenlimit ? $c->kundenlimit : null,
			));

			// Adressen

			$adressen = array(
				'base' => new Adresse(array(
					'elkline_id' => Import::BASE_ADDRESS_ID,
					'prefix' => $kunde->prefix,
					'firstname' => $kunde->firstname,
					'lastname' => $kunde->lastname,
					'company' => $kunde->company,
					'city' => $c->ort,
					'country_id' => $this->getCountryCode($c->land),
					'postcode' => $c->plz,
					'telephone' => $c->telefon,
					'fax' => null,
					'street' => $c->strasse,
					'shipping_address' => true,
					'billing_address' => true,
				))
			);

			if(isset($anschriften[$kunde->nr])) {
				foreach($anschriften[$kunde->nr] as $a) {
					$adresse = new Adresse(array(
						'elkline_id' => $a->Anschriften_ID,
						'prefix' => '',
						'firstname' => $a->Anrede,
						'lastname' => $a->Vorname,
						'company' => $a->Name,
						'city' => $a->Ort,
						'country_id' => $this->getCountryCode($a->Land),
						'postcode' => $a->PLZ,
						'telephone' => $a->Telefon,
						'fax' =>  $a->Telefax,
						'street' => $a->Strasse,
						'shipping_address' => ($a->Art == 'L' || $a->standard_anschrift == 'J') ? true : false,
						'billing_address' => ($a->Art == 'R' || $a->standard_anschrift == 'J') ? true : false,
					));

					$adressen[] = $adresse;
					if($adresse->shipping_address) $adressen['base']->shipping_address = false;
					if($adresse->billing_address) $adressen['base']->billing_address = false;
				}

			}

			$kunde->addresses = $adressen;
			$kunden[$kunde->nr] = $kunde;
		}

		/*
		$emails = array();

		foreach($kunden as $k) {
			if($k->email) {
				$email = strtolower($k->email);
				if(isset($emails[$email])) {
					$emails[$email]['count']++;
					$emails[$email]['kd'] .= ', '. $k->nr;
				} else {
					$emails[$email]['count'] = 1;
					$emails[$email]['kd'] = $k->nr;
				}
			}
		}

		foreach($emails as $e => $c) {
			if($c['count'] > 1) {
				echo $e . " {$c['kd']} ({$c['count']})<br>";
			}
		}

		exit();
		*/
		return $kunden;
	}

	public $ftp_path_DES = 'export/DESADV';
	public $local_DES_path = 'DESADV';

	public function desadv() {

		$conn_id = ftp_connect($this->ftp_host);

		$login_result = ftp_login($conn_id, $this->ftp_user, $this->ftp_pass);

		if ((!$conn_id) || (!$login_result)) {
			$this->log("Connection to {$this->ftp_host} with user {$this->ftp_user} failed [desadv]", 'error');
			exit;
		}

		$files = ftp_nlist($conn_id, $this->ftp_path . '/' . $this->ftp_path_DES);

		if (!is_dir($this->local_DES_path)) {
			mkdir ($this->local_DES_path);
		}

		foreach ($files as $file) {
			$successfullyUpdated = true;
			$basename = basename($file);
			if (substr($basename,0,4) == 'DES-') {
				$local_file = $this->local_DES_path . '/' . $basename;
				if(!ftp_get($conn_id, $local_file, $file, FTP_ASCII)) {
					$this->log("Get {$file} -> {$local_file} Failed", 'warning');
					exit;
				}

				$csvLines = $this->readCsv($local_file, array(), '^');

				$this->log("Read the CSV File: " . $local_file . " | " . count($csvLines) . " lines found!");

				if (count($csvLines) > 0) {
					$orders = array();

					foreach ($csvLines as $line) {
						if ($line->Pos == 'H') {
							if (isset($order) && is_object($order)) $orders[] = $order;
							$order = $line;
							$order->Position = array();
						} else {
							if (!isset($order->Position[$line->Pos]) || !is_array($order->Position[$line->Pos])) {
								$order->Position[$line->Pos] = array();
							}
							$order->Position[$line->Pos][] = $line;
						}
					}

					if (is_object($order)) $orders[] = $order;

					unset($line, $csvLines, $order);

					$this->log(count($orders) . " Bestellungen gefunden!");


					foreach ($orders as $order) {
						if($sales = Mage::getModel('sales/order')->loadByIncrementId($order->{'Ihre Bestellnr'})) {
							$payment = $sales->getPayment();

							// Prüft ob die Zahlart gefunden wurde.
							// Der hat bei mir Lokal eine nicht vorhandene Bestellnr angeblich gefunden aber dazu keine Zahlart und das gab einen Fehler
							if ($payment) {
								$this->log("Bestellnr " . $order->{'Ihre Bestellnr'} . " wurde gefunden!");

								//$sales->setStatus(substr(strtolower($payment->getMethod()),0,7) == 'payone_' ? 'closed' : 'pending_payment');
								$sales->setStatus('ready_for_pickup');

								$shipments = $sales->getShipmentsCollection();

								$hasShipment = false;
								foreach($shipments as $shipment) {
									if ($shipment->getIncrementId() == $order->{'Lieferschein Lieferschein Nr'}) {
										$hasShipment = true;
										break;
									}
								}

								if (!$hasShipment) {
									$shipment = $sales->prepareShipment();
									$shipment->setIncrementId($order->{'Lieferschein Lieferschein Nr'});

									try {
										$shipment->save();
										$this->log('Lieferschein wurde angelegt: ' . $order->{'Ihre Bestellnr'}  . ' | Lieferscheinnr: ' . $shipment->getIncrementId());
									} catch(Exception $e) {
										$this->log('Lieferschein konnte nicht angelegt werden: ' . $order->{'Ihre Bestellnr'} . ' | Lieferscheinnr: ' . $shipment->getIncrementId(),'error');
										$this->log($e->getMessage(), 'error');
									}
								}

								// Wenn eine Artikelposition vorhandenist, setze die Rechnngsnummer auf die von der ersten Artikelposition
								if (isset($order->Position['P']) && count($order->Position['P'])) {
									$invoices = $sales->getInvoiceCollection();

									$hasInvoice = false;
									foreach($invoices as $invoice) {
										if ($invoice->getIncrementId() == $order->Position['P'][0]->{'Lieferschein Rechnungs Nr'}) {
											$hasInvoice = true;
											break;
										}
									}

									if (!$hasInvoice && is_object($invoice)) {
										try {
											$invoice->setIncrementId($order->Position['P'][0]->{'Lieferschein Rechnungs Nr'});

											try {
												$invoice->save();
												$this->log('Rechnungsnummer wurde aktualisiert: ' . $order->{'Ihre Bestellnr'}  . ' | Rechnungsnr: ' . $invoice->getIncrementId());
											} catch(Exception $e) {
												$this->log('Rechnung konnte nicht aktualisiert werden: ' . $order->{'Ihre Bestellnr'} . ' | Rechnungsnr: ' . $invoice->getIncrementId(),'error');
												$this->log($e->getMessage(), 'error');
											}
										} catch (Exception $e) {
											$this->log('Rechnung existiert nicht, weiter machen!');
										}
									}
								}


								/*if (substr(strtolower($payment->getMethod()),0,7) != 'payone_') {
									if (isset($order->Position['P']) && count($order->Position['P'])) {
										$invoices = $sales->getInvoiceCollection();

										$hasInvoice = false;
										foreach($invoices as $invoice) {
											if ($invoice->getIncrementId() == $order->Position['P'][0]->{'Lieferschein Rechnungs Nr'}) {
												//$invoice->setState($invoice::STATE_OPEN);
												//$invoice->save();

												$hasInvoice = true;
												break;
											}
										}
										unset($invoices, $invoice);

										if (!$hasInvoice) {
											$invoice = $sales->prepareInvoice();
											$invoice->setState($invoice::STATE_OPEN);
											//$invoice->register()->pay();

											$invoice->setIncrementId($order->Position['P'][0]->{'Lieferschein Rechnungs Nr'});

											try {
												$invoice->save();
												$this->log('Rechnung wurde erstellt: ' . $order->{'Ihre Bestellnr'}  . ' | Rechnungsnr: ' . $invoice->getIncrementId());
											} catch(Exception $e) {
												$this->log('Rechnung konnte nicht erstellt werden: ' . $order->{'Ihre Bestellnr'} . ' | Rechnungsnr: ' . $invoice->getIncrementId(),'error');
												$this->log($e->getMessage(), 'error');
											}
										}
									}
								}*/

								try {
									$sales->save();
									$this->log('desadv: import ' . $order->{'Ihre Bestellnr'} . ' OK');
								} catch (Exception $e) {
									$successfullyUpdated = false;
									$this->log('desadv: import ' . $order->{'Ihre Bestellnr'} . ' failed', 'error');
									$this->log($e->getMessage(), 'error');
								}
							} else {
								$this->log("Zahlart zu Bestellnr " . $order->{'Ihre Bestellnr'} . " wurde nicht gefunden!");
							}
						} else {
							$this->log("Bestellnr " . $order->{'Ihre Bestellnr'} . " wurde nicht gefunden!");
						}
					}
				}

				rename($local_file, $this->local_DES_path . '/' . ($successfullyUpdated ? 'archiv' : 'error') . '/' . $basename);
				ftp_rename($conn_id, $file, $this->ftp_path . '/' . $this->ftp_path_DES . '/' . ($successfullyUpdated ? 'archiv' : 'error') .'/' . $basename);
			}
		}

		ftp_close($conn_id);
		$this->log("DES Files imported and Orders updated from {$this->ftp_host}");
	}

	public function createBills() {
		$orders = Mage::getModel('sales/order')->getCollection();

		foreach ($orders as $order) {
			$payment = $order->getPayment();

			if ($payment) {
				$paymentMethod = substr(strtolower($payment->getMethod()),0,7);

				if ($paymentMethod != 'payone_') {
					$invoices = $order->getInvoiceCollection();

					if (!count($invoices)) {
						$this->log("Bestellnr " . $order->getIncrementId() . " hat keine Rechnungen!");

						$invoice = $order->prepareInvoice();
						$invoice->setState($invoice::STATE_OPEN);



						try {
							$invoice->save();
							$this->log('Rechnung wurde erstellt zu Bestellnr ' . $order->getIncrementId()  . ' | Rechnungsnr: ' . $invoice->getIncrementId());
						} catch(Exception $e) {
							$this->log('Rechnung konnte nicht erstellt werden zu Bestellnr ' . $order->getIncrementId() . ' | Rechnungsnr: ' . $invoice->getIncrementId(),'error');
							$this->log($e->getMessage(), 'error');
						}
					}
				}
			}
		}
	}

	public function getWgInfo($wgn, $hwgn = false) {
		$csv = $this->readCsv($this->dir . 'wgr_mapping.csv');

		$wg = array();

		foreach($csv as $r) {
			if ($hwgn) {
				if (substr($r->Nr,0,2) == $wgn) {
					if ($r->Gruppe != '') {
						return $r;
					}
				}
			} else {
				if ($r->Nr == $wgn) {
					if ($r->Kategorie != '') {
						return $r;
					}
				}
			}
		}
		return false;
	}

	public function createShoppingXML() {

		Mage::app()->setCurrentStore(1);

		$products = Mage::getModel('catalog/product')->getCollection()
			->addFieldToFilter('type_id', array('eq' => 'simple'))
			->addFieldToFilter('status', array('eq' =>'1'));

		$namespace = 'http://base.google.com/ns/1.0';

		$xmlstr = '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>';

		$xml = new SimpleXMLExtended($xmlstr);

		$channel = $xml->addChild('channel');


		$channel->addChild('title')->addCData('elkline');
		$channel->addChild('link')->addCData(rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/'));
		$channel->addChild('description')->addCData('So wie der Elch am liebsten draußen ist, produziert elkline für die ganze Familie urbane Outdoor-Fashion, damit auch ihr bei Wind und Wetter für den Einsatz im Freien bestens gerüstet seid. Daneben sorgt der sympathisch lächelnde Elch und unsere ausgefallenen Namenskreationen für ein Schmunzeln an jedem Tag.');


		$helper = Mage::helper('pimcore/pimcore');
		$_helper = Mage::helper('catalog/output');

		$colors = $helper->getAllColors();
		$sizes = $this->getOptions('elk_size');

		$genderArray = array(
			10 => "Herren",
			20 => "Damen",
			30 => "Unisex",
			31 => "Herren",
			32 => "Damen"
		);

		$ageArray = array(
			10 => "Erwachsene",
			20 => "Erwachsene",
			30 => "Kinder",
			31 => "Kinder",
			32 => "Kinder"
		);

		$count = count($products);
		$i = 1;

		foreach ($products as $p) {
			$p = Mage::getModel('catalog/product')->load($p->getId());
			$parentIdArray = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($p->getId());
			$parentId = reset($parentIdArray);
			$parent = Mage::getModel('catalog/product')->load($parentId);

			if ($parent->getStatus() != 1) {
				$this->log("XML-Export {$i}/{$count} (Skip, Parent is inactive)");
				$i++;
				continue;
			}

			$item = $channel->addChild('item');

			$title = $p->getName() . ' | elkline';
			if (trim(strip_tags($parent->getElkBezeichnung())) != '') {
				$title .= ' - ' . $parent->getElkBezeichnung();
			}

			$item->addChild('title')->addCData($title);

			$specialOffer = false;
			if ($parent->getElkSonderaktion() == '1') {
				$fromDate = strtotime($parent->getSpecialFromDate());
				$toDate = strtotime(str_replace("00:00:00","23:59:59",$parent->getSpecialToDate()));

				if (trim($fromDate) != '' && trim($toDate) != '') {
					if (time() > $fromDate && time() < $toDate) {
						$specialOffer = true;
					}
				}
			}

			$tracking = '?utm_source=merchant%2Bcenter&utm_medium=standard%2Bproduct&utm_campaign=Google';
			if ($specialOffer) {
				$tracking = '?utm_source=merchant%2Bcenter&utm_medium=ADM%2Bproducts&utm_campaign=Google';
			}else if ($p->getElkAktionsartikel()) {
				$tracking = '?utm_source=merchant%2Bcenter&utm_medium=sale%2Bproducts&utm_campaign=Google';
			}

			$item->addChild('link')->addCData($helper->getFullProductUrl($parent) . $tracking . '#' . $p->getElkColor());
			$item->addChild('adwords_redirect', '',$namespace)->addCData($helper->getFullProductUrl($parent) . '#' . $p->getElkColor());



			if (trim(strip_tags($parent->getDescription())) != '') {
				$item->addChild('description')->addCData(strip_tags($parent->getDescription()));
			} else {
				$item->addChild('description')->addCData(strip_tags($parent->getElkBezeichnung()));
			}

			$item->addChild('id', '',$namespace)->addCData($p->getId());
			$item->addChild('gtin', '',$namespace)->addCData($p->getSku());

			$item->addChild('mpn', '',$namespace)->addCData($p->getElkArtikelNr() . 'ELK' . $p->getId());

			$price = number_format($p->getPrice(),2,".","");
			$specialPrice = number_format($p->getFinalPrice(),2,".","");

			$item->addChild('price', '',$namespace)->addCData($price . ' €');
			if ($price != $specialPrice) {
				$item->addChild('sale_price', '',$namespace)->addCData($specialPrice . ' €');
			}

			$item->addChild('online_only', '',$namespace)->addCData('y');
			$item->addChild('condition', '',$namespace)->addCData('new');

			$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($p->getId());
			$item->addChild('availability', '',$namespace)->addCData($stockItem->getIsInStock() ? 'in stock' : 'out of stock');
			$item->addChild('quantity', '',$namespace)->addCData(ceil($stockItem->getQty()));
			$item->addChild('color', '',$namespace)->addCData($colors[$p->getElkColor()]->name);
			$item->addChild('size', '',$namespace)->addCData($sizes[$p->getElkSize()]);

			$imageLink = '';
			if (isset($colors[$p->getElkColor()]) && is_object($colors[$p->getElkColor()])) {
				$parentImage = $parent->getProductImage('produkt', $colors[$p->getElkColor()]->code);
				if (is_object($parentImage)) {
					$imageLink = @$parent->getProductImage('produkt', $colors[$p->getElkColor()]->code)->path;
				}
			}
			$item->addChild('image_link', '',$namespace)->addCData(rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/') . $imageLink);

			$additionalImages = $parent->getProductImages('produkt',$colors[$p->getElkColor()]->code);

			foreach ($additionalImages as $image) {
				if ($image->path == $imageLink) continue;
				$item->addChild('additional_image_link', '',$namespace)->addCData(rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/') . $image->path);
			}

			$shipping = $item->addChild('g:shipping','',$namespace);
			$shipping->addChild('country', '',$namespace)->addCData('DE');
			$shipping->addChild('service', '',$namespace)->addCData('UPS');
			$shipping->addChild('price', '',$namespace)->addCData('5.90 €');

			if($specialOffer) {
				$specialPriceDate = date("Y-m-d\TH:iO",$fromDate);
				$specialPriceDate .= '/' . date("Y-m-d\TH:iO",$toDate);
				$item->addChild('sale_price_effective_date', '',$namespace)->addCData($specialPriceDate);
			}

			$kategorie = $this->getWgInfo($p->getElkWgNr());
			$group = $this->getWgInfo($p->getElkHwgNr(),true);

			$item->addChild('adwords_grouping', '', $namespace)->addCData($kategorie->Kategorie);


			// echo "\r\n##################\r\n";
			// echo "GTIN: " . $p->getSku() . "\r\n";
			// echo "HWG: " . $p->getElkHwgNr() . " - " . $group->Gruppe . "\r\n";
			// echo "WG: " . $p->getElkWgNr() . " - " . $kategorie->Kategorie . "\r\n";
			// var_dump($group);

			$item->addChild('adwords_labels','',$namespace)->addCData($group->Gruppe);
			$item->addChild('adwords_labels','',$namespace)->addCData($kategorie->Kategorie);

			if ($p->getElkAktionsartikel()) {
				$item->addChild('adwords_labels','',$namespace)->addCData('Sale');
			}
			if ($specialOffer) {
				$item->addChild('adwords_labels','',$namespace)->addCData('Angebot des Monats');
			}

			$item->addChild('item_group_id','',$namespace)->addCData($p->getElkArtikelNr());


			$categoryIds = $parent->getCategoryIds();

			if (count($categoryIds)) {
				foreach ($categoryIds as $categoryId) {
					$category = Mage::getModel('catalog/category')->load($categoryId);

					if (trim($category->getGoogleCategory()) != '') break;
				}

				$produkttyp = $category->getName();

				if ($category->getParentId() != '2') {
					if ($parentCategory = Mage::getModel('catalog/category')->load($category->getParentId())) {
						$produkttyp = $parentCategory->getName() . " > " . $produkttyp;
						unset($parentCategory);
					}
				}

				$item->addChild('product_type', '',$namespace)->addCData($produkttyp);

				// if ($specialOffer) {
				// 	$item->addChild('product_type', '',$namespace)->addCData("Sale > Angebot des Monats");
				// }

				$item->addChild('google_product_category', '',$namespace)->addCData($category->getGoogleCategory());
				unset($category);
			} else {
				$this->log("Product {$parent->getId()} ist keiner Kategorie zugewiesen");
			}

			$item->addChild('brand', '', $namespace)->addCData('elkline');

			if (isset($genderArray[$p->getElkHwgNr()])) {
				$item->addChild('gender', '', $namespace)->addCData($genderArray[$p->getElkHwgNr()]);
			}
			if (isset($ageArray[$p->getElkHwgNr()])) {
				$item->addChild('age_group', '', $namespace)->addCData($ageArray[$p->getElkHwgNr()]);
			}

			$this->log("XML-Export {$i}/{$count}");
			$i++;

			// break;
		}

		file_put_contents(DOCROOT . '../google/googleshopping.xml', $xml->saveXML());
		unset($xml);
	}

	public $ftp_path_DHL = 'export/DHL';
	public $local_DHL_path = 'dhl';

	public function importDHL() {
		$conn_id = ftp_connect($this->ftp_host);

		$login_result = ftp_login($conn_id, $this->ftp_user, $this->ftp_pass);

		if ((!$conn_id) || (!$login_result)) {
			$this->log("Connection to {$this->ftp_host} with user {$this->ftp_user} failed [importDHL]", 'error');
			exit;
		}

		$files = ftp_nlist($conn_id, $this->ftp_path . '/' . $this->ftp_path_DHL);

		if (!is_dir($this->local_DHL_path)) {
			mkdir ($this->local_DHL_path);
		}

		foreach ($files as $file) {
			$successfullyUpdated = true;
			$basename = basename($file);

			if (substr($basename,0,8) == 'ELSendEx') {
				$local_file = $this->local_DHL_path . '/' . $basename;

				if(!ftp_get($conn_id, $local_file, $file, FTP_ASCII)) {
					$this->log("Get {$file} -> {$local_file} Failed", 'warning');
					exit;
				}

				$this->log("Read the CSV File: " . $local_file);
				$csvLines = $this->readCsv($local_file, array('id','Tracking ID','Storniert','4','5','6','7','8','9','10','11','12','ABS_ID','14','15','16','17','18','19','20','21','22','Lieferscheinnummer'), ';','"');

				if (count($csvLines) > 0) {
					foreach ($csvLines as $line) {

						if ($line->{'Storniert'} == '0' && $line->{'ABS_ID'} == '1') { // AbsenderID 1 ist elkline
							if($shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($line->{'Lieferscheinnummer'})) {
								if (!is_null($shipment->getOrderId())) {

									$trackings = $shipment->getTracksCollection();

									$hasTracking = false;
									if (count($trackings)>0) $hasTracking = true;

									if (!$hasTracking) {

										$track = Mage::getModel('sales/order_shipment_track')
											->setNumber($line->{'Tracking ID'})
											->setCarrierCode('dhl')
											->setTitle('DHL');

										$shipment->addTrack($track);

										try {
											$shipment->save();

											$shipment->sendEmail();

											$order = $shipment->getOrder();
											$payment = $order->getPayment();
											$order->setStatus(substr(strtolower($payment->getMethod()),0,7) == 'payone_' ? 'closed' : 'pending_payment');

											try {
												$order->save();
												$this->log('Der Status der Bestellung: ' . $order->getIncrementId() . ' wurde erfolgreich geändert!');
											} catch (Exception $e) {
												$successfullyUpdated = false;
												$this->log('Der Status der Bestellung: ' . $order->getIncrementId() . ' konnte nicht geändert werden!', 'error');
												$this->log($e->getMessage(), 'error');
											}

											$this->log('DHL Import: Lieferschein: ' . $line->{'Lieferscheinnummer'} . ' | Trackingnummer: ' . $line->{'Tracking ID'} . ' | Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' OK');
										} catch (Exception $e) {
											$successfullyUpdated = false;
											$this->log('DHL Import: Lieferschein: ' . $line->{'Lieferscheinnummer'} . ' | Trackingnummer: ' . $line->{'Tracking ID'} . ' | Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' failed','error');
											$this->log($e->getMessage(), 'error');
										}
									} else {
										$this->log('Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' hat bereits einen Lieferschein (' . $line->{'Lieferscheinnummer'} . ') mit der Trackingnummer: ' . $line->{'Tracking ID'} . '');
									}
								} else {
									$this->log('Es wurde keine Bestellnummer mit der Lieferscheinnummer ' . $line->{'Lieferscheinnummer'} . ' gefunden!');
								}
							}
						} else {
							// if ($line->{'Storniert'} == '1') {
							// 	$this->log('Die Bestellnummer mit der Lieferscheinnummer ' . $line->{'Lieferscheinnummer'} . ' wurde Storniert!');
							// } else {
							// 	$this->log('Die Bestellnummer mit der Lieferscheinnummer ' . $line->{'Lieferscheinnummer'} . ' ist nicht für elkline (AbsenderID: ' . $line->{'ABS_ID'} . ')!');
							// }
						}
					}
				}

				$basenameParts = explode(".",$basename);
				$basenameEnd = end($basenameParts);
				unset($basenameParts[count($basenameParts) - 1]);
				$basename = implode(".",$basenameParts);
				$newBasename = $basename . "." . date("Y-m-d_H-i-s") . "." . $basenameEnd;
				$basename = $newBasename;

				rename($local_file, $this->local_DHL_path . '/' . ($successfullyUpdated ? 'archiv' : 'error') . '/' . $basename);
				ftp_rename($conn_id, $file, $this->ftp_path . '/' . $this->ftp_path_DHL . '/' . ($successfullyUpdated ? 'archiv' : 'error') .'/' . $basename);
			}
		}
	}

	public $ftp_path_UPS = 'export/UPS';
	public $local_UPS_path = 'ups';

	public function importUPS() {

		$conn_id = ftp_connect($this->ftp_host);

		$login_result = ftp_login($conn_id, $this->ftp_user, $this->ftp_pass);

		if ((!$conn_id) || (!$login_result)) {
			$this->log("Connection to {$this->ftp_host} with user {$this->ftp_user} failed [importUPS]", 'error');
			exit;
		}

		$files = ftp_nlist($conn_id, $this->ftp_path . '/' . $this->ftp_path_UPS);

		if (!is_dir($this->local_UPS_path)) {
			mkdir ($this->local_UPS_path);
		}

		foreach ($files as $file) {
			$successfullyUpdated = true;
			$basename = basename($file);
			if (substr($basename,0,14) == 'UPS_CSV_EXPORT') {
				$local_file = $this->local_UPS_path . '/' . $basename;

				if(!ftp_get($conn_id, $local_file, $file, FTP_ASCII)) {
					$this->log("Get {$file} -> {$local_file} Failed", 'warning');
					exit;
				}

				$this->log("Read the CSV File: " . $local_file);
				$csvLines = $this->readCsv($local_file, array('Storniert','Versandart','Tracking ID','Lieferscheinnummer','Auftragsnummer','Bestellnummer'), ';','"');


				if (count($csvLines) > 0) {
					foreach ($csvLines as $line) {

						if ($line->{'Storniert'} == 'N') {
							if($shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($line->{'Lieferscheinnummer'})) {
								if (!is_null($shipment->getOrderId())) {

									$trackings = $shipment->getTracksCollection();

									$hasTracking = false;
									if (count($trackings)>0) $hasTracking = true;
									/*foreach($trackings as $tracking) {
										if ($tracking->getNumber() == $line->{'Tracking ID'}) {
											$hasTracking = true;
											break;
										}
									}*/

									if (!$hasTracking) {

										$track = Mage::getModel('sales/order_shipment_track')
											->setNumber($line->{'Tracking ID'})
											->setCarrierCode('ups')
											->setTitle('UPS');

										$shipment->addTrack($track);

										try {
											$shipment->save();

											$shipment->sendEmail();

											$order = $shipment->getOrder();
											$payment = $order->getPayment();
											$order->setStatus(substr(strtolower($payment->getMethod()),0,7) == 'payone_' ? 'closed' : 'pending_payment');

											try {
												$order->save();
												$this->log('Der Status der Bestellung: ' . $order->getIncrementId() . ' wurde erfolgreich geändert!');
											} catch (Exception $e) {
												$successfullyUpdated = false;
												$this->log('Der Status der Bestellung: ' . $order->getIncrementId() . ' konnte nicht geändert werden!', 'error');
												$this->log($e->getMessage(), 'error');
											}

											$this->log('UPS Import: Lieferschein: ' . $line->{'Lieferscheinnummer'} . ' | Trackingnummer: ' . $line->{'Tracking ID'} . ' | Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' OK');
										} catch (Exception $e) {
											$successfullyUpdated = false;
											$this->log('UPS Import: Lieferschein: ' . $line->{'Lieferscheinnummer'} . ' | Trackingnummer: ' . $line->{'Tracking ID'} . ' | Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' failed','error');
											$this->log($e->getMessage(), 'error');
										}
									} else {
										$this->log('Bestellnummer: ' . $shipment->getOrder()->getIncrementId() . ' hat bereits einen Lieferschein (' . $line->{'Lieferscheinnummer'} . ') mit der Trackingnummer: ' . $line->{'Tracking ID'} . '');
									}
								} else {
									$this->log('Es wurde keine Bestellnummer mit der Lieferscheinnummer ' . $line->{'Lieferscheinnummer'} . ' gefunden!');
								}
							}
						}
					}
				}

				$basenameParts = explode(".",$basename);
				$basenameEnd = end($basenameParts);
				unset($basenameParts[count($basenameParts) - 1]);
				$basename = implode(".",$basenameParts);
				$newBasename = $basename . "." . date("Y-m-d_H-i-s") . "." . $basenameEnd;
				$basename = $newBasename;

				rename($local_file, $this->local_UPS_path . '/' . ($successfullyUpdated ? 'archiv' : 'error') . '/' . $basename);
				ftp_rename($conn_id, $file, $this->ftp_path . '/' . $this->ftp_path_UPS . '/' . ($successfullyUpdated ? 'archiv' : 'error') .'/' . $basename);
			}
		}
	}

	public function solrindex() {
		Mage::app()->setCurrentStore(1);

		$model = Mage::getModel('solr/result');
		$colors = Mage::helper('pimcore/pimcore')->getAllColors();
		$sizes = $this->getOptions('elk_size');
		/*
		$allowedImageSizes = array(
			//'listing',
			//'produkt',
			//'thumb',
			//'warenkorb',
			//'zoom',
		);
		*/
		$solr = Mage::helper('solr');

		$solr->clear();

		$products = Mage::getModel('catalog/product')->getCollection()
			->addFieldToFilter('type_id', array('eq' => 'configurable'))
			->addFieldToFilter('status', array('eq' =>'1'));

		$i = 1;
		$count = count($products);

		foreach($products as $_product) {
			//if($_product->getId() != 15079) continue;
			$mainProduct = Mage::getModel('catalog/product')->load($_product->getId());
			$childProducts = $mainProduct->getTypeInstance(true)
        		->getUsedProducts(null, $mainProduct);


        	$categories = array();
			$categoryIds = $mainProduct->getCategoryIds();

			if(count($categoryIds)) {
				foreach ($categoryIds as $categoryId) {
					if($category = Mage::getModel('catalog/category')->load($categoryId)) {
						array_push($categories, $category->getName());
						while($category->getParentId() != '2' AND $category = Mage::getModel('catalog/category')->load($category->getParentId())) {
							array_push($categories, $category->getName());
							foreach(explode(',', $category->getMetaKeywords()) as $word) {
								if($word) {
									array_push($categories, trim($word));
								}
							}
						}
					}
				}
			}

			$categories = array_unique($categories);
			sort($categories);

			$item = array(
				'id' => $mainProduct->getId(),
				'sku' => $mainProduct->getSku(),
				'name' => $mainProduct->getName(),
				'bezeichnung' => $mainProduct->getElkBezeichnung(),
				'price' => $mainProduct->getPrice(),
				'special_price' => $mainProduct->hasSpecialPrice() ? $mainProduct->getSpecialPrice() : 0,
				'sale' => $mainProduct->hasSpecialPrice(),
				'adm' => $mainProduct->isAdm() ? 1 : 0,
				'boebel' => ($mainProduct->boebbel() ? $mainProduct->boebbel() : ''),
				'description' => trim(str_replace(array("\r", "\r\n", "\n", "•"), '', $mainProduct->getDescription())),
				'short_description' => trim(str_replace(array("\r", "\r\n", "\n", "•"), '', $mainProduct->getShortDescription())),
				'material' => trim(str_replace(array("\r", "\r\n", "\n", "•"), '', $mainProduct->getElkMaterial())),
				'ean' => array(),
				'color' => array(),
				'size' => array(),
				'color_items' => array(),
				'categories' => $categories,
				'categories_tok' => $categories,
				'url' => Mage::helper('pimcore/pimcore')->getFullProductUrl($mainProduct),
			);

        	foreach($childProducts as $product) {
        		if($product->getStatus() != 1) continue;

        		$color = $colors[$product->getElkColor()];
        		if(!in_array($color->name, $item['color'])) {
        			$item['color'][] = $color->name;
        			$item['color_items'][$color->name] = $color;
        		}

        		$size = $sizes[$product->getElkSize()];
        		if(!in_array($size, $item['size'])) {
        			$item['size'][] = $size;
        		}

        		$item['ean'][] = $product->getSku();


			}

			foreach($item['color_items'] as &$color_item) {
					$color_item->images = array_map(function($img) { return $img->path; }, $mainProduct->getProductImages('listing', $color_item->code));
			}

			$item['color_items'] = json_encode($item['color_items']);

			$this->log("Added Product {$item['name']} {$item['sku']} ({$i} von {$count})");
			$solr->addDocument($item);

			$i++;
		}

		$this->log('Commiting');
		$solr->commit();
	}

	public function opos() {
		$file = realpath(rtrim($this->dir, '/') . '/opos.csv');

		$columns = array(
			"bl",
			"konto",
			"beschriftung",
			"rechnungs_nr",
			"datum",
			"faelligkeit",
			"betrag_soll",
			"betrag_haben",
			"saldo",
			"s_h",
			"gegenkonto",
			"r",
			"faellig",
			"ausgl",
			"belegfeld2",
			"kz",
			"buchungstext",
			"kost1",
			"kost2",
			"ust",
			"stapel_nr",
			"bsnr",
			"zi",
			"gpbank",
			"div_adresse",
			"sachverhalt",
			"zinssperre",
			"mdt_ank",
			"zahlungskondition",
			"kreditlimit",
			"plz",
			"ort",
			"unternehmensgegenstand",
			"kunden_nr",
			"kurzbezeichnung",
			"telefon",
			"nummer_fremdsystem",
		);

		$opos = $this->readCsv($file, $columns, $delimiter = ';', $enclosure = "'", $skip = 2);

		if(!$file) {
			$this->log('Opos Datei nicht gefunden', 'error');
			exit;
		}

		$opos = array_map(function($item) {
			return trim($item->rechnungs_nr);
		}, $opos);

		$opos = array_filter($opos, function($item) {
			return is_numeric($item);
		});

		$opos = array_unique($opos);

		$invoices = Mage::getModel('sales/order_invoice')
			->getCollection()
			->addFieldToFilter('state', array('eq' => Mage_Sales_Model_Order_Invoice::STATE_OPEN))
			->addFieldToFilter('created_at', array('lt' => date('Y-m-d H:i:s', strtotime('Today - 1 weeks'))))
			->setOrder('created_at', 'desc');

		foreach($invoices as $invoice) {
			$invoiceNr = (int) $invoice->getIncrementId();
			if(!in_array($invoiceNr, $opos)) {
				$invoice->pay();

				$comment = Mage::getModel('sales/order_invoice_comment');
				$comment->setComment("Rechnung auf bezahlt gesetzt durch OPOS-Liste am " . date('d.m.Y') . " um " . date('H:i:s') . " Uhr.");
				$invoice->addComment($comment);
				$invoice->save();

				$order = $invoice->getOrder();
				$order->setData('state', Mage_Sales_Model_Order::STATE_CLOSED);
				$order->setStatus(Mage_Sales_Model_Order::STATE_CLOSED);
				$history = $order->addStatusHistoryComment("Bestellung auf abgeschlossen gesetzt durch OPOS-Liste am " . date('d.m.Y') . " um " . date('H:i:s') . " Uhr.", false);
				$history->setIsCustomerNotified(false);
				$order->save();
				$this->log("{$invoiceNr} auf bezahlt gesetzt");
			} else {
				$this->log("{$invoiceNr} nicht auf bezahlt gesetzt");
			}
		}
	}

	public function mark_orders_as_closed() {
		$invoices = Mage::getModel('sales/order_invoice')
			->getCollection()
			->addFieldToFilter('state', array('eq' => Mage_Sales_Model_Order_Invoice::STATE_PAID));

		foreach($invoices as $invoice) {
			$order = $invoice->getOrder();
			$state = $order->getStatus();
			$orderNr = (int) $order->getIncrementId();
			if($state === Mage_Sales_Model_Order::STATE_PROCESSING OR $state === Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
				$order->setData('state', Mage_Sales_Model_Order::STATE_CLOSED);
				$order->setStatus(Mage_Sales_Model_Order::STATE_CLOSED);
				$history = $order->addStatusHistoryComment("Bestellung auf abgeschlossen gesetzt durch OPOS-Liste am " . date('d.m.Y') . " um " . date('H:i:s') . " Uhr.", false);
				$history->setIsCustomerNotified(false);
				$order->save();
				$this->log("{$orderNr} auf abgeschlossen gesetzt");
			} else {
				$this->log("{$orderNr} ignoriert");
			}
		}
	}

	public function clear_cache() {

		$url = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/') . '/_clearcache.php';

		$client = new Zend_Http_Client($url, array(
			'timeout' => 60 * 5
		));

		$client->resetParameters();
		$client->setMethod(Zend_Http_Client::GET);
		$client->setAuth('uandi', 'start123', Zend_Http_Client::AUTH_BASIC);
		$client->setParameterGet('key', 'elkline2011');
		$request = $client->request();
		$body = $request->getBody();

		foreach(explode('<br />', $body) as $string) {
			if($string) $this->log($string);
		}

	}
}

class SimpleXMLExtended extends SimpleXMLElement {
	public function addCData($cdata_text) {
		$node= dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}


abstract class Klass {
	protected $properties = array();

	public function __construct($data = array()) {
		$this->fromArray($data);
	}

	public function fromArray($data) {
		foreach($data as $property => $value) {
			$this->__set($property, $value);
		}
	}

	public function __set($property, $value) {
		if(!array_key_exists($property, $this->properties)) return;
		$this->properties[$property] = $value;
	}

	public function __get($property) {
		if(!array_key_exists($property, $this->properties)) return;
		return $this->properties[$property];
	}
}

class Adresse extends Klass{
	protected $properties = array(
		'elkline_id' => null,
		'prefix' => null,
		'firstname' => null,
		'lastname' => null,
		'company' => null,
		'city' => null,
		'country_id' => null,
		'postcode' => null,
		'telephone' => null,
		'fax' => null,
		'street' => null,
		'shipping_address' => null,
		'billing_address' => null,
	);
}

class Kunde extends Klass{
	protected $properties = array(
		'elkline_id' => null,
		'nr' => null,
		'company' => null,
		'prefix' => null,
		'firstname' => null,
		'lastname' => null,
		'email' => null,
		'addresses' => array(),
		'elk_kreditlimit' => null,
	);

	public function getShippingAddress() {
		$addresses = $this->properties['addresses'];
		if(count($addresses) > 1) {
			foreach($addresses as $id => $a) {
				if($id === 'base') continue;
				if($a->shipping_address) return $a;
			}
		}
		return $addresses['base'];
	}

	public function getBillingAddress() {
		$addresses = $this->properties['addresses'];
		if(count($addresses) > 1) {
			foreach($addresses as $id => $a) {
				if($id === 'base') continue;
				if($a->billing_address) return $a;
			}
		}
		return $addresses['base'];
	}
}
