<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_map_attributes.php													*
*  inkludiert von dmc_write_art.php und dmc_set_details.php					*
*  Attribute und deren IDs mappen											*
*  Copyright (C) 2012-2013 DoubleM-GmbH.de									*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
04.04.2012
- Pruefen, ob Merkmal vorhanden und ggfls anlegen
29.1.2013
- BUGFIX 1 und 2
30.1.2013
- Unterstuetzung von durch = getrennte Auspraegungen im Merkmal, z.B. 'Farbe=blau@Größe=XL' AS Artikel_Merkmal, '' AS Artikel_Auspraegung
*/

		// Merkmale ermitteln - werden als attribue1@attribe2@... übergeben
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_map_attributes Merkmal = ".$Artikel_Merkmal." mit Auspraegung = ".$Artikel_Auspraegung."\n");
		$Merkmale = explode ( '@', $Artikel_Merkmal);
		// BUGFIX 1: Magento Akzeptiert teilweise keine '' in der Ausprägung
		$Artikel_Auspraegung = str_replace("''", "'", $Artikel_Auspraegung);
		
		// Auspreagungen  und MerkmaleIDs ermitteln - werden als Auspreageung1@Auspreageung2@... übergeben
		$Auspraegungen = explode ( '@', $Artikel_Auspraegung);
		
		// Bereinigungen
		$durchlaufe=count ($Merkmale);
		for ( $Anz_Merkmale = 0; $Anz_Merkmale < $durchlaufe; $Anz_Merkmale++ ) {
			// Bereinigen um leere Merkmale / Auspraegungen,
			//if ($Merkmale[$Anz_Merkmale]=='' || $Auspraegungen[$Anz_Merkmale]=='') {	
			if ($Merkmale[$Anz_Merkmale]=='') {	
				unset ($Merkmale[$Anz_Merkmale]);
				unset ($Auspraegungen[$Anz_Merkmale]);
			}
			
			// sowie Unterstuetzung von durch = getrennte Auspraegungen im Merkmal
			if (strpos($Merkmale[$Anz_Merkmale], "=") !== false) {
				list($Merkmale[$Anz_Merkmale], $Auspraegungen[$Anz_Merkmale]) = explode ( "=", $Merkmale[$Anz_Merkmale]);
			}
			// Mindestverkaufsmenge groesser als Standard
			if (strpos($Merkmale[$Anz_Merkmale], "min_sale_qty") !== false) {			
				// Mindestverkaufsmenge verarbeiten
				if ($Auspraegungen[$Anz_Merkmale]>1) {
					// if (DEBUGGER>=1) 
					$is_min_order_qty=true;
					$stock_data = array (
							'min_sale_qty'          => $Auspraegungen[$Anz_Merkmale],
							'use_config_min_sale_qty' => 0,
							//'use_config_max_sale_qty' => 1,
							//'use_config_manage_stock' => 1
						);
					// Attribute min_sale_qty nicht weiter verarbeiten				
				}
				// fwrite($dateihandle, "min_sale_qty = ".$stock_data['min_sale_qty']."\n");
				unset ($Merkmale[$Anz_Merkmale]);
				unset ($Auspraegungen[$Anz_Merkmale]);
			} 
		//	if (DEBUGGER>=1) fwrite($dateihandle, "Merkmal[$Anz_Merkmale] = ".$Merkmale[$Anz_Merkmale]." mit Auspraegung[$Anz_Merkmale] = ".$Auspraegungen[$Anz_Merkmale]."\n");
		}
	
		// Re-index: arrays 
		$Merkmale = array_values($Merkmale);
		$Auspraegungen = array_values($Auspraegungen);
	
		// Pruefen, ob Merkmal vorhanden und ggfls anlegen
		for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Auspraegungen ); $Anz_Merkmale++ )
		{
			// GGfls Code ermitteln, sofern nicht Code sondern Klartext angegeben ist, z.B. Höhe auf hoehe?
			$MerkmaleCode[$Anz_Merkmale]=dmc_get_attribute_code_by_attribute_name($attr_type_id,$Merkmale[$Anz_Merkmale]);		
			//if (DEBUGGER>=1) fwrite($dateihandle, "184 - Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
			$MerkmaleID[$Anz_Merkmale]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Merkmale[$Anz_Merkmale]);		
			if (DEBUGGER>=1) fwrite($dateihandle, "77 - MerkmaleID von ".$Merkmale[$Anz_Merkmale]." = ".$MerkmaleID[$Anz_Merkmale]."\n");
			// Wenn Merkmal nicht vorhanden -> anlegen
			//if ($MerkmaleID[$Anz_Merkmale]==-1) {
			if ($MerkmaleCode[$Anz_Merkmale]==-1) {
				// Wenn Attributebezeichnung mit f_ (zB f_material) anfaengt, so sei dieses ein Filterattribute
				if (substr($MerkmaleCode[$Anz_Merkmale],0,2) == "f_")
					$filterbar = true;
				else 
					$filterbar = false;
				
				if ($filterbar === false)
					if (is_file('../../userfunctions/products/dmc_create_attribute.php')) 
						include ('../../userfunctions/products/dmc_create_attribute.php');
					else if (is_file('./userfunctions/products/dmc_create_attribute.php')) 
						include ('./userfunctions/products/dmc_create_attribute.php');
					else if (is_file('../../functions/products/dmc_create_attribute.php')) 
						include ('../../functions/products/dmc_create_attribute.php');
					else 
						include ('./functions/products/dmc_create_attribute.php');
				else 
					if (is_file('../../userfunctions/products/dmc_create_attribute_filterbar.php')) 
						include ('../../userfunctions/products/dmc_create_attribute_filterbar.php');
					else if (is_file('./userfunctions/products/dmc_create_attribute_filterbar.php')) 
						include ('./userfunctions/products/dmc_create_attribute_filterbar.php');
					else if (is_file('../../functions/products/dmc_create_attribute_filterbar.php')) 
						include ('../../functions/products/dmc_create_attribute_filterbar.php');
					else 
						include ('./functions/products/dmc_create_attribute_filterbar.php');
				// Anstelle Klartext soll nun der Merkmale Code verwendet werden...
				//$Merkmale[$Anz_Merkmale]=dmc_get_attribute_code_by_attribute_name($attr_type_id,$Merkmale[$Anz_Merkmale]);		
				//if (DEBUGGER>=1) fwrite($dateihandle, "NEUER CODE = ".$Merkmale[$Anz_Merkmale]."\n");
				// $MerkmaleID[$Anz_Merkmale]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Merkmale[$Anz_Merkmale]);		
			} // end Merkmal anlegen
			else
			{
				// Bei bereits vorhandenem mit dem Attribute Code (hoehe) - statt mit Klartext (Höhe) - weiterarbeiten
				$Merkmale[$Anz_Merkmale]=$MerkmaleCode[$Anz_Merkmale];
			}
		} // end for
		
		// AusprägungsIDs und AttributeIDs  aus Datenbank ermitteln
		for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
		{
			//if (DEBUGGER>=1) fwrite($dateihandle, "83\n ");
			// BUGFIX 2: Ist letzes Zeichen der Ausprägung ein Leerzeichen .> entfernen
			if (substr($Auspraegung_Name[$Anz_Merkmale],-1)==' ') {
				 $Auspraegung_Name[$Anz_Merkmale] = substr($Auspraegung_Name[$Anz_Merkmale],0,-1);
			}
			// Kommata durch Punkt ersetzen
			// $Auspraegungen[$Anz_Merkmale] = str_replace(",", ".", $Auspraegungen[$Anz_Merkmale]);
			// Überprüfen, ob der Ausprägung (Value)  eine Sortierung mitgegeben wurde (z.B. Merkmal|10) - Trennzeichen "|"
			if (preg_match('/|/', $Auspraegungen[$Anz_Merkmale]) && !preg_match('/http/', $Auspraegungen[$Anz_Merkmale])) {
				list ($Auspraegung_Name[$Anz_Merkmale], $Auspraegung_Order[$Anz_Merkmale]) = split ("|", $Auspraegungen[$Anz_Merkmale]);
			} else {
				$Auspraegung_Name[$Anz_Merkmale] = $Auspraegungen[$Anz_Merkmale];
				$Auspraegung_Order[$Anz_Merkmale] = 0;
			}
		
			// Fuer BaseAmount nicht nach ID abfragen
			if ($Merkmale[$Anz_Merkmale]=='base_price_amount' || $Merkmale[$Anz_Merkmale]=='base_price_base_unit' 
				|| $Merkmale[$Anz_Merkmale]=='base_price_base_amount' || $Merkmale[$Anz_Merkmale]=='base_price_unit' ) {
				$basePrice=true;
				if ($Merkmale[$Anz_Merkmale]=='base_price_amount') $base_price_amount=$Auspraegung_Name[$Anz_Merkmale];
				if ($Merkmale[$Anz_Merkmale]=='base_price_base_unit') $base_price_base_unit=$Auspraegung_Name[$Anz_Merkmale];
				if ($Merkmale[$Anz_Merkmale]=='base_price_base_amount') $base_price_base_amount=$Auspraegung_Name[$Anz_Merkmale];
				if ($Merkmale[$Anz_Merkmale]=='base_price_unit') $base_price_unit=$Auspraegung_Name[$Anz_Merkmale];
				//if (DEBUGGER>=1) fwrite($dateihandle,"base_price_amount = ".$base_price_amount."\n");
			// Sonderverarbeitung
			} else if ($Merkmale[$Anz_Merkmale]!="colorcode") { // Merkmale verarbeiten
				// Prüfen, ob Merkmal  in DB Table vorhanden und Auspraegung 
				// Unterstuetzung von Multi-Feldern -> Auspraegungen durch | getrennt, z.B. @Hersteller@ mit @BMW|AUDI|VW@
				$Auspraegung_Name_Merkmal = explode ( '|', $Auspraegung_Name[$Anz_Merkmale]);
				//if (DEBUGGER>=1) fwrite($dateihandle,"221 - Anz Auspraegung_Name_Merkmal = ".count ( $Auspraegung_Name_Merkmal )." stammt ab von $Auspraegung_Name[$Anz_Merkmale]\n");	
			//	if (DEBUGGER>=1) fwrite($dateihandle,"125 - Auspraegung_Name_Merkmal $Anz_Merkmale = ".$Auspraegung_Name_Merkmal['0']." -> ");
				for ( $Anz_Auspraegungen_Merkmal = 0; $Anz_Auspraegungen_Merkmal < count ( $Auspraegung_Name_Merkmal ); $Anz_Auspraegungen_Merkmal++ )
				{
					// NUR WENN AUSPRAEGUNG(EN) fuer das jeweilige Merkmal uebergeben
					// AB VERSION 1.6 wird per Magento API nicht mehr die ID gesetzt, sondern der Name
					// Ausnahme: package_id von Zusatzmodul für Versand
					if (SHOP_VERSION>=1.7 && $Merkmale[$Anz_Merkmale]!='package_id') {
						// BUGFIX: Leerzeichen vor und hinter String entfernen
						$Auspraegungen[$Anz_Merkmale]=trim ($Auspraegungen[$Anz_Merkmale]);
						$Auspraegung_Name[$Anz_Merkmale] = trim ($Auspraegung_Name[$Anz_Merkmale]);
						
						$AuspraegungenID[$Anz_Merkmale]=$Auspraegung_Name[$Anz_Merkmale];
						$Attributecode =$MerkmaleCode[$Anz_Merkmale];
						
						// BUGFIX: Nur für DropDown (select) Werte erforderlich, daher ggfls anlegen
							if ($Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!="" && 
								$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!=" " && 
								$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!="null") {
								// get_option_id_by_attribute_code_and_option_value legt nicht existente Werte mit an, 
								// wird sonst hier aber nicht benötigt.
								$optionsid = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], 
									$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal],$store_id);
								//$Auspraegung_Name[$Anz_Merkmale]=$optionsid;	
								if ($Merkmale[$Anz_Merkmale]=='auswahl' || $Merkmale[$Anz_Merkmale]=='dmc_auswahl'
								|| $Merkmale[$Anz_Merkmale]=='groesse' || $Merkmale[$Anz_Merkmale]=='dmc_groesse'
								|| $Merkmale[$Anz_Merkmale]=='farbe' || $Merkmale[$Anz_Merkmale]=='dmc_farbe'
								)
								$AuspraegungenID[$Anz_Merkmale]=$optionsid;		
								// OptionsID statts Optionswert nur für DropDown (select) Werte erforderlich
								//if (strpos(dmc_get_attribute_type ($attribute_group_name), 'select') !== false) {
									//$Auspraegungen[$Anz_Merkmale]=$optionsid;
								//}								
							}
						/*if (dmc_sql_select_value( "frontend_input", "eav_attribute", "attribute_code='".$Attributecode."'")) { 
							if (is_file('../../userfunctions/products/dmc_create_attribute_option.php')) 
								include ('../../userfunctions/products/dmc_create_attribute_option.php');
							else if (is_file('./userfunctions/products/dmc_create_attribute_option.php')) 
								include ('./userfunctions/products/dmc_create_attribute_option.php');
							else if (is_file('../../functions/products/dmc_create_attribute_option.php')) 
								include ('../../functions/products/dmc_create_attribute_option.php');
							else 
								include ('./functions/products/dmc_create_attribute_option.php');
						} // end if*/
					} else { // bis 1.6
						// OptionsID ermitteln
						if (dmc_attribute_exists($Merkmale[$Anz_Merkmale]) && $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!="" && 
							$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!=" " && $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]!="null") {
							$attributeid = dmc_get_attribute_id_by_attribute_code($attr_type_id,$Merkmale[$Anz_Merkmale]);
							//if (DEBUGGER>=1) fwrite($dateihandle,"224 - attributeid = ".$attributeid."\n");	// Std 80 fuer color
							// Wenn Ausprägung (Value)  NICHT in DB Table vorhanden	
							// und nicht dem Merkmal zugeordnet (d.h. z.b. Innengewinde kann mehreren Merkmalen zugeordnet sein) - > Anlegen
							$optionsid = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal],$store_id);
							if ($optionsid=="") {
									// Neue Optionsid ermitteln
									$optionsid = dmc_get_next_id("option_id","eav_attribute_option_value");
									// Attribute zu Option verknuepfen
									dmc_sql_insert("eav_attribute_option", "(option_id, attribute_id, sort_order)","(".$optionsid.", ".$attributeid." , ".$Auspraegung_Order[$Anz_Merkmale].")");					
									// Options Wert eintragen
									dmc_sql_insert("eav_attribute_option_value", "(value_id, option_id, store_id, value)","(NULL, '".$optionsid."', '".$store_id."', '".$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal]."')");
									$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal] = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal],$store_id);					
								} else {
									//if (DEBUGGER>=1) fwrite($dateihandle,"235 - Entry existiert.\n");
									// ID für Merkmal - Ausprägung ermitteln
									$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal] = $optionsid; 
									//if (DEBUGGER>=1) fwrite($dateihandle, "238/$Anz_Auspraegungen_Merkmal= ".$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal].".\n");	
								} // end if 
						} else {
							// Attribut nicht in DB Table vorhanden
							$message = "Merkmal ".$Merkmale[$Anz_Merkmale]." nicht in DB Table vorhanden oder Auspraegung $Anz_Merkmale = ".$Auspraegung_Name[$Anz_Merkmale]."/$Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal] nicht uebergeben.";
							dmc_write_error("dmc_map_attributes", "map merkmal", "136ff", "Artikelnummer:".$Artikel_Artikelnr." -> ".$message, true, false, $dateihandle);
							// TODO ? Attribute anlegen
							$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal] = get_option_id_by_attribute_code_and_option_value($Merkmale[$Anz_Merkmale], $Auspraegung_Name_Merkmal[$Anz_Auspraegungen_Merkmal],$store_id);					
							//	$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal] = '280273';
						} // end if dmc_entry_exist
						// IDs zusammensetzten
						if ($Anz_Auspraegungen_Merkmal==0) {
							// Erste ID bzw wenn kein MultiFeld einzige ID
							$AuspraegungenID[$Anz_Merkmale]=$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal];							
						} else {
							$AuspraegungenID[$Anz_Merkmale] .= ','.$AuspraegungenID_Multi[$Anz_Auspraegungen_Merkmal];
						}
						// colorcode (nicht mappen) zurueck setzten auf color
						if ($Merkmale[$Anz_Merkmale]=="colorcode") { 
							$Merkmale[$Anz_Merkmale]="color";
							$AuspraegungenID[$Anz_Merkmale]=$Auspraegung_Name[$Anz_Merkmale];
						}
						if (DEBUGGER>=1) fwrite($dateihandle,"183 Neue ID =".$AuspraegungenID[$Anz_Merkmale]." statt ".$Auspraegung_Name[$Anz_Merkmale]."\n");
						// Multi nicht gefuellt = ,, -> entfernen
						for ( $r=0; $r <= 5; $r++) { 
							$AuspraegungenID[$Anz_Merkmale] = str_replace(",,", ",", $AuspraegungenID[$Anz_Merkmale]);
						}
					}
				} // end for
			} // endif nicht colorcode
			
		//	if (DEBUGGER>=1) fwrite($dateihandle, " 184 - ".$AuspraegungenID[$Anz_Merkmale]."\n");	
			// Bei Multi ist letzes Zeichen ein , .> entfernen
			if ($Anz_Auspraegungen_Merkmal>0) {
				// $AuspraegungenID[$Anz_Merkmale] = substr($AuspraegungenID[$Anz_Merkmale],0,-1);
			}
			
			//  if (DEBUGGER>=1) fwrite($dateihandle, "265 AuspraegungenID No ".$Anz_Merkmale." mit ".$Merkmale[$Anz_Merkmale]." = ".$AuspraegungenID[$Anz_Merkmale]."\n");	
		} // End for
		
	
?>
	