<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop	bis Version 1.5								*
*  dmc_api_create_simple14.php												*
*  inkludiert von dmc_write_art.php 										*
*  Speichert neues simple product 											*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/

					// existing article
					if ($art_already_exists) {
						// Wenn keine Art_ID vorhanden, dann $newProductId ?
						if ($art_id=='') $art_id=$newProductId;
						// if ($client->call($sessionId, 'product.update', array($art_id, array($newProductData))))	{
						//if ($client->call($sessionId, 'product.update', array($art_id, $newProductData)))	{
							if (DEBUGGER>=1) fwrite($dateihandle, "NO Simple product ".$Artikel_Bezeichnung." with sku ".$Artikel_Artikelnr." updated\n");$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
						//} else $newProductId = 28021973;	// no update possible						
					} else { // new article
						// neue produkt id -get product id
						$set['set_id']=$attribute_set_id;
						$newProductId = $client->call($sessionId, 'product.create', array('simple', $set['set_id'], $Artikel_Artikelnr, $newProductData));

						if (DEBUGGER>=1) fwrite($dateihandle, "Simple product created with ID: ".$newProductId."\n");						
						// Magento API Bug beheben
						// Prüfen, ob Attribute gesetzt wurden, ggfls setzen
						// super attribute setzen ; set super attribute
						if ($Artikel_Merkmal!="") {
							for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
							{
								 // if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
									 if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
							&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
							&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit"
							&& is_numeric($MerkmaleID[$Anz_Merkmale]) && is_numeric($AuspraegungenID[$Anz_Merkmale])
								) {
								// if (DEBUGGER>=1) fwrite($dateihandle, "Simple zuweisen MerkmalID ".$MerkmaleID[$Anz_Merkmale]." = AuspraegungenID".$AuspraegungenID[$Anz_Merkmale]."\n");	
									$table = "catalog_product_entity_int";  
									$columns = "(`entity_type_id` ,`attribute_id` ,`store_id`,`entity_id`,`value`)";
									$values = "(".$attr_type_id.", '".$MerkmaleID[$Anz_Merkmale]."', '0', '".$newProductId."','".$AuspraegungenID[$Anz_Merkmale]."')";		
									// Eventuell alte Zuordnungen löschen
									if (dmc_entry_exits("value_id", "catalog_product_entity_int", " entity_id='".$newProductId."' and attribute_id='".$MerkmaleID[$Anz_Merkmale]."'")) 
										dmc_sql_delete("catalog_product_entity_int", " entity_id='".$newProductId."' and attribute_id='".$MerkmaleID[$Anz_Merkmale]."'");
									dmc_sql_insert($table, $columns, $values);
								} // end if
							} // end for
						} // end if ($Artikel_Merkmal!="")
					} // End if insert
							
	
?>