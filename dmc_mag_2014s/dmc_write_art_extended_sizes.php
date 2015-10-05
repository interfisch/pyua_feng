<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector  for magento shop												*
*  dmc_write_art_extended_sizes.php														*
*  Artikel schreiben														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
/*

*/

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_write_art_extended_sizes($StoreView='default',$client, $session) {
		
		global $dateihandle;
		if (DEBUGGER>=1) fwrite($dateihandle, "function dmc_write_art_extended_sizes\n");
		// Einzelne Artikel ermitteln und generieren
		$Artikel_Artikelnr=$_POST['Artikel_Artikelnr'];
		$Artikel_Bezeichnung = $_POST["Artikel_Bezeichnung1"];
		 
		// Groessen-Artikel?, Aufbau Artikelnummer  arc_1200_black_l
		$var = 'Wer steht dort hinter der Laterne?';

$treffer = substr_count ( $var, 'er' );


		if (substr_count($Artikel_Artikelnr, '_') == 3) {
		    // Groessen und Farben vorhanden
				// Groessen ermitteln und extrahieren
			if (DEBUGGER>=1) fwrite($dateihandle, "Groessen und Farben ermitteln und extrahieren - Artikel_Artikelnr=$Artikel_Artikelnr\n");
			$Artikel_Bezeichnung = $_POST["Artikel_Bezeichnung1"];			
			// Aufbau arc_1200_black_l
			// Ermitteln letztes Vorkommnis von _ 
			$pos=strrpos($Artikel_Artikelnr, '_');
			$pos2=strpos($Artikel_Artikelnr, '_', (strpos($Artikel_Artikelnr, '_')+1));
			$groesse=substr($Artikel_Artikelnr, strrpos($Artikel_Artikelnr, '_')+1);
			$farbe=substr($Artikel_Artikelnr, ($pos2+1),(strrpos($Artikel_Artikelnr, '_')-1-$pos2));
			// Variantenartikelnummer
			$Haupt_Artikel_Artikelnr=substr($Artikel_Artikelnr, 0,strrpos($Artikel_Artikelnr, '_'));
			// Configurable Product anlegen
			$_POST['Artikel_Artikelnr']=$Haupt_Artikel_Artikelnr;
			$_POST["Artikel_Bezeichnung1"]=substr($Artikel_Bezeichnung, 0,strpos($Artikel_Bezeichnung, 'Gr.')-1);
			$_POST["Artikel_ID"]='configurable'; // $Artikel_Typ 
			$_POST['Artikel_Status']=4;
			$_POST['Artikel_Variante_Von']='';
			$_POST['Artikel_URL1']='size';   // $Superattribut
			$_POST['Artikel_Merkmal']='';			
			$_POST['Artikel_Auspraegung']='';
			if (DEBUGGER>=1) fwrite($dateihandle, "Conf anlegen Artikelnummer=$Haupt_Artikel_Artikelnr, groesse=$groesse, farbe=$farbe\n");
			// echo "Conf anlegen Artikelnummer=$Haupt_Artikel_Artikelnr\n";
			$newProductId = dmc_write_art( 'default', $client, $session);
			// Zugehoeriges Simple Product anlegen
			// Configurable Product anlegen
			$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr;
			$_POST["Artikel_Bezeichnung1"]=$Artikel_Bezeichnung;
			$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
			$_POST['Artikel_Status']=1;
			$_POST['Artikel_Variante_Von']=$Haupt_Artikel_Artikelnr;
			$_POST['Artikel_URL1']='';   // $Superattribut
			$_POST['Artikel_Merkmal']='size@color';			
			$_POST['Artikel_Auspraegung']=$groesse.'@'.$farbe;
			if (DEBUGGER>=1) fwrite($dateihandle, "Simple anlegen Groesse=$groesse, Farbe=$farbe Artikel_Artikelnr=$Artikel_Artikelnr\n");
			//	echo "Simple anlegen Groesse=$groesse, Artikel_Artikelnr=$Artikel_Artikelnr\n";
			$newProductId = dmc_write_art( 'default', $client, $session);
		} else if (substr_count($Artikel_Artikelnr, '_') == 2)  {
			  //  Farben vorhanden
			if (DEBUGGER>=1) fwrite($dateihandle, "Farben ermitteln und extrahieren - Artikel_Artikelnr=$Artikel_Artikelnr\n");
			$Artikel_Bezeichnung = $_POST["Artikel_Bezeichnung1"];			
			// Aufbau arc_1200_black
			// Ermitteln letztes Vorkommnis von _ 
			$pos=strrpos($Artikel_Artikelnr, '_');
			$pos2=strrpos($Artikel_Artikelnr, '_',$pos+1);
			$farbe=substr($Artikel_Artikelnr, strrpos($Artikel_Artikelnr, '_')+1);
			// Variantenartikelnummer
			$Haupt_Artikel_Artikelnr=substr($Artikel_Artikelnr, 0,strrpos($Artikel_Artikelnr, '_'));
			// Configurable Product anlegen
			$_POST['Artikel_Artikelnr']=$Haupt_Artikel_Artikelnr;
			$_POST["Artikel_Bezeichnung1"]=$Artikel_Bezeichnung;
			$_POST["Artikel_ID"]='configurable'; // $Artikel_Typ 
			$_POST['Artikel_Status']=4;
			$_POST['Artikel_Variante_Von']='';
			$_POST['Artikel_URL1']='color';   // $Superattribut
			$_POST['Artikel_Merkmal']='';			
			$_POST['Artikel_Auspraegung']='';
			if (DEBUGGER>=1) fwrite($dateihandle, "Conf anlegen Artikelnummer=$Haupt_Artikel_Artikelnr, farbe=$farbe\n");
			// echo "Conf anlegen Artikelnummer=$Haupt_Artikel_Artikelnr\n";
			$newProductId = dmc_write_art( 'default', $client, $session);
			// Zugehoeriges Simple Product anlegen
			// Configurable Product anlegen
			$_POST['Artikel_Artikelnr']=$Artikel_Artikelnr;
			$_POST["Artikel_Bezeichnung1"]=$Artikel_Bezeichnung;
			$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
			$_POST['Artikel_Status']=1;
			$_POST['Artikel_Variante_Von']=$Haupt_Artikel_Artikelnr;
			$_POST['Artikel_URL1']='';   // $Superattribut
			$_POST['Artikel_Merkmal']='color';			
			$_POST['Artikel_Auspraegung']=$farbe;
			if (DEBUGGER>=1) fwrite($dateihandle, "Simple anlegen Groesse=$groesse, Farbe=$farbe Artikel_Artikelnr=$Artikel_Artikelnr\n");
			//	echo "Simple anlegen Groesse=$groesse, Artikel_Artikelnr=$Artikel_Artikelnr\n";
			$newProductId = dmc_write_art( 'default', $client, $session);
		} else {
			 //  keine Groessen und Farben vorhanden
						if (DEBUGGER>=1) fwrite($dateihandle, "keine Groessen und Farben vorhanden -> Normales simple product\n");
						$_POST["Artikel_ID"]='simple'; // $Artikel_Typ 
						$_POST['Artikel_Variante_Von']='';
					//	echo  "keine Groessen vorhanden -> Normales simple product\n";
						$newProductId = dmc_write_art( 'default', $client, $session);
		} // end if

		return $newProductId;	
	} // end function
	
?>
	
	