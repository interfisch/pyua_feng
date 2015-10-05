<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop	Verrsion bis 1.4							*
*  dmc_array_create_simple.php												*
*  inkludiert von dmc_write_art.php 										*
*  Array fuer neues siple product setzen									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
				$newProductData = array(				    
						'set' => $attribute_set_id, 
						'type' => 'simple', 				  
						'categories' =>$Kategorie_IDs,
						'websites' => Array
									(
										 '0' => 1
									),
						'updated_at' => 'now()',
						'created_at' => 'now()',
						'type_id' => 'simple', 				 
						'name' => $Artikel_Bezeichnung,
						'description' => $Artikel_Text,
						'short_description' => $Artikel_Kurztext,				 
						'weight' => $Artikel_Gewicht,
						'status' => $Aktiv,
						'delivery_time'=>$Artikel_Lieferstatus,			 
						'category_ids' =>$Kategorie_IDs,
						'gift_message_available' => 2,
						'price' => $Artikel_Preis,
						'tax_class_id' => $Artikel_Steuersatz,
						'tier_price' => $Kundengruppenpreise,
					   // 'meta_title' => $Artikel_MetaTitle,
					   // 'meta_keyword' => $Artikel_MetaKeywords,
					   // 'meta_description' => $Artikel_MetaDescription,
						'manufacturer' => $Hersteller_ID,
						'qty'=> $Artikel_Menge, 
						'is_in_stock'=>1,
						'has_options' =>  0 ,
						// AL-Elektronik
						'groupscatalog2_groups'=>
						  array {
							[0]=>
							string(1) "1"
							[1]=>
							string(1) "2"
							[2]=>
							string(1) "3"
							[3]=>
							string(1) "4"
						  }
					); // end newProductData Array SIMPLE
					
					// Wenn Auspragungen und Merkmale zum Produkt übergeben wurden
					if ($Artikel_Merkmal!="")
						for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
						{
							 // if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
							if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
								&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
								&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit") {
								// if (DEBUGGER>=1) fwrite($dateihandle, "535 - Simple zuweisen: ".$Merkmale[$Anz_Merkmale]." = ".$AuspraegungenID[$Anz_Merkmale]."\n");	
								$newProductData[$Merkmale[$Anz_Merkmale]]=$AuspraegungenID[$Anz_Merkmale];
								if ($AuspraegungenID[$Anz_Merkmale]=='PKG')
									$newProductData[$Merkmale[$Anz_Merkmale]]='PKG';
								if ($AuspraegungenID[$Anz_Merkmale]=='STK')
									$newProductData[$Merkmale[$Anz_Merkmale]]='STK';
								// if (DEBUGGER>=1) fwrite($dateihandle, "Dem Simple zugewiesen: ".$newProductData[$Merkmale[$Anz_Merkmale]]."\n");	
							} // end if
						} // end for
					
					// Wenn Mindestbestellmenge verarbeitet werden soll, verwende $stock_data (vgl oben)
					if (isset($stock_data)) 
						$newProductData['stock_data'] = $stock_data;
				
					// Varianten auf nicht sichbar setzen
					if ($Artikel_Variante_Von!="" && $newProductId != 28021973) 
						$newProductData ['visibility'] = '1';
					else 
						$newProductData ['visibility'] = $Artikel_Status;
?>
	