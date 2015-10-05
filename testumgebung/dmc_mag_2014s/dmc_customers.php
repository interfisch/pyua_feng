<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector  for magento shop												*
*  dmc_prices.php														*
*  Preisfunktionen														*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/

defined( 'VALID_DMC' ) || die( 'Direct Access to this location is not allowed.' );

// include('dmc_db_functions.php');

	function dmc_set_customer($StoreView='default', $client, $sessionId) {
		global $dateihandle;
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_customers Session=".$sessionId." with customer name:".$_POST['customers_lastname']." ->$customers_id \n");
	
		// Post ermitteln		  
		$ExportModus = $_POST['ExportModus'];
		$customers_id = $_POST['customers_id'];		// Wenn = 'delete', dann loeschen
		
		// Wenn in customers_id @ enthalten ist hinter dem @ die Kundengruppe
		// $website, $customers_group_id, $customers_id = customers_site_group_id
		// ID enthält auch Website und Kundengruppe		  
		$customers_group_id='';
		if (preg_match('/@/', $customers_id)) {
			// Infos zum Kunden
			// list ($website, $customers_group_id, $customers_id, $store_id, $store_view_id) = split ("@", $customers_id);
			$werte= explode ( "@", $customers_id);
			$website=$werte[0];
			$customers_group_id=$werte[1];
			$customers_id=$werte[2];
			$store_id=$werte[3];
			$store_view_id=$werte[4];			
			//$shipping_code=
			
			// Kundengruppen Mappen
			if ($customers_group_id=='') { 
				$customers_group_id=STD_CUSTOMER_GROUP;
			} else {
				// ggfls mapping
				$customers_group_id= dmc_map_customer_group($customers_group_id);
			}
			// Mapping der Sprachen STORE VIEW
			if ($store_id == 'D')
				$store_id='Deutscher Shop'; // $store_id='German';
			if ($store_id == 'F')
				$store_id='French';
			if ($_POST['customers_countries_iso_code'] == 'AT')
				$store_id='AT Shop';
			if ($store_id == 'E')
				$store_id='English Store';			
		} else {
			$website = STD_CUSTOMER_WEBSITE;
			$customers_group_id = STD_CUSTOMER_GROUP;
			$store_id = '';
		}
		if ($customers_group_id==0) $customers_group_id=1;
		
		if ($customers_group_id=='') $customers_group_id=STD_CUSTOMER_GROUP;
		 
		if (DEBUGGER>=1) fwrite($dateihandle, "Shop Kundengruppe=".$customers_group_id." in website=".$website." (Übergeben:".$_POST['customers_id'].")\n");
			
		 $customers_gender = $_POST['customers_gender'];
		if ($customers_gender=="null" || $customers_gender == null ) $customers_gender ='';
/*		if ($customers_gender == 'f' || $customers_gender = 'Frau')
			$customers_gender = 2;
		else 
			$customers_gender =1; */
		$customers_firstname = $_POST['customers_firstname'];
		if ($customers_firstname=="null" || $customers_firstname == null || $customers_firstname == '' ) $customers_firstname ='-';
		$customers_lastname = $_POST['customers_lastname'];
		if ($customers_lastname=="null" || $customers_lastname == null || $customers_lastname == '' ) $customers_lastname ='-';
		$customers_dob = $_POST['customers_dob'];
			if ($customers_dob=="null" || $customers_dob == null ) $customers_dob ='1';  		// WIRD ALS AKTIV verwendet		 
		$customers_email_address = $_POST['customers_email_address'];
		if ($customers_email_address=="null" || $customers_email_address == null ) $customers_email_address ='';  
		$customers_telephone = $_POST['customers_telephone'];
		if ($customers_telephone=="null" || $customers_telephone == null || $customers_telephone == '' ) $customers_telephone ='-';  
		$customers_fax = $_POST['customers_fax'];
		if ($customers_fax=="null" || $customers_fax == null ) $customers_fax ='';  
		$customers_date_account_created = $_POST['customers_date_account_created'];
		if ($customers_date_account_created=="null" || $customers_date_account_created == null ) $customers_date_account_created =''; 
		$customers_date_account_created = $_POST['customers_date_account_created'];
		if ($customers_date_account_created=="null" || $customers_date_account_created == null ) $customers_date_account_created ='';
		// Wenn kein Datum übergeben wurde, dann ist es eine Kundengruppe
		/*$pos = strpos ( $customers_date_account_created, '.' );
		if ($pos===false && $customers_group_id=='') {
			$customers_group_id=STD_CUSTOMER_GROUP;
		} else {
			$customers_group_id=$customers_date_account_created;
		}*/
					
		$customers_company = $_POST['customers_company'];
		if ($customers_company=="null" || $customers_company == null ) $customers_company ='';  
		$customers_street_address = $_POST['customers_street_address'];
		if ($customers_street_address=="null" || $customers_street_address == null ) $customers_street_address ='';	
		$customers_postcode = $_POST['customers_postcode'];
		if ($customers_postcode=="null" || $customers_postcode == null ) $customers_postcode ='';  
		$customers_city = $_POST['customers_city'];
		if ($customers_city=="null" || $customers_city == null ) $customers_city ='';  
		$customers_countries_iso_code = $_POST['customers_countries_iso_code'];
		if ($customers_countries_iso_code=="null" || $customers_countries_iso_code == null ) $customers_countries_iso_code ='DE';  
		$customers_password = $_POST['customers_password'];
		if ($customers_password=="null" || $customers_password == null ) $customers_password ='RCM30419';  
		if (substr($customers_password,0,2)=="%%")  $customers_password = substr($customers_password,2) ;
		if (DEBUGGER>=1) fwrite($dateihandle, "109 mit customer_group_id=".$customers_group_id."\n");
		
		// Wenn Kundengruppe nicht numerisch, dann keine ID -> ermitteln
			if (is_numeric($customers_group_id)==false) {
				$customer_group_name = $customers_group_id;
				$customers_group_id=dmc_customer_group_exists($customer_group_name);
				// Standard Magento tax_class_id
				$tax_class_id=3;
				if ($customers_group_id==-1) {
					//$group_id=dmc_customer_group_create($customer_group, $tax_class_id);
					$customer_group=Mage::getModel('customer/group');
					$customer_group->setCode($customer_group_name);
					$customer_group->setTaxClassId($tax_class_id);
					$customer_group->save();
					//  Mage::getSingleton('customer/group')->setData( 'customer_group_code' => $customer_group, 'tax_class_id' => $tax_class_id )->save(); 
					$customers_group_id=dmc_customer_group_exists($customer_group_name);
				}
			}
			
		// VORLAGE ZUR MULTISELECT VERARBEITUNG
		// Pruefe ob Kunde existiert und Ermittlung der bestehenden NAV Objekte (multiselect -> Felder durch Komma getrennt.)
		/*$newCustomerId=dmc_get_id_by_email($customers_email_address);	
		if ($newCustomerId!="") {
			$exists=1;
			$kundeninfo = $client->call($sessionId, 'customer.info', $customers_id);
			$q8y_navobjekte=$kundeninfo['q8y_navobjekt'];	// zb. 217,219
			// Wenn uebermitteltes Objekt noch nicht zugeordnet, wann ergaenzen.
			if ($strpos ($q8y_navobjekte, $customers_firstname )===false)
			{
				$q8y_navobjekte = $q8y_navobjekte.",".$customers_firstname;
			}		
		} else {
			$exists=0;
			$q8y_navobjekte=$customers_firstname;
		}
		// ENDE MULTISELECT
		*/
		try {		  
			// Wenn KundenID = 'delete' und existiert, dann den kunden loeschen
			if ($customers_dob == 'delete') {
				// loeschen, wenn existiert
				$newCustomerId=dmc_get_id_by_email($customers_email_address);	
				if ($newCustomerId !="" ) {
					$result = $client->call($sessionId, 'customer.delete', $newCustomerId);
					if (DEBUGGER>=1) fwrite($dateihandle,'Kunde ID '.$newCustomerId.'geloescht\n'.$e);
				}
			} else {
				// Kunde eintragen
				$newCustomer = array(
					// 'customer_id' =>  $customers_id,
					'increment_id'  => $customers_id,			// Kundennummer
					'firstname'  => $customers_firstname,
					'lastname'   => $customers_lastname,
					'email'      => $customers_email_address,
					// siehe unten    'password_hash'   => md5($customers_password),
					//	'updated_at' => 'now()',
					//	'created_at' => 'now()',
					// password hash can be either regular || salted md5:
					// $hash = md5($password);
					// $hash = md5($salt.$password).':'.$salt;
					// both variants are valid
					'prefix'		=> $customers_gender,
					'suffix' => $customers_id,
					'group_id'=> $customers_group_id,
					'dob' => '28.02.1973',
					/*	'default_billing'
					'default_shipping'
					'taxvat'
					'confirmation'*/
					// 'created_in' => 'german',
					
				);
						
				
				if (DEBUGGER>=1) fwrite($dateihandle, "Customer Name ".$newCustomer['lastname']." (".$customers_email_address.")\n");
					
				// Kunde  anlegen, wenn noch nicht existiert 
				// get Magento customer ID 
				$newCustomerId=dmc_get_id_by_email($customers_email_address);	
					
				if ($newCustomerId!="") {
					if (DEBUGGER>=1) fwrite($dateihandle, "UPDATE Customer  Name-".$newCustomer['lastname']." mit ID ".$newCustomerId." in Kundengruppe ".$newCustomer['group_id']." \n");
					$newCustomer['password_hash'] = md5($customers_password);
					$client->call($sessionId, 'customer.update', array($newCustomerId, $newCustomer));
				} else { // create new
					// Werte nur für neue Kunden
					// Unterstuetzung von StoreViews (z.B. fuer Fremdsprachen de en fr)
					if ($website!="")
						$newCustomer['website_id'] = $website;
					else 
						$newCustomer['website_id'] = 1;
					if ($store_id!="")
						$newCustomer['store_id'] = $store_id;
					else 
						$newCustomer['store_id'] = 1;
					if ($store_view_id!="")
						$newCustomer['created_in'] = $store_view_id;
					else 
						$newCustomer['created_in'] = 1;
				
					$newCustomer['password_hash'] = md5($customers_password);
					if (DEBUGGER>=1) fwrite($dateihandle, "NEW Customer  Name-".$newCustomer['lastname']." in Kundengruppe ".$newCustomer['group_id']."\n");
						$newCustomerId = $client->call($sessionId, 'customer.create', array($newCustomer));
				}	
					
				// Kundenadresse eintragem
				//  ["region"]=> string(13) "Niedersachsen" ["region_id"]=> string(2) "79"
				$newCustomerAdress = array(
											// "customer_id" =>  $customers_id,
											'increment_id'  => $customers_id,			// Kundennummer
											'firstname'  	=> $customers_firstname,
											'lastname'   	=> $customers_lastname,
										//	'updated_at'	=> 'now()',
										//	'created_at' 	=> 'now()',
											'prefix'		=> $customers_gender,
											// 'suffix'
											'group_id'=> $customers_group_id,
											// 'dob'
											// 'taxvat'
											// 'confirmation'
											'company'   	=> $customers_company,
											'street'   		=> $customers_street_address,
											'city'   		=> $customers_city,
											'country_id' 	=> $customers_countries_iso_code,
											'region'  		=> "Niedersachsen",
											 'region_id'	=> 79,
											'postcode'   	=> $customers_postcode,
											'telephone'   	=> $customers_telephone,
											'fax'			=> $customers_fax,	
											'dob' => '28.02.1973',
										 // 'store_id'   => 1,			// todo
											// 'website_id' => 1,
											'website_id' => $website,
											'is_default_billing'  => true,
											'is_default_shipping' => true
					);
				// Prüfe, ob Kundenadresse bereits existent
				$newAddressId = dmc_get_adressid_by_cust_id($customers_id);
				if ($newAddressId!="") {
					//Update customer address
					if (DEBUGGER>=1) fwrite($dateihandle, "DONT UPDATE Customer Adress Name: ".$newCustomerAdress['lastname']."\n");
					//$client->call($sessionId, 'customer_address.update', array($newAddressId, $newCustomerAdress));
				} else {
					if (DEBUGGER>=1) fwrite($dateihandle, "NEW Customer Adress Name: ".$newCustomerAdress['lastname']."\n");
					$newAddressId = $client->call($sessionId, 'customer_address.create', array($newCustomerId, $newCustomerAdress));	
				}// Get new customer info
				// var_dump($proxy->call($sessionId, 'customer.info', $newCustomerId));
										 
				if (DEBUGGER>=1) fwrite($dateihandle, "Customer set with success. ID -> ".$newCustomerId." and AdressID= ".$newAddressId."\n");
			} // end if anlegen / loeschen
		} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle,'Set customer failed: '.$newCustomerId.'\n'.$e);		    
		}
		 
	} // end function
	
	function dmc_update_customer($StoreView='default', $client, $sessionId) {
		$debugger=1;
		if (DEBUGGER>=1) {
			$dateiname="./dmconnector_log_magento.txt";	
			$dateihandle = fopen($dateiname,"a");			
			if (DEBUGGER>=1) fwrite($dateihandle, "dmc_set_customers Session=".$sessionId."\n");
		}
		// Post ermitteln	
		$customers_group_id='';		
		$ExportModus = $_POST['ExportModus'];
		  $customers_id = (integer)($_POST['customers_id']);	
				// Wenn in customers_id @ enthalten ist hinter dem @ die Kundengruppe
			$pos = strpos ( $customers_id, '@' );
			if ($pos===false) $customers_group_id=STD_CUSTOMER_GROUP;
			else {
				$temp = explode ( '@', $customers_id);
				$customers_id=$temp[0];
				$customers_group_id=$temp[1];
				if ($customers_group_id=='') $customers_group_id=STD_CUSTOMER_GROUP;
			}
			if (DEBUGGER>=1) fwrite($dateihandle, "KundenID=".$customers_id."\n");
			if (DEBUGGER>=1) fwrite($dateihandle, "Kundengruppe=".$customers_group_id."\n");
			
		  $customers_gender = $_POST['customers_gender'];
			if ($customers_gender=="null" || $customers_gender == null ) $customers_gender ='';
		  $customers_firstname = $_POST['customers_firstname'];
			if ($customers_firstname=="null" || $customers_firstname == null ) $customers_firstname ='';
		  $customers_lastname = $_POST['customers_lastname'];
			if ($customers_lastname=="null" || $customers_lastname == null ) $customers_lastname ='';
		  $customers_dob = $_POST['customers_dob'];
			if ($customers_dob=="null" || $customers_dob == null ) $customers_dob ='1';  		// WIRD ALS AKTIV verwendet
		  $customers_email_address = $_POST['customers_email_address'];
			if ($customers_email_address=="null" || $customers_email_address == null ) $customers_email_address ='';  
		  $customers_telephone = $_POST['customers_telephone'];
			if ($customers_telephone=="null" || $customers_telephone == null ) $customers_telephone ='';  
		  $customers_fax = $_POST['customers_fax'];
			if ($customers_fax=="null" || $customers_fax == null ) $customers_fax ='';  
		  $customers_date_account_created = $_POST['customers_date_account_created'];
			if ($customers_date_account_created=="null" || $customers_date_account_created == null ) $customers_date_account_created ='';
			// Wenn kein Datum übergeben wurde, dann ist es eine Kundengruppe
			$pos = strpos ( $customers_date_account_created, '.' );
			if ($pos===false && $customers_group_id=='') $customers_group_id=STD_CUSTOMER_GROUP;
			else {
				 $customers_group_id=$customers_date_account_created;
			}
		  $customers_company = $_POST['customers_company'];
			if ($customers_company=="null" || $customers_company == null ) $customers_company ='';  
		  $customers_street_address = $_POST['customers_street_address'];
			if ($customers_street_address=="null" || $customers_street_address == null ) $customers_street_address ='';	
		  $customers_postcode = $_POST['customers_postcode'];
			if ($customers_postcode=="null" || $customers_postcode == null ) $customers_postcode ='';  
		  $customers_city = $_POST['customers_city'];
			if ($customers_city=="null" || $customers_city == null ) $customers_city ='';  
		  $customers_countries_iso_code = $_POST['customers_countries_iso_code'];
			if ($customers_countries_iso_code=="null" || $customers_countries_iso_code == null ) $customers_countries_iso_code ='';  
		  $customers_password = $_POST['customers_password'];
			if ($customers_password=="null" || $customers_password == null ) $customers_password ='RCM30419';  

		try {		    
				$newCustomer = array(
										"customer_id" =>  $customers_id,
									    'firstname'  => $customers_firstname,
									    'lastname'   => $customers_lastname,
									    'email'      => $customers_email_address,
									    'password_hash'   => md5($customers_password),
										'group_id'=> $customers_group_id,
									    // password hash can be either regular || salted md5:
									    // $hash = md5($password);
									    // $hash = md5($salt.$password).':'.$salt;
									    // both variants are valid
									    'store_id'   => 1,			// todo
									    'website_id' => 1
										

				);
				
				// Unterstuetzung von StoreViews (z.B. fuer Fremdsprachen de en fr)
				if ($store_id != '')
					$newCustomer['created_in'] = $store_id; // StoreView
							 
				$newCustomerId = $client->call($sessionId, 'customer.update', array($newCustomer));
														 
				// Get new customer info
				// var_dump($proxy->call($sessionId, 'customer.info', $newCustomerId));
									 
				if (DEBUGGER>=1) fwrite($dateihandle, "Customer updated with success. No -> ".$newCustomerId." \n");
		} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle,'Update customer failed:\n'.$e);		    
		}
		
	} // end function

	
?>
	
	