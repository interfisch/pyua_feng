<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_api_create_simple.php												*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues simple product 											*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
					if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_simple*****\n");
					//if (DEBUGGER>=50) fwrite($dateihandle, "api create - _category neu = ".$newProductData['_category']." \n");	
					//if (DEBUGGER>=50) fwrite($dateihandle, "api create - categories neu = ".$newProductData['categories']." \n");	

					// Magento BUG Abfangroutine fuer Status = 0
					if ($newProductData['status']==0) $newProductData['status']==1;
					// existing article
					if ($art_already_exists) {
						// Wenn keine Art_ID vorhanden, dann $newProductId ?
						if ($art_id=='') $art_id=$newProductId;
						if (DEBUGGER>=1) fwrite($dateihandle, "Simple product ".$Artikel_Bezeichnung." mit sku ".$Artikel_Artikelnr." NNNNOOOOTTTT updated\n");
						$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
					} else { // new article
						// neue produkt id -get product id
						$createData = array ( $newProductData );		// Mehrere waren moeglich
						// if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_simple 32 (sku = ".$newProductData['sku'].") *****\n");
						try {
							$ausgabe=print_r($createData,true);
							fwrite($dateihandle, "****** 33 * mit  Auswahl=".$ausgabe."****\n");
							$randomString = "tesT";
    $data[] = array(
        'sku' => $i,
        '_type' => 'simple',
        '_attribute_set' => 'Default',
        '_product_websites' => 'base',
        // '_category' => rand(1, 3),
        'name' => $randomString,
        'price' => 0.99,
        'special_price' => 0.90,
        'cost' => 0.50,
        'description' => 'Default',
        'short_description' => 'Default',
        'meta_title' => 'Default',
        'meta_description' => 'Default',
        'meta_keywords' => 'Default',
        'weight' => 11,
        'status' => 1,
        'visibility' => 4, 
        'tax_class_id' => 2,
        'qty' => 0,
        'is_in_stock' => 0,
        'enable_googlecheckout' => '1',
        'gift_message_available' => '0',
        'url_key' => strtolower($randomString),
    );
	if (DEBUGGER>=1) fwrite($dateihandle, "60 \n");						
							
	require_once '.,/app/Mage.php';
	umask(0);
	Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
if (DEBUGGER>=1) fwrite($dateihandle, "65 \n");						
								

	$import = Mage::getModel('fastsimpleimport/import')
    ->setPartialIndexing(true)
    ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
    ->processProductImport($data);

							if (DEBUGGER>=1) fwrite($dateihandle, "Simple product angelegt mit ID: ".$newProductId." ->");						
							if (DEBUGGER>=50) fwrite($dateihandle, "  Kategorien ergaenzen*****\n");
							$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);	
							// Kategoriezuordnungen ergaenzen / notwendig bei AvS product import
							dmc_attach_cat_ids($newProductId,$Kategorie_IDs,$Sortierung);
						}
						catch (Mage_Core_Exception $e) {
							fwrite($dateihandle, "******dmc_api_create_simple api fehler2=".$e->getMessage()."\n");
							dmc_write_error("dmc_api_create_simple", "processProductImport", "41", "Artikelnummer:".$Artikel_Artikelnr." -> ".$e->getMessage(), true, true, $dateihandle);
							if (DEBUGGER>=1) fwrite($dateihandle, "Simple product NOT created -> Details see Error LOG\n");
							return "Produkt konnte nicht angelegt werden";
						}
						catch (Exception $e) {
							fwrite($dateihandle, "******dmc_api_create_simple api fehler2=".$e ."\n");
							dmc_write_error("dmc_api_create_simple", "processProductImport", "45",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
							if (DEBUGGER>=1) fwrite($dateihandle, "Simple product NOT created -> Details see Error LOG\n");
							return "Produkt konnte nicht angelegt werden";
						}	
													
					} // End if insert
					
	
?>