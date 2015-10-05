<?php
/*******************************************************************************************
*                                                      										*
*  dm.connector export magento shop	in andere Datenbank										*
*  Copyright (C) 2013 DoubleM-GmbH.de														*
*                                                                                          	*
*	12.12.13 - Abfrage orderlist von API auf DB umgestellt									*
*******************************************************************************************/

ini_set("display_errors", 0);
error_reporting(E_ERROR);
#error_reporting(null);

	$dateihandleExport = fopen("/var/www/pyua/dmc_mag_2014s/logs/dmconnector_log_magento_export.txt","a");
	fwrite($dateihandleExport,"dmcExport");
	fclose($dateihandleExport);
	
define('VALID_DMC',true);		// zugriff auf includes
// include needed functions
	include('./conf/definitions.inc.php');
	include ('definitions_export.inc.php');
	include ('functions/dmc_errors.php');
	include('functions/products/dmc_art_functions.php');     
	include('dmc_db_functions.php');     
	include('dmc_functions.php');     
	
	
	
				date_default_timezone_set('Europe/Berlin');
				$daten = "\n***********************************************************************\n";
				$daten .= "******************* dmconnector export ".date("YmdHis")." ****\n";
				$daten .= "************************************************************************\n";
				if (LOG_ROTATION=='size' && is_numeric(LOG_ROTATION_VALUE))
					if ((filesize(LOG_FILE_EXPORT)/1048576)>LOG_ROTATION_VALUE) 
						$dateihandle = fopen(LOG_FILE_EXPORT,"w"); // LOG File erstellen
					else
						$dateihandle = fopen(LOG_FILE_EXPORT,"a");
				else
						$dateihandle = fopen(LOG_FILE_EXPORT,"a");
				fwrite($dateihandle, $daten." DEBUGGER=".DEBUGGER);				
			

