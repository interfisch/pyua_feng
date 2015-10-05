<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento Shop												*
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
		if (DEBUGGER>=50) fwrite($dateihandle, "array_create-simple store -> ".$store_id	." \n");	
					$newProductData = array(		
						'store_id' => $store_id,
						'sku' => $Artikel_Artikelnr,
						 '_type' => 'simple',
						'_attribute_set' => $attribute_set_name, 	// 'Default',
						'_product_websites' => 'base',
					//	'_category' => $Kategorie_IDs[0],		// Zur Zeit nur eine moeglich, toDo Erweiterung Mage_ImportExport_Model_Import_Entity_Product _initCategories
					// -> neue Funktion seit April 2013 : dmc_attach_cat_ids in dmc_api_create_simple
						'name' => $Artikel_Bezeichnung,
						'description' => $Artikel_Text,
						'short_description' => $Artikel_Kurztext,				 
						'weight' => $Artikel_Gewicht,
						'status' => $Aktiv,
						'delivery_time'=>$Artikel_Lieferstatus,			 
						'price' => $Artikel_Preis,
						'tax_class_id' => $Artikel_Steuersatz,
						'tier_price' => $Kundengruppenpreise,
						// 'manufacturer' => $Hersteller_ID,
						'qty'=> $Artikel_Menge, 
						'meta_title' => $Artikel_MetaTitle,
						'meta_description' => $Artikel_MetaDescription,
						'meta_keywords' => $Artikel_MetaKeywords,
        				'is_in_stock'=>1,
						'has_options' =>0 
					); // end newProductData Array SIMPLE
					
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
								if (DEBUGGER>=1) fwrite($dateihandle, "58 - Simple zuweisen: ".$Merkmale[$Anz_Merkmale]." = ".$Auspraegungen[$Anz_Merkmale]." mit ID ".$AuspraegungenID[$Anz_Merkmale]."\n");	
								// ACHTUNG bei MAGENTO API nicht die ID verwenden !!!
								// OptionsID statt Optionswert nur für DropDown (select) Werte erforderlich
								if (strpos(dmc_get_attribute_type ($Merkmale[$Anz_Merkmale]), 'select') !== false) {
									// $Auspraegungen[$Anz_Merkmale]=$AuspraegungenID[$Anz_Merkmale];
								} 
								$newProductData[$Merkmale[$Anz_Merkmale]]=$Auspraegungen[$Anz_Merkmale];
								if ($AuspraegungenID[$Anz_Merkmale]=='PKG')
									$newProductData[$Merkmale[$Anz_Merkmale]]='PKG';
								if ($AuspraegungenID[$Anz_Merkmale]=='STK')
									$newProductData[$Merkmale[$Anz_Merkmale]]='STK';
								 if (DEBUGGER>=1) fwrite($dateihandle, "Dem Simple zugewiesen: ".$Merkmale[$Anz_Merkmale]."=".$newProductData[$Merkmale[$Anz_Merkmale]]."\n");		
							} // end if
						} // end for
					
					// Wenn Mindestbestellmenge verarbeitet werden soll, verwende $stock_data (vgl oben)
				//	if (isset($stock_data)) 
				//		$newProductData['stock_data'] = $stock_data;
				
					// Varianten auf nicht sichbar setzen
					if ($Artikel_Variante_Von!="" && $newProductId != 28021973) 
						$newProductData ['visibility'] = 1;
					else 
						$newProductData ['visibility'] = $Artikel_Status;
					
	
?>