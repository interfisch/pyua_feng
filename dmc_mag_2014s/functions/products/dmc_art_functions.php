<?php
/****************************************************************************************************
*                                                                                   				*
*  dmConnector  for magento shop																	*
*  dmc_art_functions.php																			*
*  product specific functions																		*
*  Copyright (C) 2010-2012 DoubleM-GmbH.de															*
*                                                                                					*
* 20.04.2011 - function - attach_options_to_product 												*
* 20.04.2011 - get_options_from_product																*
* 30.05.2011 - attach_images_to_product preuft auch auf die artikelnummer enthaltene Bilddateien 	*
* 29.03.2012 - dmc_prepare_seo_name Suchmaschinen Konformer Text									*
* 06.04.2012 - dmc_attach_attribute_to_attributeset - Attribute einer Gruppe und Set zuweisen		*
* 06.04.2012 - dmc_get_attribute_group_id - Attribute Gruppen ID ermitteln							*
* 11.01.2013 - dmc_set_attribute_store_label - attribut eine bezeichnung fuer einen store zuweisen	*
* 11.01.2013 - dmc_generate_attribute_code - Code für Attribute generieren							*
* 08.08.2013 - dmc_set_group_price - Kundengruppenpreis setzen										*
* 07.11.2013 - dmc_set_product_status_db - // Aktiv/Passiv setzen über DATENBANK					*
* 17.01.2014 - dmc_set_group_tier_price_fast // Setzen von Kundengruppenpreise						*
* 20.02.2014 - dmc_get_attribute_type - Art des Attributes (text, select, multiselect)				*
* 05.03.2014 - dmc_get_attached_conf_id - ID des dem Simple zugehoerigen conf products ermitteln	*	
* 05.03.2014 - dmc_get_conf_price - Preis des conf products ermitteln								*	
* 05.03.2014 - dmc_set_conf_price_by_simple_price - Preis des conf products updaten anhand simple	*
*****************************************************************************************************/

	defined( 'VALID_DMC' ) or die( 'Direct Access to this location (functions) is not allowed.' );
	
	// Attribute einer Gruppe und Set zuweisen
	function dmc_attach_attribute_to_attributeset($entity_type_id,$attribute_set_id,$attribute_group_id,$attribute_id,$sort_order)
	{
		dmc_sql_insert(	"eav_entity_attribute",  
						"(entity_type_id, attribute_set_id,  attribute_group_id, attribute_id, sort_order)", 
						"($entity_type_id, $attribute_set_id, $attribute_group_id, $attribute_id, $sort_order)");
	} // function dmc_attach_attribute_to_attributeset
	
	// ID Der Attribute Gruppe nach Name und Set ermitteln
	function dmc_get_attribute_group_id($attribute_group_name,$attribute_set_id)
	{
		return dmc_sql_select_value('attribute_group_id', 'eav_attribute_group', "attribute_group_name='".$attribute_group_name."' AND attribute_set_id=".$attribute_set_id);
	} // function dmc_get_attribute_group_id
	
	// Suchmaschinen fuer product 
	function dmc_prepare_seo_name($text,$language)
	{
	
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_art_functions - dmc_prepare_seo_name=$text\n");
				
		//$seo_text = strtolower(utf8_normalize_nfc($text));
		//$seo_text = strtolower(($text));
		$seo_text = (($text));
		if (DEBUGGER>=1) fwrite($dateihandle, "/// 738=$seo_text\n");
		
		
		$d1 = array(" ","Ä", "Ö", "Ü", "ä" , "ö", "ü", "ß","�", "�", "�", "�" , "�", "�", "�","<",">","#","\"","'","´",",","&","²","?",";");
		$d2 = array("-", "Ae","Oe","Ue","ae","oe","ue","sz","Ae","Oe","Ue","ae","oe","ue","sz","_","_","_","_","_","_","_","-","2","-","-");
		$seo_text = str_replace($d1, $d2, $seo_text);		 
		if (DEBUGGER>=1) fwrite($dateihandle, "/// 615=$seo_text\n");
		$d1 =  array(' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$');
		$d2 = array('-', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$seo_text = str_replace($d1, $d2, $seo_text);
		if (DEBUGGER>=1) fwrite($dateihandle, "///619=$seo_text\n");
		$d1 =  array('&amp;', '&quot;', '&', '"', "'", '¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '=', 'µ', '#', '~', '"', '§', '%', '|', '°', '^');
		$seo_text = str_replace($d1, '', $seo_text);
		$seo_text = str_replace(array('----', '---', '--'), '-', $seo_text);
		
		
		// return urlencode($seo_text);
		return ($seo_text);
	} // end function dmc_prepare_seo
	
	function attach_simple_to_conf($Artikel_Artikelnr, $newProductId, $Artikel_Variante_Von, $MerkmaleID, $Artikel_Preis, $dateihandle, $client, $sessionId)
	{		
			//  simple product dem configurable/grouped zuordnen // attach simple product to configurable/grouped product 
							if (DEBUGGER>=1) fwrite($dateihandle, "attach simple product zuordnen:\n");
							if (preg_match('/@/', $Artikel_Variante_Von)) {
									// Art (conf oder grp) + Artikelnummer des "Haupt"-Artikels // Type of products + sku of conf 
									list ($Zuordnungsart, $Artikel_Variante_Von) = split ("@", $Artikel_Variante_Von);
							} else { //  nicht angegeben dann conf // standard is conf
								$Zuordnungsart="conf";
								$Artikel_Variante_Von	= $Artikel_Variante_Von;
							}
							// configurable product id ermitteln // get configurable product id 	
							if ($Zuordnungsart=="conf") {
								// Configurable zuordnen
								$conf_id=dmc_get_id_by_artno($Artikel_Variante_Von);	
								//Wenn KEINE Ausprägung übergeben, das Attribute aus dem Configurable ebenfalls löschen
								// TODO if ($AuspraegungenID[1]=="") dmc_sql_delete("catalog_product_super_attribute", " product_id='".$conf_id."' and attribute_id='76'");							
								// simple -> configurable when entry not already exists
								// Eventuell alte Zuordnungen löschen
								if (dmc_entry_exits("link_id", "catalog_product_super_link", " product_id='".$newProductId."' and parent_id='".$conf_id."'")) 
									dmc_sql_delete("catalog_product_super_link", " product_id='".$newProductId."' and parent_id='".$conf_id."'");
								// Variante zuordnen	
								dmc_attach_simple_to_configurable($newProductId,$conf_id);										// dmc_db_functions
								// Varianten Preise zuordnen
								dmc_attach_simple_to_configurable_prices($newProductId,$conf_id,$MerkmaleID[0],$Artikel_Preis); // dmc_db_functions
									/*$table = "catalog_product_super_link";
									$columns = "(`link_id` ,`product_id` ,`parent_id`)";
									$values = "('' , '".$newProductId."', '".$conf_id."')";			
									dmc_sql_insert($table, $columns, $values);*/	
							} else if ($Zuordnungsart=="grp") {
								// Grouped zuordnen
								$client->call($sessionId, 'product_link.assign', array('grouped', $Artikel_Variante_Von, $Artikel_Artikelnr, array('position'=>0, 'qty'=>0)));
							} // end if Zuordnungsart
	} // end function attach_simple_to_conf
	
	// function attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $articlenumber, $new_image_name, $dateihandle,$client, $sessionId) 
	function attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $newProductId, $dateihandle,$client, $sessionId) 
	{
	
			// Vorgehensweise
			// - Wenn neue(s) Bild(er) in upload_images vorhanden, zunaechst die alten verknuepfungen loeschen.
			$articlenumber=""; $new_image_name ="";
			//$newProductId=dmc_get_id_by_artno($Artikel_Artikelnr);
			
			if (DEBUGGER>=50) fwrite($dateihandle, "attach_images_to_product $Artikel_Bilddatei fuer produktID=$newProductId \n");
			// AGS -> Bilddatei ist entsprechend der articlenumber
			// Bilder dem Produkt zuordnen // attach images to product
			// Dateiname OHNE Endung		 	
			$bilddatei_name = substr($Artikel_Bilddatei,0,-4); 
			// Dateiname  Endung
			$bilddatei_extension = substr($Artikel_Bilddatei,-4,4);
			// Auf 1-10 Bilder ueberprufen
			
			for ($bildnr=0;$bildnr<10;$bildnr++) {
					// Standard ist 0 - $bilddatei
					// if (PRODUCTS_EXTRA_PIC_NAME == "ARTIKELNUMMER") $bilddatei_name = $Artikel_Artikelnr;		
					// Wenn Bild mit Artikelnummer vorhanden, verwende dieses
					if ($bildnr==0) {
						if (file_exists(IMAGE_FOLDER . $Artikel_Artikelnr.".jpg")) 
							$Artikel_Bilddatei = $Artikel_Artikelnr.".jpg";
						else
							$Artikel_Bilddatei = $bilddatei_name.$bilddatei_extension; 
					}
					if ($bildnr>=1) {
						// Achtung, zB bei Selectline ist in Bilddatei 1 _0 enthalten, daher entfernen
						$bilddatei_name = $seo_text = str_replace(array('_0.jpg'), '.jpg', $bilddatei_name);
						//fwrite($dateihandle,"Pruefe auf ".IMAGE_FOLDER .$Artikel_Artikelnr.PRODUCTS_EXTRA_PIC_EXTENSION.$bildnr.$bilddatei_extension." \n");	
					
						if (file_exists(IMAGE_FOLDER .$Artikel_Artikelnr.PRODUCTS_EXTRA_PIC_EXTENSION.$bildnr.$bilddatei_extension)) {
							$Artikel_Bilddatei = $Artikel_Artikelnr.PRODUCTS_EXTRA_PIC_EXTENSION.$bildnr.$bilddatei_extension;
						} else {
							$Artikel_Bilddatei = $bilddatei_name.PRODUCTS_EXTRA_PIC_EXTENSION.$bildnr.$bilddatei_extension; 
						}
					}
							 
					//if ($bildnr<=2 AND DEBUGGER>=50) fwrite($dateihandle,"140 - USE Image File ($bildnr) ".IMAGE_FOLDER . $Artikel_Bilddatei." (new_image_name=$new_image_name) \n");	
					if ($Artikel_Bilddatei!="") {

						// Bildupload, wenn Exportmodus uploadimages beinhaltet, Upload Images if $ExportModus has uploadimages
						// Varianten OHNE Bildzuordnung
						// Fehler abfangen, wenn bild nicht vorhanden, dann vielleicht, wenn alles klein geschrieben oder gross geschrieben
						if (!file_exists(IMAGE_FOLDER . $Artikel_Bilddatei))
							$Artikel_Bilddatei=strtoupper ($Artikel_Bilddatei);
						if (!file_exists(IMAGE_FOLDER . $Artikel_Bilddatei))
							$Artikel_Bilddatei=strtolower($Artikel_Bilddatei);
					
						if (DEBUGGER>=50 && $bildnr<2) fwrite($dateihandle,"151 - USE Image File ($bildnr) ".IMAGE_FOLDER . $Artikel_Bilddatei." (new_image_name=$new_image_name) \n");
					
						if (file_exists(IMAGE_FOLDER . $Artikel_Bilddatei)) {
									chmod(IMAGE_FOLDER . $Artikel_Bilddatei, 0644);
									// Bildgr aenern
									// resize_image(IMAGE_FOLDER, IMAGE_FOLDER_TEMP,  $Artikel_Bilddatei, 600);
									$label=$Artikelbild_Bezeichnung." ".$bildnr; 

									// gif?
									if (strtolower($bilddatei_extension)=='gif')
										$newImage = array(
												  'file' => array(
													  'content' => base64_encode(file_get_contents(IMAGE_FOLDER . $Artikel_Bilddatei)),
													  'mime'    => 'image/gif'
												  ),
												  'label'    => $label,
												  'position' => $bildnr,
												  'exclude'  => 0
											  );
									else // jpf
										$newImage = array(
											  'file' => array(
												  'content' => base64_encode(file_get_contents(IMAGE_FOLDER . $Artikel_Bilddatei)),
												  'mime'    => 'image/jpeg'
											  ),
											  'label'    => $label,
											  'position' => $bildnr,
											  'exclude'  => 0
										  );
									if ($bildnr==0) // Hauptbild
											$newImage['types'] = array('image', 'small_image', 'thumbnail');
												
									try {
										// Alte Bilder loeschen // Remove image files
										// Kontrolle bei erstem Bild // Check at first image
										if ($bildnr==0) {
											if (SHOP_VERSION>1.5) {
												// if (DEBUGGER>=50) fwrite($dateihandle,'191 delete old images first \n ');		
												if (is_file('../../userfunctions/products/dmc_api_delete_images.php')) 
													include ('../../userfunctions/products/dmc_api_delete_images.php');
												else if (is_file('../../functions/products/dmc_api_delete_images.php')) 
													include ('../../functions/products/dmc_api_delete_images.php');
												else if (is_file('../functions/products/dmc_api_delete_images.php')) 
													include ('../functions/products/dmc_api_delete_images.php');
												else if (is_file('./functions/products/dmc_api_delete_images.php')) 
													include ('./functions/products/dmc_api_delete_images.php');
												else include ('./dmc_api_delete_images.php');
											} else {
												// if (DEBUGGER>=50) fwrite($dateihandle,'202 delete old images first \n ');		
												$ergebnis = $client->call($sessionId, 'product_media.list', $newProductId);
												for ($ii=0;$ii<10;$ii++) {  
													// BILDER VORAB LOESCHEN
													if ($ergebnis[$ii]['file']!='' && strpos($ergebnis[$ii]['label'],$label)!==false) {
														$client->call($sessionId, 'product_media.remove', array($newProductId, $ergebnis[$ii]['file']));
													}
												} // end for 
											} // endif bilder loeschen
										} // end if
										
										// Neues Bild anlegen
										// if ($ergebnis[0]['file']=='' && $ergebnis[1]['file']=='') 	// Wenn nicht bereits Bild vorhanden
										$newImageFilename = $client->call($sessionId, 'product_media.create', array($newProductId, $newImage));
										// if (DEBUGGER>=50) fwrite($dateihandle,"Product image created\n" );
									} catch (SoapFault $e) {
											fwrite($dateihandle,"Product image creation failed:\n".$e."\n".$e->getMessage() );
											/*	if (IMAGE_LOG_FILE!=''&&DEBUGGER>=50) {
													$dateihandle2 = fopen(IMAGE_LOG_FILE,"a");
													fwrite($dateihandle2,'203-Session='.$sessionId."\n" );	
													fwrite($dateihandle2,'204-Product image creation failed:\n'.$e."\n".$e->getMessage() );	
													fclose($dateihandle2);
												} 	*/										
									}							
									// if (DEBUGGER>=1 && $newImageFilename!="") fwrite($dateihandle, "Product image uploaded: ".IMAGE_FOLDER . $Artikel_Bilddatei." to ".$newImageFilename."\n");
									// Bild aus Temp löschen // delete uploaded image
									// unlink(IMAGE_FOLDER . $Artikel_Bilddatei);
						} else {
							// no pic in folder
								if (IMAGE_LOG_FILE!='' && DEBUGGER>=50 && $bildnr==0) {
									$dateihandle2 = fopen(IMAGE_LOG_FILE,"a");
									if ($bildnr==1) fwrite($dateihandle2, "Product image not exists: ".IMAGE_FOLDER . $Artikel_Bilddatei."\n");	
									fclose($dateihandle2);
									if (DEBUGGER>=50 && $bildnr==0) fwrite($dateihandle, "Product image not exists: ".IMAGE_FOLDER . $Artikel_Bilddatei."\n");
								} else {
									if (DEBUGGER>=50 && $bildnr==0) fwrite($dateihandle, "Product image not exists: ".IMAGE_FOLDER . $Artikel_Bilddatei."\n");
								}
									
						} // endif
				} // end if bilddatei != ""
			} // end for
			
	} // end function attach_images_to_product
	
	function set_website_prices_1_4($Artikel_Preise, $art_id, $dateihandle,$client, $sessionId) 
	{
		fwrite($dateihandle, "set_website_prices_1_4\n");
		// Website definiert ?
			$anzahl_preise=sizeof($Artikel_Preise);
			// Update prices
			$table = "catalog_product_index_price_idx";				// Index Prices	
			$table2 = "catalog_product_index_price";				// Index Prices	
			//$table2 = "catalogindex_minimal_price";		// Min Prices
			$table3 = "catalog_product_entity_decimal";		//Prices		
			for ($i=1;$i<=$anzahl_preise;$i++) {
				// Wenn definiert in definitions_websites... 
				if (defined('WEBSITE_PRICE' . $i) && constant('WEBSITE_PRICE' . $i) != '' && 
					defined('GROUP_PRICE' . $i) && constant('GROUP_PRICE' . $i) != ''
					&& $Artikel_Preise[$i]<> '' && $Artikel_Preise[$i]>0) {
						/*	z.B.	attribute_id = 60 für Verkaufspreis
						z.B.	attribute_id = 100 und customer_group_id =0 für EK
						z.B.	attribute_id = 567 und customer_group_id =0 für Sepecial (ACHTUNG -> Alle andren Preise werden auf special Preis geändert)
						z.B.	attribute_id = 270 und qty>0 für Staffelpreis  */		
				
					if ((constant('GROUP_PRICE' . $i) == 'all') || !is_numeric(constant('GROUP_PRICE' . $i)))  {
						// alle gruppen table1
					/*	$where = "entity_id = ".$art_id." AND qty=0.00 AND attribute_id = ".MAIN_PRICE_ATTRIBUTE_ID." AND website_id =".constant('WEBSITE_PRICE' . ($i))."";
						// if ($Artikel_Steuersatz!="") $what .= ", tax_class_id = '".$Artikel_Steuersatz."'";
						$what = " value = ".$Artikel_Preise[$i]."";
						// sales price (WEBSITE)
						if (DEBUGGER>=50) fwrite($dateihandle, "update website price ".$what." where:".$where."\n");
						dmc_sql_update($table, $what, $where); */
						// alle gruppen table 2
						$where = "entity_id = ".$art_id." AND website_id =".constant('WEBSITE_PRICE' . ($i))."";
						// if ($Artikel_Steuersatz!="") $what .= ", tax_class_id = '".$Artikel_Steuersatz."'";
						$what = " price= ".$Artikel_Preise[$i].", final_price= ".$Artikel_Preise[$i].", min_price = ".$Artikel_Preise[$i].", max_price = ".$Artikel_Preise[$i]."";
						// sales price (WEBSITE)
						if (DEBUGGER>=50) fwrite($dateihandle, "update $table1  with ".$what." where ".$where."\n");
						dmc_sql_update($table1, $what, $where);
						if (DEBUGGER>=50) fwrite($dateihandle, "update $table2 with ".$what." where ".$where."\n");
						dmc_sql_update($table2, $what, $where);						
					} else {
						// bestimmte kundengruppe
						$where = "entity_id = ".$art_id." AND qty=0.00 AND attribute_id = ".MAIN_PRICE_ATTRIBUTE_ID." AND customer_group_id = ".constant('GROUP_PRICE' . ($i))." AND website_id =".constant('WEBSITE_PRICE' . ($i))."";
						// if ($Artikel_Steuersatz!="") $what .= ", tax_class_id = '".$Artikel_Steuersatz."'";
						$what = " value = ".$Artikel_Preise[$i]."";
						// sales price (WEBSITE)
						if (DEBUGGER>=50) fwrite($dateihandle, "update website price ".$what." where:".$where."\n");
						dmc_sql_update($table, $what, $where);
					}
					
				} // end if defined 	
				
				// WENN Store Price zu setzen
				if (defined('STORE_PRICE' . $i) && constant('STORE_PRICE' . $i) != ''
					&& $Artikel_Preise[$i]<> '' && $Artikel_Preise[$i]>0)  {
					$where = "entity_id = ".$art_id." AND attribute_id = ".MAIN_PRICE_ATTRIBUTE_ID." AND store_id = ".constant('STORE_PRICE' . ($i))."";
					// Price0 for customer group 0
					$what = " value = ".$Artikel_Preise[$i]."";
					// entity of price (STORE)
					// Wenn Store-Preis nicht vorhanden, diesen setzen
					if (dmc_entry_exits('value_id', $table3, $where)) {
						if (DEBUGGER>=50) fwrite($dateihandle, "update store price ".$what." where:".$where."\n");
						dmc_sql_update($table3, $what, $where." ");
					} else {
						if (DEBUGGER>=50) fwrite($dateihandle, "insert store price ".$what." where:".$where."\n");
						dmc_sql_insert($table3, 
									"(entity_type_id, attribute_id, store_id, entity_id, value)", 
									"(4, ".MAIN_PRICE_ATTRIBUTE_ID.", ".constant('STORE_PRICE' . ($i)).", ".$art_id.", ".$Artikel_Preise[$i].")");
					}
				} // end if defined
			} // end for
	} // function set_website_prices
	
	function set_website_prices_API($Artikel_Preise, $Artikel_Artikelnr, $dateihandle,$client, $sessionId) 
	{
		fwrite($dateihandle, "set_website_prices_API\n");
		// Website definiert ?
			$anzahl_preise=sizeof($Artikel_Preise);
			for ($Preisgruppe=1;$Preisgruppe<=$anzahl_preise;$Preisgruppe++) {
				$website=$Preisgruppe; // +1;
				 fwrite($dateihandle, "Artikel_Preise[$Preisgruppe]=".$Artikel_Preise[$Preisgruppe]."\n");
				// Wenn website definiert in definitions_websites
				if (defined('WEBSITE_PRICE' . $website) && constant('WEBSITE_PRICE' . $website) != '') {
					// Wenn Preis uebermittelt
					if ( $Artikel_Preise[$Preisgruppe] >0.01){  	
						// Kundengruppen fuer diesen Preis
						// TODO -> TIERPRICE, wenn Kundengruppe <> all
						$kundengruppe = constant('GROUP_PRICE' . $website);
						fwrite($dateihandle, "set_website_prices_API kundengruppe $kundengruppe Preis setzen: ".$Artikel_Preise[$Preisgruppe]." \n");
						if ($kundengruppe == 'all') {
							fwrite($dateihandle, "322 set_website_prices_API fuer Website ".constant('WEBSITE_PRICE' . $website)." ".$Artikel_Preise[$Preisgruppe]." setzen \n");
							$sql_data_price_array = array(
							'price' => $Artikel_Preise[$Preisgruppe]);
							$client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $sql_data_price_array, constant('WEBSITE_PRICE' . $website)));
							fwrite($dateihandle, "333 set_website_prices_API fuer Website ".constant('WEBSITE_PRICE' . $website)." ".$Artikel_Preise[$Preisgruppe]." gesetzt \n");
						} else {
							// TODO -> TIERPRICE, wenn Kundengruppe <> all
							$sql_data_price_array = array(
							'price' => $Artikel_Preise[$Preisgruppe]);
							$client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $sql_data_price_array, constant('WEBSITE_PRICE' . $website)));
						}
													
					} // end if
				} // end if "defined"
			} // end for
	} // function set_website_prices_API
	
	function attach_options_to_product($Artikel_ID, $Option_title, $Option_type, $Wert, $is_require, $sku, $sort_order, $dateihandle,$client, $sessionId) 
	{
		// attach_options_to_product($Artikel_ID, 'Gewünschte Farbe und Größe', 'area', '1024', false, '1000000', '', $dateihandle,$client, $sessionId) 

		fwrite($dateihandle, "function - attach_options_to_product\n");
		
		$product = Mage::getModel('catalog/product');
		$product->load($Artikel_ID);
		
		// check if options already exists
		if ($product->hasOptions==1) {
			/*$optionsArr = array_reverse($product->getOptions(), true);
			foreach ($optionsArr as $option) {
				echo '<pre>';             
				echo print_r($option->getData());
				echo '<hr>';
				foreach ($option->getValues() as $_value) {
				echo print_r($_value->getData());    
				}            
				echo '</pre><hr>';
			}	*/
			$exists=true;
		} else {
			$exists=false;
		}
	  
		if (!$exists) {
		    $opt = Mage::getModel('catalog/product_option');
		    $opt->setProduct($product);
		  
			if ($Option_type=="area")
			  $option = array(
			    //'id' => '',
			    'is_delete' => '',
			    'is_require' => $is_require,
			    //'option_id' => 0,
			    'previous_group' => '',
			    'previous_type' => '',
				'sku' => $sku,
			    'sort_order' => $sort_order,
			    'option_type_id' => '',
			    'title' => $Option_title,
			    'type' => 'area',
				'price' => '',
			     'price_type' => 'fixed', // 'percent'
				 'max_characters' => $Wert
			    );
		    else //  if ($Option_type=="drop_down")
			    $option = array(
			    //'id' => '',
			    'is_delete' => '',
			    'is_require' => $is_require,
			    //'option_id' => 0,
			    'previous_group' => '',
			    'previous_type' => '',
			    'sort_order' => $sort_order,
			    'option_type_id' => '',
			    'title' => $Option_title,
			    'type' => $Option_type,
			    'values' => array(
			            array(
			            'is_delete' => false,
			            //'option_type_id' => '',
			            'price' => '',
			            'price_type' => 'fixed', // 'percent'
			            'sku' => $sku,
			            'sort_order' => '',
			            'title' => $Wert
			             ),
			        /*     array(
			            'is_delete' => false,
			            //'option_type_id' => '',
			            'price' => '',
			            'price_type' => 'fixed', // 'percent'
			            'sku' => '',
			            'sort_order' => '',
			            'title' => "drop down 2",
			             ), */
			          ),
			    );
		    
		    $opt->addOption( $option);
		    $opt->saveOptions( ); 
		} // end if (!$exists) 
		return $exists;
	} // end function attach_options_to_product

	function get_options_from_product($Artikel_ID, $sort_order, $dateihandle,$client, $sessionId) 
	{
		
		if (DEBUGGER>=1) fwrite($dateihandle, "function - get_options_from_product\n");
		
		$product = Mage::getModel('catalog/product');
		$product->load($Artikel_ID);
	     
		//echo 'hasCustomOptions: '.$product->hasOptions.'<br>';
	    if ($product->hasOptions==1) {
			$optionsArr = array_reverse($product->getOptions(), true);
			/*foreach ($optionsArr as $option) {
				echo '<pre>';             
				echo print_r($option->getData());
				echo '<hr>';
				foreach ($option->getValues() as $_value) {
				echo print_r($_value->getData());    
				}            
				echo '</pre><hr>';
			}	*/
		}
		
		return $optionsArr;
	} // end function get_options_from_product

	// dmc_set_attribute_store_label - einem attribute eine bezeichnung fuer einen store zuweisen
	function dmc_set_attribute_store_label($attribute_id, $store_id, $label) 
	{
		fwrite($dateihandle, "dmc_set_attribute_store_label attribute_id=$attribute_id, store_id=$store_id, label=$label \n");
		
		// Website definiert ?
		$table = "eav_attribute_label";				// Prices	
		$where = "attribute_id = ".$attribute_id." AND store_id =".$store_id."";
		 
		$what = " value = '".$label."'";

		if (dmc_entry_exits('value', $table, $where)) {
			dmc_sql_update($table, $what, $where." ");
		} else {
			dmc_sql_insert($table, 
				"(attribute_id, store_id, value)", 
				"($attribute_id, $store_id, '$label')");
		}
	} // function dmc_set_attribute_store_label

	// dmc_generate_attribute_code - Code für Attribute generieren 
	function dmc_generate_attribute_code($seo_text)
	{
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_art_functions - dmc_generate_attribute_code = $seo_text\n");
				
		$d1 = array(" ","Ä", "Ö", "Ü", "ä" , "ö", "ü", "ß","�", "�", "�", "�" , "�", "�", "�","<",">","#","\"","'","´",",","&","²","?",";");
		$d2 = array("_", "Ae","Oe","Ue","ae","oe","ue","sz","Ae","Oe","Ue","ae","oe","ue","sz","_","_","_","_","_","_","_","_","2","_","_");
		$seo_text = str_replace($d1, $d2, $seo_text);		 
		$d1 =  array(' ', 'í', 'ý', 'ß', 'ö', 'ô', 'ó', 'ò', 'ä', 'â', 'à', 'á', 'é', 'è', 'ü', 'ú', 'ù', 'ñ', 'ß', '²', '³', '@', '€', '$');
		$d2 = array('_', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$seo_text = str_replace($d1, $d2, $seo_text);
		$d1 =  array('&amp;', '&quot;', '&', '"', "'", '¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '=', 'µ', '#', '~', '"', '§', '%', '|', '°', '^');
		$seo_text = str_replace($d1, '', $seo_text);
		$d1 =  array('/', 'Ø', '°', '-');
		$seo_text = str_replace($d1, 'o', $seo_text);
		$d1 = array(" ","Ã„", "Ã–", "Ãœ", "Ã¤" , "Ã¶", "Ã¼", "ÃŸ","Ä", "Ö", "Ü", "ä" , "ö", "ü", "ß","<",">","#","\"","'","Â´",",","&","Â²","?",";");
		$d2 = array("_", "Ae","Oe","Ue","ae","oe","ue","sz","Ae","Oe","Ue","ae","oe","ue","sz","_","_","_","_","_","_","_","_","2","_","_");
		$seo_text = str_replace($d1, $d2, $seo_text);		 
		$d1 =  array(' ', 'Ã­', 'Ã½', 'ÃŸ', 'Ã¶', 'Ã´', 'Ã³', 'Ã²', 'Ã¤', 'Ã¢', 'Ã ', 'Ã¡', 'Ã©', 'Ã¨', 'Ã¼', 'Ãº', 'Ã¹', 'Ã±', 'ÃŸ', 'Â²', 'Â³', '@', 'â‚¬', '$');
		$d2 = array('_', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$seo_text = str_replace($d1, $d2, $seo_text);
		$d1 =  array('&amp;', '&quot;', '&', '"', "'", 'Â¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '=', 'Âµ', '#', '~', '"', 'Â§', '%', '|', 'Â°', '^');
		$seo_text = str_replace($d1, '', $seo_text);
		$d1 =  array('/', 'Ã˜', 'Â°', '-');
		$seo_text = str_replace($d1, '_', $seo_text);
		$seo_text = str_replace(array('----', '---', '--'), '_', $seo_text);
		$seo_text = strtolower($seo_text);
		
		// $code = dmc_prepare_seo_name($Merkmale[$Anz_Merkmale],'DE');
		// ACHTUNG: Code darf nur max 30 Stellen bei magento lang sein und nicht mit _ beginnen
		$seo_text = substr($seo_text,0,30);
		if (substr($code,0,1) == '_') {
			$seo_text = substr($seo_text,1,256);
		}
		
		// return urlencode($seo_text);
		return ($seo_text);
	} // end function dmc_generate_attribute_code

	// Kundengruppenpreis setzen
	function dmc_set_group_price($group_id,$art_id,$website_id,$store_id,$price) {
		global $dateihandle;
		// if (DEBUGGER>=1) 
		fwrite($dateihandle, "dmc_art_functions - dmc_set_group_price = $group_id,$art_id,$website_id,$store_id,$price\n");
		try {
			$produkt = Mage::getModel('catalog/product')->setStoreId($store_id)->load($art_id);
			$produkt->setData('group_price',array (
				 array (
					 "website_id" => $website_id,
					 "cust_group" => $group_id,
					 "price" => $price
				 )));
				// fwrite($dateihandle, "531");
			$produkt->save();
			//	fwrite($dateihandle, "533");
		} catch (Exception $e) {
			//		if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER - ".$e."\n");
					return false;
		}
		return true;
	}
	
	function dmc_set_group_tier_price_fast($group_id,$Artikel_Artikelnr,$art_id,$website_id,$store_id,$price,$fromqty) {
		global $dateihandle;
		// if (DEBUGGER>=1) 
		fwrite($dateihandle, "dmc_art_functions - dmc_set_group_tier_price_fast = $group_id,$Artikel_Artikelnr,$art_id,$website_id,$store_id,$price,$fromqty\n");
		try {
			$product = Mage::getModel('catalog/product');
			Mage::app()->setCurrentStore($store_id);
			$product->setStoreId($store_id);
			
			$product->load($art_id);

			// erforderliche Werte für Gruppenpreise
			$updateProductData ['sku'] = $Artikel_Artikelnr;
			if ($website_id==0) 
				$updateProductData ['_tier_price_website'] = 'all';
			else
				$updateProductData ['_tier_price_website'] = $website_id;
			if ($group_id==0) 
				$updateProductData ['_tier_price_customer_group'] = 'all';
			else
				$updateProductData ['_tier_price_customer_group'] = $group_id;
			$updateProductData ['_tier_price_qty'] = $fromqty;
			$updateProductData ['_tier_price_price'] = $price;
						
			//	$updateProductData ['_product_websites'] = 'base';
			//	if ($store_view=='sigmatherm') $updateProductData ['_product_websites'] = 'SigmaTherm';
			//$updateProductData ['_store'] = $store_view;
				
			$createData = array ( $updateProductData );		// Mehrere waeren moeglich
					
			try {
				Mage::getSingleton('fastsimpleimport/import')
					->setPartialIndexing(true)
					//  ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
					->processProductImport($createData); 
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Gruppenpreis gesetzt\n");
			}
			catch (Mage_Core_Exception $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER 575 - siehe ERROR LOG:".$e->getMessage()."\n");
				dmc_write_error("dmc_art_functions", "dmc_set_specials", "576", "Artikelid:".$Artikel_Artikelnr." -> ".$e->getMessage(), true, true, $dateihandle);
			}
			catch (Exception $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER 580 - siehe ERROR LOG:".$e->getMessage()."\n");
				dmc_write_error("dmc_art_functions", "dmc_set_specials", "580",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
			}
		} catch (Exception $e) {
					if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER - :".$e->getMessage()."\n");
					return false;
		}
		return true;
	}
	
	function dmc_set_group_pricetest($group_id,$art_id,$website_id,$store_id,$price) {
		echo  "dmc_art_functions - dmc_set_group_price = $group_id,$art_id,$website_id,$store_id,$price\n";
		try {
			$product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($art_id);
			$product->setData('group_price',array (
				 array (
					 "website_id" => $website_id,
					 "cust_group" => $group_id,
					 "price" => $price
				 )));
			$product->save();
		} catch (Exception $e) {
					echo "FEHLER - ".$e."\n";
					return false;
		}
		echo "Okay";
		return true;
	}
	
	// Aktiv/Passiv setzen ber DATENBANK
	function dmc_set_product_status_db($art_id,$Artikel_Status,$store_id) {
		// TODO -> ,$store_id unterstuetzen
		$STATUS_ATTRIBUTE_ID=dmc_get_attribute_id_by_attribute_code($attr_type_id,'status');		//std 84 
		if ($STATUS_ATTRIBUTE_ID==-1) $MAIN_PRICE_ATTRIBUTE_ID=84;
		if ($Artikel_Status==1) $Artikel_Status=1; else $Artikel_Status=2;		// 1 = aktiv, 2=passiv
		// Status setzen
		$table='catalog_product_entity_int';
		$where = "entity_id = ".$art_id." AND attribute_id=".$STATUS_ATTRIBUTE_ID;
		$what = " value = ".$Artikel_Status."";
		dmc_sql_update($table, $what, $where);	
		// Status auf FLAT setzen
		//$table='catalog_product_flat_1';
		//$where = "entity_id = ".$art_id." ";
		//$what = " price = ".$Artikel_Preise[0]."";
		//dmc_sql_update($table, $what, $where);	
		// if (DEBUGGER>=1) fwrite($dateihandle, "Product Status updated to ".$Artikel_Status."\n");							
	} 
	
	// Art des Attributes (text, select, multiselect) zurueckgeben
	function dmc_get_attribute_type ($attribute_name)
	{
		return dmc_sql_select_value('frontend_input', 'eav_attribute', "attribute_code='".$attribute_name."'");
	} // function dmc_get_attribute_type
	
	// Die ID des dem Simple zugehoerigen conf products ermitteln
	function dmc_get_attached_conf_id($simple_id) {
		global  $dateihandle;
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_attached_conf_id($simple_id)\n");				
		return dmc_sql_select_value('parent_id', 'catalog_product_super_link', "product_id='".$simple_id."'");		
	} // end function dmc_get_attached_conf_id
	
	//  Preis des conf products ermitteln	
	function dmc_get_conf_price($conf_id,$store_id) {
		global  $dateihandle;
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_conf_price($conf_id,$store_id \n");				
		if ($store_id=="") 
			return dmc_sql_select_value('value', 'catalog_product_entity_decimal', "attribute_id=(".MAIN_PRICE_ATTRIBUTE_ID.") AND entity_id='".$conf_id."' LIMIT 1");
		else
			return dmc_sql_select_value('value', 'catalog_product_entity_decimal', "attribute_id=(".MAIN_PRICE_ATTRIBUTE_ID.") AND entity_id='".$conf_id."' AND store_id=$store_id");		
	} // end function dmc_get_conf_price
	
	//  Preis des conf products updaten, wenn preis des conf = 0	
	function dmc_set_conf_price_by_simple_price($conf_id,$simple_id,$simple_price,$store_id) {
		global  $dateihandle;
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_set_conf_price_by_simple_price($conf_id,$simple_id,$simple_price,$store_id)\n");
		$conf_price=dmc_get_conf_price($conf_id,$store_id);
		// Wenn Preis upzudaten
		if ($conf_price==0) {
			if ($store_id=="") 
				dmc_sql_update('catalog_product_entity_decimal', 'value='.$simple_price, "attribute_id=(".MAIN_PRICE_ATTRIBUTE_ID.") AND entity_id='".$conf_id." AND store_id=".$store_id."'");
			else
				dmc_sql_update('catalog_product_entity_decimal', 'value='.$simple_price, "attribute_id=(".MAIN_PRICE_ATTRIBUTE_ID.") AND entity_id='".$conf_id." ");
		}
	} // end function dmc_set_conf_price_by_simple_price
	
?>