<?php
/*******************************************************************************************
*                                                                       				*
*  dm.connector  for magento shop														*
*  dmc_xsell.php																		*
*  X-Selling etc																		*
*  Copyright (C) 2008 DoubleM-GmbH.de													*
*                                                                                       *
*******************************************************************************************/

// Erweitert 05.12.12 - Unterstuetzung Kommata getrennte XSell Artikel
// 06.12.12 - Umstellung Verknuefung auf die Artikel IDs statt Artikelnummern

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

// include('dmc_db_functions.php');

	function dmc_set_xsell($StoreView='default', $client, $sessionId) {
		
		global $dateihandle;
		
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_xsell\n");
		// Post ermitteln		  
		$Xsell_Type = $_POST['Xsell_Type'];					// cross_sell, up_sell, related
		$Artikel_Artikelnr = $_POST['Artikel_Artikelnr'];  
		$Xsell_Artikel_Artikelnr = $_POST['Xsell_Artikel_Artikelnr'];
		$FreiFeld1 = $_POST['FreiFeld1'];
		$FreiFeld2 = $_POST['FreiFeld2'];
		$FreiFeld3 = $_POST['FreiFeld3'];
		$FreiFeld4 = $_POST['FreiFeld4'];

		// Artikel Verknüpfung  anlegen, wenn noch nicht existiert - create relation if article  not exists
		// get Magento article ID 
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);
				
		// if exists
		if ($art_id!="") {
			$zubehoer_array = explode(",", $Xsell_Artikel_Artikelnr);			
			for($Anzahl = 0; $Anzahl < count($zubehoer_array); $Anzahl++) {     // Artikelnummen durchlaufen	     
				$Xsell_Artikel_Artikelnr=$zubehoer_array[$Anzahl];
				$Xsell_Artikel_Artikelnr_ID=dmc_get_id_by_artno($Xsell_Artikel_Artikelnr);
				// if xsell product exists
				if ($Xsell_Artikel_Artikelnr_ID!="") {
					// Aufbau: mixed SKU or ID, float specialPrice - special price (optional), string fromDate - from date (optional), string toDate - to date (optional), mixed storeView - store view ID or code (optional)
					if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "45 - Product ".$art_id." -> ".$Xsell_Artikel_Artikelnr_ID.". \n");
					try {		    
						// art der verbindung - choose for type of relation (cross_sell, up_sell, related)
						if ($Xsell_Type=="up_sell") {
							// Assign up_sell product
							$erfolg = $client->call($sessionId, 'product_link.assign', array('up_sell', $art_id, $Xsell_Artikel_Artikelnr_ID)); // Options - array('position'=>0, 'qty'=>56)
						} else if ($Xsell_Type=="related") {
							// Assign related product
							$erfolg = $client->call($sessionId, 'product_link.assign', array('related', $art_id, $Xsell_Artikel_Artikelnr_ID)); // Options - array('position'=>0, 'qty'=>56)
						} else { 
							// Standard -> Assign cross_sell product
							$erfolg = $client->call($sessionId, 'product_link.assign', array('cross_sell', $art_id, $Xsell_Artikel_Artikelnr_ID)); // Options - array('position'=>0, 'qty'=>56)
						} // end if 				
					} catch (SoapFault $e) {
							if (DEBUGGER>=1) fwrite($dateihandle,'Set xSell failed:\n'.$e);		    
					}
					if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "Product ".$Artikel_Artikelnr." liked with ".$Xsell_Artikel_Artikelnr." \n");
				} else {
					if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "XSell-Product ".$Xsell_Artikel_Artikelnr." does not exist. \n");
				}
			} // END FOR
		} else {
			if (DEBUGGER>=1 && $erfolg) fwrite($dateihandle, "Product ".$Artikel_Artikelnr." does not exist. \n");
		}
		
	} // end function

	
?>
	
	