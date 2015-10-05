<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector  for magento shop												*
*  dmc_write_art.php														*
*  Artikel schreiben														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
/*
30.03.09
- Kategorie Zuordnung über Eintrag keywords der Kategorie
- Aenderungsdatum_SUPERATTRIBUTE uebergeben fuer configurable products
5.06.10
- nicht übergebene attribute werden durch zuweisung und abfrage aussortiert   $AuspraegungenID[$Anz_Merkmale]!='280273'
16.07.10
- Neuer Produkttyp 'variantenartikel' as Artikel_Typ
-> Hier werden Attribute Groessen und Farben (z.B. Groessen@Farben) uebergeben und deren Auspraegungen (z.B S,M,L@gruen,gelb)
-> ACHTUNG: Groessen und Farben muessen am Anfang der uebergeben Attribute in dieser Reichenfolge stehen
-> Angelegt werden die Produkte in allen Kombinationen
*/

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_write_art_extended($StoreView='default',$client, $session) {
		
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "function dmc_write_art_extended\n");
		// Einzelne Artikel ermitteln und generieren
		$Artikel_Artikelnr=$_POST['Artikel_Artikelnr'];
		$Artikel_Merkmal = $_POST['Artikel_Merkmal'];			
		$Artikel_Auspraegung = $_POST['Artikel_Auspraegung'];
		$Artikel_Variante_Von = $_POST['Artikel_Variante_Von'];	
		$Superattribut = $_POST['Artikel_URL1'];
		  
		// Groessen und Farben aus Attributen  und Auspraegungen extrahieren
		if (strpos($_POST['Artikel_Merkmal'], 'Groessen') === false) {
		    // keine Groessen vorhanden
						if (DEBUGGER>=1) fwrite($dateihandle, "keine Groessen vorhanden\n");	
		} else {
			// Groessen ermitteln und extrahieren
			if (DEBUGGER>=1) fwrite($dateihandle, "Groessen ermitteln und extrahieren\n");	
			$_POST['Artikel_Merkmal'] = substr($_POST['Artikel_Merkmal'], strpos($_POST['Artikel_Merkmal'], '@')+1); // $_POST['Artikel_Merkmal'] OHNE Groessen
			if (DEBUGGER>=1) fwrite($dateihandle, "Merkmal ohne Groessen =".$_POST['Artikel_Merkmal']."\n");	
			// durch Kommata getrennte Groessen in Array, vorher Leerzeichen entfernen
			$Groessen=explode (',', str_replace(' ','',substr($_POST['Artikel_Auspraegung'], 0, strpos($_POST['Artikel_Auspraegung'], '@'))));
			if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen [Groessen[0]]=".$Groessen[0]."\n");	
			// die groessen aus den Auspraegungen entfernen
			$_POST['Artikel_Auspraegung'] = substr($_POST['Artikel_Auspraegung'], strpos($_POST['Artikel_Auspraegung'], '@')+1); 
			if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen ohne Groessen =".$_POST['Artikel_Auspraegung']."\n");	
			
	    }
		
		if (strpos($_POST['Artikel_Merkmal'], 'Farben') === false) {
		    // keine Farben vorhanden
			if (DEBUGGER>=1) fwrite($dateihandle, "keine Farben vorhanden\n");
			// wenn andere Merkmale nicht vorhanden, d.h. @ nicht existiert
			if (strpos($_POST['Artikel_Merkmal'], '@') === false) {
				$_POST['Artikel_Merkmal'] = ""; // $_POST['Artikel_Merkmal'] OHNE Groessen
				$Farben="";
			} else {
				$Farben="";
			}
		} else {
			// Farben ermitteln und extrahieren
			if (DEBUGGER>=1) fwrite($dateihandle, "Farben ermitteln und extrahieren\n");	
			// $_POST['Artikel_Merkmal'] = substr($_POST['Artikel_Merkmal'], strpos($_POST['Artikel_Merkmal'], '@')+1); // $_POST['Artikel_Merkmal'] OHNE Groessen
			if (DEBUGGER>=1) fwrite($dateihandle, "Merkmale mit Farben =".$_POST['Artikel_Merkmal']."\n");	
			// wenn andere Merkmale (nach Farben) nicht vorhanden, d.h. @ nicht existiert
			if (strpos($_POST['Artikel_Merkmal'], '@') === false) {
				$_POST['Artikel_Merkmal'] = ""; // $_POST['Artikel_Merkmal'] OHNE Groessen
				$Farben=explode (',', $_POST['Artikel_Auspraegung']);
			} else {
				$_POST['Artikel_Merkmal'] = substr($_POST['Artikel_Merkmal'], strpos($_POST['Artikel_Merkmal'], '@')+1); // $_POST['Artikel_Merkmal'] OHNE Groessen
				$Farben=explode (',', str_replace(' ','',substr($_POST['Artikel_Auspraegung'], 0, strpos($_POST['Artikel_Auspraegung'], '@'))));
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "Merkmale ohne Farben =".$_POST['Artikel_Merkmal']."\n");	
			// durch Kommata getrennte Farben in Array, vorher Leerzeichen entfernen
			if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen [Farben[0]]=".$Farben[0]."\n");	
			if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen mit Farben =".$_POST['Artikel_Auspraegung']."\n");
			// die groessen aus den Auspraegungen entfernen
			// Wenn @ exitiert, sind noch weitere Auspraegzungen vorhanden, sonst nicht
			if (strpos($_POST['Artikel_Merkmal'], '@') === false) {
				$_POST['Artikel_Auspraegung'] = '';
			} else {
				$_POST['Artikel_Auspraegung'] = substr($_POST['Artikel_Auspraegung'], strpos($_POST['Artikel_Auspraegung'], '@')+1); 
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen ohne Farben =".$_POST['Artikel_Auspraegung']."\n");	
	    }
	
	
		// Mappings ermitteln
			if (MAP_MANUFACTURER) { 	// Hersteller Mappen 
				// HerstellerID ermitteln
				$HerstellerId=dmc_map_manufacturer_id ($_POST['Hersteller_ID']);
				$Hersteller=dmc_map_manufacturer ($_POST['Hersteller_ID']);
				$_POST['Hersteller_ID']=$HerstellerId;
				if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte HerstellerID Magento:".$_POST['Hersteller_ID']."und Name=$Hersteller\n");	
			}
		
			if (MAP_PRODUCT_GROUPS) { 	// Produktgruppen Mappen
				// $_POST['Artikel_Kategorie_ID'] = '110806, 210100';
				$kategorien=explode (CAT_DEVIDER, $_POST['Artikel_Kategorie_ID']);
				$_POST['Artikel_Kategorie_ID']='';
				
				for ( $i = 0; $i < count ( $kategorien ); $i++ ) {
					$main_cat_name[$i] = dmc_map_category_id(trim(substr($kategorien[$i],0,1) . "00000"));
					if (DEBUGGER>=1) fwrite($dateihandle, "*** 106 *** Ermittelter Kategorie_Name1 fuer ID ".trim(substr($kategorien[$i],0,1) . "00000")." Magento:".$main_cat_name[$i] ."\n");
					$cat_name[$i] = dmc_map_category_id(trim($kategorien[$i]));
					if (DEBUGGER>=1) fwrite($dateihandle, "*** 108 *** Ermittelter Kategorie_Name2 fuer ID ".trim($kategorien[$i])." Magento:".$cat_name[$i] ."\n");
					$kategorie_ids[$i] = dmc_get_catid_by_name($main_cat_name[$i],$cat_name[$i]); 
					if (DEBUGGER>=1) fwrite($dateihandle, "*** 110 *** Ermittelte kategorie_ids[$i] ".trim($kategorie_ids[$i])."\n");
					$_POST['Artikel_Kategorie_ID'] .= $kategorie_ids[$i].',';
				}
				$_POST['Artikel_Kategorie_ID'] = substr($_POST['Artikel_Kategorie_ID'], 0, -1);
				if (DEBUGGER>=1) fwrite($dateihandle, "*** 114 *** Ermittelte Kategorie_Namen Magento:".$_POST['Artikel_Kategorie_ID'] ."\n");
			}
			
			if (MAP_COLORS) { 	// Farben Mappen
				for ( $j = 0; $j < count ( $Farben ); $j++ )
					{	
						// dmc_map_color($Hersteller, $Farbnummer, $MapType)
						// color
						$FarbeID[$j]=$Farben[$j];
						$Farben[$j]=dmc_map_color($Hersteller, $FarbeID[$j],'color');
						$IntFarbeID[$j]=dmc_map_color($Hersteller, $FarbeID[$j],'intcolorcode');
						if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte Farbe $j Magento:".$Farben[$j]." fuer Hersteller=$Hersteller und Farben[$j]=".$FarbeID[$j]."\n");	
						if (DEBUGGER>=1) fwrite($dateihandle, "Ermittelte interne Farbe $j Magento:".$IntFarbeID[$j]."\n");	
						// Colorcode fuer Artikelnummer
						$IntFarbeID_ARTNR[$j]=$IntFarbeID[$j];
						// Colorcode auf color mappen
						$IntFarbeID[$j]=dmc_map_color_id ($IntFarbeID[$j]);
						if (DEBUGGER>=1) fwrite($dateihandle, "ArtNr: $Artikel_Artikelnr -  Neue Ermittelte interne Farbe $j Magento:".$IntFarbeID[$j]."\n");	
					
						// manufacturer_color
						$FarbCodes[$j]=dmc_map_color($Hersteller, $FarbeID[$j],'colorcode');
						if (DEBUGGER>=1) fwrite($dateihandle, "129 Ermittelter FarbCode $j Magento:".$FarbCodes[$j]."\n");	
						// Magento ID der Farbe ermitteln
						  $attr_type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_product");
						 // $MagentoFarbID[$j]=dmc_get_attribute_id_by_attribute_code($attr_type_id,'color');	
						//	$MagentoFarbID[$j]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Farben[$j]);
						  $MagentoFarbID[$j]= get_option_id_by_attribute_code_and_option_value('color', $Farben[$j]);
						if (DEBUGGER>=1) fwrite($dateihandle, "xxx $attr_type_id - Ermittelter MagentoFarbID $j Farbe ".$Farben[$j]." Magento:".$MagentoFarbID[$j]."\n");	
						
						// Farbe und Farbcode zusammensetzen
						//$Farben[$j]=$Farben[$j].'@'.$FarbCodes[$j];
						$Farben[$j]=$IntFarbeID[$j].'@'.$Farben[$j];
					}
			}
	
		// conf anlegen
		// Standardwerte  (uebermittelte) + geaendete Werte 
		$_POST["Artikel_ID"]='configurable'; // $Artikel_Typ 
		$_POST['Artikel_Variante_Von']='';	
		// Wenn simple products vorhanden, verwende eines deren Bilder fuer das configurable
		if (count ( $FarbeID ) > 0) {
			$zufall = rand(0,count ( $FarbeID ));
			$_POST['Artikel_Bilddatei']=$Artikel_Artikelnr."-".$FarbeID[$zufall].".jpg"; 
			if (DEBUGGER>=1) fwrite($dateihandle, "159 dmc_write_art_extended - Zufallsbild $zufall =".$_POST['Artikel_Bilddatei']."...\n");
		}
		$newProductId = dmc_write_art( 'default', $client, $session);
		
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_write_art_extended - conf written...\n");	
		
		// zugehoerige simples anlegen 
		// Standardwerte  (uebermittelte) + geaendete Werte
		if (isset($Groessen) && isset($Farben)) {
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_write_art_extended - Groessen und Farben vorhanden...\n");	
			// Groessen und Farben vorhanden
			$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
			$_POST['Artikel_Variante_Von'] = $Artikel_Artikelnr;	
			$_POST['Artikel_URL1']='';   // $Superattribut
			$Artikel_Merkmal = $_POST['Artikel_Merkmal'];			
			$Artikel_Auspraegung = $_POST['Artikel_Auspraegung'];
			for ( $i = 0; $i < count ( $Groessen ); $i++ )
			{			
					for ( $j = 0; $j < count ( $Farben ); $j++ )
					{			
						// Artikelnummer generieren
						// $_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$Groessen[$i]."-".substr($Farben[$j], 0, strpos($Farben[$j], '@'));
						//$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$FarbCodes[$j]."-".$Groessen[$i];
						//$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$MagentoFarbID[$j]."-".$Groessen[$i];
						$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$IntFarbeID_ARTNR[$j]."-".$Groessen[$i];
						$_POST['Artikel_Bilddatei']=$Artikel_Artikelnr."-".$FarbeID[$j].".jpg";  
		  			
						if (DEBUGGER>=1) fwrite($dateihandle, "Artikel_Artikelnr Simple $j =".$_POST['Artikel_Artikelnr']."\n");
						// Weitere Merkmale hinzufuegen, wenn vorhanden
						if ($Artikel_Merkmal<>'')
							$_POST['Artikel_Merkmal']=ATTR_GROESSEN.'@'.ATTR_FARBEN.'@'.$Artikel_Merkmal;
						else 
							$_POST['Artikel_Merkmal']=ATTR_GROESSEN.'@'.ATTR_FARBEN;
						if (DEBUGGER>=1) fwrite($dateihandle, "Merkmale Simple $j =".$_POST['Artikel_Merkmal'].".\n");
						// Auspragungen hinzufuegen wenn vorhanden
						if ($Artikel_Auspraegung<>'')
							$_POST['Artikel_Auspraegung']=$Groessen[$i].'@'.$Farben[$j].'@'.$Artikel_Auspraegung;
						else 
							$_POST['Artikel_Auspraegung']=$Groessen[$i].'@'.$Farben[$j];
						
						if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen Simple $j =".$_POST['Artikel_Auspraegung'].".\n");
						
						if (DEBUGGER>=1) fwrite($dateihandle, "Simple Product anlegen ArtNr=".$_POST['Artikel_Artikelnr'].", Groesse=".$Groessen[$i]." u. Farbe=".$Farben[$j]."\n");
						if (DEBUGGER>=1) fwrite($dateihandle, "Auspraegungen Simple $j =".$_POST['Artikel_Auspraegung']."\n");
					
						$newProductId = dmc_write_art( 'default', $client, $session);
					} // end for
			} // end for	
		} else if (isset($Groessen)) {
			// Nur Groessen vorhanden	
			$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
			$_POST['Artikel_Variante_Von'] = $Artikel_Artikelnr;	
			$_POST['Artikel_URL1']='';   // $Superattribut
			$Artikel_Merkmal = $_POST['Artikel_Merkmal'];			
			$Artikel_Auspraegung = $_POST['Artikel_Auspraegung'];
			for ( $i = 0; $i < count ( $Groessen ); $i++ )
			{			
						// Artikelnummer generieren
						$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$Groessen[$i];
						$_POST['Artikel_Bilddatei']=$_POST['Artikel_Artikelnr'].".jpg";  
		  
						// Merkmale hinzufuegen
						$_POST['Artikel_Merkmal']=ATTR_GROESSEN.'@'.$Artikel_Merkmal;
						// Auspragungen hinzufuegen
						$_POST['Artikel_Auspraegung']=$Groessen[$i].'@'.$Artikel_Auspraegung;
						if (DEBUGGER>=1) fwrite($dateihandle, "Simple Product anlegen ArtNr=".$_POST['Artikel_Artikelnr'].", Groesse=".$Groessen[$i]." OHNE Farbe\n");
						$newProductId = dmc_write_art( 'default', $client, $session);
			} // end for	
		} else if (isset($Farben)) {
			// Nur Farben vorhanden
			$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
			$_POST['Artikel_Variante_Von'] = $Artikel_Artikelnr;	
			$_POST['Artikel_URL1']='';   // $Superattribut
			$Artikel_Merkmal = $_POST['Artikel_Merkmal'];			
			$Artikel_Auspraegung = $_POST['Artikel_Auspraegung'];
			for ( $i = 0; $i < count ( $Farben ); $i++ )
			{			
						// Artikelnummer generieren
						$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr."-".$IntFarbeID_ARTNR[$i];
						$_POST['Artikel_Bilddatei']=$Artikel_Artikelnr."-".$FarbeID[$j].".jpg";  
		  			
						// Merkmale hinzufuegen
						$_POST['Artikel_Merkmal']=ATTR_FARBEN.'@'.$Artikel_Merkmal;
						// Auspragungen hinzufuegen
						$_POST['Artikel_Auspraegung']=$Farben[$i].'@'.$Artikel_Auspraegung;
						if (DEBUGGER>=1) fwrite($dateihandle, "Simple Product anlegen ArtNr=".$_POST['Artikel_Artikelnr'].", Farbe=".$Farben[$i]);
						$newProductId = dmc_write_art( 'default', $client, $session);
			} // end for	
		} // end if else
		
		
		return $newProductId;	
	} // end function


	
?>
	
	