// check permissions for XML-Access
// $user=$_GET['user'];
//$password=$_GET['password'];
//$ExportModus = $_POST['ExportModus'];
//$Artikel_ID = (integer)($_POST['Artikel_ID']);
// user authentification
	$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];
	$user = isset($_POST['user']) ?  $_POST['user'] : $_GET['user'];
	$password = isset($_POST['password']) ?  $_POST['password'] : $_GET['password'];
	// Kein Update des Bestelldatums
	if (isset($_POST['noupdate_order_date'])) {
		$noupdate_order_date = $_POST['noupdate_order_date'];
	} else if (isset($_GET['noupdate_order_date'])) { 
		$noupdate_order_date = $_GET['noupdate_order_date'];
	} else {
		$noupdate_order_date = -1;
	}
	
	// Überprüfen, ob nur bestimmte Bestellungen abzurufen sind
	if (isset($_POST['orders_from'])) {
		$orders_from = $_POST['orders_from'];
	} else if (isset($_GET['orders_from'])) { 
		$orders_from = $_GET['orders_from'];
	} else {
		$orders_from = -1;
	}
	if (isset($_POST['orders_to'])) {
		$orders_to = $_POST['orders_to'];
	} else if (isset($_GET['orders_to'])) { 
		$orders_to = $_GET['orders_to'];
	} else {
		$orders_to = -1;
	}
	
	
	$noupdate=false;
	
	if (isset($orders_from) && $orders_from!=-1) {
		// kein Zeitliches Update der abgerufenen Bestellungen durchführen
		$noupdate_order_date = 1;
		$noupdate=true;
	}
	// Überprüfen, ob nur eine bestimmte Anzahl von Bestellungen zu importieren ist,
	if (isset($_POST['noOfOrder'])) {
		$noOfOrder = $_POST['noOfOrder'];
	} else if (isset($_GET['noOfOrder'])) { 
		$noOfOrder = $_GET['noOfOrder'];
	} else {
		$noOfOrder = 0;
	}
	
	// pyua 
	$noOfOrder = 1;
	
	if (substr($password,0,2)=='%%') {
	 $password=md5(substr($password,2,40));
	}
	if ($user!='' and $password!='') {
	}
	if ($password=='dmconnector123') $password="";
	
	$password="dmc1308";
	// soap authentification
	$zugriff=true;
    try {		 
		// Get Soap Connection
		    $client = new SoapClient(SOAP_CLIENT);
		    	//  api authentification, ->  get session token   
			$session = $client->login($user, $password);	
			if (DEBUGGER>=1) fwrite($dateihandle,"api authentification, ->  get session token :".$session);			
	} catch (SoapFault $e) {
			// Fehlerabfangroutine, wenn Session zugeteilt aber Access Denied
			// if ($debugger==1) fwrite($dateihandle,"Access denied");
			$sessionID=dmc_get_session_id();
			if ($sessionID<>0) {
				if (DEBUGGER>=1) fwrite($dateihandle,"api authentification failed ->  get session token over dmc_get_session_id\n");
				$zugriff=true;
			} else {
				$session=0;	
				$zugriff=false;
				if (DEBUGGER>=1) fwrite($dateihandle, "user authentification Access denied for ".$user."/".$password." Error=:\n ".$e." \n");
			}
	}
	
	
	if (DEBUGGER>=1) fwrite($dateihandle,"action: ".$action." zugriff ".$zugriff.".\n");
	
	// Überprüfen, welcher Export Modus und ggfls andere Datei aufrufen
	
	if ($action == 'products_export' && $zugriff) {
	    // Artikel exportieren
		include('dmc_export_products.php');     
		dmc_export_products($client, $session);
		//		echo "NewId=".$NewId."\n";
	} elseif ($action == 'categories_export' && $zugriff) {
	    // Kategorien exportieren
		include('dmc_export_categories.php');     
		dmc_export_categories($client, $session);
		//		echo "NewId=".$NewId."\n";
	} elseif ($action == 'orders_export' && $zugriff) {
			if (DEBUGGER>=1) fwrite($dateihandle,"108\n");

		// Letzte Abgerufene Bestellung ermitteln
		$dateihandleOrderID = fopen("./order_id.txt","r");
		$last_order = fread($dateihandleOrderID, 20);
		fclose($dateihandleOrderID);
		
		
		// Ermitteln der Anzahl der vorliegenden Bestellungen
		$BestellAnzahl = checkOrders($session, $client);	
		// if (DEBUGGER>=1) fwrite($dateihandle,"124 - $BestellAnzahl\n");

		// keine zeitliche Berücksichtigung der abgerufenen Bestellungen durchführen
		if ($orders_from>0) {
			if (DEBUGGER>=1) fwrite($dateihandle,"NUR Bestellungen abrufen: ".$orders_from." bis ".$orders_to.".\n");
				if ($orders_to>0) {
					$BestellAnzahl=$orders_to-$orders_from;
					// keine zeitliche Berücksichtigung der abgerufenen Bestellungen durchführen
					$last_order='2005-01-01 01:00:00';
				}
			
		}
			
		if (DEBUGGER>=1) fwrite($dateihandle,"Anzahl Bestellungen: ".$BestellAnzahl." und abzurufende Bestellungen laut Anfrage:".$noOfOrder.".\n");
			
		// Restrictions
		if (defined(STORE_ID_EXPORT)) {
			$Magento_Shop_ID = STORE_ID_EXPORT;
		} else { 
			$Magento_Shop_ID = 1;
		}
			
		$Magento_Shop_Status = ORDER_STATUS;
	
		if (ORDER_STATUS2<>'') 
			$Magento_Shop_Status2 = ORDER_STATUS2;
		else 
			$Magento_Shop_Status2 = "";
		
		// XML Schema 
	      //          '<ORDER_LIST>' . "\n";
		
		// $schema .= 	'<ORDER_ANZAHL>' . sizeof($order_list).'</ORDER_ANZAHL>';
		$schema .= 	'<ORDER_ANZAHL>' . $BestellAnzahl.'</ORDER_ANZAHL>';
		
		// Abbrechen, wenn keine Bestellungen vorhanden.
		if ($BestellAnzahl==0) {
		//	$schema .= 	'</ORDER_LIST>';
			echo $schema;
			exit;	
		}
		
		$from_incr_id = 0;
		$last_order_abgerufen = $last_order;
		// Schrittfolge ermitteln
		// Weniger als 10 Bestellungen
		if ($BestellAnzahl <= 10) {
			$AnzahlAbruf = 1;
		} else {
			// Abgerundeter Wert+1
			$AnzahlAbruf = floor($BestellAnzahl/10) + 1;
		}
		
		$durchlaufendeBestellungen = 0;
		
		if (DEBUGGER>=1) fwrite($dateihandle,"AnzahlAbruf ".$AnzahlAbruf ." mit Session=".$session."\n");
		
		//  Abschnitt Bestellungen in 10er Schritten 
		for ($rcm=0;$rcm<$AnzahlAbruf;$rcm++){
		
		// Erste Bestellnummer des Abschnittes ermitteln
		//$first_order_id = get_first_order_id(STORE_ID,ORDER_STATUS,$last_order_abgerufen,$from_incr_id);
		//if ($rcm==0) $erste_id=$first_order_id;
		//$last_order_id = get_last_order_id(STORE_ID,ORDER_STATUS,$last_order_abgerufen,$from_incr_id);
		
		/*if ($orders_from>0) {
			$first_order_id = $orders_from;
			if ($orders_to>0) {
				$last_order_id = $orders_to;
				$noOfOrder = $orders_to - $orders_from;
			} else 
				$last_order_id = '';
		}
		*/
	
	//	if (DEBUGGER>=1) fwrite($dateihandle,"first_order_id $first_order_id last_order_id $last_order_id noOfOrder $noOfOrder $durchlaufendeBestellungen \n");
	
	// PYUA 
	$noOfOrder=1;
	
		// Abbruch wenn Begrenzung der abzurufenden Bestellungen laut Anfrage JAVA erreicht.
		if (($noOfOrder > 0) && ($durchlaufendeBestellungen > $noOfOrder)) break;


	/*	if (DEBUGGER>=1) fwrite($dateihandle,"OrderList Schritt: ".$rcm." abrufen fuer: 'from'=> ".$last_order_abgerufen." 'gteq'=> ".$first_order_id." 'lteg'=> ".
				$last_order_id."' status'=> ".$Magento_Shop_Status.". mit session $session\n");
		*/
		
		// $order_list=$client->call($sessionId, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order),
		//																				'status'=> $Magento_Shop_Status)));
		$order_list = array();

		//	$order_list=$client->call($session, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order_abgerufen),
		//																	'increment_id'=>array('gteq'=>$first_order_id),
//																			'increment_id'=>array('lteq'=>$last_order_id),
		//																	'status'=> $Magento_Shop_Status)));

		// Aus Definitions
		$last_order=GET_ORDERS_FROM;
		
		// API order_list decrepated
		// $order_list=$client->call($session, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order_abgerufen),
		//																	'status'=> $Magento_Shop_Status)));
		
		$status = "(status='".$Magento_Shop_Status."'";
		if ($Magento_Shop_Status2<>'')
			$status .= " OR status='".$Magento_Shop_Status2."'";
		$status .= ")";
		
		
		if ($Magento_Shop_ID=="") {
			$storeid="";
		} else {
			$storeid="AND store_id = ".$Magento_Shop_ID;
		}
		
		$schema2="";
		
		// Open DB, if not opened
		$link=dmc_db_connect();
		$query = "SELECT * FROM  ".DB_TABLE_PREFIX."sales_flat_order WHERE ".$status." ".$storeid." AND created_at > '".$last_order_abgerufen."'";
		if (DEBUGGER>=1) fwrite($dateihandle, "dmc_sql_select_value-SQL= ".$query." ");
		$sql_query = mysql_query($query);		
		$i = 0;		
		while ($wert = mysql_fetch_array($sql_query)) {
			// Wichtige Werte in Array $order_list
			// entity_id
			$order_list[$i]['increment_id']=$wert['increment_id'];	
			$order_list[$i]['store_id']=$wert['store_id'];
			$order_list[$i]['customer_group_id']=$wert['customer_group_id'];	 
			$order_list[$i]['created_at']=$wert['created_at'];
			$order_list[$i]['total_paid']=$wert['base_total_paid'];  
			 
			// Benoetigte Variablen
			// Allgemein
			// $order_list[$i][increment_id] =$wert['increment_id'];	
			$order_infos[subtotal]=$wert['subtotal'];	
			$order_infos[tax_amount]=$wert['tax_amount'];	
			$order_infos[discount_amount]=$wert['discount_amount'];	
			$order_infos[base_subtotal_incl_tax] = $wert['base_subtotal_incl_tax'];	
			$order_infos[base_discount_amount] =$wert['base_discount_amount'];	 
			$order_infos[order_currency_code] =$wert['order_currency_code'];	
			$order_infos[weight] = $wert['weight'];	
			$order_infos[created_at]=$wert['created_at'];	
			$order_infos[order_id]=$wert['entity_id'];			
			$order_infos[remote_ip]=$wert['remote_ip'];	
			$order_infos[updated_at]=$wert['updated_at'];	
			$order_infos[status]=$wert[''];	
 	 	 	// zZt nicht verwendet
			$order_infos[quote_id]=$wert['quote_id'];	
 	 	 	$order_infos[increment_id]=$wert['increment_id'];
			$order_infos[store_name]=$wert['store_name'];	
 	 	 	$order_infos[subtotal_incl_tax] = $wert['subtotal_incl_tax'];	
			$order_infos[grand_total] = $wert['grand_total'];	
			$order_infos[base_grand_total] = $wert['base_grand_total'];	
			$order_infos[base_subtotal]=$wert['base_subtotal'];	
 	 	 	$order_infos[base_tax_amount]=$wert['base_tax_amount'];	
 	 	 	$order_infos[total_item_count]=$wert['total_item_count'];	
 	 	 	$order_infos[total_qty_ordered]=$wert['total_qty_ordered'];	
 	 	 	$order_infos[base_shipping_discount_amount]=$wert['base_shipping_discount_amount'];	
							
			// Versand
			$order_infos[shipping_method] =$wert['shipping_method'];	
			$order_infos[shipping_amount] = $wert['shipping_amount'];	
			$order_infos[shipping_tax_amount] = $wert['shipping_tax_amount'];	
			$order_infos[shipping_discount_amount] = $wert['shipping_discount_amount'];	
			$order_infos[cod_fee] =$wert['cod_fee'];	
			$order_infos[cod_tax_amount] =$wert['cod_tax_amount'];	
			// zZt nicht verwendet
			$order_infos[shipping_description] =$wert['shipping_description'];
 	   		$order_infos[shipping_incl_tax] =$wert['shipping_incl_tax'];
 	   		$order_infos[base_shipping_incl_tax] =$wert['base_shipping_incl_tax'];
 	   	  	$order_infos[paypal_ipn_customer_notified] =$wert['paypal_ipn_customer_notified'];
 	   	  	$order_infos[gift_message_id] =$wert['gift_message_id'];
 	   	  	 	 	 	 	 
			// Zahlung
			$order_infos[payment][po_number]=$wert[''];	
			$order_infos[payment][cc_number_enc]=$wert[''];	
			$order_infos[payment][cc_type]=$wert[''];	
			$order_infos[payment][cc_exp_month]=$wert[''];	
			$order_infos[payment][cc_exp_year]=$wert[''];	
			$order_infos[payment][cc_owner]=$wert[''];	
			$order_infos[payment][method]=$wert[''];	
			$order_infos[payment][last_trans_id]=$wert[''];	
			
			// Discount etc
			$order_infos[coupon_code] =$wert['coupon_code'];	
			// zZt nicht verwendet
			$order_infos[discount_description] =$wert['discount_description'];	
			
			// Kommentare
			$order_infos[onestepcheckout_customercomment] =	$wert[''];
			// zZt nicht verwendet
			$order_infos[customer_note] =	$wert['customer_note'];

			// Rechnung
			$invoice_infos[items] = array();
												
			// Kunde
			$order_infos[customer_id]=$wert['customer_id'];	
			$order_infos[customer_email] =$wert['customer_email'];	
			// zZt nicht verwendet
			$order_infos[customer_is_guest] =$wert['customer_is_guest'];	
			$order_infos[customer_dob] =$wert['customer_dob'];	
			$order_infos[customer_gender] =$wert['customer_gender'];	
			
			// Kundenadresse (zur Zeit nicht verwendet)
			$order_infos[customer_address][address_id] =$wert[''];	
			$order_infos[customer_address][street] =$wert[''];	
			$order_infos[customer_address][prefix] =$wert['customer_prefix'];	
			$order_infos[customer_address][title] =$wert['customer_suffix'];	
			$order_infos[customer_address][gender] =$wert[''];	
			$order_infos[customer_address][firstname] =$wert['customer_firstname'];	
			$order_infos[customer_address][customer_middlename] =$wert['customer_middlename'];	
			$order_infos[customer_address][lastname] =$wert['customer_lastname'];	
			$order_infos[customer_address][company] =$wert[''];	
			$order_infos[customer_address][postcode] =$wert[''];	
			$order_infos[customer_address][city] =$wert[''];	
			$order_infos[customer_address][country_id] =$wert[''];	
			$order_infos[customer_address][telephone] =$wert[''];	
			$order_infos[customer_address][fax] =$wert[''];	
			$order_infos[customer_email] =$wert['customer_email'];	
			 	  	 
			// Versandadresse ( aus sales_flat_order_address -> auf parent_id=sales_flat_order.entity_id ? und address_type='billing' )
			$order_infos[billing_address][address_id] =$wert['billing_address_id'];
			$order_infos[billing_address][prefix] =$address_wert['prefix'];	
			$order_infos[billing_address][title] =$address_wert['suffix'];	
			$order_infos[billing_address][gender] = '';	
			$order_infos[billing_address][firstname] =$address_wert['firstname'];	
			$order_infos[billing_address][lastname] =$address_wert['lastname'];	
			$order_infos[billing_address][company] =$address_wert['company'];	
			$order_infos[billing_address][street] =$address_wert['street'];	
			$order_infos[billing_address][postcode] =$address_wert['postcode'];	
			$order_infos[billing_address][city] =$address_wert['city'];	
			$order_infos[billing_address][country_id] =$address_wert['country_id'];	
			$order_infos[billing_address][telephone] =$address_wert['telephone'];	
			$order_infos[billing_address][fax] =$address_wert['fax'];
			// zZt nicht aktiv
			$order_infos[billing_address][region] =$address_wert['region'];
			$order_infos[billing_address][email] =$address_wert['email'];
			$order_infos[billing_address][middlename] =$address_wert['middlename'];
			$order_infos[billing_address][vat_id] =$address_wert['vat_id'];
			$order_infos[billing_address][vat_is_valid] =$address_wert['vat_is_valid'];
			
			// Rechnungsadresse ( aus sales_flat_order_address -> auf parent_id=sales_flat_order.entity_id ? und address_type='shipping' )
			$order_infos[shipping_address][address_id] =$wert['shipping_address_id'];	
			$order_infos[shipping_address][prefix] =$address_wert['prefix'];	
			$order_infos[shipping_address][title] =$address_wert['suffix'];	
			$order_infos[shipping_address][gender] = '';	
			$order_infos[shipping_address][firstname] =$address_wert['firstname'];	
			$order_infos[shipping_address][lastname] =$address_wert['lastname'];	
			$order_infos[shipping_address][company] =$address_wert['company'];	
			$order_infos[shipping_address][street] =$address_wert['street'];	
			$order_infos[shipping_address][postcode] =$address_wert['postcode'];	
			$order_infos[shipping_address][city] =$address_wert['city'];	
			$order_infos[shipping_address][country_id] =$address_wert['country_id'];	
			$order_infos[shipping_address][telephone] =$address_wert['telephone'];	
			$order_infos[shipping_address][fax] =$address_wert['fax'];
			// zZt nicht aktiv
			$order_infos[shipping_address][region] =$address_wert['region'];
			$order_infos[shipping_address][email] =$address_wert['email'];
			$order_infos[shipping_address][middlename] =$address_wert['middlename'];
			$order_infos[shipping_address][vat_id] =$address_wert['vat_id'];
			$order_infos[shipping_address][vat_is_valid] =$address_wert['vat_is_valid'];
			 	 	 	     
			// Artikel
			$order_infos[items] = array();
			$order_infos[items][$product_no][item_id] =$product_wert['product_id'];
			$order_infos[items][$product_no][sku]=$product_wert['sku'];
			$order_infos[items][$product_no][name]=$product_wert['name'];
			$order_infos[items][$product_no][base_price] = $product_wert['base_price'];
			$order_infos[items][$product_no][row_total] = $product_wert['row_total'];
			$order_infos[items][$product_no][base_price_incl_tax]=$product_wert['base_price_incl_tax'];
			$order_infos[items][$product_no][row_total_incl_tax]=$product_wert['row_total_incl_tax'];
			$order_infos[items][$product_no][tax_amount]=$product_wert['tax_amount'];
			$order_infos[items][$product_no][tax_percent]=$product_wert['tax_percent'];
			$order_infos[items][$product_no][discount_amount]=$product_wert['discount_amount'];
			$order_infos[items][$product_no][discount_percent]=$product_wert['discount_percent'];
			$order_infos[items][$product_no][qty_ordered]=$product_wert['qty_ordered'];
			$order_infos[items][$product_no][weight]=$product_wert['weight'];
			$order_infos[items][$product_no][product_type]=$product_wert['product_type'];
			$order_infos[items][$product_no][product_options]=$product_wert['product_options'];
			// zZt nicht verwendet
			$order_infos[items][$product_no][order_item_id] =$product_wert['item_id'];
			$order_infos[items][$product_no][is_virtual] =$product_wert['is_virtual'];
			$order_infos[items][$product_no][description] =$product_wert['description'];
			$order_infos[items][$product_no][additional_data] =$product_wert['additional_data'];
			$order_infos[items][$product_no][price] = $product_wert['price'];
			$order_infos[items][$product_no][price_incl_tax] = $product_wert['price_incl_tax'];
			$order_infos[items][$product_no][base_row_total_incl_tax] = $product_wert['base_row_total_incl_tax'];
			$order_infos[items][$product_no][original_price] = $product_wert['original_price'];
			$order_infos[items][$product_no][base_tax_amount] = $product_wert['base_tax_amount'];
			$order_infos[items][$product_no][gift_message_id] = $product_wert['gift_message_id'];
			$order_infos[items][$product_no][gift_message_available] = $product_wert['gift_message_available'];
				  
			$i++;
		}
		// close db
		dmc_db_disconnect($link);	
		
		if (DEBUGGER>=1) fwrite($dateihandle,"--------->  Größe OrderList1: ".count($order_list)."\n\n");
		
		// weiterer Order Status
		/*if ($Magento_Shop_Status2<>'') {
			//$order_list2=$client->call($session, 'sales_order.list', array(array('status'=> $Magento_Shop_Status2)));
			$order_list2=$client->call($session, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order_abgerufen),
																			'status'=> $Magento_Shop_Status2)));
			//$order_list2=$client->call($session, 'sales_order.list', array(array('created_at'=>array('from'=>$last_order_abgerufen),
			//															'increment_id'=>array('gteq'=>$first_order_id),
			//															// 'increment_id'=>array('lteq'=>$last_order_id),
			//															'status'=> $Magento_Shop_Status2)));
			if (DEBUGGER>=1) fwrite($dateihandle,"--------->  Größe OrderList2: ".count($order_list2)."\n\n");
		
			$order_list = array_merge($order_list , $order_list2);
		}*/																	
	
		if (DEBUGGER>=1) fwrite($dateihandle,"order_list abgerufen mit ".count($order_list)." Inhalten: \n");
			
		// Datum der zuletzt abgerufenen Bestellung ermitteln
		if (count($order_list)>0 && $last_order_id !='') $last_order_abgerufen=get_order_date_by_incr_id($last_order_id);
		
		for ($i=0;$i<sizeof($order_list);$i++){
		// Abbruch wenn Begrenzung der abzurufenden Bestellungen laut Anfrage JAVA erreicht.
		$durchlaufendeBestellungen++;
		
		// Abbruch wenn Begrenzung der abzurufenden Bestellungen laut Anfrage JAVA erreicht.
		if (($noOfOrder > 0) && ($durchlaufendeBestellungen > $noOfOrder)) break;
		
		// Used temp vars
		$total_tax = 0;
		$order_sum_discounted_net=0;
		$order_sum_discounted_gros=0;
		
		if (DEBUGGER>=1) fwrite($dateihandle,"BEARBEITET WIRD Bestellung Nummer ".$i." mit increment_id ".$order_list[$i][increment_id]);
		
		// get  Order Infos Array by increment_id s from the Order List Array by API Call
		$order_infos = array();
		$order_infos = $client->call($session, 'sales_order.info', $order_list[$i][increment_id]);
		
		// Wenn  Rechnungen zu exportieren sind, an Stelle Bestellungen
		if (EXPORT_INVOICES) {
			if (SHOP_VERSION>1.3) {
				// null werte auf ''
				 clean_cybersource_token();
				// Zugehörige NOCH NICHT ABGRERUFENE Rechnungsnummern ermitteln
				$invoice_list= $client->call($session, 
													'sales_order_invoice.list', 
													array(
														array('order_id'=>$order_infos[order_id], 
																'cybersource_token'=>array('eq'=>'')
																)
														)
													);
			} else { // (SHOP_VERSION<1.4
				// Zugehörige NOCH NICHT ABGRERUFENE Rechnungsnummern ermitteln
				$invoice_list= $client->call($session, 
												'sales_order_invoice.list', 
												array(
														array('order_id'=>$order_infos[order_id])
														)
													);
				fwrite($dateihandle,"\nAnzahl aller zugehoringen Rechnungen=".count($invoice_list)."\n");
				$invoice_list_TEMP=$invoice_list;
				$invoice_list = array();
				$temp=0;
				for($x = 0; $x < count($invoice_list_TEMP); $x++) {
							$re_nr=$invoice_list_TEMP[$x]['increment_id'];
							// fwrite($dateihandle,"\n287 - Rechnungsnummer = $re_nr\n");
							$where="invoice_id ='".$re_nr."' AND status <>''";
							// Rechnung bereits abgerufen -> Bestellung nicht mit abrufen
							if (dmc_get_id('id','dmc_invoices',$where)<>'')
								fwrite($dateihandle,"\n295 - Rechnungsnummer wurde bereits abgerufen= $re_nr\n");
							else {
								// Order List neu Aufbaen
								$invoice_list[$temp]=$invoice_list_TEMP[$x];
								$temp++;
								}
				}

				for($x = 0; $x < count($invoice_list); $x++) {
							$re_nr=$invoice_list[$x]['increment_id'];
							// fwrite($dateihandle,"\n305 - Rechnungsnummer = $re_nr\n");
							
				}
			} // end if shop version
			if (count($invoice_list) > 0) {
				$invoices_exists=true;
				$invoice_no = $invoice_list[0]["increment_id"];
				$count_orders=count($invoice_list);
				if (DEBUGGER>=1) fwrite($dateihandle,"\n297 - ANZAHL RECHNUNGEN = ".$count_orders." (Erste =$invoice_no)\n");
			} else {
				$invoices_exists=false;
				$invoice_no = "-";
				$count_orders=1;
			}
		} else {
			$invoices_exists=false;
			$invoice_no = "-";
			$count_orders=1;
		} // end if if (EXPORT_INVOICES)
		
		// Restrictions:  Wenn Abruf Bestellungen oder (Abruf Rechnungen und solche auch vorhanden sind
		if (!EXPORT_INVOICES || (EXPORT_INVOICES && $invoices_exists)) 
		{
			if (DEBUGGER>=1) fwrite($dateihandle,"312 - ANZAHL RECHNUNGEN/BESTELLUNGEN ".$count_orders."\n");
			// Bei mehreren Rechnungen mehrere Order Export
			if (EXPORT_INVOICES)
				foreach($invoice_list as $actual_invoice) {
					if (DEBUGGER>=1) fwrite($dateihandle,"315 - RECHNUNGEN/BESTELLUNGEN \n");
					// Informationen zur Rechnung falls existent
					if (EXPORT_INVOICES && $invoices_exists) {
						if (DEBUGGER>=1) fwrite($dateihandle,"318 RECHNUNGENSNUMMER ".$actual_invoice[increment_id].".\n");
						$invoice_infos = $client->call($session, 'sales_order_invoice.info', $actual_invoice[increment_id]);
						// Werte aus Rechnung fuer Bestellung uebernehmen
						$invoice_no = $actual_invoice["increment_id"];
						$invoice_id = $actual_invoice["invoice_id"];
						$invoice_date = $actual_invoice["created_at"];
						$order_infos[shipping_amount] = $actual_invoice[shipping_amount]+0;
						$order_infos[discount_amount] = $actual_invoice[discount_amount];
						$order_infos[subtotal] = $actual_invoice[subtotal];	// Net
						$order_infos[grand_total] = $actual_invoice[grand_total];	// gros
						$order_infos[tax_amount] = $actual_invoice[tax_amount];
					}
					
					// XML erstellen
					include ('./functions/dmc_xml_order.php');
				} // end for Anzahl der Rechnungen 
			else // Bestellungen exportieren
				include ('./functions/dmc_xml_order.php');
				
		} // endif Restrictions

		
if (COPY_ORDER_IN_DATEBASE) {
			// Mappings
			if ($order_infos[shipping_method]=='flatrate_flatrate')
				$versandart = 'Per LKW';
			else
				$versandart = 'Per LKW';
			
			if ($order_infos[payment][method]=='bankpayment')
				$zahlungsart = '00';
			else
				$zahlungsart = '00';
			
			$Name1=(substr(umlaute_order_export($order_infos[billing_address][company]),0,30));
			
			if ($order_infos[billing_address][firstname]!="" && $order_infos[billing_address][firstname] != "-")
				$Name2 = (substr(umlaute_order_export($order_infos[billing_address][firstname]),0,30))." ".(substr(umlaute_order_export($order_infos[billing_address][lastname]),0,30));
			else 
				$Name2 = (substr(umlaute_order_export($order_infos[billing_address][lastname]),0,30));
		
			// Werte aus WaWi Datenbank ermitteln
			// Neue Auftragsnummer
			$Auftragsnummer2=0;
			$Auftragsnummer = dmc_sql_select_value_db2("MAX(Auftragsnummer)+1", "IBIS_Bestellungen_Kopf", "Auftragsnummer>1");
			$Kundennummer = dmc_sql_select_value_db2("Kundennummer", "kunden", "E_Mail = '".$order_infos[customer_email]."' LIMIT 1");
			
			if ($Kundennummer == "") $Kundennummer = "1000000000";
			
			//$link=dmc_db_connect_db2();
			$doSQL="INSERT INTO IBIS_Bestellungen_Kopf (Uebernommen, Geloescht, Bearbeitet, Res10,Auftragsnummer, Bestelldatum, Kundennummer,Name1,Name2,Adresszusatz, Straße , `Land IATA`, PLZ, Ort , Telefon, Telefax, EMailAdresse, Versandart, Zahlungsart, Kundenrabatt, Passwort, Liefertermin, Mandant) VALUES (0, 0, 0, CURRENT_TIME, '".$Auftragsnummer."', CURRENT_DATE, '".$Kundennummer."', '".$Name1."', '".$Name2."', '', '".($order_infos[billing_address][street])."' , '".$order_infos[billing_address][country_id]."', '". substr(umlaute_order_export(strtoupper ($order_infos[billing_address][postcode])),0,10)."','".substr(umlaute_order_export($order_infos[billing_address][city]),0,30)."' ,'".umlaute_order_export($order_infos[billing_address][telephone])."' ,'".umlaute_order_export($order_infos[billing_address][fax])."' ,'".umlaute_order_export($order_infos[customer_email])."' , '".$versandart."' ,'".$zahlungsart."',0,'k". substr(umlaute_order_export(strtoupper ($order_infos[billing_address][postcode])),0,10)."' , '30.09.2013', '200');";
			fwrite($dateihandle,"AUFTRAG1 $doSQL \n");
			dmc_sql_query_db2($doSQL);
			$kommissionsartikel=false;
			$standardartikel=false;
			// Preufen auf Kommissionsartikel und Standardartikel
			for ($product_no=0;$product_no<=$line_item_id;$product_no++){	
				// Pruefen auf Kommissionsware 
				fwrite($dateihandle,"Produkt SKU: ".$order_infos[items][$product_no][sku]." \n");
				$produkt_lager = dmc_get_product_attribute_value($order_infos[items][$product_no][product_id],'lager');
				if ($produkt_lager=='0002') {
					$kommissionsartikel=true;	// Kommssionsartikel enthalten
					$order_infos[items][$product_no][isKommission]=true;
				} else {
					$standardartikel=true;		// Standardartikel enthalten	
					$order_infos[items][$product_no][isKommission]=false;
				}
			} // end for		
			// Wenn Kommssionsartikel und Standardartikel, zweiten Auftrag anlegen
			if ($kommissionsartikel==true && $standardartikel==true) {
				$Auftragsnummer2=$Auftragsnummer+1; 	// Zweite Azftragsnummer
				$doSQL="INSERT INTO IBIS_Bestellungen_Kopf (Uebernommen, Geloescht, Bearbeitet, Res10,Auftragsnummer, Bestelldatum, Kundennummer,Name1,Name2,Adresszusatz, Straße , `Land IATA`, PLZ, Ort , Telefon, Telefax, EMailAdresse, Versandart, Zahlungsart, Kundenrabatt, Passwort, Liefertermin, Mandant) VALUES (0, 0, 0, CURRENT_TIME, '".$Auftragsnummer2."', CURRENT_DATE, '".$Kundennummer."', '".$Name1."', '".$Name2."', '', '".($order_infos[billing_address][street])."' , '".$order_infos[billing_address][country_id]."', '". substr(umlaute_order_export(strtoupper ($order_infos[billing_address][postcode])),0,10)."','".substr(umlaute_order_export($order_infos[billing_address][city]),0,30)."' ,'".umlaute_order_export($order_infos[billing_address][telephone])."' ,'".umlaute_order_export($order_infos[billing_address][fax])."' ,'".umlaute_order_export($order_infos[customer_email])."' , '".$versandart."' ,'".$zahlungsart."',0,'k". substr(umlaute_order_export(strtoupper ($order_infos[billing_address][postcode])),0,10)."' , '30.09.2013', '200');";
				fwrite($dateihandle,"AUFTRAG2 $doSQL \n");
				dmc_sql_query_db2($doSQL);
			}
			for ($product_no=0;$product_no<=$line_item_id;$product_no++){
				
				$AuftragsnummerTEMP=$Auftragsnummer;
				// Wenn Auftragsnummer2 -> Auftrag2 vorhanden, dann Unterscheidung Std Artikel in Auftrag 1, Komm Artikel in Auftrag 2
				if ($Auftragsnummer2>0) {
					if ($order_infos[items][$product_no][isKommission]==true) {						
						$AuftragsnummerTEMP=$Auftragsnummer2;	// Kommssionsartikel in Auftrag2
					}
				}
				$doSQL="INSERT INTO IBIS_Bestellungen_Artikel (`Auftragsnummer`, `Positionsart`, `Artikelnummer`,`Hersteller`,`Menge`,`Mengeneinheit`, `Einzelpreis`, `Gesamtpreis`,`Beschreibung`,`Beschreibung1`,`Rabattsatz`,`Preiseinheit`,`Volumen`,`Gebinde`,`Katalog`,`Artikelreferenznummer`,`HaendlerEinzelpreis`,`HaendlerGesamtpreis`,`HaendlerRabattsatz`,`Kostenstelle`,`NaturalrabattMenge`,`UrsprungsID`, Authorisierer,`Artikelgruppe`) VALUES (".$AuftragsnummerTEMP.",'A', '".$order_infos[items][$product_no][sku]."', (SELECT IFNULL(Hersteller,'') FROM artikel WHERE Artikel = '".$order_infos[items][$product_no][sku]."'), '".$order_infos[items][$product_no][qty_ordered]."', (SELECT IFNULL(Mengeneinheit,'') FROM artikel WHERE Artikel = '".$order_infos[items][$product_no][sku]."'), ".$order_infos[items][$product_no][base_price].", ".$order_infos[items][$product_no][row_total].",(SELECT IFNULL(Bezeichnung1,'".$order_infos[items][$product_no][name]."') FROM artikel WHERE Artikel = '".$order_infos[items][$product_no][sku]."'),(SELECT IFNULL(Bezeichnung2,'') FROM artikel WHERE Artikel = '".$order_infos[items][$product_no][sku]."'), 0, 0, 0, 1,'6500000000','".$order_infos[items][$product_no][sku]."',0,0,0,'',0,0,0, (SELECT IFNULL(Artikelgruppe,'') FROM artikel WHERE Artikel = '".$order_infos[items][$product_no][sku]."'));";
				//echo $doSQL."<br/>";
				dmc_sql_query_db2($doSQL);
				fwrite($dateihandle,"Produkt: $doSQL \n");
			} // end for
			
			dmc_db_disconnect($link);
			
		}


		} // end for order list
		} // end for Abschnitt Bestellungen in 10er Schritten 
		
		// $schema .=	'</ORDER_LIST>' . "\n";		
		
		// Print XML
			if (!COPY_ORDER_IN_DATEBASE) echo $schema;
		// Print Zusatzbestellung 
			if (!COPY_ORDER_IN_DATEBASE) echo $schema2;
			
		
	/*	if (BACKUP_ORDERS)
			{
				// Bestellung in Text XML Datei
				$dateiname='./backup/bestellungen'.date("YmdHis").".xml";	
				$dateihandle3 = fopen($dateiname,"w");
				fwrite($dateihandle3, $schema);		
				fclose($dateihandle3);				
			}
		if (ORDER_COPY_EMAIL)  {
			// Status eMails senden	
			$sender = ORDER_COPY_EMAIL_FROM;
			$empfaenger = ORDER_COPY_EMAIL_TO;
			$betreff = "Abgerufene Bestellungen vom ".date("YmdHis");
			$mailtext = "Bestellabruf von Bestellnummern".$erste_id." bis ".$last_order_id."\n\nInhalte:".$schema;
			mail($empfaenger, $betreff, $mailtext, "From: $sender "); 
		} // end if ORDER_COPY_EMAIL
				*/
	 // Close the session 
	      $client->endSession($session);
	// Keine Export Aktion ausgewählt oder Zugriff verweigert
	} else if (!$zugriff){
		// Zugriff verweigert
		echo "Zugriff verweigert \n access denied";
	} else {
		// Keine Export Aktion ausgewählt
		echo "Keine Aktion ausgewaehlt \n Modus not available";
	}
	
?>