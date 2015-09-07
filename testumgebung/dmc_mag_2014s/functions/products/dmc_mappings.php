<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_mappings.php														*
*  inkludiert von dmc_write_art.php 										
*  Artikel übergebene Variablen ermitteln									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
06.04.2012
- Unterstuetzung von uebergenener Attribute Set ID und Name
12.03.2012
- Erweiterte Unterstuetzung von HTML Texten
*/

		// Um verschieden angelegte identeische Attribute wie Innen(mm) und Innen (mm) zu unterstuetzen, erfolgt die Entfernung von Leerzeichen
		$Artikel_Merkmal = ( str_replace(' - ','',$Artikel_Merkmal));
		$SuperAttr = ( str_replace(' - ','',$SuperAttr));

		$attr_type_id= dmc_get_entity_type_id_by_entity_type_code("catalog_product");
		
		// Wenn attribute typ fuer produkte nicht ermittelbar, dann ABBRUCH
		if ($attr_type_id==-1) {
			fwrite($dateihandle, "*** FEHLER 28 in dmc_mappings, da attr_type_id=".$attr_type_id."\n");	
			fwrite($dateihandle, "*** ABBRUCH, da Type elementar -> HINT: Gibt es evtl ein Datenbank Tabellen PREFIX???\n");	
			return;
			break;
			$attr_type_id=4;
		}
		
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_mappings set alt=".$attribute_set_id."\n");	

		// Standard, bzw nicht korrekt uebergebener Wert
		if ($attribute_set_id == "")
			$attribute_set_id=ATTRIBUTE_SET;
		
		// attribute_set_id sollte numerisch sein, attribute_set_name der Klartext
		if(is_numeric($attribute_set_id)) {
			$attribute_set_name=dmc_sql_select_value('attribute_set_name', 'eav_attribute_set', 'attribute_set_id='.$attribute_set_id.' AND entity_type_id='.$attr_type_id);
		} else {
			$attribute_set_name=$attribute_set_id;
			$attribute_set_id=dmc_sql_select_value('attribute_set_id', 'eav_attribute_set', "attribute_set_name='".$attribute_set_name."' AND entity_type_id=".$attr_type_id);
		}
					if (DEBUGGER>=1) fwrite($dateihandle, "dmc_mappings set id neu=".$attribute_set_id."\n");	
					if (DEBUGGER>=1) fwrite($dateihandle, "dmc_mappings set name neu=".$attribute_set_name."\n");	

		if (MAP_PRODUCT_GROUPS_BY_ARTNR) { 	// Produktgruppen Mappen
			$Kategorie_ID = dmc_map_grp_by_artnr($Artikel_Artikelnr);
			if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte Kategorie_ID Magento:".$Kategorie_ID."\n");	
		}
		if (MAP_PRODUCT_COLORS_BY_ARTNR) { 	// Farben Mappen
			$farbe = dmc_map_colors_by_artnr($Artikel_Artikelnr);
			if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte Farbe Magento:".$farbe ."\n");	
				$Artikel_Merkmal .= "@color";			
				$Artikel_Auspraegung .= "@".$farbe;
		}
		
		// Wenn html header exitiert, nur Body Bereich 
		$suchstring='<body>';
		$endstring='</body>';
		if (strpos(strtolower($Artikel_Text), $suchstring) !== false) {
			$Artikel_Text=substr($Artikel_Text, strlen($suchstring)+strpos($Artikel_Text, $suchstring), (strlen($Artikel_Text) - strpos($Artikel_Text, $endstring))*(-1));
		}
		if ($Artikel_Text=='') $Artikel_Text='&nbsp;';
		if ($Artikel_Kurztext=='') $Artikel_Kurztext='&nbsp;';
		// Artikelbeschreibung um Popup ergänzen
		if (file_exists('./popup/' . $Artikel_Artikelnr.'.html')) {
			$Artikel_Text.='<br \><br \><a href="/media/popup/'.$Artikel_Artikelnr.'.html" target="_blank" onclick="window.open(this.href,this.target,\'width=300,height=300\'); return false;">Zusammensetzung</a>';
		} // endif popup
		
		// Artikelbeschreibung durch Textdatei ersetzen
		if (strpos($Artikel_Text, 'DATEI:') !== false) {
			$Dateiname=substr( $Artikel_Text,strpos($Artikel_Text, ':')+1,strlen($Artikel_Text));
			if (file_exists(str_replace('.txt','.htm',$Dateiname))) {
				$Dateiname = str_replace('.txt','.htm',$Dateiname);
			} 
			if (file_exists(str_replace('.html','.htm',$Dateiname))) {
				$Dateiname = str_replace('.html','.htm',$Dateiname);
			}
			if (file_exists(str_replace('.html','.txt',$Dateiname))) {
				$Dateiname = str_replace('.html','.txt',$Dateiname);
			}
			if (file_exists($Dateiname)) {
				$Artikel_Text='';
				$handle = fopen ($Dateiname, "r");
				while (!feof($handle)) {
					$buffer = fgets($handle);
					$Artikel_Text .= $buffer;
				}
				fclose ($handle);		
				$Artikel_Text=utf8_encode($Artikel_Text);
			} else {
				fwrite($dateihandle, "Textdatei ".$Dateiname." existiert nicht\n");	
			}
		}
		
		if ($Artikel_Text=="") $Artikel_Text="&nbsp;";
		
		// Artikelkurzbeschreibung durch Textdatei ersetzen
		if (strpos($Artikel_Kurztext, 'DATEI:') !== false) {
			$Dateiname=substr( $Artikel_Kurztext,strpos($Artikel_Kurztext, ':')+1,strlen($Artikel_Kurztext));
			if (file_exists(str_replace('.txt','.htm',$Dateiname))) {
				$Dateiname = str_replace('.txt','.htm',$Dateiname);
			} 
			if (file_exists(str_replace('.html','.htm',$Dateiname))) {
				$Dateiname = str_replace('.html','.htm',$Dateiname);
			}
			if (file_exists(str_replace('.html','.txt',$Dateiname))) {
				$Dateiname = str_replace('.html','.txt',$Dateiname);
			}
			if (file_exists($Dateiname)) {
				$Artikel_Kurztext='';
				$handle = fopen ($Dateiname, "r");
				while (!feof($handle)) {
					$buffer = fgets($handle);
					$Artikel_Kurztext .= $buffer;
				}
				fclose ($handle);	
				$Artikel_Kurztext=utf8_encode($Artikel_Kurztext);				
			} else {
				fwrite($dateihandle, "Textdatei ".$Dateiname." existiert nicht\n");	
			}
		}
		
		if ($Artikel_Kurztext=="") $Artikel_Kurztext="&nbsp;";
		
		
		if ($art_id != "")								
			if (is_file('userfunctions/products/dmc_delete_first.php')) include ('userfunctions/products/dmc_delete_first.php');
		
		
				// Überprüfen, ob eine Sortierreihenfolge angegeben
		if (preg_match('/@/', $Aktiv)) {
			//list ($Aktiv, $Sortierung) = split ("@", $Sortierung);
			$werte = explode ( '@', $Aktiv);
			$Aktiv = $werte[0];
			$Sortierung = $werte[1];
		} else {
			// Standard = keine besondere Sortierung
			$Sortierung=1;
		} // endif
		
	
?>
	