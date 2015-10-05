<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop	bis Version 1.5								*
*  dmc_api_create_conf14.php												*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues configurable product 									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/

					if ($art_already_exists) {
						// Wenn keine Art_ID vorhanden, dann $newProductId ?
						if ($art_id=='') $art_id=$newProductId;
						if ($client->call($sessionId, 'product.update', array($art_id, $newProductData)))	
							$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
						else $newProductId = 28021973;	// no update possible						
					} else { // new article
						fwrite($dateihandle, "dm_write_art - create new configurable product with sku ".$Artikel_Artikelnr."\n");
						// Zu schreibenes Array Loggen
						//if (DEBUGGER>=1) print_array($newProductData);
						$set['set_id']=$attribute_set_id;
						$newProductId = $client->call($sessionId, 'product.create', 
							array('configurable',$set['set_id'], $Artikel_Artikelnr, $newProductData));
					}					
					
					// super attribute setzen ; set super attribte
					if ($newProductId!="") {
						$table = "catalog_product_super_attribute";
						$columns = "(`product_id` ,`attribute_id` ,`position`)";
						
						for ( $i = 0; $i < count ( $SuperattributeID ); $i++ )
						{
							$values = "('".$newProductId."', '".$SuperattributeID[$i]."', '0')";			// 80 = color
							if (!dmc_entry_exits("product_super_attribute_id", "catalog_product_super_attribute", " product_id='".$newProductId."' and attribute_id='".$SuperattributeID[$i]."'")) 
								dmc_sql_insert($table, $columns, $values);		  
						} // end for
																
					}				
	
?>