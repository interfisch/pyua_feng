<?php
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
      
  	// soap authentification
	$zugriff=true;
	$user = isset($_POST['user']) ? $_POST['user'] : $_GET['user'];
	$password = isset($_POST['password']) ? $_POST['password'] : $_GET['password'];
	
    try {		 
		// Get Soap Connection
			echo "client ".SOAP_CLIENT."\n";	
		    $client = new SoapClient(SOAP_CLIENT);
		    	//  api authentification, ->  get session token   
			$session = $client->login($user,$password);	
			echo "session $session"."\n";			
	} catch (SoapFault $e) {
			echo "Access denied:\n $e"."\n";
			$session=0;	
			$zugriff=false;
	}
	
	// http://waldorfshop.eu/dmc_magento15/dmc_update_order_status.php?user=waldorfshop&password=mobilize1000&orderid=100002332&orderidto=100002411&orderstatus=pending&fromorderstatus=inmos 
	
	$orderid ='';
	$orderid = isset($_POST['orderid']) ? $_POST['orderid'] : $_GET['orderid'];
	if ($orderid=='') break;
	
	$orderidto ='';
	$orderidto = isset($_POST['orderidto']) ? $_POST['orderidto'] : $_GET['orderidto'];
	if ($orderto=='') $orderto=$orderid;
	
	$NEW_ORDER_STATUS='pending';
	$NEW_ORDER_STATUS = isset($_POST['orderstatus']) ? $_POST['orderstatus'] : $_GET['orderstatus'];
	if ($NEW_ORDER_STATUS=='') $NEW_ORDER_STATUS='pending';
	
	$fromorderstatus='inmos';
	$fromorderstatus = isset($_POST['fromorderstatus']) ? $_POST['fromorderstatus'] : $_GET['fromorderstatus'];
	if ($fromorderstatus=='') $fromorderstatus='pending';

	//$ergebnis = $client->call($session, 'sales_order.info', '100000112'); // Get   info.100002331
	echo "\n<br>-------------Update Order Status  to $NEW_ORDER_STATUS for Orders $orderid to $orderto----------------------<br>\n";

	for ($i=$orderid;$i<=$orderidto;$i++){
		if ($zugriff && $orderid > 100000000 && $fromorderstatus<>'') {
						try {
							$ergebnis = $client->call($session, 'sales_order.info', $i); // Get   info.
							if ($ergebnis['status']==$fromorderstatus) {
								$client->call($session, 'sales_order.addComment', array($i, $NEW_ORDER_STATUS,  'Bestellung wird bearbeitet',  false));
								echo "Order Status for $i updated to ".$NEW_ORDER_STATUS."<br>\n";	
							} else {
								echo "Order Status for $i NOT updated. It has Order Status.".$ergebnis['status']."<br>\n";	
							}
						} catch (SoapFault $e) {
							
							echo 'ERROR: Failed:\n'.$e.'\n'.$e->getMessage();
						}
		} // end if 
	} // end for
echo "\n<br>-----------------------------------<br>\n";

	  
	  

?>