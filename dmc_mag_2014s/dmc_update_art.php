<?php
/*******************************************************************************************
*																							*
*  dmConnector  for magento shop															*
*  dmc_update_art.php																		*
*  Artikel Preis & Bestand updaten 															*
*  Copyright (C) 2008-2013 DoubleM-GmbH.de													*
*                                                                                          	*
*******************************************************************************************/

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

// include('dmc_db_functions.php');

	function dmc_update_quantity($StoreView='default',$client, $sessionId) {
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_update_quantity Session=".$sessionId."\n");
		// Post ermitteln		  
		// z.b. http://localhost/dmconnector/magento/dmconnector_magento.php?action=Art_Update&user=mobilize&password=mobilize&ExportModus=update&Artikel_ID=&Artikel_Artikelnr=1000&Artikel_Menge=5&Artikel_Preis=39.99&Artikel_Status=&Artikel_Steuersatz=		
		$Artikel_Artikelnr = isset($_POST['Artikel_Artikelnr']) ? $_POST['Artikel_Artikelnr'] : $_GET['Artikel_Artikelnr'];
		$Artikel_Menge = isset($_POST['Artikel_Menge']) ? $_POST['Artikel_Menge'] : $_GET['Artikel_Menge'];
		  //$Artikel_Artikelnr = $_POST['Artikel_Artikelnr'];
		  //$Artikel_Menge = $_POST['Artikel_Menge'];
		  // $Lager_no = $_POST['Lager_Nummer'];
		  
		  // MIT API
		  // Bestand des Artikels und aktiv setzen - Update stock info
		  // $client->call($sessionId, 'product_stock.update', array($Artikel_Artikelnr, array('qty'=>$Artikel_Menge, 'is_in_stock'=>1)));
		
		// OHNE API

			// get Magento article ID 
			$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);		
//fwrite($dateihandle, "dmc_update_quantity art_id=".$art_id."\n");
			// attr_type_id fuer catalog_product ermitteln
			$attr_type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_product");
