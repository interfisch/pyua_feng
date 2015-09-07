<?php
/*******************************************************************************************
*                                                                              			*
*  dm.connector  for magento shop														*
*  dmc_prices.php																		*
*  Preisfunktionen																		*
*  Copyright (C) 2009 DoubleM-GmbH.de													*
*                                                                                		*
*  	03.07.2011 - sprecial price um store_view ergaenzt                             		*
*	24.01.2013 - Artikel_Startseite wird verwendet fuer news_form_Date und 				*
*	news_to_date ( heute bis heute+1Woche) in dmc_set_specials							*
*	24.01.2013 	- 	dmc_set_specials zu dmc_set_specials14								*
*	24.01.2013	-	dmc_set_specials jetzt mit direkter Mage API						*
*                                                                                		*
*******************************************************************************************/

// Erweitert 18.04.09 um Kundengruppenpreise dmc_get_customer_prices und dmc_get_customer_groups

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

// include('dmc_db_functions.php');

function dmc_get_customer_prices($Custprices, $Standardprice) {
			$dateiname="./dmconnector_log_magento.txt";	
			$dateihandle = fopen($dateiname,"a");			
			fwrite($dateihandle, "dmc_get_customer_prices mit Custprices=$Custprices\n");
		
		// Kunden-Preise getrennt durch @
		if (preg_match("/@/", $Custprices)) {
			// Einzelne Preise in Array
			$NewCustprices = explode('@', $Custprices);
		} // end if  Kunden-Preise
		
		// Europa 3000 Preise getrennt durch diverse Leerzeichen
		if (ereg("  ", $Custprices)) {
			// Mehrfach-Leerzeichen auf 1 Leerzeichen reduzieren
			$Custprices=str_replace("       "," ",$Custprices);
			$Custprices=str_replace("      "," ",$Custprices);
			$Custprices=str_replace("     "," ",$Custprices);
			$Custprices=str_replace("    "," ",$Custprices);
			$Custprices=str_replace("   "," ",$Custprices);
			$Custprices=str_replace("  "," ",$Custprices);
			
			fwrite($dateihandle, "Custprices=".$Custprices."\n");
			// Einzelne Preise in Array
			$Custprices=trim($Custprices);
			$NewCustprices = explode(" ", $Custprices);
			fwrite($dateihandle, "NewCustprices=".$NewCustprices."\n");
		} // end if Europa3000 Preise
			fwrite($dateihandle, "count(NewCustprices)=".count($NewCustprices)."\n");
		
		// Wenn Preis = 0, Standardpreis nehmen
		for ($rcm=0; $rcm <= count($NewCustprices)-1;$rcm++) 
			if ($NewCustprices[$rcm]==0) $NewCustprices[$rcm]=$Standardprice;
	    return  $NewCustprices; // $NewCustprices;	
	} // end function dmc_get_customer_prices	
	
	function dmc_get_customer_groups($Custgroups) {
				$dateiname="./dmconnector_log_magento.txt";	
			$dateihandle = fopen($dateiname,"a");			
			fwrite($dateihandle, "dmc_get_customer_groups:$Custgroups\n");
		// Kundengruppen getrennt durch @
		if (preg_match('/@/', $Custgroups)) {
			// Einzelne Preise in Array
			fwrite($dateihandle, "splitten$Custgroups\n");
			$NewCustgroups = explode('@', $Custgroups);
			// IDs ermitteln
			for ($rcm=0; $rcm <= count($NewCustgroups)-1;$rcm++) 
				$NewCustgroups[$rcm]=dmc_map_customer_group($NewCustgroups[$rcm]);
		} // end if  Kunden-Preise
		
		$result = print_r($NewCustgroups ,true);
				fwrite ($dateihandle, "\nGruppen= $result\n");
				
	    return $NewCustgroups;
	} // end function dmc_get_customer_groups

	function dmc_set_specials15($StoreView='default', $client, $sessionId) {
		
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_specials Session=".$sessionId."\n");
	
		$start = microtime(true);
		// Post ermitteln		  
		Mage::app()->setCurrentStore(STORE_ID);
		$store_view = $_POST['Artikel_ID'];					// ehemals Artikel_ID
		$Artikel_Artikelnr = isset($_POST['Artikel_Artikelnr']) ? $_POST['Artikel_Artikelnr'] : $_GET['Artikel_Artikelnr'];
		$Aktionspreis = $_POST['Aktionspreis'];
		// Pruefen, ob Waehrung mit uebergeben
		if (preg_match('/@/', $Aktionspreis)) 
					// Art (conf oder grp) + Artikelnummer des "Haupt"-Artikels
					list ($Aktionspreis, $currency) = split ("@", $Aktionspreis);
		else {
				$currency	= 'EUR';			
		}
			
		$Artikel_Anzahl = $_POST['Artikel_Anzahl'];
		$DatumVon = $_POST['DatumVon'];
		$DatumBis = $_POST['DatumBis'];

		if ($DatumVon=='') {
			$DatumVon=date("Y-m-d")." 00:00:00";	// heute
		}
		if ($DatumBis=='') {
			$tage=7;								// Standarddauer des Angebotes = 7 Tage
			$hours = $tage * 24;
			$added = ($hours * 3600)+time();
			$month = date("m", $added);
			$day = date("d", $added);
			$year = date("Y", $added);
			$DatumBis = "$year-$month-$day 00:00:00";
		}

		$zwischenzeit = microtime(true);
		$laufzeit = $zwischenzeit - $start;
			
		// Preise runden
		if (ROUND_PRICES) {
			// Rundungen durchführen
			$Aktionspreis = floor($Aktionspreis)+(PRICE_END/100);			
		} // end if Preise runden
	  
		// get Magento article ID 
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);			
	
		$product = Mage::getModel('catalog/product');
	
		// Preufen, ob (echte) storewie übergeben wurde
		if ($store_view!='' && is_numeric($store_view) && $store_view<20) {
			$store_view = $store_view;
		} else {
			$store_view =1;
		}
		// fwrite($dateihandle, "view alt=".Mage::app()->getStore()->getId()."\n");		
		
							
		//$product->setStoreId(2); // 0 = default/all store view. If you want to save data for a specific store view, replace 0 by Mage::app()->getStore()->getId().
		Mage::app()->setCurrentStore(2);
		$product->setStoreId(2);
		// fwrite($dateihandle, "view neu=".Mage::app()->getStore()->getId()."\n");		
		
		$product->load($art_id);
	
		//	print_array($product);
	
		$zwischenzeit = microtime(true);
		$laufzeit = $zwischenzeit - $start;
		if (DEBUGGER>=1) fwrite($dateihandle, "Artikelid ermittelt=".$art_id." - Laufzeit: ".$laufzeit." Sekunden!"."\n");
		 
		// if exists
		if ($art_id!="") {
			// Werte für Aktionspreis
			$updateProductData ['sku'] = $Artikel_Artikelnr;
				
			$updateProductData ['special_price'] = $Aktionspreis;
			$updateProductData ['special_from_date'] = $DatumVon;
			$updateProductData ['special_to_date'] = $DatumBis;
				
			//	$updateProductData ['_product_websites'] = 'base';
			//	if ($store_view=='sigmatherm') $updateProductData ['_product_websites'] = 'SigmaTherm';
			//$updateProductData ['_store'] = $store_view;
				
			// Wenn Startseitenartikel
			if (SPECIAL_PRICE_CATEGORY==2802) {
				// Datum generieren, wenn nicht vorhanden
				$updateProductData ['news_from_date'] = $DatumVon;
				$updateProductData ['news_to_date'] = $DatumBis;
			}	
			$createData = array ( $updateProductData );		// Mehrere waeren moeglich
					
				try {
					Mage::getSingleton('fastsimpleimport/import')
					->setPartialIndexing(true)
					//  ->setBehavior(Mage_ImportExport_Model_Import::BEHAVIOR_APPEND)
					->processProductImport($createData); 
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Aktionspreis gesetzt für Store ".$updateProductData ['_store']." über MAGEAPI bis ".$DatumBis." - Laufzeit: ".$laufzeit." Sekunden!"."\n");
				}
				catch (Mage_Core_Exception $e) {
					if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER - siehe ERROR LOG\n");
					dmc_write_error("dmc_prices", "dmc_set_specials", "169", "Artikelnummer:".$Artikel_Artikelnr." -> ".$e->getMessage(), true, true, $dateihandle);
				}
				catch (Exception $e) {
					if (DEBUGGER>=1) fwrite($dateihandle, "FEHLER - siehe ERROR LOG\n");
					dmc_write_error("dmc_prices", "dmc_set_specials", "173",  "Artikelnummer:".$Artikel_Artikelnr." -> ".$e, true, true, $dateihandle);
				}
			 
			// Artikel in Kategorie schreiben
			if (SPECIAL_PRICE_CATEGORY<>0 && SPECIAL_PRICE_CATEGORY<>2802) {
				// Kategorien ermitteln
					// Artikel in Spezielle Angebotskategorie schreiben
				fwrite($dateihandle, "SPECIAL_PRICE_CATEGORY=".SPECIAL_PRICE_CATEGORY." \n");
				// Hier ist API Performance killer - Ergebnis ca 20 sek!!!
				// -> besser ueber Datenbank
				if (SHOP_VERSION>=1.6) {
					// Kategorien uber Datenbank ermitteln -> rueckgabe IDs getrennt mit ,
					$get_product_category_ids = dmc_get_product_category_ids($art_id);
					if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte IDs ".$get_product_category_ids." (true)");
					
					$products['categories'] = explode ( ',', $get_product_category_ids);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Artikelinfos über DB angerufen - Laufzeit: ".$laufzeit." Sekunden!"."\n");
				} else {
					$products = $client->call($sessionId, 'product.info', $Artikel_Artikelnr);
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Artikelinfos über API angerufen - Laufzeit: ".$laufzeit." Sekunden!"."\n");	
				}
				
				// Erngaenzen, wenn noch nicht gesetzt
				if (!in_array(SPECIAL_PRICE_CATEGORY,$products['categories']))
				{
					$products['categories'][]=SPECIAL_PRICE_CATEGORY;
				
					for ($i=0;$i<count($products['categories']);$i++) {
						if (DEBUGGER>=1) fwrite($dateihandle, "Array $i:".$products['categories'][$i]."\n");	
					}
	
					$updateProductData['categories']=$products['categories'];
					try {		    
							$erfolg = $client->call(
							$sessionId,
							'product.update',
							array(
								$Artikel_Artikelnr,
								$updateProductData
								)
							);			
					} catch (SoapFault $e) {
							if (DEBUGGER>=1) fwrite($dateihandle,'Set special category failed:\n'.$e);	
					}
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "Kategorie über API gesetzt - Laufzeit: ".$laufzeit." Sekunden!"."\n");
				} // end if !in_array
			} 
		} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "Produkt ".$Artikel_Artikelnr." existiert nicht. \n");
		} // if ($art_id!="") 
	} // end function - dmc_set_specials
	
	function dmc_set_specials($StoreView='default', $client, $sessionId) {
		
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_specials Session=".$sessionId."\n");
	
		$start = microtime(true);
		// Post ermitteln		  
		$store_view = $_POST['Artikel_ID'];		// ehemals Artikel_ID
		$Artikel_Artikelnr = isset($_POST['Artikel_Artikelnr']) ? $_POST['Artikel_Artikelnr'] : $_GET['Artikel_Artikelnr'];
		$Aktionspreis = $_POST['Aktionspreis'];
		// Pruefen, ob Waehrung mit uebergeben
		 if (preg_match('/@/', $Aktionspreis)) 
					// Art (conf oder grp) + Artikelnummer des "Haupt"-Artikels
					list ($Aktionspreis, $currency) = split ("@", $Aktionspreis);
			else {
								$currency	= 'EUR';			
			}
			
		$Artikel_Anzahl = $_POST['Artikel_Anzahl'];
		$DatumVon = $_POST['DatumVon'];
		$DatumBis = $_POST['DatumBis'];

		$zwischenzeit = microtime(true);
		$laufzeit = $zwischenzeit - $start;
			
		// if (DEBUGGER>=1) fwrite($dateihandle, "alter preis=".$Aktionspreis." - Laufzeit: ".$laufzeit." Sekunden!"."\n");
		  // Preise runden
		  if (ROUND_PRICES) {
			// Rundungen durchführen
			$Aktionspreis = floor($Aktionspreis)+(PRICE_END/100);			
		  } // end if Preise runden
		//$zwischenzeit = microtime(true);
		//$laufzeit = $zwischenzeit - $start;
		//  if (DEBUGGER>=1) fwrite($dateihandle, "neuer preis=".$Aktionspreis." - Laufzeit: ".$laufzeit." Sekunden!"."\n");
		  
		// get Magento article ID 
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);			
		
		$zwischenzeit = microtime(true);
		$laufzeit = $zwischenzeit - $start;
		 if (DEBUGGER>=1) fwrite($dateihandle, "Artikelid ermittelt=".$art_id." - Laufzeit: ".$laufzeit." Sekunden!"."\n");
		 
		// if exists
		if ($art_id!="" && $Aktionspreis!="" && $Aktionspreis>0) {
			// Aufbau: mixed SKU or ID, float specialPrice - special price (optional), string fromDate - from date (optional), string toDate - to date (optional), mixed storeView - store view ID or code (optional)
			try {	
				//if ($store_view<>'all' && $store_view<>'0' && $store_view<>'' && $store_view>0 && $store_view<50)
				//{	
				
				$erfolg = $client->call(
				    $sessionId,
				    'product.setSpecialPrice',
				    array(
						$art_id,
				        $Aktionspreis,
						$DatumVon,
						$DatumBis
						
						)
					,$store_view
					);
				/*}
				else
				{		
					$client->call($sessionId, 'product.update', array($sku, array('ordernumbers'=>$bestehende_werte),$store_view));
					$erfolg = $client->call(
				    $sessionId,
				    'product.setSpecialPrice',
				    array(
						$Artikel_Artikelnr,
				        $Aktionspreis,
						$DatumVon,
						$DatumBis
						// $StoreView
						)
					);
				}*/
			} catch (SoapFault $e) {
					if (DEBUGGER>=1) fwrite($dateihandle,'Set special price failed:\n'.$e);		    
			}
			$zwischenzeit = microtime(true);
			$laufzeit = $zwischenzeit - $start;
			if (DEBUGGER>=1) fwrite($dateihandle, "Specialpreis gesetzt über API - Laufzeit: ".$laufzeit." Sekunden!"."\n");
			
			if (SPECIAL_PRICE_CATEGORY==2802) {
				// Datum generieren, wenn nicht vorhanden
				
				$updateProductData ['news_from_date'] = $DatumVon;
				$updateProductData ['news_to_date'] = $DatumBis;
				try {		    
					if (DEBUGGER>=1) fwrite($dateihandle,"Update Aktionspreis Startseite fuer Artikel $Artikel_Artikelnr\n".$e);
					$erfolg = $client->call(
						$sessionId,
						'product.update',
						array(
							$art_id,
							$updateProductData,
							)
						,$store_view
					);
					// if (DEBUGGER>=1) fwrite($dateihandle,"mit ID $art_id von $DatumVon bis $DatumBis erledigt \n".$e);								
				} catch (SoapFault $e) {
						if (DEBUGGER>=1) fwrite($dateihandle,'Update Aktionspreis Startseite fehlgeschlagen:\n'.$e);	
				}
				
			} // end if 
			// Artikel in Kategorie schreiben
			if (SPECIAL_PRICE_CATEGORY<>0 && SPECIAL_PRICE_CATEGORY<>2802) {
				// Kategorien ermitteln
					// Artikel in Spezielle Angebotskategorie schreiben
				fwrite($dateihandle, "SPECIAL_PRICE_CATEGORY=".SPECIAL_PRICE_CATEGORY." \n");
				// Hier ist API Performance killer - Ergebnis ca 20 sek!!!
				// -> besser ueber Datenbank
				if (SHOP_VERSION>=1.6) {
					// Kategorien uber Datenbank ermitteln -> rueckgabe IDs getrennt mit ,
					$get_product_category_ids = dmc_get_product_category_ids($art_id);
					if (DEBUGGER>=1) fwrite($dateihandle, "Ermikttelte IDs".$get_product_category_ids." (true)");
					
					$products['categories'] = explode ( ',', $get_product_category_ids);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Artikelinfos über DB angerufen - Laufzeit: ".$laufzeit." Sekunden!"."\n");
				} else {
					$products = $client->call($sessionId, 'product.info', $Artikel_Artikelnr);
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1) fwrite($dateihandle, "Artikelinfos über API angerufen - Laufzeit: ".$laufzeit." Sekunden!"."\n");	
				}
				
				// Erngaenzen, wenn noch nicht gesetzt
				if (!in_array(SPECIAL_PRICE_CATEGORY,$products['categories']))
				{
					$products['categories'][]=SPECIAL_PRICE_CATEGORY;
				
					for ($i=0;$i<count($products['categories']);$i++) {
						if (DEBUGGER>=1) fwrite($dateihandle, "Array $i:".$products['categories'][$i]."\n");	
					}
	
					$updateProductData['categories']=$products['categories'];
					try {		    
							$erfolg = $client->call(
							$sessionId,
							'product.update',
							array(
								$Artikel_Artikelnr,
								$updateProductData
								) ,$store_view
							);			
					} catch (SoapFault $e) {
							if (DEBUGGER>=1) fwrite($dateihandle,'Set special category failed:\n'.$e);	
					}
					$zwischenzeit = microtime(true);
					$laufzeit = $zwischenzeit - $start;
					if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "Kategorie über API gesetzt - Laufzeit: ".$laufzeit." Sekunden!"."\n");
				} // end if !in_array
			} 
		} else {
				if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "Product ".$Artikel_Artikelnr." does not exist. \n");
		} // if ($art_id!="") 
	} // end function - dmc_set_specials

	function dmc_set_tier_price($StoreView='default', $client, $sessionId, $Artikel_Artikelnr, $tier_prices) {
		$debugger=1;
		if (DEBUGGER>=1) {
			$dateiname="./dmconnector_log_magento.txt";	
			$dateihandle = fopen($dateiname,"a");			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_tier_price =".$tier_prices."\n");
		}
		
		for ( $Anz_Preise = 0; $Anz_Preise < count ( $tier_prices ); $Anz_Preise++ )
		{
		// Wenn Kundengruppe und Preis angegeben
			if (preg_match('/@/', $tier_prices[$Anz_Preise])) {
				$tier[$Anz_Preise] = explode ( '@', $tier_prices[$Anz_Preise]);
				if (DEBUGGER>=1) fwrite($dateihandle, "CustGroup =".$tier[$Anz_Preise][0]." and CustGroupPrice= ".$tier[$Anz_Preise][1]."\n");
					
				$tierPrices[] = array(
				    'website'           => 'all',
				    'customer_group_id' => $tier[$Anz_Preise][0],
				    'qty'               => 1,
				    'price'             => $tier[$Anz_Preise][1]
				);
			} // end if Wenn Kundengruppe und Preis angegeben
		   } // end for
		   try {		    
					$erfolg = $client->call(
				    $sessionId,
				    'product_tier_price.update',
				    array(
						$Artikel_Artikelnr,
						$tierPrices
						)
					);				
			} catch (SoapFault $e) {
					if (DEBUGGER>=1) fwrite($dateihandle,'Set tier price failed:\n'.$e);		    
			}
			if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "special tier  set with success. \n");
	} // end function - $dmc_set_tier_price
	
	
?>
	
	