<?php

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
			$session = $client->login('dmconnector', 'niconico1');	
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
	// SELECT * FROM `catalog_product_flat_1` where type_id='configurable' AND  (`small_image` is null or `small_image` like 'no%') ORDER BY `sku` ASCBuderus Logatrend K-Profil Typ 20, 300 x 600
	$query	= "SELECT sku FROM `catalog_product_flat_1` where  (`name` Like 'Buderus Logatrend K-Profil  Typ 1%') limit 3";	
	// $query	= "SELECT sku FROM `catalog_product_flat_1` where  ( `name` Like 'Buderus Logatrend K-Profil  Typ 3%')and `name` like '%400%'";	
	echo "start ".SOAP_CLIENT."\n";
	$result = mysql_query($query) or die(mysql_error());

	// Alle ermittelten Artikelnummern durchlaufen
	while($row = mysql_fetch_array($result)){
		$Artikel_Artikelnr = $row['sku'];
		$art_id=dmc_get_id_by_artno($Artikel_Artikelnr);	
		
		//echo "$Artikel_Artikelnr= ".$row['name']."\n";

		try{	// if ($Artikel_Artikelnr == 'AT-084-28-one-size') break;
			//$Xsell_Artikel_Artikelnr="110030"; //Buderus-Montage-System BMSplus-FMS BH 300 V1, Fertigwand, mehrreihig 
			// $Xsell_Artikel_Artikelnr="110031"; //Buderus-Montage-System BMSplus-FMS BH 400 V1, Fertigwand, mehrreihig 
			// $Xsell_Artikel_Artikelnr="110032"; //Buderus-Montage-System BMSplus-FMS BH 500 V1, Fertigwand, mehrreihig 
		//	$Xsell_Artikel_Artikelnr="110033"; //Buderus-Montage-System BMSplus-FMS BH 600 V1, Fertigwand, mehrreihig 
		//	$Xsell_Artikel_Artikelnr="110034"; //Buderus-Montage-System BMSplus-FMS BH 900 V1, Fertigwand, mehrreihig 
			$xsell_art_id=dmc_get_id_by_artno($Xsell_Artikel_Artikelnr);	
			$erfolg = $client->call($session, 'product_link.assign', array('related', $art_id, $xsell_art_id)); // 
			
			$weitere = array ("101203","101209","101210","101211","101220","101221","101232","101233","101252");
			for ($i=0;$i<count($weitere);$i++) {
				$xsell_art_id=dmc_get_id_by_artno($weitere[$i]);	
				$erfolg = $client->call($session, 'product_link.assign', array('related', $art_id, $xsell_art_id)); // 
			}
			if ($erfolg) {
				fwrite($dateihandle, "Product ".$Artikel_Artikelnr." liked with ".$Xsell_Artikel_Artikelnr." etc\n");
				echo  "Product ".$Artikel_Artikelnr."/ $art_id liked with ".$Xsell_Artikel_Artikelnr."/$xsell_art_id etc\n";
			}
		} catch (Exception $e)  {
				echo "Problem mit Product ".$Artikel_Artikelnr."/ $art_id liked with ".$Xsell_Artikel_Artikelnr."/$xsell_art_id $e\n";
		
		}
		
	} // end while
echo "ende\n";
	// close db
	dmc_db_disconnect($link);		


?>