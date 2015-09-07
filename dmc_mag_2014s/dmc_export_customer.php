<?php
/********************************************************************************************
*                                                                                			*
*  dmc_export_CUSTOMER for magento shop														*
*  Copyright (C) 2010 DoubleM-GmbH.de														*
*                                                                                          	*
*         31.08.10  Release																	*
*         08.10.13  Update																	*								********************************************************************************************/

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

function dmc_export_customer($client, $session) {

	//$Magento_Export_Filter= array();
	if (defined('SET_TIME_LIMIT')) { @set_time_limit(0);}
  
	if (DEBUGGER>=1) 	{
		$daten = "\n***********************************************************************\n";
		$daten .= "******************* dmconnector export CUSTOMER".date("YmdHis")." ****\n";
		$daten .= "************************************************************************\n";
		if (LOG_ROTATION=='size' && is_numeric(LOG_ROTATION_VALUE))
			if ((filesize(LOG_FILE_EXPORT)/1048576)>LOG_ROTATION_VALUE) 
				$dateihandle = fopen(LOG_FILE_EXPORT,"w"); // LOG File erstellen
			else
				$dateihandle = fopen(LOG_FILE_EXPORT,"a");
		else
				$dateihandle = fopen(LOG_FILE_EXPORT,"a");
		fwrite($dateihandle, $daten);				
	}
	
		
	// Schrittfolge
	$STARTZEIT = time();
	
	$schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
	$schema .= '<CUSTOMERS>' . "\n";
				'<EXPORT_HEADER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<GENERATOR_INFO>' . "dmconnector".'</GENERATOR_INFO>' . "\n".
						'<GENERATOR_VERSION></GENERATOR_VERSION>' . "\n".
						// todo 	aktuelles datum
						'<GENERATION_DATE></GENERATION_DATE>' . "\n".	
						'<EXPORT_STARTED>' .date("d.m.Y H:i:s") .'</EXPORT_STARTED>'. "\n".					
					'</CONTROL_INFO>' . "\n".
				'</EXPORT_HEADER>' . "\n";
		
	include '../app/Mage.php';
	
	Mage::app();

	$model = Mage::getSingleton('customer/customer');

	$result = $model->getCollection()
        ->addAttributeToSelect('*')
    //    ->addAttributeToFilter('firstname', array('like' => "%Robin%"));
		->addAttributeToFilter('group_id', array('eq' => "4"));	// hier 4 = neukunde
	
	$schema  .= '<EXPORT_DATA>' . "\n";
	
	foreach($result as $r) 
	{       
        $customer = $model->load($r->getId());
		$schema  .= '<EXPORT_DATA_SET>' . "\n" .
						'<CUSTOMER>' . "\n" .
							 '<CUSTOMER_ID>' .  $customer->getEntityId() . '</CUSTOMER_ID>' . "\n" .
							 '<CUSTOMER_INCREMENT_ID>'. $customer->getIncrementId() . '</CUSTOMER_INCREMENT_ID>' . "\n" .
							 '<CUSTOMER_PREFIX>' . $customer->getPrefix() . '</CUSTOMER_PREFIX>' . "\n" .
							 '<CUSTOMER_NAME>' . $customer->getFirstname() . '</CUSTOMER_NAME>' . "\n" .
							 '<CUSTOMER_NAME2>' . $customer->getLastname() . '</CUSTOMER_NAME2>' . "\n" .
						//	 '<CUSTOMER_NAME3>'.  '</CUSTOMER_NAME3>' . "\n" .
							 '<CUSTOMER_DOB>'  . '</CUSTOMER_DOB>' . "\n" .
							 '<CUSTOMER_EMAIL>' . $customer->getEmail() . '</CUSTOMER_EMAIL>' . "\n" .
							 '<CUSTOMER_GROUP>' . $customer->getData('group_id'). '</CUSTOMER_GROUP>' . "\n" .
							 '<CUSTOMER_VAT>' . $customer->getTaxvat() . '</CUSTOMER_VAT>' . "\n".
							 '<CUSTOMER_WEBSITE>' . $customer->getWebsiteId() . '</CUSTOMER_WEBSITE>' . "\n".
							 '<CUSTOMER_STORE>' . $customer->getStoreId() . '</CUSTOMER_STORE>' . "\n".
							 '<CUSTOMER_ACTIVE>' . $customer->getIsActive() . '</CUSTOMER_ACTIVE>' . "\n".
							 '<CUSTOMER_PDF>' . $customer->getPdf() . '</CUSTOMER_PDF>' . "\n".
							 '<CUSTOMER_BILLING_ADDRESS_ID>' . $customer->getDefaultBilling() . '</CUSTOMER_BILLING_ADDRESS_ID>' . "\n".
							 '<CUSTOMER_SHIPPING_ADDRESS_ID>' . $customer->getDefaultShipping() . '</CUSTOMER_SHIPPING_ADDRESS_ID>' . "\n".
						'</CUSTOMER>' . "\n" ;
						
		$customerAddressId=$customer->getDefaultBilling();
		if ($customerAddressId){
			$address = Mage::getModel('customer/address')->load($customerAddressId);
			//var_dump($address);
			$schema  .= 	'<CUSTOMER_ADDRESS>' . "\n" .
							 '<CUSTOMER_ADDRESS_ID>'    . $customerAddressId . '</CUSTOMER_ADDRESS_ID>' . "\n" .
							 '<CUSTOMER_ADDRESS_PREFIX>'. $address->getPrefix() . '</CUSTOMER_ADDRESS_PREFIX>' . "\n" .
							 '<CUSTOMER_ADDRESS_NAME>'  . $address->getFirstname() . '</CUSTOMER_ADDRESS_NAME>' . "\n" .
							 '<CUSTOMER_ADDRESS_NAME2>' . $address->getLastname() . '</CUSTOMER_ADDRESS_NAME2>' . "\n" .
							 '<CUSTOMER_ADDRESS_NAME3>' . $address->getCompany() . '</CUSTOMER_ADDRESS_NAME3>' . "\n" .
							 '<CUSTOMER_ADDRESS_STREET>' . $address->getData('street') . '</CUSTOMER_ADDRESS_STREET>' . "\n" .
							 '<CUSTOMER_ADDRESS_ZIP>' . $address->getPostcode() . '</CUSTOMER_ADDRESS_ZIP>' . "\n" .
							 '<CUSTOMER_ADDRESS_CITY>' . $address->getCity() . '</CUSTOMER_ADDRESS_CITY>' . "\n" .
							 '<CUSTOMER_ADDRESS_COUNTRY_CODE>' . $address->getCountryId() . '</CUSTOMER_ADDRESS_COUNTRY_CODE>' . "\n" .
							 '<CUSTOMER_ADDRESS_REGION>' . $address->getRegion() . '</CUSTOMER_ADDRESS_REGION>' . "\n" .
							 '<CUSTOMER_ADDRESS_TELEPHONE>' . $address->getTelephone() . '</CUSTOMER_ADDRESS_TELEPHONE>' . "\n" .
							 '<CUSTOMER_ADDRESS_EMAIL>' . /*$address->getTelephone() . */'</CUSTOMER_ADDRESS_EMAIL>' . "\n" .
							 
						'</CUSTOMER_ADDRESS>' . "\n"; 
		} // End if Address
   						 
       $schema  .= '</EXPORT_DATA_SET>' . "\n";
	}
	$schema  .= '</EXPORT_DATA>' . "\n";
	$schema .= '<EXPORT_FOOTER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<EXPORT_FINISHED>' .date("d.m.Y H:i:s") .'</EXPORT_FINISHED>'."\n" .
						'<EXPORT_DURATION>' . (time() - $STARTZEIT) .' seconds</EXPORT_DURATION>'."\n" .						
					'</CONTROL_INFO>' . "\n".
				'</EXPORT_FOOTER>' . "\n\n";
	$schema .= '</CUSTOMERS>' . "\n";
        
	echo umlaute_order_export($schema);
	$client->endSession($session);
} // end function dmc_export_products
?>