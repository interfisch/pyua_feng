<?php
/*******************************************************************************************
*                                                     										*
*  dmConnector ´ for magento shop															*
*  Copyright (C) 2008-2013 DoubleM-GmbH.de													*
*                                                                                          	*
Erweiterung 06.09.2013
- Installationsprogramm dmconnector_magento.php?action=dmc_install&user=info@mobilize.de&password=dmconnector123
*	06.01.2013 - delFiles("../var/session/","",3600); eingebunden								*

*******************************************************************************************/
	
define('VALID_DMC',true);		// zugriff zu includes

define('C8a6899ef',true);		// zugriff zu includes

//include ('definitions.inc.php'); decr 0913
include('./conf/definitions.inc.php');
include ('definitions_websites.inc.php');
include ('definitions_export.inc.php');
include ('functions/dmc_errors.php');


$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
//Mage::app();
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

	// debug modus
	
	if (DEBUGGER>=1)
	{ 
		
		date_default_timezone_set('Europe/Berlin');
		$datum = date("d.m.Y");
		$uhrzeit = date("H:i");
		$daten = "\n***********************************************************************\n";
		$daten .= "************************* dmconnector Shop *****************************\n";
		$daten .= "***********************************************************************\n";
		$daten .= $datum." - ".$uhrzeit." Uhr\n";
		if (LOG_ROTATION=='size' && is_numeric(LOG_ROTATION_VALUE))
			if (!file_exists(LOG_FILE)) 
				$dateihandle = fopen(LOG_FILE,"w"); // LOG File erstellen
			else if ((filesize(LOG_FILE)/1048576)>LOG_ROTATION_VALUE) 
				$dateihandle = fopen(LOG_FILE,"w"); // LOG File neu erstellen
			else
				$dateihandle = fopen(LOG_FILE,"a");
		else
			if (!file_exists(LOG_FILE)) 
				$dateihandle = fopen(LOG_FILE,"w"); // LOG File erstellen
			else
				$dateihandle = fopen(LOG_FILE,"a");
		// Fehlerlog
		if (LOG_ROTATION=='size' && is_numeric(LOG_ROTATION_VALUE))
			if (!file_exists(str_replace('.txt', '_error.txt', LOG_FILE))) 
				$dateihandleError = fopen(str_replace('.txt', '_error.txt', LOG_FILE),"w"); // Error LOG File neu erstellen
			else if ((filesize(str_replace('.txt', '_error.txt', LOG_FILE))/1048576)>LOG_ROTATION_VALUE) 
				$dateihandleError = fopen(str_replace('.txt', '_error.txt', LOG_FILE),"w"); // Error LOG File erstellen
			else
				$dateihandleError = fopen(str_replace('.txt', '_error.txt', LOG_FILE),"a");
			else
				$dateihandleError = fopen(str_replace('.txt', '_error.txt', LOG_FILE),"a");
		fwrite($dateihandle, $daten);	
	}
	
	
	// include needed functions
	//* if (DEBUGGER>=1) fwrite($dateihandle, "1\n");
	//* if (DEBUGGER>=1) fwrite($dateihandle, "2\n");
	//* if (DEBUGGER>=1) fwrite($dateihandle, "4\n");
	include('dmc_status.php');
	//* if (DEBUGGER>=1) fwrite($dateihandle, "5\n");
	include('dmc_functions.php');     
	include('dmc_db_functions.php');  
			
	//* if (DEBUGGER>=1) fwrite($dateihandle, "6\n");
	include('dmc_prices.php');
	//* if (DEBUGGER>=1) fwrite($dateihandle, "7\n");
	include('dmc_customers.php');
	//* if (DEBUGGER>=1) fwrite($dateihandle, "8\n");
	include('dmc_xsell.php');
	// include('dmc_set_specials.php');
	//* if (DEBUGGER>=1) fwrite($dateihandle, "9\n");
	// include('dmconnector_export.php');     
	include('dmc_set_details.php');
	include('dmc_mappings.php');
	
			
	// Uebergebene Daten loggen
	if (DEBUGGER>=1 && PRINT_POST) print_post($dateihandle);
	 	
	// user authentification
	$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
	$user = isset($_POST['user']) ?  $_POST['user'] : $_GET['user'];
	$password = isset($_POST['password']) ?  $_POST['password'] : $_GET['password'];
	if (substr($password,0,2)=='%%') {
		$password=md5(substr($password,2,40));
	}
	if ($user=='' || $password=='') {
		echo "Willkommen";
		exit;
	}
	
	if (DEBUGGER>=1) fwrite($dateihandle, "ExportModus :".$_POST['ExportModus'].".\n");	
	
	// Installationsprogramm starten zum Shop überprüfen.	 
	if ($action== 'dmc_install') {
			//if (CheckLogin($user,$password)) {
				include('./install/dmc_install.php');
				showDefinitions($user,$password);
			//}
			exit;
	}
	
	$USE_API==false;
	
	// Unterscheidung, ob API, SOAP API und dóder reiner Datenbankzugriff erforderlich
	if ($action == 'Art_Update' && $USE_API==false) {
		// DB Zugriff erforderlich
		$zugriff=true;
		// include ('functions/dmc_use_db.php');
	} else if ($action == 'setDetails' && $USE_API==false) {
		// MAE API Zugriff erforderlich
		if ($user=='dmconnector' && $password=='1234dmc2014') 
			$zugriff=true;
		else 
			$zugriff=false;
		//include ('functions/dmc_use_api.php');
	} else if ($action != 'Art_Update') { // || ($action == 'Art_Update' && $USE_API==true)) {
		// SOAP API erforderlich
		$zugriff=false;
		include ('functions/dmc_use_soap.php');
	}
	
	if (DEBUGGER>=1) fwrite($dateihandle, "Action= *".$action."* mit Session= ".$session."\n");
	
	if ($zugriff)
	{		
		// Abfangroutine " Loesche alle Magento Sessions aelter als 1 Stunde "
		delFiles("../var/session/","",3600);
		if ($action == "write_artikel") {	
			if (is_file('userfunctions/products/dmc_art_functions.php')) include ('functions/products/dmc_art_functions.php');
			else include ('functions/products/dmc_art_functions.php');
			//* if (DEBUGGER>=1) fwrite($dateihandle, "3\n");
			if (EXTENDED_PRODUCTS && (strpos($_POST['Artikel_Merkmal'], 'Groessen') !== false || strpos($_POST['Artikel_Merkmal'], 'Farben') !== false)) {	
				// Extended Artikel anlegen
				if (DEBUGGER>=1) fwrite($dateihandle, "Extended Artikel anlegen\n");		
				include('dmc_write_art_extended.php');
				$NewId = dmc_write_art_extended( 'default', $client, $session);
			} else if (EXTENDED_PRODUCTS_SIZE) {	
				// Extended Artikel anlegen
				if (DEBUGGER>=1) fwrite($dateihandle, "Extended Artikel Groessen anlegen\n");		
				include('dmc_write_art_extended_sizes.php');
				$NewId = dmc_write_art_extended_sizes( 'default', $client, $session);
			} else {
				// Standard Artikel anlegen
				if (DEBUGGER>=1) fwrite($dateihandle, "Standard Artikel anlegen\n");		
					include('dmc_write_art.php');	
					$NewId = dmc_write_art( 'default', $client, $session);
				if (DEBUGGER>=1) fwrite($dateihandle, "120-id=".$NewId."\n");
			} // end if 
			
			if ($NewId!=28021973)
					if (DEBUGGER>=1) fwrite($dateihandle, "Article created with NewId=".$NewId.".\n");					
			else 
					if (DEBUGGER>=1) fwrite($dateihandle, "Article already exits.\n");
					
			$done = true;
			echo "<XML><MESSAGE>OK</MESSAGE><CONFIRMED>Shop-ID=".$NewId."<CONFIRMED></XML>\n";
				// Update quantity
				// dmc_update_quantity('default', $client, $session);	
		} elseif ($action == 'write_categorie' ) {
			// Kategorie schreiben
			include('dmc_write_cat.php');	
			$StoreView='default'; 
			$NewId = dmc_write_cat($StoreView, $client, $session); 
			echo "<XML><MESSAGE>OK</MESSAGE><CONFIRMED>Shop-ID=".$NewId."<CONFIRMED></XML>\n";
		} elseif ($action == 'Art_Update' ) {
			fwrite($dateihandle, "180.\n");
			if (is_file('userfunctions/products/dmc_art_functions.php')) include ('functions/products/dmc_art_functions.php');
			else include ('functions/products/dmc_art_functions.php');
			fwrite($dateihandle, "183.\n");
			include('dmc_update_art.php');	
			fwrite($dateihandle, "185.\n");
			$Artikel_Menge = isset($_POST['Artikel_Menge']) ? $_POST['Artikel_Menge'] : $_GET['Artikel_Menge'];
			$Artikel_Preis = isset($_POST['Artikel_Preis']) ?  $_POST['Artikel_Preis'] : $_GET['Artikel_Preis'];	 
			// check for quantity_update		
			if ($Artikel_Menge!='' && ($_POST['ExportModus'] == 'QuantityOnly' || $_POST['ExportModus'] == 'PreisQuantity'))
				dmc_update_quantity('default', $client, $session);
			// check for price_update
			if ($Artikel_Preis!='' && ($_POST['ExportModus'] == 'PreisOnly' || $_POST['ExportModus'] == 'PreisQuantity'))
				dmc_update_price('default', $client, $session);			
		} elseif ($action == 'check_status' ) { 
		// Verbindung zum Shop überprüfen.	 
	        getStatus();		
		} elseif ($action == 'check_orders' ) { 
	    // Anzahl der Bestellungen prüfen	
			fwrite($dateihandle, "checkOrders aufrufen mit session=".$session.".\n");	
	        echo (checkOrders($session, $client));
	    } elseif ($action == 'setSpecials' ) { 	  
		// Aktionspreise - Specials
			fwrite($dateihandle, "dmc_set_specials **\n");
			dmc_set_specials('default', $client, $session);
	    } elseif ($action == 'write_customer' ) { 
		// Kunden - Customers
			dmc_set_customer('default', $client, $session);   
	    } elseif ($action == 'setXsell' ) { 
		// Cross-Selling 
			dmc_set_xsell('default', $client, $session); 
		} elseif ($action == 'setDetails' ) { 
			// Details
			if (is_file('userfunctions/products/dmc_art_functions.php')) include ('functions/products/dmc_art_functions.php');
			else include ('functions/products/dmc_art_functions.php');
			dmc_set_details('default', $client, $session); 	
		} elseif ($action == 'setOrderStatus' ) { 
			// Bestellstatus aendern - set new Order Status
			dmc_set_OrderStatus('default', $client, $session); 
		} elseif ($action == 'Status' ) { 
			// Bestellstatus aendern - set new Order Status
			dmc_status($client, $session); 
		} elseif ($action == 'backup' ) { 
			// Datenbank Backup
			dmc_db_backup(); 				
		} else {    
			echo "no action";
			showDebug();	
		}	// end if action		
   } else {
		echo "access denied";
   } // end if session			
 
	// Close the session 
    if($session!=0) {
	  $client->endSession($session);
	  fwrite($dateihandle, "Session $session closed.\n\n");
	 }
?>