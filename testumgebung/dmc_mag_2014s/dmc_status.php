<?php
	/**
	 *
	 * @write Verarbeite spezielle Status von der Schnittstelle
	 * @param string $s
	 * @return string $s
	 *
	 * Version vom 18.09.2013
	 *
	 * 18.09.2013 - Verarbeitung vom Status write_artikel_begin etc (Beginn Artikelabgleich)
	 */
	
	
	ini_set("display_errors", 1);
	error_reporting(E_ERROR);

	defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_status($client, $session) {
		global $action,$dateihandle; 		
		$status=$_POST['Status'];
		if (DEBUGGER>=1) fwrite($dateihandle, "\n******************dmc_status mit Status $status ******************\n");
		
		// Verarbeitung vom Status write_artikel_begin (Beginn Artikelabgleich
		if ($status=='write_artikel_begin') {
			//dmc_db_backup();
			// Basierend auf den Definitionsdateien koennen zu Beginn des Artikelabgleichs bestimmte Aktionenn durchgeführt werden
			// Alle Artikel loeschen, (nur) alle Varianten loeschen, alle Artikel deaktivieren
			
/*			if (STATUS_WRITE_ART_BEGIN_DETELE_ART)
				dmc_delete_first(); // löschen aller Artikel / dbc_db_functions.php
			
			if (STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART)
				dmc_deactivte_first(); // deaktivieren aller Artikel  / dbc_db_functions.php
				
			if (STATUS_WRITE_ART_BEGIN_DETELE_ART_VARIANTS)
				dmc_delete_variants_first(); // löschen aller Varianten Artikel / dbc_db_functions.php
			
			if (STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART_VARIANTS)
				dmc_deactivate_variants_first(); // unktion um alle Varianten Produkte zu deaktivieren / dbc_db_functions.php
				*/
			Mage::app();
			$processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
			$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_MANUAL));
			$processes->walk('save');
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Indexierung in Magento deaktiviert\n"); 
			
		}	//write_artikel_begin
		
		if ($status=='write_artikel_end') {
		}
		
		if ($status=='write_artikel_details_end') {
			// Indexe neu aufbauen
			/*Product Attributes 	1 	catalog_product_attribute
			Product Prices 	2 	catalog_product_price
			Catalog URL Rewrites 	3 	catalog_url
			Product Flat Data 	4 	catalog_product_flat
			Category Flat Data 	5 	catalog_category_flat
			Category Products 	6 	catalog_category_product
			Catalog Search Index 	7 	catalogsearch_stock
			Stock Status 	8 	cataloginventory_stock
			Tag Aggregation Data 	9 	tag_summary */
			require_once $_SERVER['DOCUMENT_ROOT']."/app/Mage.php";
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Neu indexieren:\n");
			//Mage::app();
			//$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute');
			//$process->reindexEverything();
			//if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Attributes  erfolgt\n");
			$indexingProcesses = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
			foreach ($indexingProcesses as $process) {
				  $process->reindexEverything();
				  if (DEBUGGER>=1) fwrite($dateihandle, "*** Prozess Indexierung erfolgt\n");			
			}
/*			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Price erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_stock');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Search erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('cataloginventory_stock');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Stock erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_product');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Category Products erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Flat Data erfolgt\n");
			*/
			//if (DEBUGGER>=1) fwrite($dateihandle, "*** ALL\n");
			//$process = Mage::getSingleton('index/indexer')->getProcessesCollection();
			//$process->walk('reindexAll');
			//if (DEBUGGER>=1) fwrite($dateihandle, "*** Neu Indexierung erfolgt\n");
			$process->walk('setMode', array(Mage_Index_Model_Process::MODE_REAL_TIME));
			$process->walk('save');
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Indexierung in Magento aktiviert\n"); 
			// Bilder Cache leeren
			//Mage::getModel('catalog/product_image')->clearCache();
			//if (DEBUGGER>=1) fwrite($dateihandle, "*** Bilder Cache geleert\n"); 
			//Mage::app()->cleanCache();
			//if (DEBUGGER>=1) fwrite($dateihandle, "*** Cache geleert\n"); 
			
		}	//write_artikel_begin

		if (strpos($status,'categorie_begin')!==false) { 
		//	dmc_delete_categories_first(); // löschen aller Kategorien / dbc_db_functions.php
		//	dmc_deactivte_first(); // deaktivieren aller Artikel  / dbc_db_functions.php
		//	dmc_delete_variants_first(); // löschen aller Varianten Artikel / dbc_db_functions.php
		}
		
	}// end function dmc_status	
	
?>