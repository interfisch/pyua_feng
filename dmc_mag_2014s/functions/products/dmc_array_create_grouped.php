<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_array_create_grouped.php												*
*  inkludiert von dmc_write_art.php 										*
*  Array fuer neues grouped product setzen									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
				$newProductDataTmp = array(				    
					'set' => $attribute_set_id, 
					'type' => 'grouped', 				  
			        'categories' => $Kategorie_IDs,
			        'websites' => Array
								        (
								                 '0' => 1
								        ),
			        'updated_at' => 'now()',
			        'created_at' => 'now()',
					'type_id' => 'grouped', 				 
			        'name' => $Artikel_Bezeichnung,
			        'description' => $Artikel_Text,
			        'short_description' => $Artikel_Kurztext,				 
			        'weight' => $Artikel_Gewicht,
			        'status' => $Aktiv,
			        'visibility' => $Artikel_Status,		
					'delivery_time'=>$Artikel_Lieferstatus,			 
			        'gift_message_available' => 2,
					// Wenn Optionen zugeordnet
					// 'required_options' => 1,
					// 'has_options' => 0,				 
			        'price' => $Artikel_Preis,
			        // 'special_price' => $Artikel_Preis1,
			        // 'special_from_date' => 2008-08-21 00:00:00,
			        // 'special_to_date' => 2008-08-25 00:00:00,
			        'tax_class_id' => $Artikel_Steuersatz,
			        'tier_price' => $Kundengruppenpreise,
			        //'meta_title' => $Artikel_MetaTitle,
			        //'meta_keyword' => $Artikel_MetaKeywords,
			        //'meta_description' => $Artikel_MetaDescription,
			        'manufacturer' => $Hersteller_ID
				); // end  grouped
				// Konfigurationen - Configurations, 
				$newConfData = array (
					// 'qty'=>$Artikel_Menge, 
					'is_in_stock'=>1,
					// 'required_options'=>  1, 
					'has_options'=>  0 
				);  // end newConfData
				// Wenn Auspragungen und Merkmale zum Produkt übergeben wurden
				if ($Artikel_Merkmal!="")
					for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
					{
						if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273') {
						//	if (DEBUGGER>=1) fwrite($dateihandle, "Grouped zuweisen: ".$Merkmale[$Anz_Merkmale]." = ".$AuspraegungenID[$Anz_Merkmale]."\n");	
							$newConfData[$Merkmale[$Anz_Merkmale]]=$AuspraegungenID[$Anz_Merkmale];
						} // end if
					} // end for
					
					$newProductData = array_merge($newProductDataTmp , $newConfData);
	
?>
	