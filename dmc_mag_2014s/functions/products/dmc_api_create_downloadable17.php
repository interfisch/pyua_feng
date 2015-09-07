<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_api_create_downloadable.php												*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues simple product 											*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/

					if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_downloadable*****\n");
				
					// Magento BUG Abfangroutine fuer Status = 0
					if ($newProductData['status'] <> '1' && $newProductData['status'] != '2') $newProductData['status']==2;
					//if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_downloadable 21*****\n");
					// existing article
					if ($art_already_exists) {
						// Wenn keine Art_ID vorhanden, dann $newProductId ?
					//	if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_simple25*****\n");
						if ($art_id=='') $art_id=$newProductId;
						if (DEBUGGER>=1) fwrite($dateihandle, "NO downloadable product ".$Artikel_Bezeichnung." with sku ".$Artikel_Artikelnr." updated\n");
						$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
					} else { // new article
						// neue produkt id -get product id
						$createData = array ( $newProductData );		// Mehrere waren moeglich
					// if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_simple32*****\n");
						try {
							Mage::getSingleton('fastsimpleimport/import')
							->setPartialIndexing(true)
							//  ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
							->processProductImport($createData); 
						   if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_downloadable39*****\n");
						//	if (DEBUGGER>=50) fwrite($dateihandle, "******Kategorien ergaenzen*****\n");
							$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);	
							if (DEBUGGER>=1) fwrite($dateihandle, "Downloadable product created with ID: ".$newProductId."\n");						
							// Kategoriezuordnungen ergaenzen / notwendig bei AvS product import
							dmc_attach_cat_ids($newProductId,$Kategorie_IDs);
						}
						catch (Mage_Core_Exception $e) {
							// if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_create_simple 47*****\n");
							fwrite($dateihandle, "api fehler=".$e->getMessage());
							dmc_write_error("dmc_api_create_Downloadable", "processProductImport", "41", "Artikelnummer:".$Artikel_Artikelnr." -> ".$e->getMessage(), true, true, $dateihandle);
							if (DEBUGGER>=1) fwrite($dateihandle, "Downloadable product NOT created -> Details see Error LOG\n");
						}
						catch (Exception $e) {
							fwrite($dateihandle, "api fehler2=".$e);
							dmc_write_error("dmc_api_create_Downloadable", "processProductImport", "45",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
							if (DEBUGGER>=1) fwrite($dateihandle, "Downloadable product NOT created -> Details see Error LOG\n");
						}						
					} // End if insert
					
	
?>