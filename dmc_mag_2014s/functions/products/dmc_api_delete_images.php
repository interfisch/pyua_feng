<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_api_delete_images.php												*
*  inkludiert von dmc_write_art.php 										*
*  Löscht Bilder eines Produtes 											*
*  Ersatz für soap media_remove												*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
11.10.2012
- neu
*/
					if (DEBUGGER >=1) fwrite($dateihandle, "******dmc_api_delete_images*****\n");

					// Wenn keine Art_ID vorhanden, dann $newProductId ?
					$art_id=$newProductId;
					if ($art_id=='') {
						$art_id = dmc_get_id_by_artno($Artikel_Artikelnr);
					}
					
					try {
						$ergebnis = $client->call($sessionId, 'product_media.list', $newProductId);
						// var_dump($ergebnis);
						for ($i=0;$i<count($ergebnis);$i++){
							// fwrite($dateihandle, "****** Remove image ".$ergebnis[$i]['file']." from product ".$art_id."****\n"); // $ergebnis[$i]['file']
							
							$image_name = $ergebnis[$i]['file'];
							
							// require_once '../app/Mage.php';
							// Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

							$resource = Mage::getSingleton('core/resource');
							$read = $resource->getConnection('core_read');
							$write = $resource->getConnection('core_write');

							$select = $read->select()->from($resource->getTableName('catalog_product_entity_media_gallery'), array('*'));
							$select->where('entity_id = ?', $art_id);
							$select->where('value = ?', $image_name);

							$item = $read->fetchRow($select);

							if ($item) {
								$write->delete($resource->getTableName('catalog_product_entity_media_gallery'), $write->quoteInto('value_id = ?', $item['value_id']));
								$write->delete($resource->getTableName('catalog_product_entity_media_gallery_value'), $write->quoteInto('value_id = ?', $item['value_id']));
								$write->query('DELETE FROM catalog_product_entity_varchar WHERE value = "'.$item['value'].'" AND entity_id = ' . $item['entity_id']);

								$path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product';
								unlink($path . $item['value']);
							}
						} // end for
						
					}
					catch (Mage_Core_Exception $e) {
							 fwrite($dateihandle, "api fehler=".$e->getMessage());
							dmc_write_error("dmc_api_delete_images", "processDeleteImages", "58",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e->getMessage(), true, true, $dateihandle);
					}
					catch (Exception $e) {
							 fwrite($dateihandle, "api fehler2=".$e);
							dmc_write_error("dmc_api_delete_images", "processDeleteImages", "62",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
					}
						
	
?>