//fwrite($dateihandle, "dmc_update_quantity attr_type_id=".$attr_type_id."\n");			
			// if exists
			if ($art_id!="") {
				// Update quantities
				fwrite($dateihandle, "Artikel $art_id Artikel_Menge= $Artikel_Menge ...");
					
				$table = "cataloginventory_stock_item";		
				$what = "qty = '".$Artikel_Menge."'";
				$where = "product_id = '".$art_id."'";
				// $where .= " AND stock_id = '".$Lager_no."'";
				// todo -> get exeption when article not exists
				dmc_sql_update($table, $what, $where);
				// todo -> In der catalog_product_entity die Spalte updated_at abgleichen
				// return true;	
				if ($Artikel_Menge>=1) {
					fwrite($dateihandle, "Artikel $art_id als lieferbar setzen ...");
					// Bestand des Artikels und aktiv setzen - Update stock info
					dmc_sql_update("cataloginventory_stock_status", "stock_status=1", "product_id=".$art_id);
					dmc_sql_update("cataloginventory_stock_status_idx", "stock_status=1", "product_id=".$art_id);
					dmc_sql_update("cataloginventory_stock_item", "is_in_stock=1", "product_id=".$art_id);					
					fwrite($dateihandle, "\n");
				}
				if ($Artikel_Menge<1) {
					fwrite($dateihandle, "Artikel $art_id als nicht  lieferbar setzen ...");
					// Bestand des Artikels und aktiv setzen - Update stock info
					dmc_sql_update("cataloginventory_stock_status", "stock_status=0", "product_id=".$art_id);
					dmc_sql_update("cataloginventory_stock_status_idx", "stock_status=0", "product_id=".$art_id);
					dmc_sql_update("cataloginventory_stock_item", "is_in_stock=0", "product_id=".$art_id);					
					fwrite($dateihandle, "\n");
				}
			} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "article with sku ".$Artikel_Artikelnr." does not exist.\n");
			}
	} // end function dmc_update_quantity
	
	// decrepATED
	function dmc_update_price_api($StoreView='default',$client, $sessionId) {
		
		// Statisch
		$USE_API=true;
		
		global $dateihandle, $dateihandleError;
		fwrite($dateihandle, "dmc_update_price_api Session=".$sessionId."\n");
				
			// Post ermitteln		  
		$ExportModus = ($_POST['ExportModus']);
		$Store_View = (($_POST['Artikel_ID']));	// FUER API Update -> 0 oder '' ist Standard, es koennen auch mehrere durch @ getrennt sein 
		$Artikel_Artikelnummern = isset($_POST['Artikel_Artikelnr']) ? $_POST['Artikel_Artikelnr'] : $_GET['Artikel_Artikelnr'];
		$Artikel_Menge = ($_POST['Artikel_Menge']);
		$Artikel_Preis = isset($_POST['Artikel_Preis']) ? $_POST['Artikel_Preis'] : $_GET['Artikel_Preis'];
		$Artikel_Status = ($_POST['Artikel_Status']); // HIer Aktiv/Passiv
		$Artikel_Steuersatz = ($_POST['Artikel_Steuersatz']);
		$Artikel_Lieferstatus = ($_POST['Artikel_Lieferstatus']);

		// Artikelnummer kann zusammengesetzt sein durch Artikelnummer und Variante und Größevon
		$artnr = explode ( '@', $Artikel_Artikelnummern);
		$Artikel_Artikelnr = $artnr[0];
		if (count ( $artnr ) == 2)  {
			$Artikel_Variante_Von = $artnr[1];
			$groesse = '';
		}  elseif (count ( $artnr ) == 3) { 
			$groesse = $artnr[1];
		} else  {
			$Artikel_Variante_Von = '';
			$groesse = '';
		}
		
		// get Magento article ID 
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);
		
		if ($art_id=="" || $Artikel_Preis == "0" || $Artikel_Preis == "0.00" || $Artikel_Preis == "") {
			fwrite($dateihandle, "FEHLER in update price -> Artikel konnte nicht ermittelt werden oder kein Preis.\n... ABBRUCH ...\n");
			fwrite($dateihandleError,  "FEHLER in dmc_update_price -> Artikel ".$Artikel_Artikelnr." konnte nicht ermittelt werden oder kein Preis. ABBRUCH ...\n");
			return;
		}
		
		// Es koennen mehrere Preise Ubergeben sein.
		$Artikel_Preise = explode ( '@', $Artikel_Preis);
		$anzahl_preise=sizeof($Artikel_Preise);
		// Hauptpreis
		$Artikel_Preis = $Artikel_Preise[0];
		// if exists		
		if ($art_id!="" && $USE_API) {
			fwrite($dateihandle, "dmc_update_price API Update ArtID $art_id for View $Store_View\n");
			
			if ($Store_View == '0' || $Store_View == '') { // Standard
				$result = $client->call($sessionId, 'catalog_product.update', array($art_id, array('price' => $Artikel_Preis)));
			} else {
				// Preis fuer eine Website(s) aendern
				$Store_Views = explode("@", $Store_View);			
				for($i = 0; $i < count($Store_Views); $i++) {     // Store_Views durchlaufen	     
					$result = $client->call($sessionId, 'catalog_product.update', array($art_id, array('price' => $Artikel_Preis),$Store_Views[$i]));
					if (DEBUGGER>=1) fwrite($dateihandle, " ... updated store_view ".$Store_Views[$i]." ");
				}
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "article with sku $Artikel_Artikelnr and ID ".$art_id." updated with price $Artikel_Preis.\n");		
		
		} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "article with sku ".$Artikel_Artikelnr." does not exist.\n");
		} // end 
		
		// Aktiv/Passiv setzen über API
		// Ggfls auf Passiv (2) setzen
		if($Artikel_Status==0 && $art_id!="" && $USE_API === true) {
			try {
				// update product status
				if (DEBUGGER>=1) fwrite($dateihandle, "update product status to ".$Artikel_Status."\n");
				$updateProductData = array(	
				 'status' => $Artikel_Status
			);
				
			if ($client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $updateProductData)))	
				if (DEBUGGER>=1) fwrite($dateihandle, "Product Status Update to ".$Artikel_Status."\n");					
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "Product Status Update failed:\nError:\n".$e."\n");		 
			}
		} // end if($Artikel_Status==2) 
			
	} // end function
	
	// Preisupdate auf Datenbankbasis
	function dmc_update_price($StoreView='default',$client, $sessionId) {
		global $dateihandle, $dateihandleError;
		fwrite($dateihandle, "dmc_update_price Session=".$sessionId."\n");
		
		// Pyua - SKUs aufgrund Modellnummmer ermitteln.
		$update_bei_model=true;
		
		// Posts ermitteln		  
		$ExportModus = ($_POST['ExportModus']);
		$Store_View = (($_POST['Artikel_ID']));	// FUER API Update -> 0 oder '' ist Standard, es koennen auch mehrere durch @ getrennt sein 
		$Artikel_Artikelnummern = isset($_POST['Artikel_Artikelnr']) ? $_POST['Artikel_Artikelnr'] : $_GET['Artikel_Artikelnr']; // zb test@conftest@size
		$Artikel_Menge = ($_POST['Artikel_Menge']);
		$Artikel_Preis = isset($_POST['Artikel_Preis']) ? $_POST['Artikel_Preis'] : $_GET['Artikel_Preis'];
		$Artikel_Status = ($_POST['Artikel_Status']); // HIer Aktiv/Passiv
		$Artikel_Steuersatz = ($_POST['Artikel_Steuersatz']);
		$Artikel_Lieferstatus = ($_POST['Artikel_Lieferstatus']);

		// Artikelnummer kann zusammengesetzt sein durch Artikelnummer und Variante (und "Haupt-Merkmal-Attribut" zur Preisbestimmung, zB Groesse)
		$artnr = explode ( '@', $Artikel_Artikelnummern);
		$Artikel_Artikelnr = $artnr[0];
		
		$attr_type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_product");
		
		if (count ( $artnr ) == 2)  {
			$Artikel_Variante_Von = $artnr[1];
			$Haupt_Merkmal_AttributsID = '';
		}  elseif (count ( $artnr ) == 3) { 
			$Haupt_Merkmal_Attribut = $artnr[2];	// ZB Groesse, wenn Preisaufschlag fuer Groesse
			$Haupt_Merkmal_AttributsID=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Haupt_Merkmal_Attribut);
		} else  {
			$Artikel_Variante_Von = '';
			$Haupt_Merkmal_AttributsID = '';
		}
		
		// Pyua - Artikelnummern nach model ermitteln
		if ($update_bei_model==true) {
			// ProduktArtNrs SELECT p.sku FROM `pyua_onlineshop_2649_catalog_product_entity_varchar` AS pvc INNER JOIN pyua_onlineshop_2649_catalog_product_entity AS p ON pvc.entity_id=p.entity_id where pvc.attribute_id=149 and pvc.Store_id=0 AND pvc.value='11103001'
			$query = "SELECT p.sku FROM `pyua_onlineshop_2649_catalog_product_entity_varchar` AS pvc INNER JOIN pyua_onlineshop_2649_catalog_product_entity AS p ON pvc.entity_id=p.entity_id ".
				"WHERE pvc.attribute_id=149 and pvc.Store_id=0 AND pvc.value='".$Artikel_Artikelnr."'" ;
				$link=dmc_db_connect();
				$beginn = microtime(true); 
				if (DEBUGGER==99)  fwrite($dateihandle, "181 dmc_update_price-SQL= ".$query." BEGINN .\n");
				$artId=="";
				$sql_query = mysql_query($query);	
				$anzahl=0;
				WHILE ($ERGEBNIS = mysql_fetch_array($sql_query)) {
					$ArtikelNr[$anzahl]=$ERGEBNIS['sku'];
					$anzahl++;	
				}
				dmc_db_disconnect($link);		
		} else {
			$ArtikelNr[0]=$Artikel_Artikelnr;
			$anzahl=1;
		}
		
		for ($j=0;$j<$anzahl;$j++) { // end for Artikelnummerndurchlauf
			fwrite($dateihandle,  "196 Update Artikelpreis $j von $anzahl für ".$ArtikelNr[$j].". ...\n");
			// aktuelle Artikelnummer
			$Artikel_Artikelnr=$ArtikelNr[$j];
			// get Magento article ID 
			$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);
			//	fwrite($dateihandle, "ArtikelID für SKU *$Artikel_Artikelnr* = *$art_id*.\n");
			
			if ($art_id=="") {
				fwrite($dateihandle, "FEHLER in update price -> Artikel konnte nicht ermittelt werden.\n... ABBRUCH ...\n");
				fwrite($dateihandleError,  "FEHLER in dmc_update_price -> Artikel ".$Artikel_Artikelnr." konnte nicht ermittelt werden. ABBRUCH ...\n");
				// return;
			}
			// Return, wenn nur 1 Preis und der = 0
			if ($Artikel_Preis == "0" || $Artikel_Preis == "0.00" || $Artikel_Preis == "") {
				fwrite($dateihandle,  "PROBLEM in dmc_update_price -> Preis = 0 bei Artikel ".$Artikel_Artikelnr.". ABBRUCH ...\n");
				fwrite($dateihandleError,  "PROBLEM in dmc_update_price -> Preis = -".$Artikel_Preis."- bei Artikel ".$Artikel_Artikelnummern.". ABBRUCH ...\n");
				// return;
			}
			
			// Es koennen mehrere Preise Ubergeben sein.
			$Artikel_Preise = explode ( '@', $Artikel_Preis);
			$anzahl_preise=sizeof($Artikel_Preise);
			
			// Preise runden
			if (ROUND_PRICES) {
				// Rundungen durchführen
				for ($i=0;$i<=$anzahl_preise;$i++) {
					$Artikel_Preise[$i] = floor($Artikel_Preise[$i])+(PRICE_END/100); 
				} // end for 
			} // end if Preise runden´
			
			// Hauptpreis
			$Artikel_Preis = $Artikel_Preise[0];
			
			// if exists		
			if ($art_id!="") {
				// Update auf Datenbank
				fwrite($dateihandle, "dmc_update_price DB Update\n");
				// attr_type_id fuer catalog_product ermitteln
				
				//fwrite($dateihandle, "dmc_update_price attr_type_id= ".$attr_type_id."\n");
				//if (DEBUGGER>=1) fwrite($dateihandle, "Preis0 =".$Artikel_Preise[0]." Preis1 =".$Artikel_Preise[1]." fuer ArtID= $art_id setzen \n");
				// get main price id
				$MAIN_PRICE_ATTRIBUTE_ID=dmc_get_attribute_id_by_attribute_code($attr_type_id,'price');		//std 60 
				// Wenn nicht ermittelbar, dann Abbruch
				//fwrite($dateihandle, "dmc_update_price line=122 Preisattribute= ".$MAIN_PRICE_ATTRIBUTE_ID."\n");
				if ($MAIN_PRICE_ATTRIBUTE_ID==-1) {
					fwrite($dateihandle, "FEHLER in update price -> Preis Datenbank Attribut konnte nicht ermittelt werden.\n... ABBRUCH ...\n");
					$MAIN_PRICE_ATTRIBUTE_ID=MAIN_PRICE_ATTRIBUTE_ID;
				}
				// Update prices
				if (SHOP_VERSION>1.3)
					$table = "catalog_product_index_price";		// Prices	MAGENTO 1.4
				else 
					$table = "catalogindex_price";				// Prices	MAGENTO 1.3
				//$table2 = "catalogindex_minimal_price";		// Min Prices
				$table3 = "catalog_product_entity_decimal";		// Entity of Prices		
				for ($i=0;$i<=$anzahl_preise;$i++) {
						//fwrite($dateihandle, "dmc_update_price line=136\n");

						// Wenn definiert in definitions_websites...
						if (defined('WEBSITE_PRICE' . $i) && constant('WEBSITE_PRICE' . $i) != '' && 
							defined('GROUP_PRICE' . $i) && constant('GROUP_PRICE' . $i) != ''
							&& $Artikel_Preise[$i]<> '' && $Artikel_Preise[$i]>0) {
								/*	z.B.	attribute_id = 60 für Verkaufspreis
								z.B.	attribute_id = 100 und customer_group_id =0 für EK
								z.B.	attribute_id = 567 und customer_group_id =0 für Sepecial (ACHTUNG -> Alle andren Preise werden auf special Preis geändert)
								z.B.	attribute_id = 270 und qty>0 für Staffelpreis  */		
							//fwrite($dateihandle, "dmc_update_price line=146\n");

							if ((constant('GROUP_PRICE' . $i) == 'all') || !is_numeric(constant('GROUP_PRICE' . $i)))  {
								// alle gruppen
								if (SHOP_VERSION>1.3) {
									//fwrite($dateihandle, "dmc_update_price line=151\n");

									// Nur wenn alle preise (mix max final) gleich waren
									$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_price SET price='.$Artikel_Preise[$i].', final_price='.$Artikel_Preise[$i].', min_price='.$Artikel_Preise[$i].', max_price='.$Artikel_Preise[$i].' WHERE entity_id='.$art_id.' AND min_price=max_price AND price=final_price and price=min_price AND website_id ='.constant('WEBSITE_PRICE' . ($i)).'';
									dmc_sql_query($query);
									// Nur wenn alle preise (mix max final) gleich waren
									$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_price_idx SET price='.$Artikel_Preise[$i].', final_price='.$Artikel_Preise[$i].', min_price='.$Artikel_Preise[$i].', max_price='.$Artikel_Preise[$i].' WHERE entity_id='.$art_id.' AND min_price=max_price AND price=final_price and price=min_price AND website_id ='.constant('WEBSITE_PRICE' . ($i)).'';
									dmc_sql_query($query);
								} else {
									$where = "entity_id = ".$art_id." AND qty=0.00 AND attribute_id = ".$MAIN_PRICE_ATTRIBUTE_ID." AND website_id =".constant('WEBSITE_PRICE' . ($i))."";
									$what = " value = ".$Artikel_Preise[$i]."";
									// sales price (WEBSITE)
									if (DEBUGGER>=1) fwrite($dateihandle, "update website price ".$what." where:".$where."\n");
									dmc_sql_update($table, $what, $where);
								}
								// if ($Artikel_Steuersatz!="") $what .= ", tax_class_id = '".$Artikel_Steuersatz."'";
							} else {
								// bestimmte kundengruppe
								$where = "entity_id = ".$art_id." AND qty=0.00 AND attribute_id = ".$MAIN_PRICE_ATTRIBUTE_ID." AND customer_group_id = ".constant('GROUP_PRICE' . ($i))." AND website_id =".constant('WEBSITE_PRICE' . ($i))."";
								// if ($Artikel_Steuersatz!="") $what .= ", tax_class_id = '".$Artikel_Steuersatz."'";
								$what = " value = ".$Artikel_Preise[$i]."";
								// sales price (WEBSITE)
								if (DEBUGGER>=1) fwrite($dateihandle, "update website price ".$what." where:".$where."\n");
								dmc_sql_update($table, $what, $where);
							}
							
						} // end if defined 	
						
						// if (DEBUGGER>=1) fwrite($dateihandle, "158 -$i-  ".constant('STORE_PRICE' . $i) ." preis$i= ".$Artikel_Preise[$i]."\n");
						// if (constant('STORE_PRICE' . $i) <> '' || constant('STORE_PRICE' . $i) >= 0) fwrite($dateihandle, "160 x\n");
						// if ($Artikel_Preise[$i]<> '') fwrite($dateihandle, "155 y\n");
						// if ($Artikel_Preise[$i]>0) fwrite($dateihandle, "155 z\n");
						// WENN Store Price zu setzen
						if ((constant('STORE_PRICE' . $i) <> '' || constant('STORE_PRICE' . $i) >= 0)
							&& $Artikel_Preise[$i]<> '' && $Artikel_Preise[$i]>0) {
							$where = "entity_id = ".$art_id." AND attribute_id = ".$MAIN_PRICE_ATTRIBUTE_ID." AND store_id = ".constant('STORE_PRICE' . ($i))."";
							// Price0 for customer group 0
							$what = " value = ".$Artikel_Preise[$i]."";
							// entity of price (STORE)
							// Wenn Store-Preis nicht vorhanden, diesen setzen
							if (dmc_entry_exits('value_id', $table3, $where)) {
								//if (DEBUGGER>=1) fwrite($dateihandle, "update store price ".$what." where:".$where."\n");
								dmc_sql_update($table3, $what, $where." ");
							} else {
								//if (DEBUGGER>=1) fwrite($dateihandle, "insert store price ".$what." where:".$where."\n");
								dmc_sql_insert($table3, 
											"(entity_type_id, attribute_id, store_id, entity_id, value)", 
											"(".$attr_type_id.", ".$MAIN_PRICE_ATTRIBUTE_ID.", ".constant('STORE_PRICE' . ($i)).", ".$art_id.", ".$Artikel_Preise[$i].")");
							}
						} // end if defined
						
						// todo -> In der catalog_product_entity die Spalte updated_at abgleichen
						// return true;	
					
						// Update Preisunterschiede Simple to Configurable
						if ($Artikel_Variante_Von!="") {
							if (DEBUGGER>=1) fwrite($dateihandle, "\nupdate simple product to conf:\n");
							// configurable product id ermitteln - get configurable product id 				
							$conf_id=dmc_get_id_by_artno($Artikel_Variante_Von);	
							// Varianten Preise zuordnen 
							if ($conf_id!='') dmc_attach_simple_to_configurable_prices($art_id, $conf_id, $Haupt_Merkmal_AttributsID, $Artikel_Preis);
								/*$table = "catalog_product_super_link";
								$columns = "(`link_id` ,`product_id` ,`parent_id`)";
								$values = "('' , '".$newProductId."', '".$conf_id."')";			
								dmc_sql_insert($table, $columns, $values);*/	
						} // end if Artikel_Variante_Von
				} // end for	
				// Standard Preis auf FLAT Tabelle setzen
				$table='catalog_product_flat_1';
				$where = "entity_id = ".$art_id." ";
				$what = " price = ".$Artikel_Preise[0]."";
				dmc_sql_update($table, $what, $where);	
			} else {
					if (DEBUGGER>=1) fwrite($dateihandle, "article with sku ".$Artikel_Artikelnr." does not exist.\n");
			} // end 
			
			// Aktiv/Passiv setzen über DATENBANK
			if($Artikel_Status!='' && $art_id!="" && $USE_API !== true) {
				//dmc_set_product_status_db($art_id,$Artikel_Status,'0');
				if (DEBUGGER>=1) fwrite($dateihandle, "Product Status updated to ".$Artikel_Status."\n");					
			} // end if($Artikel_Status==2) 	
		} // end for Artikelnummerndurchlauf
	} // end function
	
?>
	
	