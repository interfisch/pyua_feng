<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector  bildzuordnung nicht existenter Bilder for magento shop						*
*  test.php																*
*  Status ausgeben														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
  
      //$proxy = new SoapClient('http://www.d-server.info/magento09/api/soap/?wsdl');
	  //$proxy = new SoapClient('http://localhost/magento09/api/soap/?wsdl');
	  define('VALID_DMC',true);	
	include ('/conf/definitions.inc.php');
	include ('dmc_db_functions.php');
	if (is_file('userfunctions/products/dmc_art_functions.php')) include ('functions/products/dmc_art_functions.php');
	else include ('functions/products/dmc_art_functions.php');
			
      
  	// soap authentification
	$zugriff=true;
    try {		 
		// Get Soap Connection
		    $client = new SoapClient(SOAP_CLIENT);
		    	//  api authentification, ->  get session token   
			$session = $client->login('dmconnector', 'dmc2003');	
			// if ($debugger==1) fwrite($dateihandle,"api authentification, ->  get session token ");			
	} catch (SoapFault $e) {
			echo "Access denied to".SOAP_CLIENT;
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
	$query	= "SELECT sku, name FROM `catalog_product_flat_1` where type_id='configurable' AND (`small_image` is null or `small_image` like 'no%') ORDER BY `sku` ASC";	
	
	$result = mysql_query($query) or die(mysql_error());

	// Alle ermittelten Artikelnummern durchlaufen
	while($row = mysql_fetch_array($result)){
		$Artikel_Artikelnr = $row['sku']."_0";
		$Artikelbild_Bezeichnung=$row['name'];
		// if ($Artikel_Artikelnr == 'AT-084-28-one-size') break;
		fwrite($dateihandle, "Bildzuordung $Artikelnummer - ");
		echo  "Bildzuordung $Artikel_Artikelnr - ";
		$Artikel_Bilddatei = $Artikel_Artikelnr.".jpg";
		
		$newProductId=dmc_get_id_by_artno($Artikel_Artikelnr);
		echo $Artikel_Bilddatei."<br />";
		
		 
		fwrite($dateihandle, $Artikel_Bilddatei."\n");
		
		if ($Artikel_Bilddatei != '') 
			attach_images_to_product($Artikel_Bilddatei, $Artikelbild_Bezeichnung, $Artikel_Artikelnr, $newProductId, $dateihandle,$client, $session); 
			
			
	} // end while

	// close db
	dmc_db_disconnect($link);		


?>