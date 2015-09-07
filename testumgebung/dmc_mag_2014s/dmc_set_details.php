<?php
// Skriptlaufzeit hochsetzen
ini_set('max_execution_time', 600); 

/****************************************************************************
*                                                                           *
*  dmConnector  for magento shop											*
*  dmc_set_details.php														*
*  Funktionen um Details zu schreiben										*
*  Copyright (C) 2009-2013 DoubleM-GmbH.de									*
*                                                                        	*
Funktionalitäten von Magento Details Übergabe - 
Parameter 1 "ExportModus" des Details SQL Statements	
	
		ExportModus -> 'pdfs'
		Exportmöglichkeit von Artikel PDF Dateien
		
		// ExportModus -> 'language_by_api' decrepated
		ExportModus -> 'languages'
		Exportmöglichkeit von Artikel Fremdsprachenbezeichnungen 
		
		ExportModus -> 'catlanguages'
		Exportmöglichkeit von Kategorie Fremdsprachenbezeichnungen
		
		ExportModus -> 'product_to_categorie'
		Exportmöglichkeit von separaten Artikel - Kategorie Zuweisungen
		
		ExportModus -> 'dyn_nav_text'
		Exportmöglichkeit von Artikeltextzeilen aus Microsoft Dynamics NAV
		
		ExportModus -> 'dmc_delete_tier_price'
		Aufruf um alle Staffelpreise zu löschen
		
		ExportModus -> 'staffelpreis' oder 'staffelpreise'
		Exportmöglichkeit von Artikel Staffelpreise
	
		ExportModus -> 'ustorelocator_location'
		Zuordnungen von google maps Funktionalitäten
	
		ExportModus -> 'order_update'
		Änderung vom Auftragsstatus
	
		ExportModus -> 'customer_prices'
		Übergabe kundenindividueller Preise in zugehörige Extensiontabelle
	
		ExportModus -> 'dmc_invoice_create'
		Exportmodus Rechnungen anlegen in entsprechender Extensiontabelle

		ExportModus -> 'set_attribute_value'
		Update per API auf ein Artikelattribut-Wert
	
		ExportModus -> 'language_set_attribute_value'
		Fremdsprachenbezeichungen für Attributwerte setzen
	
		ExportModus -> 'set_store_prices'
		Preise fuer spezielle Stores setzen
	
		ExportModus -> 'dyn_nav_text_cat'
		Exportmöglichkeit von Kategorietextzeilen aus Microsoft Dynamics NAV
		
		ExportModus -> 'customer_discount_group'
		Übergabe kundenindividueller Rabatte in zugehörige Extensiontabelle
	
		ExportModus -> 'customer_discount_rule'
		Übergabe kundenindividueller Rabattregeln in zugehörige Extensiontabelle
	
		ExportModus -> 'dmc_documents_header'
		Dokumentinformationen in Dokument Extensiontabelle anlegen
		
		ExportModus -> 'dmc_documents_header'
		Dokumentpositionen in Dokument Extensiontabelle anlegen
		
		ExportModus -> 'dmc_document_hub'
		Zuordnung und Verteilung von Online Dokumenten vornehmen
		
		ExportModus -> 'dmc_handelsstueckliste'
		Handelsstücklisteninformationen in zugehörige Extensiontabelle hinterlegen
	
		ExportModus -> 'dmc_magento_customer_prices'
		Übergabe kundenindividueller Preise in zugehörige Extensiontabelle
	
		ExportModus -> 'dmc_magento_file_downloads'
		Exportmodus Dokumente fuer Extension "File Downloads & Product Attachments Magento Extension"
		
		ExportModus -> 'dmc_set_magento_attribute_values' 
		Übergabe, Anlage und Updates auf eine Reihe von Attributswerten durchführen
	
		ExportModus -> 'dmc_set_dispo_table_values' 
		Übergabe und Anlage Bestände Dispositions Tabelle

		ExportModus -> 'index_shop_neu' 
		Indexe neu aufbauen
		
		ExportModus -> 'dmc_magento_customer_group_prices'
		Kundengruppenpreise anlegen
	
		ExportModus -> 'dmc_de_aktive_product'
		Schnelles aktivieren und deaktivieren von Produkten

		ExportModus -> 'dmc_attach_download_link'
		Einem Downloadartikel einen Link zuordnen
		
*****************************************************************************/

		
ini_set("display_errors", 1);
error_reporting(E_ERROR);

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_set_details($StoreView, $client, $sessionId){
		
		global  $action, $version_major, $version_minor, $dateihandle; 		
		
		if (DEBUGGER>=1) fwrite($dateihandle, " dmc_set_details export = ".$_POST['ExportModus']." / ".$_GET['ExportModus']."\n");
	 	  /* Details, z.b.
		* 	Fa JH2000:
		* Freifeld1 (Art) = pdfs, Freifeld2 = Artikelnummer, Freifeld3 = Upload Beschreibung1, Freifeld4 = Upload1, Freifeld5 = Upload Beschreibung2, Freifeld6 = Upload2, Freifeld7 = Upload Beschreibung3, Freifeld8 = Upload3
		
		*/
		
		for ($i=1;$i<=12;$i++) {
			// $Freifeld{$i} = $_POST["Freifeld{$i}"];  
			$Freifeld{$i} = isset($_POST["Freifeld{$i}"]) ? $_POST["Freifeld{$i}"] : $_GET["Freifeld{$i}"];
		}
		
		$ExportModusSpecial = $Freifeld{1};
		
		if (DEBUGGER>=1) fwrite($dateihandle, "ExportModusSpecial=$ExportModusSpecial\n");
		
	/*	if (DEBUGGER>=1) {
		  	for ($i=1;$i<=12;$i++) {
				if (DEBUGGER>=1)  fwrite($dateihandle, "Freifeld{$i} = ".$Freifeld{$i}."\n");	
			}
		}		*/
		
		// Exportmodus pdf
		if ($ExportModusSpecial=='pdfs') {
			// select DISTINCT 'pdfs' as ExportModus, p.Artikelnummer AS Bestellnummer, a.ArchivPfad AS PDF_Datei, '' as Freifeld4, '' as Freifeld5,  '' as Freifeld6,  '' as Freifeld7, '' as Freifeld8, '' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM Archiv AS a, beleg as b WHERE  a.GeaendertAM > '12.02.2011' AND a.Archivsystem='PDF' AND a.Druckvorlage='REC' AND a.blobkey='QR'+b.Belegnummer
			$Artikel_Artikelnr = $Freifeld{2};
			
				/* $sql_data_array = array(	'products_upload_title_1' => $Freifeld{3},
											'products_upload_file_1' => $Freifeld{4},
											'products_upload_title_2' => $Freifeld{5},
											'products_upload_file_2' => $Freifeld{6},
											'products_upload_title_3' => $Freifeld{7},
											'products_upload_file_3' => $Freifeld{8}
											);	*/
											
											
		} // end exportmodus pdfs

		// Exportmodus languages
		if ($ExportModusSpecial=='languages') {
			if (DEBUGGER>=1) fwrite($dateihandle, "Export Sprachen\n");
			// select 'languages' AS uebertragungsart, ab.Artikelnummer AS Artikel_Artikelnr, '2' AS SprachID, '' as Artikel_Preis, ab.Bezeichnung AS Artikel_Bezeichnung, ISNULL(ab.Langtext, ab.HTMLLangtext) AS Artikel_Text, ab.Zusatz AS Artikel_Kurztext, '' AS Meta_Title, '' AS Meta_Desc, '' AS Meta_Keyw, '' AS Artikel_Merkmal, '' AS Artikel_Auspraegung FROM ART as p LEFT JOIN ARTBEZ as ab ON p.Artikelnummer = ab.Artikelnummer  WHERE ab.Sprache='E' AND p.ShopAktiv = 'true' AND p.Artikelnummer like '%' AND (p.GeaendertAm > GETDATE() - 365)

			// select distinct 'language_by_api' as ExportModus, p.APOID_ELEMENT_COUNT_0 as Artikel_Artikelnr, '12' as Store_View, (Select MIN(PRICE_ELEMENT_COUNT_0) AS preis FROM ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku2 INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulangtemp ON (sku2.PARENT_ELEMENT_ROW_ID_XX = skulangtemp.ROW_ID_XX AND skulangtemp.LANGUAGE_ELEMENT_COUNT_0='-S-S')  WHERE (p.ARTNUM_ELEMENT_COUNT_0 = sku2.ARTNUM_ELEMENT_COUNT_0)) as Artikel_Preis, p.NAME_ELEMENT_COUNT_0 as Artikel_Bezeichnung,  cast(p.DESCRIPTION_ELEMENT_COUNT_0 as VARCHAR(32000)) as Artikel_Text, ''  as Artikel_KurzText, p.NAME_ELEMENT_COUNT_0||' im Shop' as Artikel_MetaTitle, p.NAME_ELEMENT_COUNT_0||' im Shop' as Artikel_MetaDescription, p.NAME_ELEMENT_COUNT_0 as Artikel_MetaKeywords, 'material@washcomment' as Merkmal, p.material_ELEMENT_COUNT_0||'@'||p.washcomment_ELEMENT_COUNT_0 as Auspraegung , '' AS FreiFeld11, '' AS FreiFeld12 FROM ARTICLE_AS_CHILD_IN_WSARTICLEDATA p INNER JOIN ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku ON (p.ARTNUM_ELEMENT_COUNT_0 = sku.ARTNUM_ELEMENT_COUNT_0)  INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulang ON (sku.PARENT_ELEMENT_ROW_ID_XX = skulang.ROW_ID_XX AND skulang.LANGUAGE_ELEMENT_COUNT_0='-S-S') INNER JOIN HEADER_AS_CHILD_IN_WSARTICLEDATA plang ON (p.PARENT_ELEMENT_ROW_ID_XX = plang.ROW_ID_XX AND plang.LANGUAGE_ELEMENT_COUNT_0='-S-S') WHERE p.APOID_ELEMENT_COUNT_0 like '8666#%'
			$Artikel_Artikelnr = $Freifeld{2};
			$Sprache_id = $Freifeld{3};			// Store_ID
			$Artikel_Preis=$Freifeld{4};
			$Artikel_Bezeichnung = str_replace("'", "`", $Freifeld{5});
			$Artikel_Text = str_replace("'", "`",$Freifeld{6});
			$Artikel_Kurztext = str_replace("'", "`",$Freifeld{7});
			$Meta_Title = str_replace("'", "`",$Freifeld{8});
			$Meta_Desc = str_replace("'", "`",$Freifeld{9});
			$Meta_Keyw = str_replace("'", "`",$Freifeld{10});
			$Artikel_Merkmal = str_replace("'", "`",$Freifeld{11});
			$Artikel_Auspraegung = str_replace("'", "`",$Freifeld{12});
			if (is_file('userfunctions/products/dmc_art_functions.php')) include_once ('userfunctions/products/dmc_art_functions.php');
				else include_once ('functions/products/dmc_art_functions.php');
			
			// IDs ermitteln
			$ENTITY_TYPE_ID = dmc_get_entity_type_id_by_entity_type_code ('catalog_product');
			$ATTR_ID_NAME =  dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'name');
			$ATTR_ID_LANGTEXT = dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'description');
			$ATTR_ID_KURZTEXT = dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'short_description');
			$ATTR_ID_META_DESC = dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'meta_description');
			$ATTR_ID_META_KEYW = dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'meta_keyword');
			$ATTR_ID_META_TITLE = dmc_get_attribute_id_by_attribute_code($ENTITY_TYPE_ID,'meta_title');
			
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
			
			if (DEBUGGER>=1 && $ProductId<>"") fwrite($dateihandle, "language_by_api without api Artikel $Artikel_Bezeichnung mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId Sprache $Sprache_id setzten\n");
			if (DEBUGGER>=1 && $ProductId=="") fwrite($dateihandle, "Artikel NICHT VORHANDEN: $Artikel_Bezeichnung mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId Sprache $Sprache_id.\n");
			
			// Wenn Artikel existiert, Details zuordnen 
			if ($ProductId <> "") {
				
				// Artikel Bezeichnung eintragen : 
				if ($Artikel_Bezeichnung!='') {
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_NAME." AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where))
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$Artikel_Bezeichnung."'", $where);
				else
					// Insert
					dmc_sql_insert("catalog_product_entity_varchar", 
									"(entity_type_id, attribute_id, store_id, entity_id, value)", 
									"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_NAME.", ".$Sprache_id.", ".$ProductId.", '".$Artikel_Bezeichnung."')");
				}
				
				// Description eintragen : 
				if ($Artikel_Text!='') {
					 fwrite($dateihandle, "206\n");
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_LANGTEXT." AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_text', $where))
						// Update
						dmc_sql_update("catalog_product_entity_text", "value='".$Artikel_Text."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_text", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_LANGTEXT.", ".$Sprache_id.", ".$ProductId.", '".$Artikel_Text."')");
				}
				
				// Short Description eintragen : 
				if ($Artikel_Kurztext!='') {
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_KURZTEXT." AND entity_id=".$ProductId;
					fwrite($dateihandle, "221 $where\n");
					if (dmc_entry_exits('value_id', 'catalog_product_entity_text', $where))
						// Update
						dmc_sql_update("catalog_product_entity_text", "value='".$Artikel_Kurztext."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_text", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_KURZTEXT.", ".$Sprache_id.", ".$ProductId.", '".$Artikel_Kurztext."')");
				}
				
				// meta title eintragen : 
				if ($Meta_Title!='') {
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_META_TITLE." AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where))
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$Meta_Title."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_varchar", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_META_TITLE.", ".$Sprache_id.", ".$ProductId.", '".$Meta_Title."')");
				}
				
				// meta desc eintragen : 
				if ($Meta_Desc!='') {
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_META_DESC." AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where))
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$Meta_Desc."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_varchar", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_META_DESC.", ".$Sprache_id.", ".$ProductId.", '".$Meta_Desc."')");
				}
				
				// meta keywords eintragen : 
				if ($Meta_Keyw!='') {
					$where="store_id=".$Sprache_id." AND attribute_id=".$ATTR_ID_META_KEYW." AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_text', $where))
						// Update
						dmc_sql_update("catalog_product_entity_text", "value='".$Meta_Keyw."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_text", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_META_KEYW.", ".$Sprache_id.", ".$ProductId.", '".$Meta_Keyw."')");
				}
				
					// catalog product flat updaten
				$flat_table="catalog_product_flat_".$Sprache_id;
				$where="entity_id=".$ProductId;
				if (dmc_entry_exits('entity_id', $flat_table, $where)) {
					// Update
					$do_update = "";
					if ($Artikel_Bezeichnung!="") $do_update .= "name='$Artikel_Bezeichnung', ";
					if ($Artikel_Kurztext!="") $do_update .= "short_description='$Artikel_Kurztext', ";
					if ($Artikel_Preis!="") $do_update .= "price='$Artikel_Preis', ";
					if ($do_update != "") {
						$do_update .= "updated_at='now()'";
						dmc_sql_update($flat_table, $do_update, $where);
					}
				}
				// else
					// Insert
				//	dmc_sql_insert("catalog_product_entity_varchar", 
					//				"(entity_type_id, attribute_id, store_id, entity_id, value)", 
						//			"(".$ENTITY_TYPE_ID.", ".$ATTR_ID_NAME.", ".$Sprache_id.", ".$ProductId.", '".$Artikel_Bezeichnung."')");
				
				
				
				// Moegliche Attribute ermitteln
				if ( $Artikel_Merkmal!="" ) {
					// Merkmale ermitteln - werden als attribue1@attribe2@... übergeben
					//  if (DEBUGGER>=1) fwrite($dateihandle, "Merkmal = ".$Artikel_Merkmal." \n Auspraegung = ".$Artikel_Auspraegung."\n");
					$Merkmale = explode ( '@', $Artikel_Merkmal);
					//for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
					//{
					//   if (DEBUGGER>=1) fwrite($dateihandle, "Merkmal ".$Anz_Merkmale." = ".$Merkmale[$Anz_Merkmale]."\n");
					//}			
					
					// Auspreageungen  und MerkmaleIDs ermitteln - werden als Auspreageung1@Auspreageung2@... übergeben
					$Auspraegungen = explode ( '@', $Artikel_Auspraegung);
					$attr_type_id=ENTITY_TYPE_ID;
					for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Auspraegungen ); $Anz_Merkmale++ )
					{
						if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
						if ($Merkmale[$Anz_Merkmale]!="attribute_set") 
						{
								$MerkmaleID[$Anz_Merkmale]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Merkmale[$Anz_Merkmale]);		
						//		if (DEBUGGER>=1) fwrite($dateihandle, "Superattribute=".$Merkmale[$Anz_Merkmale]."/".$MerkmaleID[$Anz_Merkmale]."\n");
						} // end if
					} // end for
						
					// AusprägungsIDs und AttributeIDs  aus Datenbank ermitteln
					for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
					{
						// Punkt durch Kommata ersetzen
						$Auspraegungen[$Anz_Merkmale] = str_replace(",", ".", $Auspraegungen[$Anz_Merkmale]);
						// Überprüfen, ob der Ausprägung (Value)  eine Sortierung mitgegeben wurde (z.B. Merkmal:10) - Trennzeichen ":"
						if (preg_match('/:/', $Auspraegungen[$Anz_Merkmale])) 
						{
							// Wert + VPE
							list ($Auspraegung_Name[$Anz_Merkmale], $Auspraegung_Order[$Anz_Merkmale]) = split (":", $Auspraegungen[$Anz_Merkmale]);
						} else {
							$Auspraegung_Name[$Anz_Merkmale] = $Auspraegungen[$Anz_Merkmale];
							$Auspraegung_Order[$Anz_Merkmale] = 0;
						}
							
						// colorcode (nicht mappen) zurueck setzten auf color
						if ($Merkmale[$Anz_Merkmale]=="colorcode") { 
							$Merkmale[$Anz_Merkmale]="color";
							$AuspraegungenID[$Anz_Merkmale]=$Auspraegung_Name[$Anz_Merkmale];
						}
						if (DEBUGGER>=1) fwrite($dateihandle, "AuspraegungenID No ".$Anz_Merkmale." mit ".$Merkmale[$Anz_Merkmale]." = ".$AuspraegungenID[$Anz_Merkmale]."\n");	
					} // End FOR Merkmale
					
					
					// auspraegungen uebersetzen
					for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
					{
						// if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegung ".$Anz_Merkmale." = ".$Auspraegungen[$Anz_Merkmale]."\n");
						/*if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $AuspraegungenID[$Anz_Merkmale]!='280273'
							&& $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
							&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit"
							&& is_numeric($MerkmaleID[$Anz_Merkmale]) && is_numeric($AuspraegungenID[$Anz_Merkmale])) */
						if ($Merkmale[$Anz_Merkmale]!="attribute_set"  && $Merkmale[$Anz_Merkmale]!="base_price_amount" && $Merkmale[$Anz_Merkmale]!="base_price_base_unit"
							&& $Merkmale[$Anz_Merkmale]!="base_price_base_amount" && $Merkmale[$Anz_Merkmale]!="base_price_unit") 
						{
							if (DEBUGGER>=1) fwrite($dateihandle, "Sprache ".$Sprache_id." Simple zuweisen MerkmalID ".$MerkmaleID[$Anz_Merkmale]." = AuspraegungenID ".$AuspraegungenID[$Anz_Merkmale]."\n");	
							$option_id=get_option_id_by_attribute_code_and_entity($Merkmale[$Anz_Merkmale], $ProductId, $MerkmaleID[$Anz_Merkmale]);
							
							// Nur wenn Standard-Wert auch bereits zugewiesen
							if ($option_id!="") {
								if ($option_id=='text' || $option_id=='varchar') // Attribut hat als Werte "text"
								{
									$table = "catalog_product_entity_".$option_id;

									$columns = "(`entity_type_id` ,`attribute_id` ,`store_id`,`entity_id`,`value`)";
									$values = "(".$attr_type_id.", ".$MerkmaleID[$Anz_Merkmale].", ".$Sprache_id.", ".$ProductId.",'".$Auspraegung_Name[$Anz_Merkmale]."')";

									// Eventuell alte Zuordnungen löschen
									$Sprache_id=0;
									if (dmc_entry_exits("value_id", "catalog_product_entity_int", " entity_id='".$ProductId."' AND attribute_id='".$MerkmaleID[$Anz_Merkmale]."' AND store_id=".$Sprache_id." ")) 
										dmc_sql_delete("catalog_product_entity_int", " entity_id='".$ProductId."' AND attribute_id='".$MerkmaleID[$Anz_Merkmale]."'AND store_id=".$Sprache_id." ");
									dmc_sql_insert($table, $columns, $values);	
								}
								else // drop downs
								{
									$table = "eav_attribute_option_value";  
									$columns = "(`option_id`, `store_id`,`value`)";
									$values = "(".$option_id.", ".$Sprache_id.", '".$Auspraegungen[$Anz_Merkmale]."')";		
									// Eventuell alte Zuordnungen löschen
									if (dmc_entry_exits("value_id", "eav_attribute_option_value", " option_id='".$option_id."' AND store_id=".$Sprache_id." ")) 
										dmc_sql_delete("eav_attribute_option_value", " option_id='".$option_id."' AND store_id=".$Sprache_id." ");
									dmc_sql_insert($table, $columns, $values);
								}
							} // if ($option_id!="") 
							
								
						} // end if
					} // end for
				
				} // End If Artikel Merkmale
				
				//  Produkt im ... StoreView aktivieren
				// dmc_set_products_status($ProductId, 1, 0); 		// default
				// dmc_set_products_status($ProductId, 1, 1); 		// english
			
			} //  endif Wenn Artikel existieren
		} // end exportmodus languages
		
		// Exportmodus categorie languages
		if ($ExportModusSpecial=='catlanguages') {
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export Kategorie Sprachen\n");
			// Select distinct 'catlanguages' as ExportModus, gr.ID_ELEMENT_COUNT_0 as Artikelgruppe, '4' as Store_View,  gr.LABEL_ELEMENT_COUNT_0 as Bezeichnung, '' as Langtext, CASE WHEN gr.ACCENTFLAGS_ELEMENT_COUNT_0 = 'hidden' THEN 0 ELSE 1 END as Aktiv FROM WEBSHOPCATEGORY_AS_CHILD_IN_WSNAVTREEDATA gr INNER JOIN HEADER_AS_CHILD_IN_WSNAVTREEDATA navlang ON (gr.PARENT_ELEMENT_ROW_ID_XX = navlang.PARENT_ELEMENT_ROW_ID_XX AND navlang.LANGUAGE_ELEMENT_COUNT_0='-GB-GB')

			$Category_ID = $Freifeld{2};
			$Sprache_id = $Freifeld{3};			// Store_ID
			$Kategorie_Bezeichnung = $Freifeld{4};
			$Kategorie_Text = $Freifeld{5};
			$Kategorie_Aktiv = $Freifeld{6};
			
			// Check if category already exists (-1 if not)
			if (!GENERATE_CAT_ID)
				$cat_id=dmc_get_category_id("entity_id=".$Category_ID);	
			else 
				$cat_id=dmc_category_exists($Category_ID);	
			
			if (DEBUGGER>=1 && $cat_id<>-1) fwrite($dateihandle, "Kategorie $cat_id mit Name=$Kategorie_Bezeichnung Sprache $Sprache_id setzten\n");
		
			// Wenn Kategorie existiert, Details zuordnen 
			if ($cat_id<>-1) {
			
				// Categorie Bezeichnung eintragen : 
				$where="store_id=".$Sprache_id." AND attribute_id=".ATTR_ID_CATEGORY_NAME." AND entity_id=".$cat_id;
				if (dmc_entry_exits('value_id', 'catalog_category_entity_varchar', $where))
					// Update
					dmc_sql_update("catalog_category_entity_varchar", "value='.$Kategorie_Bezeichnung.'", $where);
				else
					// Insert
					dmc_sql_insert("catalog_category_entity_varchar", 
									"(entity_type_id, attribute_id, store_id, entity_id, value)", 
									"(3, ".ATTR_ID_CATEGORY_NAME.", ".$Sprache_id.", ".$cat_id.", '".$Kategorie_Bezeichnung."')");
									
				
			} //  endif Wenn Categorien existieren
		} // end exportmodus categorie languages
		
		// Exportmodus product_to_categorie
		if ($ExportModusSpecial=='product_to_categorie') {
			// select 'product_to_categorie' as Freifeld1,  p.[Artikel-Nr_] AS Artikel_Artikelnr, '2' AS SpracheID, CASE WHEN p.[Produktgruppe]='' OR p.[Produktgruppe] IS NULL THEN SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]),1,19) ELSE isnull(SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]+'_'+p.[Produktgruppe]),1,19), SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]),1,19)) END AS Artikel_Kategorie_ID ,'' as Freifeld4,'' as Freifeld5,'' as Freifeld6,'' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12  FROM [fms$Mehrfachzuordnung] AS p WHERE p.[Im Webshop ausblenden] = 0
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export product_to_categorie\n");
			
			$Artikel_Artikelnr = $Freifeld{2};
			$Sprache_id = $Freifeld{3};			// Store_ID
			$Kategorie_id = $Freifeld{4};
		
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
				
			// Check if category already exists and get Magento Cat_id(-1 if not)
			$cat_id=dmc_category_exists($Kategorie_id );	
		
			
			if (DEBUGGER>=1 && $ProductId<>"" && $cat_id<>-1) fwrite($dateihandle, "Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId Sprache $Sprache_id  zu Magento Kategorie mit id $cat_id \n");
			if (DEBUGGER>=1 && ($ProductId=="" || $cat_id == -1) ) fwrite($dateihandle, "Artikel NICHT VORHANDEN: Artikel_Artikelnr = $Artikel_Artikelnr.\n");
			
			// Wenn Artikel  und Kategorie existiert -> zuordnen 
			if ($ProductId <> "" && $cat_id<>-1) {
				$client->call(
					$sessionId, 
					'category.assignProduct', 
					array(
						$cat_id, 
						$Artikel_Artikelnr
						)
					);
			} //  endif Wenn Artikel und Categorien existieren
		} // end exportmodus cproduct_to_categorie
		
		// Exportmodus Länder Preislisten Walkowiak
		if ($ExportModusSpecial=='laenderpreisliste') {
		
			// Übergabe: select 'laenderpreisliste' as art, '1' as Preisliste, pr.Bezeichnung, vk.Artikelnummer, vk.AuspraegungID, vk.AbMenge, vk.Einzelpreis, pr.GueltigVon, pr.GueltigBis, pr.IstBruttopreis,  pr.WKz AS Waehrung, '' as Freifeld12 FROM KHKPreislistenArtikel AS vk INNER JOIN KHKPreislisten AS pr ON vk.ListeID = pr.ID WHERE (pr.Bezeichnung = 'Standard (Euro)') AND (vk.AbMenge = 0) AND (pr.Aktiv = '-1') AND (pr.GueltigBis > GETDATE() OR pr.GueltigBis IS NULL) AND (pr.GueltigVon < GETDATE())
				/*
				* countryprice.setCountryprices
				          o Argumente
				                + Product ID
				                + Array mit allen neuen Länderpreisen:
				                      # Jeder Array Eintrag ist wieder ein Array mit den keys:
				                            * 'all_customer_groups': Preis gilt für alle Kundengruppen
				                            * 'customer_group_id': Kundengruppe, für die der Preis gilt. Wird ignoriert wenn 'all_customer_groups' true ist
				                            * 'value': Der Preis
				                            * 'website_id': ID der Website
				                            * 'country_group_id': Die Ländergruppe, für die der Preis gilt 	
			*/
		
		if (DEBUGGER>=1) {
			$dateiname="./dmconnector_log_magento_status.txt";	
			$dateihandle = fopen($dateiname,"a");
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_write_art Session=".$sessionId."\n");
		}
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export laenderpreisliste\n");
			
			$Preisliste_Nr = $Freifeld{2};
			$Preisliste_Name= $Freifeld{3};
			$Artikel_Artikelnr= $Freifeld{4};
			$Artikel_AuspraegungID= $Freifeld{5};
			$AbMenge= $Freifeld{6};
			$Einzelpreis= $Freifeld{7};
			$GueltigVon= $Freifeld{8};
			$GueltigBis= $Freifeld{9};
			$IstBruttopreis= $Freifeld{10};
			$Waehrung= $Freifeld{11};
			$website_id = $Freifeld{12};
		
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
			
			if (DEBUGGER>=1 && $ProductId<>"") fwrite($dateihandle, "Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr mit Preisliste_Nr =$Preisliste_Nr Preis $Einzelpreis setzten\n");
			if (DEBUGGER>=1 && $ProductId=="") fwrite($dateihandle, "Artikel NICHT VORHANDEN: Artikel_Artikelnr = $Artikel_Artikelnr .\n");
			
			// Wenn Artikel existiert, Preisliste zuordnen 
			if ($ProductId <> "") {
				$pricelist_data = array(				    
					 'all_customer_groups' => true,
					 // 'customer_group_id' => $Preisliste_Nr,
					 'value' => $Einzelpreis,
					 'website_id' => $website_id,
					 'country_group_id' => $Preisliste_Nr
				);
					 
				// Response true oder false
				$ergebnis = $client->call($sessionId, 'countryprice.setCountryprices', array($ProductId, $pricelist_data));
				
				if ($ergebnis===false && DEBUGGER>=1)
					 fwrite($dateihandle, "Kein Preis geschrieben fuer Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr");
				else 
					fwrite($dateihandle, "Preis geschrieben fuer Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr");
									
				// $client->call($sessionId, 'category.assignProduct', array($categoryId, 'someProductSku', 5));
								
				
			} //  endif Wenn Artikel existiert
		} // Exportmodus Länder Preislisten Walkowiak
					  
	
		// Exportmodus beschriebungen fuer Dynamics Nav Artikel
		if ($ExportModusSpecial=='dyn_nav_text') {
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export dyn_nav_text\n");
			
			$Artikel_Artikelnr = $Freifeld{2};
			$Zeilennummer = $Freifeld{3};			// Zeilennummer
			$Description = $Freifeld{4};		// Text
		
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
			
			if (DEBUGGER>=1) fwrite($dateihandle, "Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId Zeilennummer $Zeilennummer  zu Beschreibung  $Description \n");
		
			// Wenn Artikel  existiert -> zuordnen 
			if ($ProductId <> "") {
				
				if ($Zeilennummer<=10000) {
					// Ueberpruefen, ob erste Zeile uebermittelt, dann komplett neu initialisieren
					$updateProductData = array(				    
						'short_description' => $Description,		// erste 3 Zeilennummern
					   'description' => $Description
					);  // end updateProductData
				} else { // Folgezeilen
					// Alte Beschreibung ermitteln
					$products = $client->call($sessionId, 'product.info', array($Artikel_Artikelnr));
					$Description_old=$products[description];
					$Description = $Description_old."<BR />".$Description;
					$updateProductData = array(				    
						//    'short_description' => $Artikel_Kurztext,	
					   'description' => $Description
					);  // end updateProductData
					// Zeile 2 und 3 fuer shortdesciption verwenden.
					if ($Zeilennummer==20000 || $Zeilennummer==30000) {
						$Short_Description_old=$products[short_description];
						$Short_Description = $Short_Description_old."<BR />".$Short_Description;
						$updateProductData[short_description] = $Short_Description;
					}
				} // end if 
				
				// update product
				if ($client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $updateProductData)))	
							$newProductId = dmc_get_id_by_artno($Artikel_Artikelnr);					
				else $newProductId = 28021973;	// no update possible						
				
			} //  endif Wenn Artikel  existiert
		} // end exportmodus dyn_nav_text
		
		
		if ($ExportModusSpecial=='dmc_delete_tier_price') {
		
		// select TOP(1) 'dmc_delete_tier_price' as ExportModus, 'all' AS Artikel_Artikelnr, '' AS Artikel_Preis, ''  AS Artikel_Preis_Gruppe, ''  AS Artikel_Preis_Ab_Menge, '' as Waehrung, '' as Steuersatz, '' as Freifeld8, '' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM ART AS p INNER JOIN ARPREIS AS vk ON p.Artikelnummer = vk.Artikelnummer WHERE PreisTyp='G' 
			$Artikel_Artikelnr = $Freifeld{2};
			if ($Artikel_Artikelnr='all') {
				// alle Kundengruppenpreise loeschen 
				$query='DELETE FROM '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price';
				 dmc_sql_query($query);
			}
		} // end  if ($ExportModusSpecial=='dmc_delete_tier_price') {
		
	// Staffelpreise mit Unterstuertzung von Kundengruppen.
	if ($ExportModusSpecial=='staffelpreis' || $ExportModusSpecial=='staffelpreise' || $ExportModusSpecial=='dmc_set_tier_price') {
		if (DEBUGGER>=1) fwrite($dateihandle, "staffelpreise bzw dmc_set_tier_price \n");		
		// UPDATE üBER API ODER DATENBANK
		$USE_API=false;
		// select DISTINCT 'staffelpreis' as ExportModus, p.Artikelnummer AS Artikel_Artikelnr, vk.[Menge] AS Artikel_Preis_Ab_Menge, ISNULL(vk.[Preis],p.[PreisVK])-ISNULL(vk.[Abzug],0) AS Artikel_Preis, '' as Artikel_Preis2, '' as Artikel_Preis3, '' as Artikel_Preis4, '' as Artikel_Preis5, 'all' AS Magento_Artikel_Preis_Gruppe, ISNULL(vk.[Rabatt],'') AS RabattProzent, 'EUR' AS Waehrung,  '0' AS Website_ID, p.GeändertAm as timestamp FROM ewa.Artikel AS p INNER JOIN [ewa].[ArtikelStaffel] AS vk ON p.GUID=vk.Artikel WHERE (p.WebShop1Artikel = 1) 		
   		// select 'staffelpreise' as Freifeld1, vk.[No_] as Freifeld2, ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='A' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='A' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='A' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='B' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='B' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='B' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='C' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='C' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'')+'@'+ ISNULL(CONVERT(varchar(200),(select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='C' AND [Variant Code]='' AND [Item No_] = vk.[No_])),'') AS Preis1, '5@8@11@6@9@12@7@10@13' AS Preis_Gruppe, '1' as abAnzahl,'EUR' AS Waehrung, '1' as Steuersatz,  '0' as websiteNr, '' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM [Cronus$Item] AS vk WHERE ((select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='A' AND [Variant Code]='' AND [Item No_] = vk.[No_]) IS NOT NULL OR (select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='B' AND [Variant Code]='' AND [Item No_] = vk.[No_]) IS NOT NULL OR (select [Unit Price] FROM [Cronus$Sales Price] WHERE [Sales Type]=1 AND ([Price Includes VAT] = 0) AND [Minimum Quantity] = 0 AND [Starting Date]<GetDate() AND ([Ending Date]>GetDate() OR [Ending Date]='1753-01-01 00:00:00.000') AND [Sales Code] ='C' AND [Variant Code]='' AND [Item No_] = vk.[No_]) IS NOT NULL) AND vk.[No_] LIKE '%'  

		$Artikel_Artikelnr = $Freifeld{2};
		$Artikel_Preis = $Freifeld{3};
		$Artikel_Preis_Gruppe= $Freifeld{4};
		$Artikel_Preis_Ab_Menge= $Freifeld{5};
		if ($Artikel_Preis_Ab_Menge==0)
			$Artikel_Preis_Ab_Menge=1;
		$Artikel_Preis_Waehrung = $Freifeld{6};
		/* $Artikel_Preis2 = $Freifeld{5};	// Werden bei Magento (zur Zeit) nicht verwendet
		$Artikel_Preis3 = $Freifeld{6};
		$Artikel_Preis4 = $Freifeld{7};
		$Artikel_Preis5 = $Freifeld{8}; */
		$Waehrung = $Freifeld{9};
		$Artikel_Preis_Rabatt = $Freifeld{10};
			if ($Artikel_Preis_Rabatt=='') $Artikel_Preis_Rabatt=0;
		$websiteNr = $Freifeld{11};					// Multiple Websites moeglich, zB 1 oder 1@2 für Website 1 und 2
		if ($websiteNr=='') {
			$website_ids[0]=1;
		} else
			$website_ids = explode ( '@', $websiteNr);
		$storeviewNr = $Freifeld{12};				// Multiple store_views moeglich, zB 1 oder 5@7 für StoreView 5 und 7
		if ($storeviewNr=='') {
			$store_view[0]=1;
			// $store_view[1]=2;
			// $store_view[2]=4;
		} else
			$store_view = explode ( '@', $storeviewNr);
		$fehler=false;
		
		if (DEBUGGER>=1) fwrite($dateihandle, "websiteNr=$websiteNr mit website_ids[0] = ".$website_ids[0]." \n");		
		
		
		// Magento entity_id des Artikels ermitteln
		$entity_id = dmc_get_id_by_artno ($Artikel_Artikelnr);
		if ($entity_id=="-1" || $entity_id=="") break;
		
		// benoetigte Attribute_ids ermitteln
		$entity_type_id=10;
		$price_attribute_id=dmc_get_attribute_id_by_attribute_code($entity_type_id,'price');	
			
		// Staffelpreise für Artikel loeschen, wenn erster Preis fuer den Artikel kommt
		if ($Artikel_Preis_Ab_Menge=='1' && $Artikel_Preis_Waehrung=='CHF' && $website_ids[0]==1 && $store_view[0]==1) {
			$query='DELETE FROM '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price WHERE entity_id='.$entity_id;
			dmc_sql_query($query);
		}
		/*
		// Storeview und Website für die einzelnen Preise ermitteln und setzen
		if ($Waehrung == 'CHF') { // CHF Kunden
			if($Artikel_Preis_Gruppe == 1) { // chinatrading.ch Ärzte
				$websites = array(1);
				$store_views = array(1);
			}
			if($Artikel_Preis_Gruppe == 2) { // chinatrading.ch Privat
				$websites = array(4);
				$store_views = array(11);
			}
			if($Artikel_Preis_Gruppe == 3) { // chinatrading.ch Haendler
				$websites = array(5);
				$store_views = array(12);
			}
		} // End if CHF 
		
		if ($Waehrung == 'EUR') { // EUR Kunden			
			if($Artikel_Preis_Gruppe == 1) {  // medizinbaumn.de Ärzte
				$websites = array(11,12);
				$store_views = array(31, 34, 36);
			}
			if($Artikel_Preis_Gruppe == 2) {  // medizinbaumn.de Privat
				$websites = array(8);
				$store_views = array(18);
			}
			if($Artikel_Preis_Gruppe == 3) { // medizinbaumn.de Haendler
				$websites = array(9);
				$store_views = array(17);
			}
		} // End if EUR 
		*/
		
		// Ermitteln, ab mehrere Preise fuer Gruppen uebergeben
		$Artikel_Preise = explode ( '@', $Artikel_Preis);
		// Ermitteln der zugehoerigen Gruppen
		$Artikel_Preis_Gruppen = explode ( '@', $Artikel_Preis_Gruppe);
		// if (DEBUGGER>=1) fwrite($dateihandle, count ( $Artikel_Preise )." fuer ".count ( $Artikel_Preis_Gruppen )." Gruppen vorhanden\n");
	 
		if (count ( $Artikel_Preise )!=count ( $Artikel_Preis_Gruppen )) {
			$fehler=true;
			if (DEBUGGER>=1) fwrite($dateihandle,"Fehler: Unterschiedliche Anzahl von Preisen und deren Gruppen.\n");
		}
	 
		// EinzelnePreise durchlaufen
		for ( $durchlauf = 0; $durchlauf < count($Artikel_Preise); $durchlauf++ ) 
		{
			$Artikel_Preis=$Artikel_Preise[$durchlauf];
			$Artikel_Preis_Gruppe=$Artikel_Preis_Gruppen[$durchlauf];
			if ($fehler==false && $Artikel_Preis<>"") {
				// Preisberechnung, wenn separater Rabatt (oder Aufpreis) uebermittelt
				if ($Artikel_Preis_Rabatt<>0) {
					$Artikel_Preis = $Artikel_Preis - ($Artikel_Preis*$Artikel_Preis_Rabatt/100);
				}
					
				if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details exportmodus dmc_set_tier_price Preis =".$Artikel_Preis.", Group=$Artikel_Preis_Gruppe, SKU=$Artikel_Artikelnr, AbMenge=$Artikel_Preis_Ab_Menge \n");
				
				// Unterscheiden nach Anzahl
				if ($Artikel_Preis_Ab_Menge==1) {
					// Standardpreis fuer Store_Views setzen
					for ( $durchlauf_store_view = 0; $durchlauf_store_view < count($store_view); $durchlauf_store_view++ ) {
						// Ermitteln, ab mehrere Preis fuer View vorhanden
						// Dezimaltabelle
						$where="(entity_id=$entity_id AND attribute_id=$price_attribute_id AND store_id=".$store_view[$durchlauf_store_view]." )";
						if (dmc_entry_exits('entity_id', 'catalog_product_entity_decimal', $where)) {
							$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_entity_decimal SET value='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND store_id='.$store_view[$durchlauf_store_view].' ';
						} else {									
							$query='INSERT INTO '.DB_TABLE_PREFIX.'catalog_product_entity_decimal (entity_type_id, attribute_id, store_id, entity_id, value) 
							VALUES ('.$entity_type_id.','.$price_attribute_id.','.$store_view[$durchlauf_store_view].','.$entity_id.','.$Artikel_Preis.');';
						}
						dmc_sql_query($query);
						// FLAT Tabelle
						$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_flat_'.$store_view[$durchlauf_store_view].' SET price='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' ';
						dmc_sql_query($query);
						// Update Minimum Preis
						//$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_tier_price SET min_price='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND min_price>'.$Artikel_Preis.'';
						//dmc_sql_query($query);
						//$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_price SET tier_price='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND tier_price>'.$Artikel_Preis.'';
						//dmc_sql_query($query);
						if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - 728 preis $Artikel_Preis fuer store_view=".$store_view[$durchlauf_store_view].".\n");	
					}
					
				} else if ($Artikel_Preis_Ab_Menge==99999) {
					// Standardpreis
					$updateData['price']  = $Artikel_Preis;
					$updateData['tax_class_id']  = $Artikel_Steuersatz;
					
					try {
						for ( $Anz_Store_Views = 0; $Anz_Store_Views < count ( $store_view ); $Anz_Store_Views++ )
						{
							if (DEBUGGER>=1) fwrite($dateihandle, "sku ".$Artikel_Artikelnr." with website ".$website_ids[0]." with store view ".$store_view[$Anz_Store_Views]." ($Anz_Store_Views of Storeviews) with price $websitePreis\n");
							if (!$client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $updateData, $store_view[$Anz_Store_Views])))	{
									if (DEBUGGER>=1) fwrite($dateihandle, "Step $rcm of 4 - creation failed for Simple product sku ".$Artikel_Artikelnr." for website ".$website_ids[0]." with price $Artikel_Preis\n");
									$newProductId = 28021973;	// no update possible
							}
						} // end for
					} catch (SoapFault $e) {
						if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - 328 - Product prices update failed:\nError:\n".$e."\n");		 
					}
				} else {
					// Staffelpreis über API setzen
					if ($USE_API) { 
						// Bestehende Staffelpreise ermitteln
						 $ergebnis = $client->call($sessionId, 'catalog_product.info',$Artikel_Artikelnr);
						foreach($ergebnis['tier_price'] as $staffelpreis) {
							// Bestehende Staffelpreise ergaenzen, sofern nicht der übermittelte
							if ( $staffelpreis['website_id'] <> $website_ids[0])
								$tierPrices[] = array(
									'website'           => $staffelpreis['website_id'],
									'customer_group_id' => 'all',
									'qty'               => $staffelpreis['price_qty'],
									'price'             => $staffelpreis['website_price']
								);
						};	
						// Neuer Staffelpreis
						$tierPrices[] = array(
								'website'           => $website_ids[0],
								'customer_group_id' => 'all', 
								'qty'               => $Artikel_Preis_Ab_Menge,
								'price'             => $Artikel_Preis
						);
						try {
							if (DEBUGGER>=1) fwrite($dateihandle, "Update tier prices for sku ".$Artikel_Artikelnr." for website ".$website_ids[0]." amount ".$Artikel_Preis_Ab_Menge."  with price $Artikel_Preis and ".(count($tierPrices)-1)." other tier prices \n");
							if (!$client->call($sessionId, 'product_tier_price.update', array($Artikel_Artikelnr, $tierPrices)))	{
									if (DEBUGGER>=1) fwrite($dateihandle, "UPDATE FAILED\n");
									$newProductId = 28021973;	// no update possible
									}
							} catch (SoapFault $e) {
								if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - 405 - Product tier prices update failed:\nError:\n".$e."\n");		 
							}
					} else { // if (!$USE_API) 
						// Staffelpreise über Datenbank
						if ($entity_id<>'') {
							//  Staffelpreis hinzfuegen, wenn noch nicht existiert, sonst UPDATE
							
							// Einzelne websites durchlaufen
							for ( $durchlauf_website = 0; $durchlauf_website < count ( $website_ids ); $durchlauf_website++ ) {
								// Ermitteln, ab mehrere Preise fuer Gruppen uebergeben
								if ($Artikel_Preis_Gruppe=='' || $Artikel_Preis_Gruppe=='0' || $Artikel_Preis_Gruppe=='all') 
									$where="(entity_id=$entity_id AND qty=$Artikel_Preis_Ab_Menge AND website_id=".$website_ids[$durchlauf_website]." AND all_groups=1)";
								else
									$where="(entity_id=$entity_id AND qty=$Artikel_Preis_Ab_Menge AND website_id=".$website_ids[$durchlauf_website]." AND customer_group_id=".$Artikel_Preis_Gruppe.")";
								
								if (dmc_entry_exits('entity_id', 'catalog_product_entity_tier_price', $where)) {
									if ($Artikel_Preis_Gruppe=='' || $Artikel_Preis_Gruppe=='0' || $Artikel_Preis_Gruppe=='all') 
										$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price SET value='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND qty='.$Artikel_Preis_Ab_Menge.' AND website_id='.$website_ids[$durchlauf_website].' AND all_groups=1';
									else
										$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price SET value='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND qty='.$Artikel_Preis_Ab_Menge.' AND website_id='.$website_ids[$durchlauf_website].' AND customer_group_id='.$Artikel_Preis_Gruppe.' ';
								} else {
									if ($Artikel_Preis_Gruppe=='' || $Artikel_Preis_Gruppe=='0' || $Artikel_Preis_Gruppe=='all') 
										$query='INSERT INTO '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price (entity_id, all_groups, customer_group_id, qty, value, website_id) VALUES ('.$entity_id.',1,0,'.$Artikel_Preis_Ab_Menge.','.$Artikel_Preis.','.$website_ids[$durchlauf_website].');';
									else
										$query='INSERT INTO '.DB_TABLE_PREFIX.'catalog_product_entity_tier_price (entity_id, all_groups, customer_group_id, qty, value, website_id) VALUES ('.$entity_id.',0,'.$Artikel_Preis_Gruppe.','.$Artikel_Preis_Ab_Menge.','.$Artikel_Preis.','.$websiteNr.');';

								}
								dmc_sql_query($query);
								// Update Minimum Preis
								$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_tier_price SET min_price='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND min_price>'.$Artikel_Preis.'';
								dmc_sql_query($query);
								$query='UPDATE '.DB_TABLE_PREFIX.'catalog_product_index_price SET tier_price='.$Artikel_Preis.' WHERE entity_id='.$entity_id.' AND tier_price>'.$Artikel_Preis.'';
								dmc_sql_query($query);
								if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - 787 - ID:$entity_id Amount:$Artikel_Preis_Ab_Menge Product price:$Artikel_Preis Website=".$website_ids[$durchlauf_website].".\n");	
							} // end for durchlauf_website
						} else {
							if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - 790 - Product tier prices update failed: Product not exists.\n");	
						}									
					} // end if if ($USE_API) 
				} // end if Staffelpreis
			} // end if kein Fehler und Preis vorhanden
		} // end for		
	  } // end  - dmc_set_tier_price
  
	// Exportmodus ustorelocator_location
		if ($ExportModusSpecial=='ustorelocator_location') {
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Details ustorelocator_location\n");
			
			$title = $Freifeld{2};				// Store_Name
			$customers_street_address = $Freifeld{3};
			$customers_postcode = $Freifeld{4};
			$customers_city = $Freifeld{5};
			$customers_countries_iso_code = $Freifeld{6};
			$website_url = $Freifeld{7};			// URL
			$store_phone = $Freifeld{8};
			$customers_email_address = $Freifeld{9};
			$product_types = $Freifeld{10};
			$grade = $Freifeld{11};
			$brands = $Freifeld{12};
			
			// Standard
			$latitude='0.00';
			$longitude='0.00';
			$map_address=$customers_street_address.', '.$customers_countries_iso_code." ".$customers_postcode.' '.$customers_city;
			$address_display=$customers_street_address.', '.$customers_postcode.', '.$customers_city;
			
			// get Magento customer ID 
			$CustomerId=dmc_get_id_by_email($customers_email_address);	
			$brands=substr($brands,0,-1);
			if (DEBUGGER>=1 && $brands <> "") fwrite($dateihandle, "Google Infos fuer $title Kundenid $CustomerId mit eMail $customers_email_address und Brands $brands setzen.\n");
			
			
			// Wenn Kunde existiert, und Brands eingetragen Details zuordnen 
			if ($CustomerId<> "" && $brands<>"") {
				$where="cid=".$CustomerId;
				if (dmc_entry_exits('cid', 'ustorelocator_location', $where)) {
					// Update
					$query="UPDATE ".DB_TABLE_PREFIX."ustorelocator_location SET title='$title', address_display='$address_display', website_url='$website_url', store_phone='$store_phone', product_types='$product_types', grade='$grade', brands='$brands' WHERE cid=$CustomerId";
					dmc_sql_query($query);
				} else {
					// Insert
					dmc_sql_insert(DB_TABLE_PREFIX."ustorelocator_location", 
									"(title, map_address, latitude, longitude, address_display, notes, website_url, store_phone, product_types, grade, brands, cid)", 
									"('$title', '$map_address', $latitude, $longitude, '$address_display', '', '$website_url', '$store_phone', '$product_types', $grade, '$brands', $CustomerId)");
				}
									
				// require_once ('../app/Mage.php');    // /home/magento/www/ ist zu ersetzen
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); // ADMIN_STORE_ID ist ggfls direkt zu schreiben, findet sich in der Tabelle core_stores oder so ähnlich der Magento Datebank

			    $collection = Mage::getModel('ustorelocator/location')->getCollection();
			    $collection->getSelect()->where('latitude=0');
			    foreach ($collection as $loc) {
			        echo $loc->getTitle()."<br/>";
			        $loc->save();
			    }
				
			} //  endif Wenn Kunde existieren
		
		} // end exportmodus ustorelocator_location
		
	// ****************************************************************************
	// Ändert den Auftragsstatus
	// ****************************************************************************
	if ($ExportModusSpecial=='order_update') 
	{
		// Aufruf: http//www.fantastisch.info/dmc_magento15/dmconnector_magento.php?user=webuser&password=xxx&action=setDetails&Freifeld1=order_update&Freifeld2=100000001&Freifeld3=pending
				$daten = "--- OrderUpdate ---";
				fwrite($dateihandle, $daten. "\n");
				 // select 'order_update' as Freifeld1,	angebot.IhrAuftrag AS Order_ID,	'3' as Status, paket.Paketnummer as Trackingnummer, '' as Versender, '' as Rechnungsnummer, Lieferschein.Belegnummer AS Lieferscheinnummer, '' as Rechnung_dok, '' as Lieferschein_dok, '' as Bemerkung, '' as Verwende_Magento_Lieferschein, '' as Freifeld12 FROM BELEG AS angebot INNER JOIN BELEG AS Lieferschein ON angebot.Belegnummer = Lieferschein.LieferBelegNr INNER JOIN PAKET AS paket ON Lieferschein.Belegnummer = paket.Belegnummer WHERE (angebot.Belegtyp = 'A') AND (Lieferschein.Belegtyp = 'L') AND (angebot.IhrAuftrag <> '') AND (Lieferschein.BearbeitetAm > GETDATE() - 1)
				// select 'order_update' as Freifeld1,	ls.[Webshop Key] AS Order_ID, 'complete' as Status, ls.[Package Tracking No_] as Trackingnummer, ls.[Shipping Agent Code] AS Versender, '' as Rechnungsnummer, ls.[No_] AS Lieferscheinnummer, '' as Rechnung_dok, '' as Lieferschein_dok, '' as Bemerkung, '' as Freifeld11, '' as Freifeld12 FROM [PLANSHOP-TEST].[dbo].[TEST Plan Shop GmbH$Sales Shipment Header] AS ls WHERE (ls.[Shipment Date] > GETDATE() - 30) AND ls.[Webshop Key] <> '' AND ls.[Webshop Key] IS NOT NULL 
				 
				$Order_ID = $Freifeld{2};
				$Status = $Freifeld{3};
				$Trackingnummer = $Freifeld{4};
				$Versender = $Freifeld{5};
				$Rechnungsnummer = $Freifeld{6};
				$Lieferscheinnummer = $Freifeld{7};
				$Rechnung_dok = $Freifeld{8};
				$Lieferschein_dok = $Freifeld{9};
				$Bemerkung = $Freifeld{10};
				$Verwende_Magento_Lieferschein = $Freifeld{11};	// Magento Lieferschein erstellen und Tracking Inofs hinzufuegen
				if ($Verwende_Magento_Lieferschein!="1") $Verwende_Magento_Lieferschein=false;
				
				// Variablendeklarationen
				$LangID = 2;	// 2= deutsch
				// Text entwickeln
				$comments = $Bemerkung;
				if ($Rechnungsnummer != '')  $comments .= 'Rechnung erstellt mit Rechnungnummer: '.$Rechnungsnummer.'<br>';
				if ($Lieferscheinnummer != '')  $comments .= 'Lieferscheinnummer: '.$Lieferscheinnummer.'<br>';
				if ($Trackingnummer != '')  $comments .= 'Ware versendet mit Paketscheinnummer: '.$Trackingnummer.'<br>';
				if ($Trackingnummer != '')  $comments .= 'Sendungsverfolgung: '.$Trackingnummer;
				if ($Trackingnummer != '' && $Versender != '')  $comments .= 'Paketdienstleister: '.$Versender.'<br>';

				    if (DEBUGGER>=1) fwrite($dateihandle, "order_update Order_ID=".$Order_ID." auf Status=".$Status." mit Bemerkung: <br>".$comments."\n");
			
			// NOTIFY_CUSTOMER - Kundeninformation per eMail senden
				
				if ($Verwende_Magento_Lieferschein) { 
					try {		  
						// Lieferschein erstellen
						$itemsQty=1;
						$includeComment=true;
						$return = $client->call($sessionId, 
						'sales_order_shipment.create', array($Order_ID, $itemsQty, 
						$comments, $sendEmail = FALSE, $includeComment));
						// Tracker hinzufügen mit email Nachricht
						$title="Versand";
						$return = $client->call($sessionId, 
						'sales_order_shipment.addTrack', array($shipmentIncrementId, 
						$Versender, $title, $Trackingnummer, $sendNotice = NOTIFY_CUSTOMER));
					} catch (SoapFault $e) {
						if (DEBUGGER>=1) fwrite($dateihandle,'Set Tracking failed: '.$Order_ID.'\n'.$e);	
						$notified=0;
					}
				} else {
					// Status eintragen
					// Aktuellen Online Betsellstatus ermitteln
					$aktueller_status = dmc_sql_select_value("status", "sales_flat_order", "entity_id=".$Order_ID." OR increment_id=".$Order_ID);
					try {
						if ($aktueller_status != $Status) { // if (UPDATE_ORDER_STATUS) { 
							// array (orderIncrementId - order increment id, status - order status,  comment - order comment (optional),  notify - otification flag (optional)
							$client->call($sessionId, 'sales_order.addComment', array($Order_ID,  $Status,  $comments,  NOTIFY_CUSTOMER));
							if (DEBUGGER>=1) fwrite($dateihandle,"Order Status ".$Order_ID." updated to ".$Status." with comments=$comments\n");	
							$notified=1;
						} 
					} catch (SoapFault $e) {
						if (DEBUGGER>=1) fwrite($dateihandle,'Set OrderStatus failed for: '.$Order_ID.'\n'.$e);	
						$notified=0;
					}
				}		
						
				//	}  else {
				//		$notified=0;
				//	} // endif Kunden Info Mail senden
			  
			  echo '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
			       "<STATUS>\n" .
			       "  <STATUS_INFO>\n" .
			       "    <ACTION>$action</ACTION>\n" .
			       "    <CODE>0</CODE>\n" .
			       "    <MESSAGE>OK</MESSAGE>\n" .
			       "    <ORDER_ID>$Order_ID</ORDER_ID>\n" .
			       "    <ORDER_STATUS>$Status</ORDER_STATUS>\n" .
			       "    <SCRIPT_VERSION_MAJOR>Für $version_major</SCRIPT_VERSION_MAJOR>\n" .
			       "    <SCRIPT_VERSION_MINOR>$version_minor</SCRIPT_VERSION_MINOR>\n" .
			       "  </STATUS_INFO>\n" .
			       "</STATUS>\n\n";
			}	// end if order_update
			
	// Exportmodus customer_prices fuer magento bis 1.6
	if ($ExportModusSpecial=='customer_prices') {
			//  select 'customer_prices' as ExportModus, ad.EMail as Kunden_EMAIL, p.Artikelnummer as Artikelnummer, p.AuspraegungID as Artikel_Variante, p.Einzelpreis AS Artikel_Preis, p.Rabattsatz as Rabattsatz, '1' as Menge, '0' as Website, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKArtikelKunden as p, KHKAdressen as ad, KHKKontokorrent as deb WHERE p.Kunde=deb.Kto AND deb.Adresse=ad.Adresse AND p.Mandant='10' AND ad.EMail IS NOT NULL AND (p.Rabattsatz IS NULL OR p.Rabattsatz=0) AND p.Einzelpreis IS NOT NULL

			$customers_email_address = $Freifeld{2};				// Store_Name
			$sku = $Freifeld{3};
			$var_id = $Freifeld{4};
			$price = $Freifeld{5};
			$discount = $Freifeld{6};
			$qty = $Freifeld{7};
			if ($qty=='') $qty=1;
			$website_id = $Freifeld{8};
			if ($website_id=='') $website_id=0;
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - customer_prices - for customer $customers_email and sku $sku -> $price \n");
			
			// Preiberechnung, wenn discount angegeben
			$price = $Freifeld{5};
			$discount = $Freifeld{6};
			if ($discount > 0) $price = $price -($price *$discount/100);
			
			// get Magento customer ID 
			$CustomerId=dmc_get_id_by_email($customers_email_address);	
			$art_id=dmc_get_id_by_artno($sku);	
			if (DEBUGGER) fwrite($dateihandle, "Magento Artikel ID = $art_id und CustomerId=$CustomerId\n");			
			// Wenn Kunde existiert, kundenpreis zuordnen  value_id 	entity_id 	customer_id 	qty 	value 	website_id
			if ($CustomerId<> "" && $art_id<> "") {
				$where="qty=".$qty." AND website_id=".$website_id." AND customer_id=".$CustomerId. " AND entity_id=".$art_id;
				if (dmc_entry_exits('value_id', DB_TABLE_PREFIX.'catalog_product_entity_customer_price', $where)) {
					// Update
					$query="UPDATE ".DB_TABLE_PREFIX."catalog_product_entity_customer_price ".
							"SET value='$price' WHERE ".$where;
					dmc_sql_query($query);
				} else {
					// Insert
					dmc_sql_insert(DB_TABLE_PREFIX."catalog_product_entity_customer_price", 
									"(entity_id, customer_id, qty, value, website_id)", 
									"('$art_id', '$CustomerId', $qty, $price, '$website_id')");
					
				} // end if else
			} //  endif Wenn Kunde existieren
		} // end exportmodus customer_prices
			
	// Exportmodus Rechnungen anlegen
	if ($ExportModusSpecial=='dmc_invoice_create') {
			/* select 'dmc_invoice_create' AS Freifeld1, 'Rechnung' AS Belegart, 'RE_' + B.Belegnummer, B.Datum, K.EMail, B.Name, B.Vorname, B.Zusatz, B.EuroNetto AS GesamtpreisNetto, B.EuroBrutto AS GesamtpreisBrutto, '' AS Freifeld11, 'S:\System\M100\Archiv\RE_' + B.Belegnummer+'.PDF' AS PDF_Upload FROM BELEG AS B INNER JOIN KUNDEN AS K ON B.Adressnummer = K.Nummer WHERE (B.Datum > '05.02.2012') AND (B.Belegtyp = 'R') AND K.EMail<>'' ORDER BY B.Datum DESC
*/
			$Belegart = $Freifeld{2};				// Store_Name
			$Belegnummer = $Freifeld{3};
			$Datum = $Freifeld{4};
			$EMail = $Freifeld{5};
			$Name = $Freifeld{6};
			$Vorname = $Freifeld{7};
			$Zusatz = $Freifeld{8};
			$GesamtpreisNetto = $Freifeld{9};
			$Gesamtpreis = $Freifeld{10};
		
			$pdf_datei = $Freifeld{12};
			
			// Wenn Datei Verzeichnis enthaelt, dann den Dateinamen separieren
		  	$pdf_datei = str_replace(' ','',$pdf_datei); 
			if (strpos($pdf_datei, "\\") !== false) {
				$pdf_datei=substr($pdf_datei,(strrpos($pdf_datei,"\\")+1),254); 
			} 
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_invoice_create - Belegnummer  $Belegnummer for $EMail  and belegdatei = $pdf_datei\n");
			if (file_exists(IMAGE_FOLDER . $pdf_datei)) {
					// PDF DAtei in korrektes Verzeichnis kopieren
				copy(IMAGE_FOLDER . $pdf_datei, PDF_FOLDER .$pdf_datei);
				// Upload Datei löschen
				unlink(IMAGE_FOLDER . $pdf_datei);
			}
					
			// get Magento customer ID 
			$customer_shop_id=dmc_get_id_by_email($EMail);	
			if (DEBUGGER) fwrite($dateihandle, "Magento CustomerId=$customer_shop_id\n");			
			// Wenn Kunde existiert, kundeninformationen zuordnen
			if ($customer_shop_id<> "") {
				// Strasse, Ort, PLZ etc
			} else {
				$customer_shop_id=0;
			}
			
			// Neue Datensaetze anlegen
			$where="Belegnummer='".$Belegnummer."'";
			if (dmc_entry_exits('Belegnummer', 'dmc_billings_header', $where)) {
					// Update
					// $query="UPDATE ".DB_TABLE_PREFIX."dmc_billings_header ".
						//	"SET value='$price' WHERE ".$where;
					//dmc_sql_query($query); 
			} else {
					// Insert
					dmc_sql_insert(DB_TABLE_PREFIX."dmc_billings_header", 
									"(customer_shop_id , Belegart ,Belegnummer ,Datum ,Email ,Anrede ,
										Name ,Vorname ,Zusatz ,Strasse,Land,Plz ,Ort  ,link, GesamtpreisNetto, Gesamtpreis )", 
									"($customer_shop_id, '$Belegart', '$Belegnummer', '$Datum', '$EMail', '',
										'$Name', '$Vorname', '$Zusatz', '','','','','',$GesamtpreisNetto,$GesamtpreisNetto)");
			} // end if else
			
		} // end exportmodus dmc_invoice_create
	
	// Exportmodus set_attribute_value - Update per Api auf ein Artikelattribut-Wert
	if ($ExportModusSpecial=='set_attribute_value') {
		// Select 'set_attribute_value' as ExportModus, 'all' as Store_View_ID, 'ordernumbers' as attribute, 'add' as Type_Add_Change, aon.SOURCEAPOID_ELEMENT_COUNT_0 as Conf_Artikelnummer, aon.ORDERNUM_ELEMENT_COUNT_0 as Bestellnummer, aon.ARTNUM_ELEMENT_COUNT_0 as Simple_Artikelnummer, '' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM ARTICLEORDERNUMBER_AS_CHILD_IN_WSARTICLEORDERNUMBERDATA aon
		// select 'set_attribute_value' as ExportModus, 'all' as Store_View_ID, 'material' as attribute, 'update' as Type_Add_Change, p.APOID_ELEMENT_COUNT_0 as Conf_Artikelnummer, p.material_ELEMENT_COUNT_0 as Aauspraegung, '' as Simple_Artikelnummer, '' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM ARTICLE_AS_CHILD_IN_WSARTICLEDATA p INNER JOIN ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku ON (p.ARTNUM_ELEMENT_COUNT_0 = sku.ARTNUM_ELEMENT_COUNT_0) INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulang ON (sku.PARENT_ELEMENT_ROW_ID_XX = skulang.ROW_ID_XX AND skulang.LANGUAGE_ELEMENT_COUNT_0='-D-D') INNER JOIN HEADER_AS_CHILD_IN_WSARTICLEDATA plang ON (p.PARENT_ELEMENT_ROW_ID_XX = plang.ROW_ID_XX AND plang.LANGUAGE_ELEMENT_COUNT_0='-D-D') WHERE (sku.SIZECATEGORYID_ELEMENT_COUNT_0 <> 'SIZERUN_N') AND p.ARTNUM_ELEMENT_COUNT_0='22138'

		$store_view = $Freifeld{2};
		$attribute = $Freifeld{3};
		$Type_Add_Change = $Freifeld{4};
		$sku = $Freifeld{5};
		$neue_werte = $Freifeld{6};
		
		// Bestehende werte abfragen
		if ($store_view=='all')
			$bestehende_werte=dmc_get_flat_attibute_value($sku,$attribute,1);
		else 
			$bestehende_werte=dmc_get_flat_attibute_value($sku,$attribute,$store_view);
			
		if (DEBUGGER>=1) fwrite($dateihandle, "set_attribute_value ".$attribute." for ".$sku." - ".$neue_werte." (alt=".$bestehende_werte.") -> ");
		
		$pos = strpos ( $bestehende_werte, $neue_werte );
		// Wenn noch nicht vorhanden
		if ($pos===false) { 
			// Wert ergaenzen oder aendern?
			if ($Type_Add_Change=='add')
				$bestehende_werte .= $neue_werte.' ';
			else
				$bestehende_werte = $neue_werte;
			
			if (DEBUGGER>=1) fwrite($dateihandle, "NEU=".$bestehende_werte." storeview=$store_view\n");
			#
			// Update product on deutsch store view
			#
			try {
				if ($attribute=="ordernumbers")
					if ($store_view=='all')
						$client->call($sessionId, 'product.update', array($sku, array('ordernumbers'=>$bestehende_werte)));
					else
						$client->call($sessionId, 'product.update', array($sku, array('ordernumbers'=>$bestehende_werte),$store_view));
				else
					if ($store_view=='all')
						$client->call($sessionId, 'product.update', array($sku, array($attribute=>$bestehende_werte)));
					else
						$client->call($sessionId, 'product.update', array($sku, array($attribute=>$bestehende_werte),$store_view));
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "set_attribute_value - Product Update failed:\nError:\n".$e."\n");		 
			}
			
			#
		} // end if 
	} // end exportmodus set_attribute_value

	// Exportmodus language_set_attribute_value -> Fremdsprachenbezeichungen fuer attribute
	if ($ExportModusSpecial=='language_set_attribute_value') {
		// select distinct 'language_set_attribute_value' as ExportModus, '12' as Store_View_ID, 'colordetail' as attribute, p.COLOR_ELEMENT_COUNT_0 as Hauptsprache, p2.COLOR_ELEMENT_COUNT_0 as Fremdsprache, '' as Freifeld6, '' as Freifeld7, '' as Freifeld8, '' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM ARTICLE_AS_CHILD_IN_WSARTICLEDATA p INNER JOIN HEADER_AS_CHILD_IN_WSARTICLEDATA plang ON (p.PARENT_ELEMENT_ROW_ID_XX = plang.ROW_ID_XX AND plang.LANGUAGE_ELEMENT_COUNT_0='-D-D') INNER JOIN ARTICLE_AS_CHILD_IN_WSARTICLEDATA p2 ON (p.ARTNUM_ELEMENT_COUNT_0=p2.ARTNUM_ELEMENT_COUNT_0 AND p2.PARENT_ELEMENT_ROW_ID_XX = (SELECT ROW_ID_XX FROM HEADER_AS_CHILD_IN_WSARTICLEDATA WHERE LANGUAGE_ELEMENT_COUNT_0 = '-S-S')) 

		$store_view = $Freifeld{2};
		$attribute = $Freifeld{3};
		$hauptsprache = $Freifeld{4};
		$fremdsprache = $Freifeld{5};
		
		$attr_type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_product");
		// ID des Attributs ermitteln
		$attribute_id=dmc_get_attribute_id_by_attribute_code($attr_type_id,$attribute);	
		// Bestehende id der Hauptsprache abfragen
		$option_id=dmc_get_eav_attribute_option_value_option_id_by_value($attribute_id,$hauptsprache,'0');
		// Wenn hauptsprache vorhanden -> Fremdsprache ergaenzen
		if ($option_id!="") {
			if (DEBUGGER>=1) fwrite($dateihandle, "language_set_attribute_value option_id ".$option_id." for ".$attribute." -> ".$fremdsprache." (storeview=".$store_view.") -> ");
			$table = "eav_attribute_option_value";  
			$columns = "(`option_id`, `store_id`,`value`)";
			$values = "(".$option_id.", ".$store_view.", '".$fremdsprache."')";		
			// Eventuell alte Zuordnungen löschen
			if (dmc_entry_exits("value_id", "eav_attribute_option_value", " option_id='".$option_id."' AND store_id=".$store_view." ")) 
				dmc_sql_delete("eav_attribute_option_value", " option_id='".$option_id."' AND store_id=".$store_view." ");
			dmc_sql_insert($table, $columns, $values);
		} // end if 
			
	} // end exportmodus language_set_attribute_value

	// Exportmodus set_store_prices -> preise fuer spezielle stores setzen
	if ($ExportModusSpecial=='set_store_prices') {
		// select distinct 'set_store_prices' as ExportModus, p.APOID_ELEMENT_COUNT_0 as Artikel_Artikelnr, '7' as Store_View, (Select MIN(PRICE_ELEMENT_COUNT_0) AS preis FROM ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku2 INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulangtemp ON (sku2.PARENT_ELEMENT_ROW_ID_XX = skulangtemp.ROW_ID_XX AND skulangtemp.LANGUAGE_ELEMENT_COUNT_0='-F-F')  WHERE (p.ARTNUM_ELEMENT_COUNT_0 = sku2.ARTNUM_ELEMENT_COUNT_0)) as Artikel_Preis, '9' AS Artikel_Preis_Tax_ID, '' AS FreiFeld5, '' AS FreiFeld6, '' AS FreiFeld7, '' AS FreiFeld8, '' AS FreiFeld9, '' AS FreiFeld10, '' AS FreiFeld11, '' AS FreiFeld12 FROM ARTICLE_AS_CHILD_IN_WSARTICLEDATA p INNER JOIN ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku ON (p.ARTNUM_ELEMENT_COUNT_0 = sku.ARTNUM_ELEMENT_COUNT_0) INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulang ON (sku.PARENT_ELEMENT_ROW_ID_XX = skulang.ROW_ID_XX AND skulang.LANGUAGE_ELEMENT_COUNT_0='-F-F') INNER JOIN HEADER_AS_CHILD_IN_WSARTICLEDATA plang ON (p.PARENT_ELEMENT_ROW_ID_XX = plang.ROW_ID_XX AND plang.LANGUAGE_ELEMENT_COUNT_0='-F-F') WHERE (sku.SIZECATEGORYID_ELEMENT_COUNT_0 = 'SIZERUN_EK' OR sku.SIZECATEGORYID_ELEMENT_COUNT_0 = 'SIZERUN_K' OR sku.SIZECATEGORYID_ELEMENT_COUNT_0 = 'SIZERUN_N')  

		$sku = $Freifeld{2};
		$store_view = $Freifeld{3};
		$price = $Freifeld{4};
		$tax_id = $Freifeld{5};
		
		$website = dmc_get_website_id_by_store_view($store_view);
		
		$attr_type_id = dmc_get_entity_type_id_by_entity_type_code("catalog_product");
		
		// Wenn attribute typ fuer produkte nicht ermittelbar, dann ABBRUCH
		if ($attr_type_id==-1) {
			fwrite($dateihandle, "*** FEHLER 959 in dmc_set_details, da attr_type_id=".$attr_type_id."\n");	
			fwrite($dateihandle, "*** ABBRUCH, da Type elementar -> HINT: Gibt es evtl ein Datenbank Tabellen PREFIX???\n");	
			return;
			$attr_type_id=4;	// Standard
		}
		
		// ID des Attributs tax_class_id ermitteln
		$attribute='tax_class_id';
		$tax_attribute_id=dmc_get_attribute_id_by_attribute_code($attr_type_id,$attribute);	
		$attribute='price';
		$price_attribute_id=dmc_get_attribute_id_by_attribute_code($attr_type_id,$attribute);	
		// EXISTENTE ArtikelID
		$art_id=dmc_get_id_by_artno($sku);	
		
		// Wenn attrinute und preis etc vorhanden -> store price setzen
			
 		if ($art_id<>"" && $price_attribute_id!="" && $tax_attribute_id!="" && $price>0 && $tax_id>=0) {
			
			// Standard Preis setzen
			if (DEBUGGER>=1) fwrite($dateihandle, "set_store_prices sku ".$sku." price ".$price." \n");
			$table = "catalog_product_entity_decimal";  
			$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
			$values = "($attr_type_id ,".$price_attribute_id.", ".$store_view.", ".$art_id.", ".$price.")";		
			// Eventuell alte Zuordnungen löschen
			if (dmc_entry_exits("value_id", $table, " entity_id=".$art_id." AND entity_type_id=".$attr_type_id." AND attribute_id = ".$price_attribute_id." AND store_id=".$store_view." ")) 
				dmc_sql_delete($table, " entity_id=".$art_id." AND entity_type_id=".$attr_type_id." AND attribute_id = ".$price_attribute_id." AND store_id=".$store_view." ");
			dmc_sql_insert($table, $columns, $values);
			
			
			// Steuersatz setzen
			$table = DB_TABLE_PREFIX."catalog_product_entity_int";  
			$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
			$values = "(".$attr_type_id." ,".$tax_attribute_id.", ".$store_view.", ".$art_id.", ".$tax_id.")";		
			// Eventuell alte Zuordnungen löschen
			if (dmc_entry_exits("value_id", $table, " entity_id=".$art_id." AND entity_type_id=".$attr_type_id." AND attribute_id	=".$tax_attribute_id." AND store_id=".$store_view." ")) 
				dmc_sql_delete($table, " entity_id=".$art_id." AND entity_type_id=".$attr_type_id." AND attribute_id =".$tax_attribute_id." AND store_id=".$store_view." ");
			dmc_sql_insert($table, $columns, $values);
			
			// Product flat update
			// catalog product flat updaten
			$table = "catalog_product_flat_".$store_view; 
			$where="entity_id=".$art_id;
			if (dmc_entry_exits('entity_id', $table, $where))
					// Update
					dmc_sql_update($table, "price=$price, tax_class_id=$tax_id, updated_at='now()'", $where);
		
			if ($website<>"") {
				// indexierung 1 setzen
				$table = "catalog_product_index_price";  
				$columns = "(entity_id, customer_group_id, website_id, tax_class_id, price,	final_price, min_price, max_price)";
				$values = "(".$art_id." , 0, ".$website.", ".$tax_id.", ".$price.", ".$price.", ".$price.", ".$price.")";		
				// Eventuell alte Zuordnungen löschen
				if (dmc_entry_exits("entity_id", $table, " entity_id=".$art_id." AND customer_group_id=0 AND website_id	=".$website." ")) {
					dmc_sql_update($table, " tax_class_id=$tax_id, price=$price, final_price=$price, min_price=$price, max_price=$price", " entity_id=".$art_id." AND customer_group_id=0 AND website_id	=".$website." ");
				} else {
					dmc_sql_insert($table, $columns, $values);
				}
				// indexierung 2			setzen
				$table = "catalog_product_index_price_idx";  
				$columns = "(entity_id, customer_group_id, website_id, tax_class_id, price,	final_price, min_price, max_price)";
				$values = "(".$art_id." , 0, ".$website.", ".$tax_id.", ".$price.", ".$price.", ".$price.", ".$price.")";		
				// Eventuell alte Zuordnungen löschen
				if (dmc_entry_exits("entity_id", $table, " entity_id=".$art_id." AND customer_group_id=0 AND website_id	=".$website." ")) 
					dmc_sql_update($table, " tax_class_id=$tax_id, price=$price, final_price=$price, min_price=$price, max_price=$price", " entity_id=".$art_id." AND customer_group_id=0 AND website_id	=".$website." ");
				else 
					dmc_sql_insert($table, $columns, $values);
			}
		} // end if 
			
	} // end exportmodus set_store_prices

	// Exportmodus language_by_api
	if ($ExportModusSpecial=='language_by_api_alt') {
		// select distinct 'language_by_api' as ExportModus, p.APOID_ELEMENT_COUNT_0 as Artikel_Artikelnr, '12' as Store_View, (Select MIN(PRICE_ELEMENT_COUNT_0) AS preis FROM ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku2 INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulangtemp ON (sku2.PARENT_ELEMENT_ROW_ID_XX = skulangtemp.ROW_ID_XX AND skulangtemp.LANGUAGE_ELEMENT_COUNT_0='-S-S')  WHERE (p.ARTNUM_ELEMENT_COUNT_0 = sku2.ARTNUM_ELEMENT_COUNT_0)) as Artikel_Preis, p.NAME_ELEMENT_COUNT_0 as Artikel_Bezeichnung,  cast(p.DESCRIPTION_ELEMENT_COUNT_0 as VARCHAR(32000)) as Artikel_Text, ''  as Artikel_KurzText, p.NAME_ELEMENT_COUNT_0||' im Shop' as Artikel_MetaTitle, p.NAME_ELEMENT_COUNT_0||' im Shop' as Artikel_MetaDescription, p.NAME_ELEMENT_COUNT_0 as Artikel_MetaKeywords, 'material@washcomment' as Merkmal, p.material_ELEMENT_COUNT_0||'@'||p.washcomment_ELEMENT_COUNT_0 as Auspraegung , '' AS FreiFeld11, '' AS FreiFeld12 FROM ARTICLE_AS_CHILD_IN_WSARTICLEDATA p INNER JOIN ARTICLESKU_AS_CHILD_IN_WSARTICLESKUDATA sku ON (p.ARTNUM_ELEMENT_COUNT_0 = sku.ARTNUM_ELEMENT_COUNT_0)  INNER JOIN HEADER_AS_CHILD_IN_WSARTICLESKUDATA skulang ON (sku.PARENT_ELEMENT_ROW_ID_XX = skulang.ROW_ID_XX AND skulang.LANGUAGE_ELEMENT_COUNT_0='-S-S') INNER JOIN HEADER_AS_CHILD_IN_WSARTICLEDATA plang ON (p.PARENT_ELEMENT_ROW_ID_XX = plang.ROW_ID_XX AND plang.LANGUAGE_ELEMENT_COUNT_0='-S-S') WHERE p.APOID_ELEMENT_COUNT_0 like '8666#%'

		$Artikel_Artikelnr = $Freifeld{2};
		$Store_View = $Freifeld{3};			// Store_view_ID
		$Artikel_Preis=$Freifeld{4};
		$Artikel_Bezeichnung = $Freifeld{5};
		$Artikel_Text = $Freifeld{6};
		$Artikel_Kurztext = $Freifeld{7};
		$Artikel_MetaTitle = $Freifeld{8};
		$Meta_Desc = $Freifeld{9};
		$Meta_Keyw = $Freifeld{10};
		$Artikel_Merkmale = $Freifeld{11};
		$Artikel_Auspraegungen = $Freifeld{12};
			
		if ($Store_View=='all') $Store_View='1';
		
		// Magento Produkt ID ermitteln
		$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
			
		if (DEBUGGER>=1) fwrite($dateihandle, "language_by_api ProductId ".$ProductId." for SKU=".$Artikel_Artikelnr." Store View $Store_View Artikel_Bezeichnung ".$Artikel_Bezeichnung." Artikel_Auspraegungen:$Artikel_Auspraegungen\n");
		// Wenn Artikel existiert, Details zuordnen 
		if ($ProductId <> "") {
			
			$updateProductData = array(				    
			        'updated_at' => 'now()',
					'name' => $Artikel_Bezeichnung,
			        'description' => $Artikel_Text,
			        // 'short_description' => $Artikel_Kurztext,				 
			        // 'status' => $Aktiv,
			        // 'visibility' => $Artikel_Status,				// siehe etwas weiter unten 
			         'price' => $Artikel_Preis,
			        // 'tax_class_id' => $Artikel_Steuersatz,
					'meta_title' => $Artikel_MetaTitle,
			        'meta_keyword' => $Artikel_MetaKeywords,
			        'meta_description' => $Artikel_MetaDescription,
			        //'qty'=>$Artikel_Menge, 
					// 'is_in_stock'=>1
					
			);  // end updateProductData
			
			try {
			// Update product onstore view
				if ($client->call($sessionId, 'product.update', array($Artikel_Artikelnr, $updateProductData, $Store_View)))
					if (DEBUGGER>=1) fwrite($dateihandle, "Erfolgreich\n");				
				else 
					if (DEBUGGER>=1) fwrite($dateihandle, "nicht erfolgreich\n");			
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "language_by_api - Product Update failed:\nError:\n".$e."\n");		 
			}
		} // end if 
	} // end exportmodus language_by_api

	// Exportmodus Beschreibungen fuer Dynamics Nav Kategorien
		if ($ExportModusSpecial=='dyn_nav_text_cat') {
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export dyn_nav_text_cat\n");
			
			$Kategorie_ID = $Freifeld{2};
			$Zeilennummer = $Freifeld{3};			// Zeilennummer
			$Description = $Freifeld{4};		// Text
			$Image = $Freifeld{5};		// bild muss dann in media/catalog/category liegen
			$sortkey =  $Freifeld{6};
		
			// Srtkey aufbereiten
			$sortkey  = str_replace(".", "", $sortkey );
			$sortkey  = str_replace("A", "99", $sortkey );
						
			// Check if category already exists (-1 if not)
			if (!GENERATE_CAT_ID)
				$cat_id=dmc_get_category_id("entity_id=".$Kategorie_ID);	
			else 
				$cat_id = dmc_get_cat_keywords($Kategorie_ID) ; 
				
			if ($cat_id==-1) {
				if (DEBUGGER>=1) fwrite($dateihandle, "Category (WaWi=$Kategorie_ID) NOT exists \n");
				break;
			} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "Category (WaWi=$Kategorie_ID) exists with shopId=$cat_id \n");	
			}
			
			
		//	if (DEBUGGER>=1) fwrite($dateihandle, "Add lineNo $Zeilennummer with desc=$Description ");
		
			// Wenn Kategorie  existiert -> zuordnen 
			if ($cat_id <> -1) {
				//					if (DEBUGGER>=1) fwrite($dateihandle, "1065n");

				if ($Zeilennummer<=10000) {
					// Ueberpruefen, ob erste Zeile uebermittelt, dann komplett neu initialisieren
					$updateCatData = array(				    
						//    'short_description' => $Artikel_Kurztext,	
						'available_sort_by'=> "Name",
						'default_sort_by'=> "Name",
						'position' => $sortkey
					);  // end updateCatData
					
					if ($Description !='')	$updateCatData['description'] = $Description;
					if ($Image !='') $updateCatData['image']=$Image;
				
			//		if (DEBUGGER>=1) fwrite($dateihandle, "1072\n");
		
				} else if ($Zeilennummer<>"") { // Folgezeilen
					// Alte Beschreibung ermitteln
					//					if (DEBUGGER>=1) fwrite($dateihandle, "1077\n");

					$cat = $client->call($sessionId, 'category.info', array($cat_id));
					$Description_old=$cat['description'];
					$Description = $Description_old." ".$Description;
					
					$updateCatData = array(				    
					  'available_sort_by'=> "Name",
					  'default_sort_by'=> "Name"
					);  // end updateCatData

					if ($Description !='')	$updateCatData['description'] = $Description;
					//if (DEBUGGER>=1) fwrite($dateihandle, "1092 desc neu=$Description\n");
				} // end if 
				
				// Wenn Bild verarbeitet werden soll, pruefen ob existent und kopieren in Ordner
				if (DEBUGGER>=1) fwrite($dateihandle, " pruefen auf bild: ".IMAGE_FOLDER . $Image." \n");				
				if ($Image !='') {
					$updateCatData['image']=$Image;
					if (file_exists(IMAGE_FOLDER . $Image)) {
						// Datei in Ordner kopieren
						copy(IMAGE_FOLDER . $Image , "./../media/catalog/category/".$Image);
						if (DEBUGGER>=1) fwrite($dateihandle, "copy(IMAGE_FOLDER . $Image , ./../media/catalog/category/.$Image)\n");				
						// Upload Datei löschen
						unlink(IMAGE_FOLDER . $Image);
					} // end if file_exists
				}
						
				// update cat
				try {		    
					if ($client->call($sessionId, 'category.update', array($cat_id, $updateCatData)))	
						if (DEBUGGER>=1) fwrite($dateihandle, " update dyn_nav_text_cat mit bild: ".$updateCatData['image']."... done \n");						
					else if (DEBUGGER>=1) fwrite($dateihandle, " ... FAILED (ERROR) \n");		// no update possible
				} catch (SoapFault $e) {
						if ($debugger==1) fwrite($dateihandle,'Update category failed:\n'.$e);		    
				}
				
			} //  endif Wenn Kategorie existiert 
		} // end exportmodus dyn_nav_text_cat		
		
		// Exportmodus customer_discout_group
	if ($ExportModusSpecial=='customer_discount_group') {
			//  select 'customer_discount_group' as ExportModus, ad.[E-Mail] as Kunden_EMAIL, price.[Code] as Rabatt_Gruppe, price.[Line Discount %] as Rabatt_Prozent, '' AS FF3, '' as FF4, '' as FF5, '' as FF6, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM [NAV].[dbo].[Herminghaus$Sales Line Discount] as price INNER JOIN [NAV].[dbo].[Herminghaus$Contact] AS ad ON ad.[External ID] =  price.[Sales Code] WHERE  ad.[E-Mail] IS NOT NULL AND ad.[E-Mail] <> '' AND price.[Code] like 'Abus%'
			// CREATE TABLE `customer_discount_group` ( `customer_discount_group_code` varchar(32) NOT NULL COMMENT 'Customer Discount Group Code', `customer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Customer Id', `discout_percent` decimal(12,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT 'Discount in %') ENGINE=MyISAM DEFAULT CHARSET=utf8; 

			$customers_email = $Freifeld{2};				// 
			$customer_discount_group_code = $Freifeld{3};
			$discount = $Freifeld{4};				// Fuer Kundengruppe A,B, oder C
			$standard_discount = $Freifeld{5};		// Fuer alle Kundengruppen
			$customer_price_goup = $Freifeld{6};	// Bei Debitorenpreise ist auch die Debitorengruppe zu berücksichtigen (höchter Rabatt zählt)
			// nehme den hoechren Discount
			$discount=max($discount,$standard_discount);
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - customer_discount_group - for customer $customers_email and group $customer_discount_group_code -> $discount % \n");
			
			// get Magento customer ID 
			// nicht bei Kundengruppen a,b,c
			if (strpos($customers_email, "@") !== false) 
				$CustomerId=dmc_get_id_by_email($customers_email);	
			else 
				$CustomerId=$customers_email;
				
			// Wenn Kunde existiert und RABATT hoeher (nur der hoechste Rabatt zaehlt)
			if ($CustomerId<> "" && $discount>0) {
				// Sonderfall -> fuer alle gruppen setzen
				if ($CustomerId=='ABC') {
					for ($i=1;$i<=3;$i++) {
						if ($i==1) { $CustomerId='A'; } elseif ($i==2) { $CustomerId='B'; } elseif ($i==3) { $CustomerId='C'; }; 
						$where="customer_discount_group_code='".$customer_discount_group_code."' AND customer_id='".$CustomerId."'";
						if (dmc_entry_exits('discout_percent', 'customer_discount_group', $where)) {
							// Update
							// Wenn Rabattgruppe existiert und RABATT hoeher (nur der hoechste Rabatt zaehlt)
							$where = $where." AND discout_percent<'$discount'";
							$query="UPDATE "."customer_discount_group ".
									"SET discout_percent='$discount' WHERE ".$where;
							dmc_sql_query($query);
						} else {
							// Insert
							dmc_sql_insert("customer_discount_group", 
											"(customer_discount_group_code, customer_id, discout_percent)", 
											"('$customer_discount_group_code', '$CustomerId', '$discount')");					
						} // end if else
					}
				} else {
					$where="customer_discount_group_code='".$customer_discount_group_code."' AND customer_id='".$CustomerId."'";
					if ($customer_price_goup != '')	{
						// Bei Debitorenpreisen ist auch die Debitorengruppe zu berücksichtigen (höchter Rabatt zählt)
						//Ermittle den Preis der Preisgruppe
						$rabattIST=dmc_sql_select_value('discout_percent', 'customer_discount_group', "customer_discount_group_code='".$customer_discount_group_code."' AND customer_id='".$customer_price_goup."'");						
					}
			
					// Nur fuer eine Gruppe setzen
					if ($rabattIST=='' || $rabattIST<$discount) {
						if (dmc_entry_exits('discout_percent', 'customer_discount_group', $where)) {
							// Wenn Rabattgruppe existiert und RABATT hoeher (nur der hoechste Rabatt zaehlt)
							$where = $where." AND discout_percent<'$discount'";							
							$query="UPDATE "."customer_discount_group ".
									"SET discout_percent='$discount' WHERE ".$where;
							dmc_sql_query($query);
						} else {
							// Insert
							dmc_sql_insert("customer_discount_group", 
											"(customer_discount_group_code, customer_id, discout_percent)", 
											"('$customer_discount_group_code', '$CustomerId', '$discount')");					
						} // end if else
					}
				}
			} //  endif Wenn Kunde existieren
	} // end exportmodus customer_discout_group
	
	
	// Exportmodus customer_discount_rule
	if ($ExportModusSpecial=='customer_discount_rule') {
			//  select 'customer_discount_rule' as ExportModus, ad.[E-Mail] as Kunden_EMAIL, price.[Code] as Rabatt_Gruppe, price.[Line Discount %] as Rabatt_Prozent, '' AS FF3, '' as FF4, '' as FF5, '' as FF6, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM [Kelch GmbH produktiv$Sales Line Discount] as price INNER JOIN [Kelch GmbH produktiv$Contact] AS ad ON ad.[External ID] =  price.[Sales Code] WHERE  ad.[E-Mail] IS NOT NULL AND ad.[E-Mail] <> ''

			$customers_email = $Freifeld{2};				// 
			$customer_discount_group_code = $Freifeld{3};
			$discount = $Freifeld{4};
			
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - customer_discount_rule - for customer $customers_email and group $customer_discount_group_code -> $discount % \n");
			// get Magento customer ID 
			$CustomerId=dmc_get_id_by_email($customers_email);	
			// Wenn Kunde existiert
			if ($CustomerId<> "") {
				$where="rule_id='".$customer_discount_group_code."' AND customer_id=".$CustomerId;
				if (dmc_entry_exits('discount_amount', 'rule_id', $where)) {
					// Update
					$query="UPDATE "."customer_discount_rule ".
							"SET discount_amount='$discount' WHERE ".$where;
					dmc_sql_query($query);
				} else {
					// Insert
					dmc_sql_insert("customer_discount_rule", 
									"(rule_id, customer_id, discount_amount)", 
									"('$customer_discount_group_code', '$CustomerId', '$discount')");
					
				} // end if else
			} //  endif Wenn Kunde existieren
		} // end exportmodus customer_discount_rule	
		
		// Exportmodus Dokument in Dokument Tabelle anlegen (neu ab 06062012)
		if ($ExportModusSpecial=='dmc_documents_header') {
			/* <!-- Auftraege NAV -->
				select 'dmc_documents_header' AS ExportModus, 'Auftrag' AS Belegart, b.[No_] AS document_no, b.[Order Date] AS document_date,(SELECT TOP 1 ad.[E-Mail] FROM [NAV].[dbo].[Cronus$Contact] AS ad  WHERE ad.[E-Mail] is not null AND ad.[E-Mail]<>''  AND ad.[External ID]=b.[Sell-to Customer No_]) AS customer_email_adress, b.[Bill-to Name] AS document_printed_name, b.[Bill-to Name] AS document_printed_name2, b.[Your Reference] AS document_referenz, 0.00 AS document_sum_net, b.[Payment Discount %] AS document_discount, '' AS Freifeld11, 'S:\System\M100\Archiv\RE_' + b.[No_]+'.PDF' AS PDF_Upload FROM [NAV].[dbo].[Cronus$Sales Header] AS b WHERE (b.[Order Date] > '2010-01-01') AND (b.[No_] like 'A%') AND (b.[No_] not like 'AG%')  ORDER BY b.[Order Date] ASC
			*/
			/* CREATE TABLE IF NOT EXISTS `dmc_documents_header` (
					 `document_id` int(11)  NULL auto_increment,
				  `customer_web_user_id` int(11)  NULL,
				  `customer_email_adress` varchar(80)  NULL,
					`document_type` varchar(100)  NULL,
				  `document_file_type` varchar(100)  NULL,
				  `document_link` varchar(100)  NULL,
					`document_no` varchar(20)  NULL,
					`document_date` varchar(150)  NULL,
				  `delivery_date` varchar(150)  NULL,
				  `document_printed_name` varchar(100)  NULL,
				  `document_printed_name2` varchar(100)  NULL,
				  `document_printed_name3` varchar(100)  NULL,
				  `document_printed_company` varchar(150)  NULL,
				  `document_printed_street` varchar(150)  NULL,
				  `document_printed_zip` varchar(50)  NULL,
				  `document_printed_city` varchar(150)  NULL,
				  `document_printed_country_code` varchar(30)  NULL,
				  `document_referenz` varchar(30)  NULL,
				  `document_sum_net` decimal(8,2)  NULL,
				  `document_vat` decimal(8,2)  NULL,
				  `document_sum_vat` decimal(8,2)  NULL,
				  `document_sum_gros` decimal(8,2)  NULL,
				  `document_discount` decimal(8,2)  NULL,
					  PRIMARY KEY  (`document_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; */
			$Belegart = $Freifeld{2};				
			$Belegnummer = $Freifeld{3};
			$Datum = $Freifeld{4};
			$EMail = $Freifeld{5};
			$Name = $Freifeld{6};
			$Vorname = $Freifeld{7};
			$Zusatz = $Freifeld{8};
			$GesamtpreisNetto = $Freifeld{9};
				if ($GesamtpreisNetto =='') $GesamtpreisNetto =0.00;
			$GesamtRabatt = $Freifeld{10};
				if ($GesamtRabatt =='') $GesamtRabatt =0.00;
			$pdf_datei = $Freifeld{12};
			
			// Wenn Datei Verzeichnis enthaelt, dann den Dateinamen separieren
		  	$pdf_datei = str_replace(' ','',$pdf_datei); 
			if (strpos($pdf_datei, "\\") !== false) {
				$pdf_datei=substr($pdf_datei,(strrpos($pdf_datei,"\\")+1),254); 
			} 
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_documents_header - Belegnummer  $Belegnummer for $EMail  and belegdatei = $pdf_datei\n");
			if (file_exists(IMAGE_FOLDER . $pdf_datei)) {
					// PDF DAtei in korrektes Verzeichnis kopieren
				copy(IMAGE_FOLDER . $pdf_datei, PDF_FOLDER .$pdf_datei);
				// Upload Datei löschen
				unlink(IMAGE_FOLDER . $pdf_datei);
			}
					
			// get Magento customer ID 
			$customer_shop_id=dmc_get_id_by_email($EMail);	
			if (DEBUGGER) fwrite($dateihandle, "Magento CustomerId=$customer_shop_id\n");			
			// Wenn Kunde existiert, kundeninformationen zuordnen
			if ($customer_shop_id<> "") {
				// Strasse, Ort, PLZ etc
			} else {
				$customer_shop_id=0;
			}
			
			// Neue Datensaetze anlegen
			$where="document_no='".$Belegnummer."'";
			if (!dmc_entry_exits('document_no', 'dmc_documents_header', $where)) {
					// Insert
					dmc_sql_insert("dmc_documents_header", 
									"(customer_web_user_id , document_type ,document_no ,document_date ,customer_email_adress ,
									document_printed_name ,document_printed_name2 ,document_referenz, 
									document_sum_net, document_discount ,document_status)", 
									"($customer_shop_id, '$Belegart', '$Belegnummer', '$Datum', '$EMail', 
										'$Name', '$Vorname', '$Zusatz',$GesamtpreisNetto,$GesamtRabatt,'$document_status')");
			} // end if else
		} // end exportmodus dmc_documents_header
		
		
		// Exportmodus Dokumentpositionen in Dokument Tabelle anlegen (neu ab 06062012)
		if ($ExportModusSpecial=='dmc_documents_positions') {
			/* <!-- Auftraege NAV -->
				select 'dmc_documents_positions' AS ExportModus, 'Auftrag' AS Belegart, b.[No_] AS document_no, b.[Line No_] AS position_no, b.[No_] AS product_sku, b.[Description] AS product_name, b.[Quantity] AS product_qty, b.[Unit Price] AS product_price, b.[Line Discount %] AS product_discount,b.[Quantity] * b.[Unit Price] AS product_price_amount, b.[VAT %] AS product_vat_percent, '' AS FF12 FROM [NAV].[dbo].[Cronus$Sales Line] AS b WHERE (b.[Shipment Date] > '2010-01-01') AND (b.[No_] like 'A%') AND (b.[No_] not like 'AG%')  ORDER BY b.[No_] , b.[Line No_],b.[Shipment Date] ASC
				CREATE TABLE IF NOT EXISTS `dmc_documents_positions` (
				  `document_id` int(11)  NULL,
				  `document_no` varchar(20)  NULL,
				  `document_type` varchar(20)  NULL,
				  `pos` varchar(100)  NULL,
				  `product_sku` varchar(50)  NULL,
				  `product_name` varchar(200)  NULL,
				  `product_qty` int(11)  NULL,
				  `product_price` int(11)  NULL,
				  `product_discount` int(11)  NULL,
				  `product_price_amount` int(11)  NULL,
				  `product_vat_percent` int(11)  NULL,
				  `document_user` varchar(100)  NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
			*/
		
			$Belegart = $Freifeld{2};				
			$Belegnummer = $Freifeld{3};
			$position_no = $Freifeld{4};
			$product_sku_variant_vpe = $Freifeld{5};
			$product_name = $Freifeld{6};
			$product_qty = $Freifeld{7};
				if ($product_qty =='') $product_qty =0.00;
			$product_price = $Freifeld{8};
						if ($product_price =='') $product_price =0.00;
			$product_discount = $Freifeld{9};
						if ($product_discount =='') $product_discount =0.00;
			$product_price_amount = $Freifeld{10};  
						if ($product_price_amount =='') $product_price_amount =0.00;
			$product_vat_percent = $Freifeld{11};
						if ($product_vat_percent =='') $product_vat_percent =0.00;
			$product_referenz = $Freifeld{12};
			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_documents_positions - Belegnummer  $Belegnummer \n");
			
			// sku + variantencode evtl
			$product_sku = explode ( '@', $product_sku_variant_vpe);
			// qty moeglicherweise mehrere, 
			$product_qties = explode ( '@', $product_qty);
		
			if (DEBUGGER>=1) fwrite($dateihandle, "SKU =".$product_sku[0]." \n");
			
			// Neue Datensaetze anlegen
			$where="document_no='".$Belegnummer."' AND pos='".$position_no."'"; 
			if (dmc_entry_exits('document_no', 'dmc_documents_positions', $where)) {
				if (DEBUGGER>=1) fwrite($dateihandle, "delete first\n");
				// Delete first, damit es keine doppelten Eintraege gibt
				dmc_sql_delete('dmc_documents_positions', $where);
				// Insert
				dmc_sql_insert("dmc_documents_positions", 
								"(document_no, document_type, pos, product_sku,product_variant,product_vpe, product_name ,
								product_qty,product_qty2,product_qty3, product_price, product_discount, 
								product_price_amount, product_vat_percent,product_referenz )", 
								"('$Belegnummer', '$Belegart', '$position_no', '".$product_sku[0]."', '".$product_sku[1]."', '".$product_sku[2]."', '$product_name', 
									'".$product_qties[0]."', '".$product_qties[1]."','".$product_qties[2]."', '$product_price', '$product_discount',$product_price_amount,$product_vat_percent, '$product_referenz')");
						// Pruefen ob der Kommissionseintrag etc auch in den Spalten document_kommissionen, (document_products,) document_skus sind 
						$where = "document_skus like '%".$product_sku[0]."%' AND document_kommissionen like '%".$product_referenz."%'";
						if (!dmc_entry_exits('document_no', 'dmc_documents_header', $where)) {
							// Insert document_kommissionen, (document_products,) document_skus
							$update = "document_skus = document_skus+'|'+'".$product_sku[0]."'";
							if ($product_referenz<>'') $update .= ", document_kommissionen = document_kommissionen+'|'+'".$product_referenz."'";
							dmc_sql_update("dmc_documents_header", $update, " document_no='".$Belegnummer."' AND document_type='".$Belegart."' ");
						} // end if else
			} else {
				// Insert
				dmc_sql_insert("dmc_documents_positions", 
								"(document_no, document_type, pos, product_sku,product_variant,product_vpe, product_name ,
								product_qty,product_qty2,product_qty3, product_price, product_discount, 
								product_price_amount, product_vat_percent,product_referenz )", 
								"('$Belegnummer', '$Belegart', '$position_no', '".$product_sku[0]."', '".$product_sku[1]."', '".$product_sku[2]."', '$product_name', 
									'".$product_qties[0]."', '".$product_qties[1]."','".$product_qties[2]."', '$product_price', '$product_discount',$product_price_amount,$product_vat_percent, '$product_referenz')");
				
				// Pruefen ob der Kommissionseintrag etc auch in den Spalten document_kommissionen, (document_products,) document_skus sind 
				$where = "document_skus like '%".$product_sku[0]."%' AND document_kommissionen like '%".$product_referenz."%'";
				if (!dmc_entry_exits('document_no', 'dmc_documents_header', $where)) {
				// Insert document_kommissionen, (document_products,) document_skus
					$update = "document_skus = document_skus+'|'+'".$product_sku[0]."'";
					if ($product_referenz<>'') $update .= ", document_kommissionen = document_kommissionen+'|'+'".$product_referenz."'";
					dmc_sql_update("dmc_documents_header", $update, " document_no='".$Belegnummer."' AND document_type='".$Belegart."' ");
				} // end if else
			} // end if else
				
		} // end exportmodus dmc_documents_positions

		if ($ExportModusSpecial=='dmc_document_hub') {
			/* select 'dmc_document_hub' AS Freifeld1, '/media/pdfs' AS Online_Verzeichnis, 'add_pdf_to_attribute_id' AS Features, p.Artikel AS Features_id, CONCAT('<a href="http://www.meinshop.eu/media/pdfs/',p.Artikel,'.pdf" target="_blank">Expertise als PDF</a>') as Feature_Name, '169' AS Attribute_ID, 'varchar' as Attribut_Typ,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11, CONCAT(p.Artikel,'.pdf') AS PDF_Upload FROM  Artikel as p WHERE p.Artikelgruppe = '010' AND p.Artikel like '%';
			select 'dmc_document_hub' AS Freifeld1, '/media/pdfs' AS Online_Verzeichnis, 'add_pdf_to_attribute_id' AS Features, p.Artikelnummer AS Features_id, CONCAT('<a href="http://www.meinshop.eu/media/pdfs/',p.Artikel,'.pdf" target="_blank">Handbuch als PDF</a>') as Feature_Name, '169' AS Attribute_ID, 'varchar' as Attribut_Typ,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11, CONCAT(p.Artikelnummer,'.pdf') AS PDF_Upload FROM  ART as p WHERE p.Artikelnummer like '%'			*/

			// Groesse der Datei in Link integrieren
			$set_size_to_link=true;
						
			// Uebergebene Variablen
			$Online_Verzeichnis = "..".$Freifeld{2};
			$Features = $Freifeld{3};
			$Features_ID = $Freifeld{4};		// ZB Artikelnummer
			$Features_Name = $Freifeld{5};
			$Attribute_ID = $Freifeld{6};
			$typ = $Freifeld{7};				// Typ des Attributes , zB text oder varchar
			if ($typ=="") $typ = "varchar";		// Standard
			$datei_name = $Freifeld{12};
			
			// Definitionen
			$store_id=0;
			
			// Wenn Datei Verzeichnis enthaelt, dann den Dateinamen separieren
		  	$datei_name = str_replace(' ','',$datei_name); 
			if (strpos($datei_name, "\\") !== false) {
				$datei_name=substr($datei_name,(strrpos($datei_name,"\\")+1),254); 
			} 
			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_document_hub - Belegdatei = $datei_name in Dateiverzeichnis $Online_Verzeichnis\n");
			
			if (DEBUGGER>=1) fwrite($dateihandle, "Datei: ".IMAGE_FOLDER ."$datei_name\n");
			if (DEBUGGER>=1) fwrite($dateihandle, "Dateiendung: ".substr($datei_name,-4)."\n");
			
			if (file_exists(IMAGE_FOLDER . $datei_name)) {
				if (DEBUGGER>=1) fwrite($dateihandle, "Kopiere -> copy(".IMAGE_FOLDER ."$datei_name,".$Online_Verzeichnis."/$datei_name) ... ");
				copy(IMAGE_FOLDER . $datei_name, $Online_Verzeichnis.'/'.$datei_name);
				fwrite($dateihandle, "erfolgt.\n");
				// Upload Datei löschen
				unlink(IMAGE_FOLDER . $datei_name);
				$fileexists=true;
			} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "Datei ".IMAGE_FOLDER . $datei_name." existiert leider nicht..\n");
				$fileexists=false;
			}
			
			// ggfls Dateigroesse ermitteln und Link ergaenzen
			if ($set_size_to_link) {
				$dateigroesse = (round(filesize($Online_Verzeichnis.'/'.$datei_name)/1024)); // in bytes / 1024 -> kb if (DEBUGGER>=1) 
				$Features_Name = str_replace('</a>','('.$dateigroesse.' kb) </a>',$Features_Name); 
			}
			
			// Pruefen, ob noch besondere Aktionen durchzufuehren sind
			if (strpos($Features, "add_pdf_to_cat_desc") !== false && $fileexists) {
				// Definitionen
				$entity_type_id=3;
				$Attribute_ID=36;
				// Kategorie ID 
				$cat_id=dmc_get_cat_keywords($Features_ID);
				if ($cat_id<>-1) {
					if (DEBUGGER>=1) fwrite($dateihandle, "add_pdf_to_cat_desc -> cat_id=$cat_id\n");
					// PDF Link zu Kategorie-Beschreibung 
					$table = "catalog_category_entity_text";  
					$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
					$values = "($entity_type_id , $Attribute_ID, $store_id, $cat_id,$value)";		
					// Alte Desciption ermitteln
					$value=dmc_sql_select_value('value', $table, "entity_id= $cat_id AND attribute_id=$Attribute_ID");
					if (strpos($value, $Features_Name) === false) {
						// Link existiert noch nicht -> Anhaengen
						$value = $value."<br />\n".$Features_Name;
						dmc_sql_update($table, " value='$value' ", " entity_id=$cat_id AND attribute_id=$Attribute_ID AND entity_type_id=$entity_type_id AND store_id=$store_id ");
					}
				}
			} // end add_pdf_to_cat_desc
			if (strpos($Features, "add_pdf_to_product_desc") !== false && $fileexists) {
				// Definitionen
				$entity_type_id=4;
				$Attribute_ID=72;
				// Pruefen ob Artikel existent und ggfls magento id ermitteln
				// get Magento article ID 
				$art_id=dmc_get_id_by_artno($Features_ID);
				if ($art_id<>-1 && $art_id<>'') {
					if (DEBUGGER>=1) fwrite($dateihandle, "add_pdf_to_product_desc -> art_id=$art_id\n");
					// PDF Link zu Kategorie-Beschreibung 
					$table = "catalog_product_entity_text";  
					$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
					$values = "($entity_type_id , $Attribute_ID, $store_id, $art_id,$value)";		
					// Alte Desciption ermitteln
					$value=dmc_sql_select_value('value', $table, "entity_id= $art_id AND attribute_id=$Attribute_ID");
					if (strpos($value, $Features_Name) === false) {					
						// Link existiert noch nicht -> Anhaengen
						$value = $value."<br />\n".$Features_Name;
						// Attribut ist noch nicht gesetzt/gesetzt
						if ($value!="") {
							dmc_sql_insert($table, $columns, "($entity_type_id , $Attribute_ID, $store_id, $art_id,'$value')");
						} else {
							// Link existiert noch nicht -> Anhaengen
							$value = $value."<br />\n".$Features_Name;
							dmc_sql_update($table, " value='$value' ", " entity_id=$art_id AND attribute_id=$Attribute_ID AND entity_type_id=$entity_type_id AND store_id=$store_id ");
						}
					} 					
				}
			} // end 
			// PDF Verlinkung an Attribut ergaenzen
			if (strpos($Features, "add_pdf_to_attribute_id") !== false && $fileexists) {
				// Definitionen
				$entity_type_id=4;
				// Pruefen ob Artikel existent und ggfls magento id ermitteln
				// get Magento article ID 
				$art_id=dmc_get_id_by_artno($Features_ID);
				if ($art_id<>-1 && $art_id<>'') {
					if (DEBUGGER>=1) fwrite($dateihandle, "add_pdf_to_attribute_id -> art_id=$art_id\n");
					
					// PDF Link zu Attribut ergaenzen
					$table = "catalog_product_entity_".$typ;  
					$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
					$values = "($entity_type_id , $Attribute_ID, $store_id, $art_id,'$Features_Name')";	
					$where = "entity_type_id = $entity_type_id AND attribute_id=$Attribute_ID AND store_id=$store_id AND entity_id=$art_id";
					// Alte Desciption ermitteln
					$value=dmc_sql_select_value('value', $table, $where);
					if (strpos($value, $Features_Name) === false) {					
						// Attribut ist noch nicht gesetzt/gesetzt
						if ($value=="") {
							// Link existiert noch nicht -> Einfuegen
							$value = $Features_Name;
							dmc_sql_insert($table, $columns, "($entity_type_id , $Attribute_ID, $store_id, $art_id,'$value')");
						} else {
							// Link existiert noch nicht -> Anhaengen
							$value = $value."<br />\n".$Features_Name;
							dmc_sql_update($table, " value='$value' ", $where);
						}
					} 
				}
			} // end 
			// PDF Verlinkung als Attribut setzen
			if (strpos($Features, "add_pdf_as_attribute_id") !== false && $fileexists) {
				// Definitionen
				$entity_type_id=4;
				// Pruefen ob Artikel existent und ggfls magento id ermitteln
				// get Magento article ID 
				$art_id=dmc_get_id_by_artno($Features_ID);
				if ($art_id<>-1 && $art_id<>'') {
					if (DEBUGGER>=1) fwrite($dateihandle, "add_pdf_as_attribute_id -> art_id=$art_id\n");
					// PDF Link zu Attribut ergaenzen
					$table = "catalog_product_entity_".$typ;  
					$columns = "(entity_type_id, attribute_id, store_id, entity_id, value)";
					$values = "($entity_type_id , $Attribute_ID, $store_id, $art_id,'$Features_Name')";	
					$where = "entity_type_id = $entity_type_id AND attribute_id=$Attribute_ID AND store_id=$store_id AND entity_id=$art_id";
					if (dmc_entry_exits('attribute_id', $table , $where)) {
						if (DEBUGGER>=1) fwrite($dateihandle, "delete first\n");
						// Delete first, damit es keine doppelten Eintraege gibt
						dmc_sql_delete($table, $where);
					} 
					dmc_sql_insert($table, $columns, $values);
				} // end if 
			} // end if
			
		} // end exportmodus dmc_document_hub
		
		// Exportmodus dmc_handelsstueckliste 
		if ($ExportModusSpecial=='dmc_handelsstueckliste') {
			// select 'dmc_handelsstueckliste' AS uebertragungsart,  st.Artikelnummer AS Artikel_Artikelnr, (SELECT TOP (1) ART.Bezeichnung FROM ART WHERE (st.Artikelnummer = ART.Artikelnummer)) as Bezeichnung,  st.SetArtikelnummer AS Set_Artikelnr, st.[Position] AS HST_Position, st.Menge AS Menge, st.Mengeneinheit AS Einheit, st.Zielpreis AS Preis, '19' AS MwSt_Satz, FLOOR(SUM(l.Bestand)/st.Menge) AS BestandHStListe,  '' AS FF11, '' AS FF12, '' AS FF13 FROM ART AS p, ARTSET AS st, LAGERP AS l WHERE p.Artikelnummer = st.SetArtikelnummer AND p.ShopAktiv = 'true' AND p.Artikelnummer like '%' AND l.Artikelnummer=st.SetArtikelnummer GROUP BY st.SetArtikelnummer, l.Artikelnummer, st.Menge, st.Artikelnummer, st.[Position], st.Mengeneinheit, st.Zielpreis ORDER BY st.[Position]
			// "CREATE TABLE IF NOT EXISTS `dmc_handelsstueckliste` ( `id` int(11) NOT NULL AUTO_INCREMENT, `artnr` varchar(20) NOT NULL DEFAULT '', `set_artnr` varchar(20) NOT NULL DEFAULT '', `set_position` int NULL DEFAULT 0, `menge` double NOT NULL DEFAULT 1, `einheit` varchar(20) NULL DEFAULT '', `preis` double NOT NULL DEFAULT '0', `mwst` double NOT NULL DEFAULT '0', `BestandHStListe` double NOT NULL DEFAULT '0', PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
			$Artikel_Artikelnr = html_entity_decode (sonderzeichen2html(true,$Freifeld{2}), ENT_NOQUOTES);
			$Bezeichnung=html_entity_decode (sonderzeichen2html(true,$Freifeld{3}), ENT_NOQUOTES);
			$Set_Artikelnr=html_entity_decode (sonderzeichen2html(true,$Freifeld{4}), ENT_NOQUOTES);
			$HST_Position=$Freifeld{5};
			$Menge=$Freifeld{6};
			$Einheit=html_entity_decode (sonderzeichen2html(true,$Freifeld{7}), ENT_NOQUOTES);
			$Preis=$Freifeld{8};
			$MwSt_Satz=$Freifeld{9};
			$BestandHStListe=$Freifeld{10};
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_handelsstueckliste\n");
			
			// Der Artikelnummer im der SetNummer ERSTE id ermitteln
			$where = " WHERE artnr = '$Artikel_Artikelnr' AND set_artnr='$Set_Artikelnr' LIMIT 1";
			$existiert=dmc_entry_exits('Artikel_Artikelnr', 'dmc_handelsstueckliste', $where);
			
			// Wenn bereits existent und der erste Artikel der Handelsstueckliste uebergeben wird
			if ($existiert && $HST_Position==1) {
				// Stueckliste leeren, wenn erster Artikel (Position=1)
				// Leeren / delete first
				$where= " WHERE artnr='$Artikel_Artikelnr' ";
				dmc_sql_delete('dmc_handelsstueckliste', $where);
				$existiert=false;
			}
			
			// Wenn bereits existent und der erste Artikel der Handelsstueckliste uebergeben wird
			if ($existiert) {
				// Bestand Handelsstueckliste ermitteln und ggfls korregieren, wenn kleiner
				$where=" artnr='".$Artikel_Artikelnr."' AND set_position = $HST_Position ";
				$altbestand=dmc_sql_select_value("BestandHStListe", "dmc_handelsstueckliste" , $where);
				if ($BestandHStListe<$altbestand) {
					// Bestand geringer bei dem neu uebergebenen Bestandteil der Handelsstckliste
					// Dieser ist zu setzen
					// Update auf den magento Artikel hinsichtlich des Bestandes durchführen.
					// get Magento article ID 
					$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);			
					
					// if exists
					if ($art_id!="") {
						// Update quantities
						$table = "cataloginventory_stock_item";		
						$what = "qty = '".$Artikel_Menge."'";
						$where = "product_id = '".$art_id."'";
						// $where .= " AND stock_id = '".$Lager_no."'";
						// todo -> get exeption when article not exists
						dmc_sql_update($table, $what, $where);
						// Update quantities in Flat table
					} else 
						if (DEBUGGER>=1) fwrite($dateihandle, "article with sku ".$Artikel_Artikelnr." does not exist.\n");
				} else {
					// Bestand ist bei diesem Bestandteil der HandelsStueckliste groesser als bei einem anderen. Daher bleibt der andere
					$BestandHStListe=$altbestand;
				}
				$update = "bezeichnung = '$Bezeichnung', set_artnr=$Set_Artikelnr, menge = $Menge, einheit = '$Einheit', preis = $Preis, mwst = '$MwSt_Satz', BestandHStListe=$BestandHStListe";
				dmc_sql_update("dmc_handelsstueckliste", $update, $where);
			} else {
				// Existiert noch nicht
				dmc_sql_insert("dmc_handelsstueckliste", 
								"(artnr, bezeichnung, set_artnr, set_position, 
								menge, einheit, preis, mwst,BestandHStListe)", 
								"('$Artikel_Artikelnr', '$Bezeichnung', '$Set_Artikelnr', '".$HST_Position.
								"', '".$Menge."', '".$Einheit."', '$Preis', '$MwSt_Satz','$BestandHStListe'");
				// get Magento article ID 
				$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);			
				
				// if exists
				if ($art_id!="") {
					// Update quantities
					$table = "cataloginventory_stock_item";		
					$what = "qty = '".$Artikel_Menge."'";
					$where = "product_id = '".$art_id."'";
					// $where .= " AND stock_id = '".$Lager_no."'";
					// todo -> get exeption when article not exists
					dmc_sql_update($table, $what, $where);
					// Update quantities in Flat table
				} else 
					if (DEBUGGER>=1) fwrite($dateihandle, "article with sku ".$Artikel_Artikelnr." does not exist.\n");
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "Detail Zuordnung $Artikel_Artikelnr zu handelsstueckliste $Set_Artikelnr eingetragen.\n");
		} // end exportmodus dmc_handelsstueckliste
		
		// Exportmodus Dokumente fuer Extension "File Downloads & Product Attachments Magento Extension"
		if ($ExportModusSpecial=='dmc_magento_file_downloads') {
			
			// select DISTINCT 'dmc_magento_file_downloads' as ExportModus, p.Artikelnummer AS Artikelnummer, p.Bezeichnung AS Dokument_Bezeichnung, p.Beschreibung AS Dokument_Beschreibung, 'pdf' as DateiTyp, '' as Kategorie,   '1' as Gastzugriff, '' as Kundengruppen, '0' as Download_Limit,'' as Freifeld10,'' as Freifeld11, 'S:\Archiv\RE_' + b.[No_]+'.PDF' AS PDF_Upload FROM Archiv AS a
			// Lexware:
			// select DISTINCT 'dmc_magento_file_downloads' as ExportModus, p.ArtikelNr AS Artikelnummer, doc.szBeschreibung AS Dokument_Bezeichnung, 'PDF-Download' AS Dokument_Beschreibung, 'pdf' as DateiTyp, '' as Kategorie,   '1' as Gastzugriff, '' as Kundengruppen, '0' as Download_Limit,'' as Freifeld10,'' as Freifeld11, doc.szDokumentDateiPfad AS PDF_Upload FROM (F1.FK_Artikel_View AS p INNER JOIN F1.FK_ArtikelDokument as a2d ON (p.ArtikelID=a2d.lArtikelID)) INNER JOIN F1.FK_Dokument as doc ON (a2d.lDocumentID=doc.lID) WHERE p.bStatus_WebShop=1

			define('DATEIVERZEICHNIS','../media/downloads/');				// Standard Dateiverzeichnis der Extension -> + FileId + Dateiname
			
			$Artikelnummer = $Freifeld{2};				
			$Bezeichnung = $Freifeld{3};
			$Beschreibung = $Freifeld{4};
			$DateiTyp = $Freifeld{5};
			if ($DateiTyp=="" || $DateiTyp='pdfs') $DateiTyp='pdf';
			$Kategorie = $Freifeld{6};
			if ($Kategorie=="") $Kategorie='1';
			// Check if category already exists (-1 if not)
			if ($Kategorie<>'1')
				if (!GENERATE_CAT_ID)
					$Kategorie=dmc_get_category_id("entity_id=".$Kategorie);	
				else 
					$Kategorie=dmc_category_exists($Kategorie);	
			$Gastzugiff = $Freifeld{7};
			if ($Gastzugiff=="" || !is_numeric($Gastzugiff)) $Gastzugiff='1';
			$Kundengruppen = $Freifeld{8};
			if ($Kundengruppen=="") $Kundengruppen='1';
			$downloads_limit = $Freifeld{9};
			if ($downloads_limit=="" || !is_numeric($downloads_limit)) $downloads_limit='0';
			
			$datei = $Freifeld{12};
			// Wenn Datei Verzeichnis enthaelt, dann den Dateinamen separieren
		  	$datei = str_replace(' ','',$datei); 
			if (strpos($datei, "\\") !== false) {
				$datei=substr($datei,(strrpos($datei,"\\")+1),254); 
			} 
			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_magento_file_downloads - Artikelnummer  $Artikelnummer Belegdatei = $datei ... ");
					
			// get Magento Product ID 
			$art_id=dmc_get_id_by_artno($Artikelnummer);	
	
			// Wenn Artikel existiert, Datei-Informationen zuordnen
			if ($art_id!="" && file_exists(IMAGE_FOLDER . $datei)) {
				// STEP 1 Neuen Datensatz in Download Tabelle anlegen
				if (DEBUGGER>=1) fwrite($dateihandle, "Filzsize von ".PDF_FOLDER .$datei." ");
				$filesize = filesize(PDF_FOLDER .$datei);
				if ($filesize == '') $filesize = '10000';
				$table='downloads_files';
				$where="filename='".$datei."'";
				if (!dmc_entry_exits('file_id', $table, $where)) {
					// Insert
					$file_id=dmc_get_highest_id('file_id',$table)+1;
					dmc_sql_insert($table, 
									"(`file_id`, `category_id`, `name`, `file_description`, `type`, `size`, `allow_guests`, `customer_groups`, `downloads`, `downloads_limit`, `date_added`, `date_modified`, `is_active`, `url`, `embed_code`, `filename`)", 
									"($file_id, $Kategorie, '$Bezeichnung', '$Beschreibung', '$DateiTyp', '$filesize', '$Gastzugiff', '$Kundengruppen', 0, 0, 'now()', 'now()', 1, NULL, NULL, '$datei')");
					if (DEBUGGER>=1) fwrite($dateihandle, " - zugeordnet in Tabelle 1 ... ");
					// STEP 2 -(PDF) Datei in korrektes Verzeichnis kopieren -> Standard Dateiverzeichnis der Extension -> + FileId + Dateiname
					$neue_datei=DATEIVERZEICHNIS.$file_id."/".$datei;				
					if (!is_dir(DATEIVERZEICHNIS.$file_id))
						mkdir (DATEIVERZEICHNIS.$file_id);
					copy(IMAGE_FOLDER . $datei, $neue_datei);	
				}
				// STEP 3 Datensatz mit Datei verknüpfen
				$filesize = filesize(PDF_FOLDER .$datei);
				$table='downloads_relation';
				$where="product_id='".$art_id."'";
				if (!dmc_entry_exits('file_id', $table, $where)) {
					// Insert
					$file_id=dmc_get_highest_id('file_id',$table)+1;
					dmc_sql_insert($table, 
									"(`file_id`, `product_id`)", 
									"($file_id, $art_id)");
					if (DEBUGGER>=1) fwrite($dateihandle, " - zugeordnet in Tabelle 2 ... \n");
				}
				// Upload Datei löschen
				unlink(IMAGE_FOLDER . $datei);
			} else {
				if (DEBUGGER>=1) fwrite($dateihandle, " - Artikelnummer  existiert nicht ... \n");
			}
		} // end exportmodus dmc_magento_file_downloads
		
	// Exportmodus customer_prices dmc_magento_customer_prices mit webtex extension am magento 1.7
	if ($ExportModusSpecial=='dmc_magento_customer_prices') {
			//  select 'dmc_magento_customer_prices' as ExportModus, ad.EMail as Kunden_EMAIL, p.Artikelnummer as Artikelnummer, p.AuspraegungID as Artikel_Variante, p.Einzelpreis AS Artikel_Preis, p.Rabattsatz as Rabattsatz, '1' as Menge, '0' as Website, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKArtikelKunden as p, KHKAdressen as ad, KHKKontokorrent as deb WHERE p.Kunde=deb.Kto AND deb.Adresse=ad.Adresse AND p.Mandant='10' AND ad.EMail IS NOT NULL AND (p.Rabattsatz IS NULL OR p.Rabattsatz=0) AND p.Einzelpreis IS NOT NULL

			$customers_email_address = trim($Freifeld{2});				// Store_Name
			$sku = trim($Freifeld{3});
			$var_id = $Freifeld{4};
			$price = trim($Freifeld{5});
			$discount = $Freifeld{6};
			// Preisberechnung, wenn discount angegeben
			if ($discount > 0) $price = $price -($price *$discount/100);
			$qty = $Freifeld{7};
			if ($qty=='') $qty=1;
			$store_id = $Freifeld{8};
			if ($store_id=='') $store_id=0;
			
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - dmc_magento_customer_prices - for customer $customers_email_address and sku $sku -> $price \n");
			
			// get Magento customer ID 
			$CustomerId=dmc_get_id_by_email($customers_email_address);	
			$art_id=dmc_get_id_by_artno($sku);	
			if (DEBUGGER) fwrite($dateihandle, "Magento ArtikelID = $art_id und CustomerID=$CustomerId ($customers_email_address)\n");			
			// Wenn Kunde existiert, kundenpreis zuordnen
			if ($CustomerId<> "" && $art_id<> "") {
				$where="qty=".$qty." AND store_id=".$store_id." AND customer_id=".$CustomerId. " AND product_id=".$art_id;
				if (dmc_entry_exits('price', DB_TABLE_PREFIX.'customerprices_prices', $where)) {
					// Update
					$query="UPDATE ".DB_TABLE_PREFIX."customerprices_prices ".
							"SET price='$price' WHERE ".$where;
					dmc_sql_query($query);
				} else {
					// Insert 
					dmc_sql_insert(DB_TABLE_PREFIX."customerprices_prices", 
									"(customer_id, product_id, store_id, qty, price, special_price, customer_email)", 
									"('$CustomerId', '$art_id', $store_id, $qty, $price, 0.00, '$customers_email_address')");
					
				} // end if else
			} //  endif Wenn Kunde existieren
		} // end exportmodus  dmc_magento_customer_prices
	
	// Exportmodus dmc_set_magento_attribute_values - Direktes Updates auf eine Reihe von Attributswerten
	if ($ExportModusSpecial=='dmc_set_magento_attribute_values') {
		// Merkmale und Auspreagungen ermitteln - werden als Auspreageung1@Auspreageung2@... übergeben
		
		// zB Attribute zum Preisabgleich eBay und Amazon aus Selectline Preis 3 und 4 an Magento Attribute preisebay@preisamazon
		// mit Abfangroutine auf Preis2 wenn anderer Preis = 0
		// select 'dmc_set_magento_attribute_values' as ExportModus, 'all' as Store_View_ID,  'add' as Type_Add_Change, p.Artikelnummer as Artikelnummer, 'preisebayhaustechnikhandel24@preisamazon' as Attribute_Codes, CASE WHEN (CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',3,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',3,1,1)),2)*1.19))='0' THEN CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',2,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',2,1,1)),2)*1.19) ELSE CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',3,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',3,1,1)),2)*1.19) END +'@'+CASE WHEN (CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',4,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',4,1,1)),2)*1.19))='0' THEN CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',2,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',2,1,1)),2)*1.19) ELSE CONVERT(VARCHAR(20),ROUND((SELECT TOP(1) (dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',4,1,1)) FROM ART WHERE p.Artikelnummer = ART.Artikelnummer ORDER BY dbo.SL_fnPreis(ART.ARTIKELNUMMER,getdate(),'EUR',4,1,1)),2)*1.19) END as Attribute_Werte,'' AS Freifeld7, '' as Freifeld8, '' as Freifeld9, '' as Freifeld10, '' as Freifeld11, '' as Freifeld12 FROM ART AS p INNER JOIN [ARPREIS] AS pr ON p.Artikelnummer = pr.Artikelnummer WHERE (p.Stueckliste <> 'V') AND pr.Typnummer=1 AND p.ShopAktiv='1' AND (pr.GeaendertAm > GETDATE() - 365) 
 
		$store_view = $Freifeld{2};			// all oder 1,2,3 etc.
		$Type_Add_Change = $Freifeld{3};	// Zur Zeit nicht verwendet
		$Artikel_Artikelnr = $Freifeld{4};
		$Artikel_Merkmal = $Freifeld{5};
		$Artikel_Auspraegung = $Freifeld{6};
		
		// INSERT INTO [SL_MSIG].[dbo].[ARKALK] ([Artikelnummer],[LetzterES],[AutoKalk],[RundungTyp],[ESPreis],[AufschlagES],[AufschlagESAbs],[LPSumme],[Kalkulationspreis],[AufschlagKP],[AufschlagKPAbs],[AutoLPreis],[Listenpreis],[AutoPreisTyp],[LetzterEK],[GroessterEK],[KleinsterEK],[MittlererEK],[MengeFuerEK],[EKAutomatik],[GeaendertAm],[GeaendertVon]) VALUES ($Artikelnummer,0,'K','100',0,0,0,0,0,0,0,'K',$Listenpreis,,'-1',0,0,0,0,1,'A',GETDATE(),1);
		
		//  Magento articleID 
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);
		
		if (DEBUGGER>=1) fwrite($dateihandle," dmc_set_magento_attribute_values für ArtID=".$art_id);
	 
		// Nur fuer existente Artikel forfahren 
		if ($art_id!="") {
			// (super)attribute und deren werte durch Ermittlung magento IDs mappen und ggfls anlegen
			// "Rueckgabe" von dmc_map_attributes.php -> Arrays $Merkmale, $Auspraegungen und $AuspraegungenID
			// $AuspraegungenID[0] ="";
			//if ($Artikel_Merkmal!="")
			//	if (is_file('userfunctions/products/dmc_map_attributes.php')) include ('userfunctions/products/dmc_map_attributes.php');
			//	else include ('functions/products/dmc_map_attributes.php');
		
			$Merkmale = explode ( '@', $Artikel_Merkmal);
			//for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ )
			//{
			//   if (DEBUGGER>=1) fwrite($dateihandle, "Merkmal ".$Anz_Merkmale." = ".$Merkmale[$Anz_Merkmale]."\n");
			//}			
				
			// Auspreageungen  und MerkmaleIDs ermitteln - werden als Auspreageung1@Auspreageung2@... übergeben
			$Auspraegungen = explode ( '@', $Artikel_Auspraegung);
					
			if (DEBUGGER>=1) fwrite($dateihandle, count ( $Merkmale )." Artikel Merkmale");
	 
			// Einzelne Attribute mit Werten setzen/aktualisieren
			for ( $Anz_Merkmale = 0; $Anz_Merkmale < count ( $Merkmale ); $Anz_Merkmale++ ) 
			{
				if (DEBUGGER>=1) fwrite($dateihandle, "Artikel ".$art_id." mit Attribute ".$Merkmale[$Anz_Merkmale]." und Wert ".$Auspraegungen[$Anz_Merkmale]." ID ".$AuspraegungenID[$Anz_Merkmale]." \n");
				#
				// Update product-attribute API
				# Array aufbauen
				$update_array[$Merkmale[$Anz_Merkmale]] = $Auspraegungen[$Anz_Merkmale];				
			} // end for
			# Array absetzen
			try {
				if ($store_view=='all')
					$client->call($sessionId, 'product.update', array($art_id, $update_array));
				else
					$client->call($sessionId, 'product.update', array($art_id, $update_array,$store_view));
				if (DEBUGGER>=1) fwrite($dateihandle, "session $sessionId ");
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_magento_attribute_values - Product Update failed:\nError:\n".$e."\n");		 
			}
			
		} // end if ($art_id=="")
			
	} // end exportmodus dmc_set_magento_attribute_values
	
	// Indexe neu aufbauen
	if ($ExportModusSpecial=='index_shop_neu') {
			/*Product Attributes 	1 	catalog_product_attribute
			Product Prices 	2 	catalog_product_price
			Catalog URL Rewrites 	3 	catalog_url
			Product Flat Data 	4 	catalog_product_flat
			Category Flat Data 	5 	catalog_category_flat
			Category Products 	6 	catalog_category_product
			Catalog Search Index 	7 	catalogsearch_stock
			Stock Status 	8 	cataloginventory_stock
			Tag Aggregation Data 	9 	tag_summary */
			require_once $_SERVER['DOCUMENT_ROOT']."/app/Mage.php";
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Neu indexieren:\n");
			Mage::app();
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_attribute');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Attributes  erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Product Flat Data erfolgt\n");
			$process = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_product');
			$process->reindexEverything();
			if (DEBUGGER>=1) fwrite($dateihandle, "*** Category Products erfolgt\n");
			
	}
	
	
	// ExportModus -> 'dmc_set_dispo_table_values' -> Übergabe und Anlage Bestände Dispositions Tabelle
	if ($ExportModusSpecial=='dmc_set_dispo_table_values') {
			//select TOP 1 'dmc_set_dispo_table_values' as ExportModus, 'delete' AS PosID, '' AS Typ, '' AS Artikelnummer, '' AS VariantenID, '' AS Lieferwoche, '' AS Anzahl, '' as Freifeld7, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKDispoArtikel WHERE Mandant=1
			//  select 'dmc_set_dispo_table_values' as ExportModus, '1' AS PosID, '99' AS Typ, [Artikelnummer] AS Artikelnummer, [AuspraegungID] AS VariantenID, 'Aktuell' AS Lieferwoche, sum(Bestand) AS Anzahl, '' as Freifeld7, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKLagerplatzbestaende WHERE Mandant=1 GROUP BY Artikelnummer, AuspraegungID
			//  select 'dmc_set_dispo_table_values' as ExportModus, [BelPosID] AS PosID, [Type] AS Typ, [Artikelnummer] AS Artikelnummer, [AuspraegungID] AS VariantenID,[Lieferwoche] AS Lieferwoche, [Menge] AS Anzahl, '' as Freifeld7, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKDispoArtikel WHERE Mandant=1 
			//select TOP 1 'dmc_set_dispo_table_values' as ExportModus, 'createcsv' AS PosID, '' AS Typ, '' AS Artikelnummer, '' AS VariantenID, '' AS Lieferwoche, '' AS Anzahl, '' as Freifeld7, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM KHKDispoArtikel WHERE Mandant=1
			// CREATE TABLE `dmc_dispo` ( `PosID` int(10), `Typ` varchar(20), `Artikelnummer` varchar(20), `VariantenID` int(5), `Lieferwoche` varchar(20),`Anzahl` int(5), `Art_ID` int(5)) ENGINE=MyISAM DEFAULT CHARSET=utf8; 
	
			$PosID = $Freifeld{2};				
			$Typ = $Freifeld{3};
			$Artikelnummer = $Freifeld{4};
			$VariantenID = $Freifeld{5};
			$Lieferwoche = $Freifeld{6};
			if ($Lieferwoche=="Aktuell") $Lieferwoche=="0Aktuell";
			$Anzahl = $Freifeld{7};
			// Art_ID=$art_id
			
			// (Anfangs)funktion um Tabelle zu leeren
			IF ($PosID=='delete' || $PosID=='truncate') {
				dmc_sql_query("truncate ".DB_TABLE_PREFIX."dmc_dispo;");
			} ELSE IF ($PosID=='createcsv') {
				$attribute_id=152;
				$entity_type_id=4;
				$store_id=0;
				$attribute_table='catalog_product_entity_text';
				$query .= "SELECT Art_ID, Lieferwoche, Anzahl FROM `dmc_dispo` WHERE Art_ID >1 ORDER BY Art_ID, Lieferwoche LIMIT 14000,5000" ;
				$link=dmc_db_connect();
				$beginn = microtime(true); 
				if (DEBUGGER==99)  fwrite($dateihandle, "1910 dmc_set_dispo_table_values-SQL= ".$query." BEGINN .\n");
				$artId=="";
				$sql_query = mysql_query($query);	
					$i=0;
				WHILE ($ERGEBNIS = mysql_fetch_array($sql_query)) {
					$i++;
					if (DEBUGGER==99)  fwrite($dateihandle, "1921 ($i)= ".$ERGEBNIS['Art_ID']." (bisherige Dauer:".(microtime(true) - $beginn).").\n");
					if ($ERGEBNIS['Art_ID']!=$artId) {
						// Neuer Artikel
						// - Vorherigen Artikel aktualisieren, wenn es nicht der erste ist
						if ($artId!="") {
							dmc_sql_delete($attribute_table, "attribute_id=$attribute_id AND store_id=$store_id AND entity_id=$artId");
							dmc_sql_insert($attribute_table, 
								"( entity_type_id	, attribute_id	, store_id	, entity_id	, value)", 
								"($entity_type_id, $attribute_id, $store_id, $artId, '$csv')");
						}
						
						// Neue Daten
						$csv="";
						$artId = $ERGEBNIS['Art_ID'];
						$bestand = $ERGEBNIS['Anzahl'];
					} else {
						$bestand = $bestand + $ERGEBNIS['Anzahl'];
					}
					$kw = $ERGEBNIS['Lieferwoche'];
						
					// CSV Aufbauen, besp
					// Aktuelle Woche;0;0;170000
					// 2013/17;0;-130000;40000
					// 2013/18;0;-40000;0
					// Aktuell an den Anfang sonst anhaengen
					if ($kw=="0Aktuell") 
						if ($csv=="")
							$csv  = "Aktuelle Woche;0;0;".$bestand."\n";
						else
							$csv  = "Aktuelle Woche;0;0;".$bestand."\n".$csv;
					else 
						if ($ERGEBNIS['Anzahl']>=0) // zugang
							$csv  = $csv.$kw.";".$ERGEBNIS['Anzahl'].";0;".$bestand."\n";
						else	// Abgang
							$csv  = $csv.$kw.";0;".$ERGEBNIS['Anzahl'].";".$bestand."\n";	
					if (DEBUGGER==99)  fwrite($dateihandle, "csv = $csv.\n");
				} // end while	
				// Letzer Artikel
				dmc_sql_delete($attribute_table, "attribute_id=$attribute_id AND store_id=$store_id AND entity_id=$artId");
				dmc_sql_insert($attribute_table, 
							"( entity_type_id	, attribute_id	, store_id	, entity_id	, value)", 
							"($entity_type_id, $attribute_id, $store_id, $artId, '$csv')");
			} ELSE {
				// get Art ID 
				$art_id=dmc_get_id_by_artno($Artikelnummer);	
				if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - dmc_set_dispo_table_values - fuer ArtikelnummerID $Artikelnummer/ID $art_id mit Menge $Anzahl \n");
				// Wenn Artikel existiert, Eintrag anlegen
				if ($art_id<> "") {
					$where="PosID=".$PosID. " AND Art_ID=".$art_id;
					if (dmc_entry_exits('PosID', DB_TABLE_PREFIX.'dmc_dispo', $where)) {
						// Update
						$query="UPDATE ".DB_TABLE_PREFIX."dmc_dispo ".
								"SET Anzahl=$Anzahl WHERE ".$where;
						dmc_sql_query($query);
					} else {
						// Insert 
						dmc_sql_insert(DB_TABLE_PREFIX."dmc_dispo", 
										"(PosID, Typ, Artikelnummer, VariantenID, Lieferwoche, Anzahl,Art_ID)", 
										"($PosID, '$Typ', '$Artikelnummer', 'VariantenID', '$Lieferwoche', '$Anzahl', $art_id)");
						
					} // end if else
				} //  endif Wenn Artikel existiert
			}
		} // end exportmodus  dmc_set_dispo_table_values
		
	// Exportmodus dmc_magento_customer_group_prices an magento 1.7
	if ($ExportModusSpecial=='dmc_magento_customer_group_prices') {
	
			//  select 'dmc_magento_customer_group_prices' as ExportModus, pr.[Customer Group] AS Kunden_Guppe, pr.[Item No_] as Artikelnummer, '' as Artikel_Variante, pr.[Unit Price incl_ Discount] AS Artikel_Preis, '0' as Rabattsatz, pr.[Minimum Quantity] as AbMenge, '0' as Website_ID, '0' as Store_ID,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROM [fms$Item Prices] AS pr WHERE pr.[Customer Group]<> '1         _ALLCUSTOMS'

			$customer_group_name = str_replace('1         _','',$Freifeld{2});				
			$sku = $Freifeld{3};
			$var_id = $Freifeld{4};
			$price = $Freifeld{5};
			$discount = $Freifeld{6};
			// Preisberechnung, wenn discount angegeben
			if ($discount > 0) $price = $price -($price *$discount/100);
			$fromqty = $Freifeld{7};
			if ($fromqty=='') $fromqty=1;
			if ($fromqty!='' && is_numeric($fromqty) && $fromqty<>'0E-20' && $fromqty<>'0') {
				$website_id = $website_id;
			} else {
				$fromqty = 1;		
			}		
			$website_id = $Freifeld{8};
			// Pruefen, ob (korrekte) website übergeben wurde
			if ($website_id!='' && is_numeric($website_id) && $website_id<20) {
				$website_id = $website_id;
			} else {
				$website_id = 0;		// Default, d.h. alle websites
			}
			$store_id = $Freifeld{9};
			if ($store_id=='') $store_id=0;
			// Pruefen, ob (korrekte) storeview übergeben wurde
			if ($store_id!='' && is_numeric($store_id) && $store_id<20) {
				$store_id = $store_id;
			} else {
				$store_id = 0;		// Default, d.h. alle Views
			}
			// Standard Magento tax_class_id
			$tax_class_id=3;
		// ggls Kundengruppe anlegen
			$group_id=dmc_customer_group_exists($customer_group_name);
			if ($group_id==-1) {
				//$group_id=dmc_customer_group_create($customer_group, $tax_class_id);
				//Get Customer Group Model
				$customer_group=Mage::getModel('customer/group');
				//Here Set Your Customer Group Code Liked as General,Guest,Reatiler
				$customer_group->setCode($customer_group_name);
				//Here Set Your Customer Group TaxClass id Based on your region setting location
				$customer_group->setTaxClassId($tax_class_id);
				//Save it now.
				$customer_group->save();
				//  Mage::getSingleton('customer/group')->setData( 'customer_group_code' => $customer_group, 'tax_class_id' => $tax_class_id )->save(); 
				$group_id=dmc_customer_group_exists($customer_group_name);
			}
			 
			$art_id=dmc_get_id_by_artno($sku);	
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_details - dmc_magento_customer_group_prices -  cust_group ID $group_id, website=$website_id, store=$store_id, art_id $art_id preis=$price ");
			// Wenn Artikel existiert 
			if ($art_id<> "") {
				// aus functions/products/dmc_art_functions.php
				if (is_file('userfunctions/products/dmc_art_functions.php')) include ('userfunctions/products/dmc_art_functions.php');
				else include ('functions/products/dmc_art_functions.php');
				// dmc_set_group_price($group_id,$art_id,$website_id,$store_id,$price);
				dmc_set_group_tier_price_fast($group_id,$sku,$art_id,$website_id,$store_id,$price,$fromqty);				
				if (DEBUGGER>=1) fwrite($dateihandle, "erfolgt \n ");
			} //  endif Wenn existierT
		} // end exportmodus  dmc_magento_customer_group_prices
		
			//  18.10.2013 - dmc_de_aktive_product - Schnelles aktivieren und deaktivieren 
		if ($ExportModusSpecial=='dmc_de_aktive_product') {
			// select 'dmc_de_aktive_product' AS uebertragungsart,  Artikelnummer AS Artikel_Artikelnr, Status AS Aktiv,  '' AS FF4, ''  AS FF5,''  AS FF6,''  AS FF7,''  AS FF8,''  AS FF9, ''  AS FF10, '' AS FF11, '' AS FF12, '' AS FF13 FROM ART
			
			// Uebergabe
			$Artikel_Artikelnr = html_entity_decode (sonderzeichen2html(true,$Freifeld{2}), ENT_NOQUOTES);
			$Status=$Freifeld{3};
			// 1 aktiv, 2 deaktiv
			if ($Status==0)
				$Status=2;
			
			// ATTRIBUTE ID FUER STATUS
			// Produkt aktivieren
			$ATTRIBUTE_STATUS = 84;
			
			// get Magento article ID 
			$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);			
				
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_de_aktive_product ID $art_id to Status ");
			
			// Wenn Produkt existent 
			// Wenn Produkt existent 
			if ($art_id!="") {
				// Update 
				$table = "catalog_product_entity_int";		
				$what = "value = '".$Status."'";
				$where = "attribute_id = ".$ATTRIBUTE_STATUS."  AND entity_id=".$art_id;
				dmc_sql_update($table, $what, $where);
				if (DEBUGGER>=1) fwrite($dateihandle, "erfolgt\n");
			} else {
				if (DEBUGGER>=1) fwrite($dateihandle, "nicht erfolgt, da Artikel nicht vorhanden.\n");
			}
			
		} // end exportmodus dmc_de_aktive_product
		
		// Exportmodus Lagerbestand updaten API
		if ($ExportModusSpecial=='lager_update') {
			// select 'lager_update' as Freifeld1, p.artikelnummer as Freifeld2, (SELECT SUM(lb.Bestand) FROM [Lagerbestand] AS lb INNER JOIN [Lagerplatz] AS lo ON lb.LagerplatzId=lo.ID INNER JOIN [Lager] AS l ON l.Lager = lo.Lager AND l.Auslagersperre=0 WHERE lb.Artikelnummer=p.Artikelnummer)  as Freifeld3, '' as Freifeld4, '' AS Freifeld5, '' as Freifeld6, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROMART as a where a.ShopAktiv = 'true' AND a.Artikelnummer like '$variable1%'
			$Artikel_Artikelnr = $Freifeld{2};
			$Artikel_Menge = $Freifeld{3};
			//$entity_id = dmc_get_id_by_artno($Artikel_Artikelnr);
			try {
				$client->call($sessionId, 'product_stock.update', array($Artikel_Artikelnr, array('qty'=>$Artikel_Menge, 'is_in_stock'=>1, 'use_config_manage_stock'=>'1')));		
				if ($debugger) fwrite($dateihandle, "Lagerbestand update erfolgreich für Artikel ".$Artikel_Artikelnr." \n");
			} 
			catch (SoapFault $e) {
				if ($debugger) fwrite($dateihandle, "Lagerbestand update NICHT erfolgreich für Artikel ".$Artikel_Artikelnr.": \nError:\n".$e."\n");		 
			}
		} // end  if ($ExportModusSpecial=='lager_update') {

		// Exportmodus Lagerbestand updaten Datenbank
		if ($ExportModusSpecial=='quick_stock_update') {
			// select 'quick_stock_update' as Freifeld1, p.artikelnummer as Freifeld2, (SELECT SUM(lb.Bestand) FROM [Lagerbestand] AS lb INNER JOIN [Lagerplatz] AS lo ON lb.LagerplatzId=lo.ID INNER JOIN [Lager] AS l ON l.Lager = lo.Lager AND l.Auslagersperre=0 WHERE lb.Artikelnummer=p.Artikelnummer)  as Freifeld3, '' as Freifeld4, '' AS Freifeld5, '' as Freifeld6, '' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12 FROMART as a where a.ShopAktiv = 'true' AND a.Artikelnummer like '$variable1%'
			$Artikel_Artikelnr = $Freifeld{2};
			$Artikel_Menge = $Freifeld{3};				
			if (DEBUGGER>=1) fwrite($dateihandle, "quick_stock_update\n");
			
			$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);	
				
			// Wenn existent und der erste Artikel der Handelsstueckliste uebergeben wird
			if ($art_id!='') {
				// Bestand korregieren
				// Update quantities
				$table = "cataloginventory_stock_item";		
				$what = "qty = '".$Artikel_Menge."'";
				$where = "product_id = '".$art_id."'";
				// $where .= " AND stock_id = '".$Lager_no."'";
				// todo -> get exeption when article not exists
				dmc_sql_update($table, $what, $where);
				// Website unabhaengig, ohne Status Aenderung
					// Update quantities in cataloginventory_stock_status
				$table = "cataloginventory_stock_status";		
				dmc_sql_update($table, $what, $where);
					// Update quantities in cataloginventory_stock_status_idx
				$table = "cataloginventory_stock_status_idx";		
				dmc_sql_update($table, $what, $where);
				
				
				$update = "bezeichnung = '$Bezeichnung', set_artnr=$Set_Artikelnr, menge = $Menge, einheit = '$Einheit', preis = $Preis, mwst = '$MwSt_Satz', BestandHStListe=$BestandHStListe";
				dmc_sql_update("dmc_handelsstueckliste", $update, $where);
			} else {
				// Existiert noch nicht
					if (DEBUGGER>=1) fwrite($dateihandle, "Produkt mit sku ".$Artikel_Artikelnr." existiert nicht.\n");
			}
		} // end quick_stock_update
		
		// Exportmodus languages_china
		if ($ExportModusSpecial=='languages_china') {
			if ($debugger) fwrite($dateihandle, "Export Sprachen Chinatrading\n");			
			$Artikel_Artikelnr = $Freifeld{2};
			$Artikel_Bezeichnung_D = $s = str_replace("'", "\'", $Freifeld{3});
			$Artikel_Bezeichnung_F = $s = str_replace("'", "\'", $Freifeld{4});
			$Artikel_Bezeichnung_E = $s = str_replace("'", "\'", $Freifeld{5});
			
			// Title Tags erstellen
			$titlelist = array(
				// D chinatrading.ch
				array(
					'store_id' => 2, 
					'text' => $Artikel_Bezeichnung_D . ' - TCM Online-Shop - Produkte zur traditionellen chinesischen Medizin'),
				// D medizinbaum.de
				array(
					'store_id' => 31,
					'text' => $Artikel_Bezeichnung_D . ' - TCM Produkte, günstig kaufen'),
				// D herbavital.com
				array(
					'store_id' => 11,
					'text' => $Artikel_Bezeichnung_D . ' - 100% Natur - 100% Qualität- Gesundheit, Fitnessprodukte günstig einkaufen'),
				// D herbavital.com/eu/
				array(
					'store_id' => 18,
					'text' => $Artikel_Bezeichnung_D . ' - 100% Natur - 100% Qualität - Europas Shop für günstige Gesundheits- & Fitness-Produkte '),
			
				// F chinatrading.ch
				array(
					'store_id' => 4,
					'text' => $Artikel_Bezeichnung_F . ' - Médecine traditionnelle chinoise - shop online avec des prix les plus avantageux'),
				// F medizinbaum.de
				array(
					'store_id' => 33,
					'text' => $Artikel_Bezeichnung_F . ' - acheter les produits TCM à bas prix - top qualité au prix top  - grand choix'),
				// F acushop.eu
				array(
					'store_id' => 34,
					'text' => $Artikel_Bezeichnung_F . ' - Votre premier partenaire  pour la médecine complémentaire en Europe'),
				// F herbavital.com
				array(
					'store_id' => 23,
					'text' => $Artikel_Bezeichnung_F . ' - 100% nature - 100% qualité top - produits pour votre Santé et votre Bien-être'),
				// F herbavital.com/eu/
				array(
					'store_id' => 29,
					'text' => $Artikel_Bezeichnung_F . ' - 100% nature - 100% qualité top - produits pour votre Santé et votre Bien-être'),
			
				// E chinatrading.ch
				array(
					'store_id' => 1,
					'text' => $Artikel_Bezeichnung_E . ' - Your Swiss complementary partner for Traditional Chinese Medicine'),
				// E medizinbaum.de
				array(
					'store_id' => 32,
					'text' => $Artikel_Bezeichnung_E . ' - Europe’s no. 1 online shop for Chinese Medicine'),
				// E acushop.eu
				array(
					'store_id' => 35,
					'text' => $Artikel_Bezeichnung_E . ' - Europe’s no. 1 online shop for Chinese Medicine'),
				// E herbavital.com
				array(
					'store_id' => 24,
					'text' => $Artikel_Bezeichnung_E . ' - 100% nature Health, Wellness and cosmetic products - top quality'),
				// E herbavital.com/eu/
				array(
					'store_id' => 30,
					'text' => $Artikel_Bezeichnung_E . ' - 100% nature Health, Wellness and cosmetic products - top quality')
			);
			
			
			
			// Meta Description erstellen
			$descriptionlist = array(
					
				// D chinatrading.ch
				array(
					'store_id' => 2, 
					'text' => $Artikel_Bezeichnung_D . ' bei Ihrem TCM Händler für Akupunkturnadeln, Schröpfgläser, Moxa, Kinesio-Taping, und Lasertherapie.'),
				// D medizinbaum.de
				array(
					'store_id' => 31,
					'text' => $Artikel_Bezeichnung_D . ' bei Ihrem langjährigen und zuversichtlichen Partner für TCM, spezialisiert auf Akupunktur, Schröpfen, Moxa.'),
				// D herbavital.com
				array(
					'store_id' => 11,
					'text' => $Artikel_Bezeichnung_D . ' bei Ihrem Schweizer Partner für Wellness, Beauty und Gesundheit durch Massage, Wärmetherapie, Tee.'),
				// D herbavital.com/eu/
				array(
					'store_id' => 18,
					'text' => $Artikel_Bezeichnung_D . ' und weitere Produkte für Kosmetik, Wellness, Gesundheit: Wärmetherapie, Lotionen & Cremes, Tee.'),
			
				// F chinatrading.ch
				array(
					'store_id' => 4,
					'text' => $Artikel_Bezeichnung_F . " à China TCM Trading - votre partenaire pour la médecine chinoise: aiguilles d\'acupuncture, moxa, ventouses et massage."),
				// F medizinbaum.de
				array(
					'store_id' => 33,
					'text' => $Artikel_Bezeichnung_F . ' à Medizinbaum.de - Votre partenaire fiable pour Acupuncture, ventouses et moxibustion.'),
				// F acushop.eu
				array(
					'store_id' => 34,
					'text' => $Artikel_Bezeichnung_F . " Aiguilles d\'acupuncture, moxa, ventouses... Livraison rapide et bon marché. Plus que 20\'000 produits pour la médecine chinoise."),
				// F herbavital.com
				array(
					'store_id' => 23,
					'text' => $Artikel_Bezeichnung_F . ' à Herbavital.com - Votre partenaire suisse pour les accessoires de la Santé et Mieux-être par le massage et la thérapie de chaleur.'),
				// F herbavital.com/eu/
				array(
					'store_id' => 29,
					'text' => $Artikel_Bezeichnung_F . ' Produits pour la Santé & Bien-être: Thérapie de chaleur et massage.'),
			
				// E chinatrading.ch
				array(
					'store_id' => 1,
					'text' => $Artikel_Bezeichnung_E . ' at China TCM Trading. Specialized in Acupuncture needles, massage, laser therapy, cupping and moxa.'),
				// E medizinbaum.de
				array(
					'store_id' => 32,
					'text' => $Artikel_Bezeichnung_E . ' at Medizinbaum.de - specialized in acupuncture, cupping, massage and moxibustion - more than 20000 articles on stock at best price.'),
				// E acushop.eu
				array(
					'store_id' => 35,
					'text' => $Artikel_Bezeichnung_E . ' and more products: Acupuncture needles, moxa, cupping... Reliable and fast delivery with best price.'),
				// E herbavital.com
				array(
					'store_id' => 24,
					'text' => $Artikel_Bezeichnung_E . ' and other products for Wellness / Health and traditional Chinese medicine. Reliable with fast delivery.'),
				// E herbavital.com/eu/
				array(
					'store_id' => 30,
					'text' => $Artikel_Bezeichnung_E . ' at Europe’s leading store for Health, Fitness, Wellness / Beauty - good price - best offer.')
					
				
			);
			
			// Artikel Name
			$artikelnamelist = array(
					
				// F chinatrading.ch
				array(
					'store_id' => 4,
					'text' => $Artikel_Bezeichnung_F),
				// F medizinbaum.de
				array(
					'store_id' => 33,
					'text' => $Artikel_Bezeichnung_F),
				// F acushop.eu
				array(
					'store_id' => 34,
					'text' => $Artikel_Bezeichnung_F),
				// F herbavital.com
				array(
					'store_id' => 23,
					'text' => $Artikel_Bezeichnung_F),
				// F herbavital.com/eu/
				array(
					'store_id' => 29,
					'text' => $Artikel_Bezeichnung_F),
			
				// E chinatrading.ch
				array(
					'store_id' => 1,
					'text' => $Artikel_Bezeichnung_E),
				// E medizinbaum.de
				array(
					'store_id' => 32,
					'text' => $Artikel_Bezeichnung_E),
				// E acushop.eu
				array(
					'store_id' => 35,
					'text' => $Artikel_Bezeichnung_E),
				// E herbavital.com
				array(
					'store_id' => 24,
					'text' => $Artikel_Bezeichnung_E),
				// E herbavital.com/eu/
				array(
					'store_id' => 30,
					'text' => $Artikel_Bezeichnung_E)
					
				
			);
			
			
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
			

			// Wenn Artikel existiert, Details zuordnen 
			if ($ProductId <> "") {
				if ($debugger ) fwrite($dateihandle, "Title und Description von Artikel $Artikel_Bezeichnung_D mit Artikel_Artikelnr=$Artikel_Artikelnr mit Magento ID=$ProductId setzen.\n");
				
				// Title Tag (catalog_product_entity_varchar: attribute_id=103)
				foreach($titlelist as $title) {
					$where="store_id=".$title['store_id']." AND attribute_id=103 AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where)) {
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$title['text']."'", $where);
					}
					else {
						// Insert
						dmc_sql_insert("catalog_product_entity_varchar", "(entity_type_id, attribute_id, store_id, entity_id, value)", "(10, 103, ".$title['store_id'].", ".$ProductId.", '".$title['text']."')");
					}

				} // end for
				
				// Meta Description (catalog_product_entity_varchar: attribute_id=105)
				foreach($descriptionlist as $desc) {
					$where="store_id=".$desc['store_id']." AND attribute_id=105 AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where)) {
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$desc['text']."'", $where);
					}
					else {
						// Insert
						dmc_sql_insert("catalog_product_entity_varchar", "(entity_type_id, attribute_id, store_id, entity_id, value)", "(10, 105, ".$desc['store_id'].", ".$ProductId.", '".$desc['text']."')");
					}

				} // end for

				// Artikelname (catalog_product_entity_varchar: attribute_id=96)
				foreach($artikelnamelist as $name) { 
					// Artikel Bezeichnung fuer Storeview eintragen : 
					$where="store_id=".$name['store_id']." AND attribute_id=96 AND entity_id=".$ProductId;
					if (dmc_entry_exits('value_id', 'catalog_product_entity_varchar', $where))
						// Update
						dmc_sql_update("catalog_product_entity_varchar", "value='".$name['text']."'", $where);
					else
						// Insert
						dmc_sql_insert("catalog_product_entity_varchar", 
										"(entity_type_id, attribute_id, store_id, entity_id, value)", 
										"(10, 96, ".$name['store_id'].", ".$ProductId.", '".$name['text']."')");
				}

			} //  endif Wenn Artikel existiert
			else{
				if ($debugger) fwrite($dateihandle, "Artikel NICHT VORHANDEN: $Artikel_Bezeichnung_D mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId.\n");
			}
			
		} // end exportmodus languages

		
		// Exportmodus dmc_attach_download_link
		if ($ExportModusSpecial=='dmc_attach_download_link') {
			// select 'product_to_categorie' as Freifeld1,  p.[Artikel-Nr_] AS Artikel_Artikelnr, '2' AS SpracheID, CASE WHEN p.[Produktgruppe]='' OR p.[Produktgruppe] IS NULL THEN SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]),1,19) ELSE isnull(SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]+'_'+p.[Produktgruppe]),1,19), SUBSTRING(HashBytes('MD5', p.[Artikelkategorie]),1,19)) END AS Artikel_Kategorie_ID ,'' as Freifeld4,'' as Freifeld5,'' as Freifeld6,'' as Freifeld7,'' as Freifeld8,'' as Freifeld9,'' as Freifeld10,'' as Freifeld11,'' as Freifeld12  FROM [fms$Mehrfachzuordnung] AS p WHERE p.[Im Webshop ausblenden] = 0
		
			if (DEBUGGER>=1) fwrite($dateihandle, "Export dmc_attach_download_link\n");
			
			$Artikel_Artikelnr = $Freifeld{2};
			$Sprache_id = $Freifeld{3};			// Store_ID
			$Kategorie_id = $Freifeld{4};
		
			// Magento Produkt ID ermitteln
			$ProductId = dmc_get_id_by_artno($Artikel_Artikelnr);
				
		
			if (DEBUGGER>=1 && $ProductId<>"") fwrite($dateihandle, "Artikel mit Artikel_Artikelnr = $Artikel_Artikelnr mit Magento ID=$ProductId \n");

/*			
			// Wenn Artikel  existiert -> Link zuordnen 
			if ($ProductId <> "" ) {
				$client->call(
					$sessionId, 
					'category.assignProduct', 
					array(
						$cat_id, 
						$Artikel_Artikelnr
						)
					);
			} //  endif Wenn Artikel existieren
	
$filesPath = '/var/www/ws/tests/WebService/etc/Modules/Downloadable/Product/Link';
$downloadableProductId = $ProductId;

$items = array(
    'small' => array(
        'link' => array(
            'title' => 'Test file',
            'price' => '123',
            'is_unlimited' => '1',
            'number_of_downloads' => '111',
            'is_shareable' => '0',
            'sample' => array(
                'type' => 'file',
                'file' =>
                array(
                    'filename' => 'files/test.txt',
                ),
                'url' => 'http://www.magentocommerce.com/img/logo.gif',
            ),
            'type' => 'file',
            'file' =>
            array(
                'filename' => 'files/test.txt',
            ),
            'link_url' => 'http://www.magentocommerce.com/img/logo.gif',
        ),
        'sample' => array(
            'title' => 'Test sample file',
            'type' => 'file',
            'file' => array(
                'filename' => 'files/image.jpg',
            ),
            'sample_url' => 'http://www.magentocommerce.com/img/logo.gif',
            'sort_order' => '3',
        )
    ),
    'big' => array(
        'link' => array(
            'title' => 'Test url',
            'price' => '123',
            'is_unlimited' => '0',
            'number_of_downloads' => '111',
            'is_shareable' => '1',
            'sample' => array(
                'type' => 'url',
                'file' => array(
                    'filename' => 'files/book.pdf',
                ),
                'url' => 'http://www.magentocommerce.com/img/logo.gif',
            ),
            'type' => 'url',
            'file' => array(
                'filename' => 'files/song.mp3',
            ),
            'link_url' => 'http://www.magentocommerce.com/img/logo.gif',
        ),
        'sample' => array(
            'title' => 'Test sample url',
            'type' => 'url',
            'file' => array(
                'filename' => 'files/image.jpg',
            ),
            'sample_url' => 'http://www.magentocommerce.com/img/logo.gif',
            'sort_order' => '3',
        )
    )
);

$result = true;
foreach ($items as $item) {
    foreach ($item as $key => $value) {
        if ($value['type'] == 'file') {
            $filePath = $filesPath . '/' . $value['file']['filename'];
            $value['file'] = array('name' => str_replace('/', '_', $value['file']['filename']), 'base64_content' => base64_encode(file_get_contents($filePath)), 'type' => $value['type']);
        }
        if ($value['sample']['type'] == 'file') {
            $filePath = $filesPath . '/' . $value['sample']['file']['filename'];
            $value['sample']['file'] = array('name' => str_replace('/', '_', $value['sample']['file']['filename']), 'base64_content' => base64_encode(file_get_contents($filePath)));
        }
        if (!$proxy->call(
            $sessionId,
            'product_downloadable_link.add',
            array($downloadableProductId, $value, $key)
        )
        ) {
            $result = false;
        }
    }
}
*/			
		} // end exportmodus dmc_attach_download_link
	
	
}// end function    SetDetails
	
	
	
?>
	
	