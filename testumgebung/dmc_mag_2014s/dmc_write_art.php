<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for magento shop											*
*  dmc_write_art.php														*
*  Artikel schreiben														*
*  Copyright (C) 2008-12 DoubleM-GmbH.de									*
*                                                                       	*
*****************************************************************************/
/*
30.03.09
- Kategorie Zuordnung über Eintrag keywords der Kategorie
- Aenderungsdatum_SUPERATTRIBUTE uebergeben fuer configurable products
5.06.10
- nicht übergebene attribute werden durch zuweisung und abfrage aussortiert   $AuspraegungenID[$Anz_Merkmale]!='280273'
23.09.2010
- Update BasePrice
30.11.2010
- AKTIV=delete, dann produkt loeschen
04.01.2011
- $Artikel_Startseite wird verwendet fuer news_form_Date und news_to_date ( heute bis heute+1Woche)
23.12.2011
- Groesstenteils Umstellung von SKU auf IDs fuer Update etc
05.01.2012
- Unterstuetzung von Multi-Feldern -> Auspraegungen durch | getrennt, z.B. @Hersteller@ mit @BMW|AUDI|VW@
02.03.2012
- Unterstuetzung von Mindestverkaufsmenge -> min_sale_qty (muss als Attribut mit dem simple product uebergeben werden)
08.10.2013
- Unterstuetzung von Bildtext in $Artikel_Bild, getrennt durch @
*/
 
	defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );
	
	function dmc_write_art($StoreView='default',$client, $sessionId) {
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_write_art\n");
		
		/*if (is_file('../app/Mage.php')) include ('../app/Mage.php');
		else if (is_file('../../app/Mage.php')) include ('../../app/Mage.php');
		else if (is_file('../../../app/Mage.php')) include ('../../../app/Mage.php');
		else include ('./app/Mage.php');
		Mage::app(); */
	
		//	Mage::getSingleton('core/session', array('name' => 'frontend'));
		// Gepostete Werte ermitteln
		if (is_file('userfunctions/products/dmc_get_posts.php')) include ('userfunctions/products/dmc_get_posts.php');
		else include ('functions/products/dmc_get_posts.php');
		// Pruefen ob Artikel existent und ggfls magento id ermitteln
		// get Magento article ID 
		if ($Artikel_Typ == "bundle") { // bundle product
			$art_id=dmc_get_id_by_artno("B_".$Artikel_Artikelnr);
 		} else { 
			$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);
 		} 
		// (vorab) loeschen? / delete (first)? / $Aktiv == 'loeschen' || $Aktiv == 'delete'
		if ($art_id != "")
			if (is_file('userfunctions/products/dmc_delete_first.php')) include ('userfunctions/products/dmc_delete_first.php');
			else  include ('functions/products/dmc_delete_first.php');

		// Mapping von Produktgruppen MAP_PRODUCT_GROUPS_BY_ARTNR etc
		if (is_file('userfunctions/products/dmc_mappings.php')) include ('userfunctions/products/dmc_mappings.php');
		else include ('functions/products/dmc_mappings.php');

		// (super)attribute und deren werte durch ermittlung magento IDs mappen und ggfls anlegen
		//  SUPERATTRIBUTE getrennt mit @ uebergeben fuer configurable products
	    $AuspraegungenID[0] ="";
		if ($Artikel_Merkmal!="")
			if (is_file('userfunctions/products/dmc_map_attributes.php')) include ('userfunctions/products/dmc_map_attributes.php');
			else include ('functions/products/dmc_map_attributes.php');
		if ($Artikel_Typ=='configurable')
			if (is_file('userfunctions/products/dmc_map_super_attributes.php')) include ('userfunctions/products/dmc_map_super_attributes.php');
			else include ('functions/products/dmc_map_super_attributes.php');
		 
		// KategorieID ermitteln oder uebergebene verwenden
		if (is_file('userfunctions/products/dmc_generate_cat_id.php')) include ('userfunctions/products/dmc_generate_cat_id.php');
		else include ('functions/products/dmc_generate_cat_id.php');
	
		// HerstellerID ermitteln / ggfls eintragen
		if (!is_numeric($Hersteller_ID) && $Hersteller_ID!="") 
			if (is_file('userfunctions/products/dmc_generate_manuf_id.php')) include ('userfunctions/products/dmc_generate_manuf_id.php');
			else include ('functions/products/dmc_generate_manuf_id.php');
		  
		if (DEBUGGER>=1) {
			fwrite($dateihandle, "*********** ArtNr ".$Artikel_Artikelnr." mit Preis ".$Artikel_Preis." fuer Kategorie[0] ".$Kategorie_IDs[0]." schreiben:\n");
			$i=1;
			if (DEBUGGER>=50) fwrite($dateihandle, "Artikel_Bezeichnung = ".$Artikel_Bezeichnung." mit Desc(Anfang)=".substr($Artikel_Text,0,200)."\n");	
			if ($Artikel_Variante_Von != ""){
				if (DEBUGGER>=50) fwrite($dateihandle, "Artikel_Variante_Von - ".$Artikel_Variante_Von." mit Ausprägung: ".$Artikel_Auspraegung." size=$AuspraegungenID[0] bzw $Merkmale[0] und color=$AuspraegungenID[1] bze $Merkmale[1]");
				fwrite($dateihandle, "\n");		
			}
			fwrite($dateihandle, "**************\n");
		}		
		
		// Ggfls Arrays fuer Kundengruppenpreise fuellen / Fill customer group price arrays
		if (is_file('userfunctions/products/dmc_group_prices_arrays.php')) include ('userfunctions/products/dmc_group_prices_arrays.php');
		else include ('functions/products/dmc_group_prices_arrays.php');
		fwrite($dateihandle, "******95*****\n");
		// if not exists
		if ($art_id=="") {
			// get attribute set - Attribute Set ermitteln
			$attributeSets = $client->call($sessionId, 'product_attribute_set.list');
			$set = current($attributeSets);
			fwrite($dateihandle, "******99*****\n");

			// check for product type: 0 is a simple product ; 1 is a configurable product
			if ($Artikel_Typ == "configurable")  // configurable product	
			{
				// Arrays zur Anlage configurable product einlesen / Fill array for configurable product
				if (is_file('userfunctions/products/dmc_array_create_conf.php')) include ('userfunctions/products/dmc_array_create_conf.php');
				else include ('functions/products/dmc_array_create_conf.php');
			} else if ($Artikel_Typ == "grouped")  // grouped product	
			{
				// Arrays zur Anlage grouped product einlesen / Fill array for grouped product
				if (is_file('userfunctions/products/dmc_array_create_grouped.php')) include ('userfunctions/products/dmc_array_create_grouped.php');
				else include ('functions/products/dmc_array_create_grouped.php');
			} else if ($Artikel_Typ == "downloadable")  // downloadable product	
			{
				// Arrays zur Anlage download product einlesen / Fill array for download product
				if (is_file('userfunctions/products/dmc_array_create_downloadable.php')) include ('userfunctions/products/dmc_array_create_downloadable.php');
				else include ('functions/products/dmc_array_create_downloadable.php');
			} else if ($Artikel_Typ == "bunlde")  // downloadable product	
			{
				// Arrays zur Anlage bundle product einlesen / Fill array for bundle product
				// hier ERST WEITER UNTEN gemeinsam mit create function
			} else { // simple product
				// Arrays zur Anlage grouped product einlesen / Fill array for grouped product
				if (SHOP_VERSION<1.5) 
					if (is_file('userfunctions/products/dmc_array_create_simple14.php')) include ('userfunctions/products/dmc_array_create_simple14.php');
					else include ('functions/products/dmc_array_create_simple14.php');
				else 
					if (is_file('userfunctions/products/dmc_array_create_simple.php')) include ('userfunctions/products/dmc_array_create_simple.php');
					else include ('functions/products/dmc_array_create_simple.php');
				fwrite($dateihandle, "******120 Bez $Artikel_Bezeichnung mit ".$Auspraegung_Name[0]." *****\n");
			}; // end if conf/grouped/simple Product
			// Startseiten Unterstuetzung mit $Artikel_Startseite = Anzahl der Tage
			if ($Artikel_Startseite!='' && is_numeric($Artikel_Startseite)
				&& $Artikel_Startseite>0 && SHOP_VERSION>1.3) {
				$heute=date("Y-m-d")." 00:00:00";
				$tage=$Artikel_Startseite;
				$hours = $tage * 24;
			    $added = ($hours * 3600)+time();
			    $month = date("m", $added);
			    $day = date("d", $added);
			    $year = date("Y", $added);
				$plusTage = "$year-$month-$day 00:00:00";
				$newProductData ['news_from_date'] = $heute;
				$newProductData ['news_to_date'] = $plusTage;
			} else {
				$newProductData ['news_from_date'] = '';
				$newProductData ['news_to_date'] = '';
			}
			// Create new product and return product id
			try {
			    // create product
			   if ($Artikel_Typ == "configurable") { // configurable product	
					if (SHOP_VERSION<1.5) 
						if (is_file('userfunctions/products/dmc_api_create_conf14.php')) include ('userfunctions/products/dmc_api_create_conf14.php');
						else include ('functions/products/dmc_api_create_conf14.php');
					else 
						if (is_file('userfunctions/products/dmc_api_create_conf.php')) include ('userfunctions/products/dmc_api_create_conf.php');
						else include ('functions/products/dmc_api_create_conf.php');
				} else if ($Artikel_Typ == "grouped") {  // grouped product	
					if (SHOP_VERSION<1.5) 
						if (is_file('userfunctions/products/dmc_api_create_simple14.php')) include ('userfunctions/products/dmc_api_create_simple14.php');
						else include ('functions/products/dmc_api_create_simple14.php');
					else 
						if (is_file('userfunctions/products/dmc_api_create_simple.php')) include ('userfunctions/products/dmc_api_create_simple.php');
						else include ('functions/products/dmc_api_create_simple.php');
										
				} else  if ($Artikel_Typ == "downloadable") { // downloadable product
						if (is_file('userfunctions/products/dmc_api_create_downloadable.php')) include ('userfunctions/products/dmc_api_create_downloadable.php');
						else include ('functions/products/dmc_api_create_downloadable.php');
						// Link des Downloadproduktes hinzufügen
						// -> über set_details_funktion
				} else  if ($Artikel_Typ == "bundle") { // bundle product
						// hier gemeinsam mit array function
						if (is_file('userfunctions/products/dmc_array_create_bundle.php')) include ('userfunctions/products/dmc_array_create_bundle.php');
						else include ('functions/products/dmc_array_create_bundle.php');			
				} else  if ($Artikel_Typ == "simple") { // simple product
					if (SHOP_VERSION<1.5) 
						if (is_file('userfunctions/products/dmc_api_create_simple14.php')) include ('userfunctions/products/dmc_api_create_simple14.php');
						else include ('functions/products/dmc_api_create_simple14.php');
					else 
						if (is_file('userfunctions/products/dmc_api_create_simple.php')) include ('userfunctions/products/dmc_api_create_simple.php');
						else include ('functions/products/dmc_api_create_simple.php');
					// simple product dem configurable/grouped zuordnen - add simple product to configurable product
					if ($Artikel_Variante_Von!="" && $newProductId != 28021973) {
						//  Preis des conf products updaten, wenn preis des conf = 0
						$conf_id=dmc_get_id_by_artno($Artikel_Variante_Von);
						if ($conf_id=="") {
							if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER 191: Conf product nicht gefunden");							
						} else {
							if (dmc_get_conf_price($conf_id,$store_id)==0) {
								$neues_conf=true;
								dmc_set_conf_price_by_simple_price($conf_id,$newProductId,$Artikel_Preis,$store_id);
							}
							// Simple dem Conf zuweisen	
							attach_simple_to_conf($Artikel_Artikelnr, $newProductId, $Artikel_Variante_Von, $MerkmaleID, $Artikel_Preis,$client, $sessionId);
						}
					} // end if Artikel_Variante_Von
					// update fuer baseprice
					if ($basePrice) {
						try {
							// update product base price
							$basePriceUpdate = array(	
								'base_price_amount' => $base_price_amount,
								'base_price_unit' => $base_price_base_unit,
								'base_price_base_amount' => $base_price_base_amount,
								'base_price_base_unit' => $base_price_unit
							);
							if (DEBUGGER>=1) fwrite($dateihandle, "basePriceUpdate['base_price_amount'] = $base_price_amount;
							basePriceUpdate['base_price_unit'] = $base_price_base_unit;
							basePriceUpdate['base_price_base_amount'] = $base_price_base_amount;
							basePriceUpdate['base_price_base_unit'] = $base_price_unit;
							Artikelnummer= $Artikel_Artikelnr");
							// Wenn keine Art_ID vorhanden, dann $newProductId ?
							if ($art_id=='') $art_id=$newProductId;
							if ($client->call($sessionId, 'product.update', array($art_id, $basePriceUpdate)))	
									$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
							else $newProductId = 28021973;	// no update possible						
						
						} catch (SoapFault $e) {
							dmc_write_error("dmc_write_art", "product baseprice", "195", "Artikelnummer: ".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
						}
						// if (DEBUGGER>=1) fwrite($dateihandle, "Product BASEPRICE Update 634 dmc_write_art newProductId=".$newProductId."\n");
					} // end basePrice	
					// Bestand des Artikels und aktiv setzen - Update stock info
					if ($newProductId != 28021973 && SHOP_VERSION<1.5) {
						$stockdata = array (
									'manage_stock'=>1,
									'use_config_manage_stock'=>1,
						//			'qty'=>$Artikel_Menge,  
									'is_in_stock'=>1
								);  // end newConfData
			
						$client->call($sessionId, 'product_stock.update', array($Artikel_Artikelnr,  $stockdata));
						dmc_sql_update("cataloginventory_stock_status", "stock_status=1", "product_id=".$newProductId);
					}
				} // end simple product
			} catch (SoapFault $e) {
				// if (DEBUGGER>=1) print_array($newProductData);
					dmc_write_error("dmc_write_art", "product.create", "216", "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
			} catch (Exception $e) {

				// if (DEBUGGER>=1) print_array($newProductData);
					dmc_write_error("dmc_write_art", "product.create", "217", "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_write_art newProductId=".$newProductId."\n");
			
			if ($newProductId=="") {
				fwrite($dateihandle, "ABBRUCH dmc_write_art, da Produktanlage fehlgeschlagen \n");
			} else {
				 if (DEBUGGER>=50) fwrite($dateihandle, "253 Anzahl Kategorie_IDs= ".sizeof($Kategorie_IDs)." ($newProductId,$Kategorie_IDs,$Sortierung).\n");
				if ($newProductId != 28021973)
					// Kategoriezuordnungen ergaenzen / notwendig bei AvS product import
					dmc_attach_cat_ids($newProductId,$Kategorie_IDs,$Sortierung);
			
				// Bildupload, wenn Exportmodus uploadimages beinhaltet, Upload Images if $ExportModus has uploadimages
				// Varianten OHNE Bildzuordnung   AND $Artikel_Variante_Von=="" 
				//if (!$art_already_exists && (ATTACH_IMAGES  || (!$SkipImages && strpos($ExportModus, 'uploadimages') && $Artikel_Bilddatei != ""))) 
				//	{		
					// Bilder hinzufügen mit Unterstuetzung von Artikelbild Text
					if (preg_match('/@/', $Artikel_Bilddatei)) {
						$Bild_Infos = explode ( '@', $Artikel_Bilddatei);
						$Artikel_Bilddatei=$Bild_Infos[0];	
						if ($Bild_Infos[1] != "")
							$Artikelbild_Bezeichnung=$Bild_Infos[1];
						else 
							$Artikelbild_Bezeichnung=$Artikel_Bezeichnung;
					} else {
						$Artikelbild_Bezeichnung=$Artikel_Bezeichnung;
					}
					
					attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $newProductId, $dateihandle, $client, $sessionId); 	
					// Simple Bild auch dem Conf zuordnen
					if ($Artikel_Variante_Von!="" && $newProductId != 28021973 && $conf_id!='' && $neues_conf==true) {
						attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $conf_id, $dateihandle, $client, $sessionId); 
					}						
				//	} // end (if) Upload Images
			} // end if 					
		} else { 
				// Artikel Update Modus // product update mode
				// if (DEBUGGER>=1) fwrite($dateihandle, "Product with sku ".$Artikel_Artikelnr." already exists -> ");
				// Update Artikel
			//	$attributeSets = $client->call($sessionId, 'product_attribute_set.list');
			//	$set = current($attributeSets);
				if (DEBUGGER>=1) fwrite($dateihandle, "UPDATE Artikel mit SKU = $Artikel_Artikelnr und Kategorie_ID ".$Kategorie_IDs[0]." \n");	
		
				$updateProductData = array(				    
						//		 'product_id' => 1,
					//	  'set' => $attribute_set_id, 
					//  'categories' => $Kategorie_IDs, // $Kategorie_IDs, // -> neue Funktion seit April 2013 : dmc_attach_cat_ids
					/*	 'websites' => Array
								        (
								                 '0' => 1
								        ), */
			         'updated_at' => 'now()',
			       //  'created_at' => 'now()',
					 'name' => $Artikel_Bezeichnung,
//			        'description' => $Artikel_Text,
//			         'short_description' => $Artikel_Kurztext,				 
			         'weight' => $Artikel_Gewicht,
//			         'status' => $Aktiv,
			         // 'visibility' => $Artikel_Status,				// siehe etwas weiter unten
//					'delivery_time'=>$Artikel_Lieferstatus,					 
//			         'tax_class_id' => $Artikel_Steuersatz,
//					 'tier_price' => $Kundengruppenpreise,
				 //   'meta_title' => $Artikel_MetaTitle,
			      //   'meta_keyword' => $Artikel_MetaKeywords,
			      //   'meta_description' => $Artikel_MetaDescription,
			         'qty'=>$Artikel_Menge, 
					 'is_in_stock'=>1,
					 'manufacturer' => $Hersteller_ID				
				);  // end updateProductData
				
				// KEIN Update auf den Preis, wenn Preis = 0, zB bei Conf, die den min Preis vom simple bekommen.
	//			if ($Artikel_Preis>0)
	//				$updateProductData['price'] = $Artikel_Preis;
								
				
				// Wenn Auspragungen und Merkmale zum Produkt übergeben wurden
				$update_merkmale=true;
				if ($Artikel_Merkmal!="" && $update_merkmale)
					for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
					{
						// if (DEBUGGER>=1) fwrite($dateihandle, "321 - Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
					//	if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
					//		&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
					//		&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit") 
						// PYUA - nur model abgleichen
						if ($Merkmale[$Anz_Merkmale]=="model")
						{
								if (DEBUGGER>=1) fwrite($dateihandle, "325 - Simple zuweisen: ".$Merkmale[$Anz_Merkmale]." = ".$AuspraegungenID[$Anz_Merkmale]."\n");	
								// ACHTUNG bei MAGENTO API nicht die ID verwenden !!!
								// OptionsID statt Optionswert nur für DropDown (select) Werte erforderlich
								if (strpos(dmc_get_attribute_type ($Merkmale[$Anz_Merkmale]), 'select') !== false) {
									//$Auspraegungen[$Anz_Merkmale]=$AuspraegungenID[$Anz_Merkmale];
									 $AuspraegungenID[$Anz_Merkmale] = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], 
										$Auspraegungen[$Anz_Merkmale],$store_id);	
									 $Auspraegungen[$Anz_Merkmale]=$AuspraegungenID[$Anz_Merkmale];
								}
								$updateProductData[$Merkmale[$Anz_Merkmale]]=$Auspraegungen[$Anz_Merkmale];
								if (DEBUGGER>=1) fwrite($dateihandle, "322-Merkmale zuweisen: ".$Merkmale[$Anz_Merkmale]."=".$updateProductData[$Merkmale[$Anz_Merkmale]]."\n");		
						} // end if
						
						
					} // end for
					// Wenn Auspragungen und Merkmale zum Produkt übergeben wurden
			
				// update product
				try {
					if ($Artikel_Typ == "configurable") { // configurable product	
						// existing article
						if ($art_id=='') $art_id=$newProductId;
						if ($client->call($sessionId, 'product.update', array($art_id, $updateProductData)))	
								$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
						else $newProductId = 28021973;	// no update possible				
						
						// super attribute aktualisieren ; update super attribte
						if ($newProductId!="") {
							$table = "catalog_product_super_attribute";
							$columns = "(`product_id` ,`attribute_id` ,`position`)";
							
							for ( $i = 0; $i < count ( $SuperattributeID ); $i++ )
							{
								$values = "('".$newProductId."', '".$SuperattributeID[$i]."', '0')";			// 80 = color
								if (!dmc_entry_exits("product_super_attribute_id", "catalog_product_super_attribute", " product_id='".$newProductId."' and attribute_id='".$SuperattributeID[$i]."'")) {
									if (DEBUGGER>=1) fwrite($dateihandle, "Update - Set Superattribute: ".$table." / ".$columns." / ".$values."\n");
									dmc_sql_insert($table, $columns, $values);	
								}
							} // end for
																	
						}
					} else { // NOT configurable 
						// existing article
						// Wenn Mindestbestellmenge verarbeitet werden soll, wird aus map_attributes  $stock_data als Grundlage verwendet 
						// allgem Bestandverwaltung
						$stock_data['manage_stock'] = 1;
						$stock_data['use_config_manage_stock'] = 1;
						$stock_data['qty'] = $Artikel_Menge;
						$stock_data['is_in_stock'] = 1;
						$updateProductData['stock_data'] = $stock_data;
				
						if ($art_id=='') $art_id=$newProductId;
						if ($client->call($sessionId, 'product.update', array($art_id, $updateProductData)))	
								$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
						else $newProductId = 28021973;	// no update possible						
						if (DEBUGGER>=1) fwrite($dateihandle, "404  Simple Product $Artikel_Artikelnr updated: $newProductId (Variante von= $Artikel_Variante_Von) mit Sichtbarkeit .".$updateProductData ['visibility'].".\n");
						
						
					} // end if
				} catch (SoapFault $message) {
					dmc_write_error("dmc_write_art", "product.create", "345", "Artikelnummer:".$Artikel_Artikelnr." -> ".$message, true, true, $dateihandle);
				}				

				// if (DEBUGGER>=50) fwrite($dateihandle, "Anzahl Kategorie_IDs= ".sizeof($Kategorie_IDs)." .\n");
				//if ($newProductId != 28021973)
			 		// Kategoriezuordnungen ergaenzen / notwendig bei AvS product import
				//	dmc_attach_cat_ids($newProductId,$Kategorie_IDs,$Sortierung);
				// if (DEBUGGER>=1) fwrite($dateihandle, "Product updated with Artikel_Merkmal = $Artikel_Merkmal in dmc_write_art with newProductId=".$newProductId."\n");
			
				if ($Artikel_Variante_Von!="" && $newProductId != 28021973) {
					//  Preis des conf products updaten, wenn preis des conf = 0
					$conf_id=dmc_get_id_by_artno($Artikel_Variante_Von);
					if ($conf_id=="") {
						if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER 424: Conf product nicht gefunden");							
					} else {
						if (dmc_get_conf_price($conf_id,$store_id)==0) {
							$neues_conf=true;
							dmc_set_conf_price_by_simple_price($conf_id,$newProductId,$Artikel_Preis,$store_id);
						}
						// Simple dem Conf zuweisen	
						attach_simple_to_conf($Artikel_Artikelnr, $newProductId, $Artikel_Variante_Von, $MerkmaleID, $Artikel_Preis,$client, $sessionId);
					}
				} // end if Artikel_Variante_Von
						
				// Bildupload, wenn Exportmodus uploadimages beinhaltet, Upload Images if $ExportModus has uploadimages
				// Varianten OHNE Bildzuordnung
				
				// if (UPDATE_IMAGES && ATTACH_IMAGES) {
				// Bilder hinzufügen mit Unterstuetzung von Artikelbild Text
				if (preg_match('/@/', $Artikel_Bilddatei)) {
				    $Bild_Infos = explode ( '@', $Artikel_Bilddatei);
					$Artikel_Bilddatei=$Bild_Infos[0];	
					if ($Bild_Infos[1] != "")
						$Artikelbild_Bezeichnung=$Bild_Infos[1];
					else 
						$Artikelbild_Bezeichnung=$Artikel_Bezeichnung;
				} else {
					$Artikelbild_Bezeichnung=$Artikel_Bezeichnung;
				}
				
//				attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $newProductId, $dateihandle, $client, $sessionId); 	
				// Simple Bild auch dem Conf zuordnen
//				if ($Artikel_Variante_Von!="" && $newProductId != 28021973 && $conf_id!='' && $neues_conf==true) {
	//				attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $conf_id, $dateihandle, $client, $sessionId); 
		//		}				
				//} // end if if (UPDATE_IMAGES) 
		} // end if article exists
		
			 
		if ($newProductId != '' && $newProductId != 28021973 && $Artikel_Preis1>0 && ($Artikel_Preis<>$Artikel_Preis1 || $Artikel_Preis<>$Artikel_Preis2 || $Artikel_Preis<>$Artikel_Preis3 || $Artikel_Preis<>$Artikel_Preis4)) {
		/*
			// Preise fuer Websites setzen
			$Artikel_Preise[0] = $Artikel_Preis;
			$Artikel_Preise[1] = $Artikel_Preis1;
			fwrite($dateihandle, "Product Artikel_Preis1=".$Artikel_Preis1);
			$Artikel_Preise[2] = $Artikel_Preis2;
			$Artikel_Preise[3] = $Artikel_Preis3;
			$Artikel_Preise[4] = $Artikel_Preis4;
			set_website_prices_API($Artikel_Preise, $newProductId, $dateihandle,$client, $sessionId);
			*/
		} 
		
		
		return $newProductId;	
	} // end function


	
?>
	