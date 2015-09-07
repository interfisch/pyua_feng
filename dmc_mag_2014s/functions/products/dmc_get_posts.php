<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_get_posts.php														*
*  inkludiert von dmc_write_art.php 										
*  Artikel übergebene Variablen ermitteln									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
*/
 
		// Post ermitteln
		$ExportModus = $_POST['ExportModus'];
		// $Artikel_ID = (integer)($_POST['Artikel_ID']);
		$Artikel_Typ = $_POST["Artikel_ID"];
		if ($Artikel_Typ=="" || is_numeric($Artikel_Typ)) $Artikel_Typ = "simple"; // Standard Product-Typ ist simple
		$Kategorie_ID = $_POST['Artikel_Kategorie_ID'];
		$Hersteller_ID = $_POST['Hersteller_ID'];
		$Artikel_Artikelnr = trim($_POST['Artikel_Artikelnr']);
		// Überprüfen, ob eine EANNUMMER angegeben
		if (preg_match('/@/', $Artikel_Artikelnr)) {
			//list ($Aktiv, $Sortierung) = split ("@", $Sortierung);
			$werte = explode ( '@', $Artikel_Artikelnr);
			$Artikel_Artikelnr = $werte[0];
			$EAN_Nummer = $werte[1];
		} 
		$Artikel_Menge = $_POST['Artikel_Menge'];
		if ($Artikel_Menge == '' ) $Artikel_Menge = 0;
		// if (DEBUGGER>=1) fwrite($dateihandle, "Artikel_Menge=".$Artikel_Menge."\n");
		$Artikel_Preis = $_POST['Artikel_Preis'];
		$Artikel_Preis1 = $_POST['Artikel_Preis1'];
		$Artikel_Preis2 = $_POST['Artikel_Preis2'];
		$Artikel_Preis3 = $_POST['Artikel_Preis3'];
		$Artikel_Preis4 = $_POST['Artikel_Preis4'];
		$Artikel_Gewicht = $_POST['Artikel_Gewicht'];
		if ($Artikel_Gewicht == '' || !is_numeric($Artikel_Gewicht) || $Artikel_Gewicht == '0E-20') $Artikel_Gewicht = 0;
		if (isset($_POST['Artikel_Status'])) $Artikel_Status = $_POST['Artikel_Status'];
			else $Artikel_Status = '4';
		
		$Artikel_Steuersatz = (integer)($_POST['Artikel_Steuersatz']);
		$Artikel_Bilddatei = $_POST['Artikel_Bilddatei'];  
		$Artikel_Bilddatei = str_replace(' ','',$Artikel_Bilddatei); 
		// Wenn Bilddatei Verzeichnis enthaelt, dann das Bild separieren
		if (strpos($Artikel_Bilddatei, "\\") !== false) {
			$Artikel_Bilddatei=substr($Artikel_Bilddatei,(strrpos($Artikel_Bilddatei,"\\")+1),254); 
		} 
		$Artikel_VPE = $_POST['Artikel_VPE'];
		$Artikel_Lieferstatus = $_POST['Artikel_Lieferstatus']; 
		// Standard 2-3 tage
		if ($Artikel_Lieferstatus == "" || $Artikel_Lieferstatus =='1' || $Artikel_Lieferstatus =='0')
			$Artikel_Lieferstatus="2-3 Tage";
		$Artikel_Startseite = ($_POST['Artikel_Startseite']);
		if ($Artikel_Startseite==-1) $Artikel_Startseite=7;
		// $Artikel_Startseite wird verwendet fuer news_from_date ...
		$SkipImages = (bool)($_POST['SkipImages']);
		//if ($SkipImages &&  DEBUGGER>=1) fwrite($dateihandle, "Skipimages\n");
		//elseif (DEBUGGER>=1) fwrite($dateihandle, "Do not Skipimages\n");
		$SkipImages = false;
		$Artikel_Variante_Von = $_POST['Artikel_Variante_Von'];	
		// Wenn letzes Zeichen ein @, dann entfernen
		if (substr($Artikel_Variante_Von,-1)=="@")
			$Artikel_Variante_Von=substr($Artikel_Variante_Von,0,-1);
			
		$Artikel_Bezeichnung = $_POST["Artikel_Bezeichnung1"];
		$Artikel_Text = ($_POST["Artikel_Text1"]);
		$Artikel_Kurztext = $_POST["Artikel_Kurztext1"];
		$store_id=$Artikel_TextLanguage = (integer)($_POST["Artikel_TextLanguage1"]);
		$Artikel_MetaTitle = $_POST["Artikel_MetaTitle1"];
		$Artikel_MetaDescription = substr($_POST["Artikel_MetaDescription1"],0,254);
		$Artikel_MetaKeywords = $_POST["Artikel_MetaKeywords1"];
		$Artikel_Merkmal = $_POST['Artikel_Merkmal'];		
		// Wenn letzes Zeichen ein @, dann entfernen
		if (substr($Artikel_Merkmal,-1)=="@")
			$Artikel_Merkmal=substr($Artikel_Merkmal,0,-1);
		// DECREPATED if ($Artikel_Merkmal != '') $Artikel_Merkmal=dmc_map_merkmale($Artikel_Merkmal); 
		$Artikel_Auspraegung = $_POST['Artikel_Auspraegung'];
		$Auspraegung = $_POST["Auspraegung"];		 		  
		$Aktiv = $_POST["Aktiv"];
		if  ($Aktiv=="" || $Aktiv==0)
			$Aktiv=2;	
	    // Abfangroutinen dynamics NAV 0E-20=0
		if ($Artikel_Menge == "0E-20") $Artikel_Menge = 0;
		if ($Artikel_Preis1 == "0E-20") $Artikel_Preis1 = 0;
		if ($Artikel_Preis2 == "0E-20") $Artikel_Preis2 = 0;
  		if ($Artikel_Preis3 == "0E-20") $Artikel_Preis3 = 0;
		if ($Artikel_Preis4 == "0E-20") $Artikel_Preis4 = 0;
		$Artikel_Auspraegung = str_replace('0E-20','0',$Artikel_Auspraegung); 
		if (substr($Artikel_Auspraegung)=="@")
				$Artikel_Auspraegung=substr($Artikel_Auspraegung,0,-1);		//  
		// $store_id=0;
			// Ubergaben an  
		// Magento $_POST['Artikel_URL1']=Superattribute , z.B.size@color
		// Magento $_POST['Aenderungsdatum'] = AttibuteSet 
		$SuperAttr = $_POST['Artikel_URL1'];
		// DECREPATED if ($Superattribut != '') $Superattribut=dmc_map_merkmale($Superattribut); 
		// Wenn Artikel_URL1 Verzeichnis enthaelt, dann die Datei zur Download URL separieren
		if (strpos($_POST['Artikel_URL1'], "\\") !== false) {
			$Download_URL=substr($_POST['Artikel_URL1'],(strrpos($_POST['Artikel_URL1'],"\\")+1),254); 
		} 

		// $Artikel_URL = $_POST["Artikel_URL1"];
	    $attribute_set_id = $_POST["Aenderungsdatum"];
		
		
	
?>
	