<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector  bildzuordnung nicht existenter Bilder for magento shop						*
*  test.php																*
*  Status ausgeben														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
  
  echo "Bilderexport";
  
      //$proxy = new SoapClient('http://www.d-server.info/magento09/api/soap/?wsdl');
	  //$proxy = new SoapClient('http://localhost/magento09/api/soap/?wsdl');
	  define('VALID_DMC',true);	
	include ('definitions.inc.php');
	include ('dmc_db_functions.php');
	if (is_file('userfunctions/products/dmc_art_functions.php')) include ('functions/products/dmc_art_functions.php');
	else include ('functions/products/dmc_art_functions.php');
			
      
  	// soap authentification
	$zugriff=true;
    try {		 
		// Get Soap Connection
		    $client = new SoapClient(SOAP_CLIENT);
		    	//  api authentification, ->  get session token   
			$session = $client->login('dmconnector', 'dmc1406');	
			// if ($debugger==1) fwrite($dateihandle,"api authentification, ->  get session token ");			
	} catch (SoapFault $e) {
			echo "Access denied";
			if ($debugger==1) fwrite($dateihandle,"Access denied");
			$session=0;	
			$zugriff=false;
	}
	
	// Open DB
	$link=dmc_db_connect();
		$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		//fwrite($dateihandle, "Bildzuordungen\n");					
	
	// Produkte Ohne Bilder ermitteln
	// SELECT * FROM `catalog_product_flat_1` where type_id='configurable' AND  (`small_image` is null or `small_image` like 'no%') ORDER BY `sku` ASC
	$query	= "SELECT sku,image FROM `catalog_product_flat_1` where (`image` is NOT null AND `image` <>'') ORDER BY `sku` ASC  limit 10 ";	
	
	$result = mysql_query($query) or die(mysql_error());

	// Alle ermittelten Artikelnummern durchlaufen
	while($row = mysql_fetch_array($result)){
		$Artikel_Artikelnr = $row['sku'];
		$Artikel_Bilddatei = $row['image'];
		// if ($Artikel_Artikelnr == 'AT-084-28-one-size') break;
		echo  "Bildzuordung $Artikel_Artikelnr - ";
		
		$export_verzeichnis = "./rakuten/":
		$quelle = '../media/catalog/product'.$Artikel_Bilddatei;
		$ziel = $export_verzeichnis.$Artikel_Artikelnr.'.jpg';
		if ($Artikel_Bilddatei != '') 
		{
			if (!copy($quelle, $ziel )) {
				echo "copy von $quelle zu $ziel schlug fehl...\n";
			}
		}
		
	} // end while 

	// close db
	dmc_db_disconnect($link);		


?>