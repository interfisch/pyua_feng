<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
*  dmc_array_create_downloadable.php										*
*  inkludiert von dmc_write_art.php 										*
*  Array fuer neues download product setzen									*
*  Copyright (C) 2014 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
07.05.2014
- neu
*/
		if (DEBUGGER>=50) fwrite($dateihandle, "dmc_array_create_downloadable- _category neu = ".$Kategorie_IDs[0]." \n");	
						$newProductData = array(				    
						'set' => $attribute_set_id, 
						'type' => 'downloadable', 				  
						'type_id' => 'downloadable', 				 
						'categories' =>$Kategorie_IDs,
						'websites' => Array
									(
										 '0' => 1
									),
						'updated_at' => 'now()',
						'created_at' => 'now()',
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
						'has_options' =>  0 
					); // end newProductData Array downloadable
					
			
					// ggfls Metas generieren
					if ($Artikel_MetaTitle =='' && $Artikel_MetaKeywords == '')
						$newProductData['generate_meta']=1;
					else 
						 $newProductData['generate_meta']=0;
    				
					// Wenn Auspragungen und Merkmale zum Produkt übergeben wurden
					if ($Artikel_Merkmal!="")
						for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
						{
							 // if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
							if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
							&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
							&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit") {
								$AuspraegungenID[$Anz_Merkmale] = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], 
										$Auspraegungen[$Anz_Merkmale],$store_id);	
								if (DEBUGGER>=1) fwrite($dateihandle, "47 - Downloadable zuweisen: ".$Merkmale[$Anz_Merkmale]." = ".$Auspraegungen[$Anz_Merkmale]." mit ID ".$AuspraegungenID[$Anz_Merkmale]."\n");	
								// ACHTUNG bei MAGENTO API nicht die ID verwenden !!!
								// OptionsID statt Optionswert nur für DropDown (select) Werte erforderlich
							//	if (strpos(dmc_get_attribute_type ($Merkmale[$Anz_Merkmale]), 'select') !== false) {
									//$Auspraegungen[$Anz_Merkmale]=$AuspraegungenID[$Anz_Merkmale];
							//	}
								$newProductData[$Merkmale[$Anz_Merkmale]]=$Auspraegungen[$Anz_Merkmale];
								if ($AuspraegungenID[$Anz_Merkmale]=='PKG')
									$newProductData[$Merkmale[$Anz_Merkmale]]='PKG';
								if ($AuspraegungenID[$Anz_Merkmale]=='STK')
									$newProductData[$Merkmale[$Anz_Merkmale]]='STK';
								 if (DEBUGGER>=1) fwrite($dateihandle, "Dem Produkt zugewiesen: ".$Merkmale[$Anz_Merkmale]."=".$newProductData[$Merkmale[$Anz_Merkmale]]."\n");		
							} // end if
						} // end for
					
					// Wenn Mindestbestellmenge verarbeitet werden soll, verwende $stock_data (vgl oben)
				//	if (isset($stock_data)) 
				//		$newProductData['stock_data'] = $stock_data;
					$newProductData['stock_data']['manage_stock'] = 0 ;
								
					// Varianten auf nicht sichbar setzen
					if ($Artikel_Variante_Von!="" && $newProductId != 28021973) 
						$newProductData ['visibility'] = '1';
					else 
						$newProductData ['visibility'] = $Artikel_Status;
					
	
?>