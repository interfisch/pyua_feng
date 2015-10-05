<?php











exit;
 /*******************************************************************************************
*                                                                                          									*
*  dm.connector  test for magento shop											*
*  test.php																*
*  Status ausgeben														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
   
      //$client = new SoapClient('http://www.d-server.info/magento09/api/soap/?wsdl');
	  //$client = new SoapClient('http://localhost/magento09/api/soap/?wsdl');
	  define('VALID_DMC',true);	
   include ('definitions.inc.php');
      ini_set("display_errors", 1);
	  
    ini_set("default_socket_timeout", 6000);
      
	error_reporting(E_ERROR);

  	// soap authentification
	$zugriff=true;
    try {		 
		// Get Soap Connection
			echo "client http://www.pyua.de/index.php/api/soap/?wsdl\n";	
		//    $client = new SoapClient("http://nordiska.de/shop/index.php/api/soap/?wsdl");
			$client = new SoapClient("http://www.pyua.de/index.php/api/soap/?wsdl",array('trace' => 1,'exceptions' => 1));
			var_dump($client);
			echo "getLastRequest".$client->__getLastRequest();
			echo "__getLastResponse".$client->__getLastResponse();
			
			
			// $client = new SoapClient("http://nordiska.de/shop/index.php/api/soap/index/?wsdl=1");
			echo "Verbunden\n";
		    	//  api authentification, ->  get session token   
			echo $session = $client->login('dmc', 'dmc1308');	
			echo "session".$session."\n";	 		
	} catch (Exception $e) { //while an error has occured
            echo "==> Error: ".$e->getMessage(); //we print this
               exit();
       }
	
       exit;
	   ?>