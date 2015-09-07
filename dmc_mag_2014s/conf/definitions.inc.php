<?php
/************************************************************************************************
*                                                         										*
*  dmConnector Definitionen for shop															*
*  Copyright (C) 2012 DoubleM-GmbH.de															*
*                                                                           					*
* 24.7.2013 - Zusaetzlicher Bereich fuer Definitionen für Status wie Beginn Artikelabgleich 	*
*************************************************************************************************/
	
	define('SHOPSYSTEM','Magento'); 		

	$files = array ( 'SOAP_CLIENT.dmc', 'DB_SERVER.dmc', 'DATABASE.dmc','DB_USER.dmc','DB_PWD.dmc','DB_TABLE_PREFIX.dmc',
				 'CAT_ROOT.dmc', 'GENERATE_CAT_ID.dmc', 'CAT_DEVIDER.dmc', 'SHOP_VERSION.dmc', 'ATTRIBUTE_SET.dmc',
				 'MAIN_PRICE_ATTRIBUTE_ID.dmc','STD_CUSTOMER_GROUP.dmc', 'STD_ART_SET_GROUP.dmc', 'SPECIAL_PRICE_CATEGORY.dmc',
				 'STORE_ID.dmc', 'PRODUCTS_EXTRA_PIC_EXTENSION.dmc','ATTACH_IMAGES.dmc','UPDATE_IMAGES.dmc',  'STD_CUSTOMER_WEBSITE.dmc',
				 'STD_CUSTOMER_STORE.dmc', 'STD_CUSTOMER_STORE_VIEW.dmc'
				/*
				'SHOPSYSTEM.dmc', 'SHOPSYSTEM_VERSION.dmc','WAWI.dmc', 'DMC_FOLDER.dmc', 'CHARSET.dmc','PRODUCT_TEMPLATE.dmc',
				'OPTIONS_TEMPLATE.dmc',  'KATEGORIE_TRENNER.dmc','STANDARD_CAT_ID.dmc', 
				'UPDATE_ORDER_STATUS_ERP.dmc', 'NEW_ORDER_STATUS_ERP.dmc','NEW_ORDER_STATUS_FAILED.dmc','NOTIFY_CUSTOMER_ERP.dmc',
				'GM_OPTIONS_TEMPLATE.dmc', 'LISTING_TEMPLATE.dmc','PRODUCTS_SORTING.dmc','PRODUCTS_SORTING2.dmc',
				'CATEGORIES_TEMPLATE.dmc', 'GM_SITEMAP_ENTRY.dmc','GM_SHOW_weight.dmc','GM_SHOW_QTY_INFO.dmc',
				 'GROUP_PERMISSION_0.dmc','GROUP_PERMISSION_1.dmc','GROUP_PERMISSION_2.dmc',
				'GROUP_PERMISSION_3.dmc', 'GROUP_PERMISSION_4.dmc','GROUP_PERMISSION_5.dmc','GROUP_PERMISSION_6.dmc',
				'GROUP_PERMISSION_7.dmc', 'GROUP_PERMISSION_8.dmc','GROUP_PERMISSION_9.dmc','GROUP_PERMISSION_10.dmc',
				'FSK18.dmc', 'UPDATE_DESC.dmc','UPDATE_PROD_TO_CAT.dmc','UPDATE_CATEGORY.dmc','UPDATE_CATEGORY_DESC.dmc',
				'DELETE_INACTIVE_PRODUCT.dmc','SONDERZEICHEN.dmc','SOAP_CLIENT.dmc', 
				 'MAX_CAT.dmc','STORE_ID_EXPORT.dmc',
					'WEBSITE_ID.dmc', 'ORDER_STATUS.dmc','ORDER_STATUS2.dmc','UPDATE_ORDER_STATUS.dmc','STANDARD_QUANTITY.dmc', 'ORDER_SHOP_IDS'*/
				);

		// Definitionen einlesen
		for ( $i = 0; $i < count ( $files  ); $i++ ) {
				$defName = substr($files[$i],0,-4);
				$dateihandle = fopen("./conf/definitions/".$files[$i],"r");
				$defValue = fread($dateihandle, 100);
				// echo"$defName=$defValue<br>";
				define($defName , trim($defValue));
				fclose($dateihandle);
		} // end for

		// Kundengruppenpreise		
		$files_prices = array ( 'CUST_PRICE_GROUP1.dmc','CUST_PRICE_GROUP2.dmc','CUST_PRICE_GROUP3.dmc','CUST_PRICE_GROUP4.dmc'
						/*'TABLE_PRICE1.dmc', 'GROUP_PRICE1.dmc','TABLE_PRICE2.dmc', 'GROUP_PRICE2.dmc', 'TABLE_PRICE3.dmc', 'GROUP_PRICE3.dmc',
						'TABLE_PRICE4.dmc', 'GROUP_PRICE4.dmc','TABLE_PRICE5.dmc', 'GROUP_PRICE5.dmc', 'TABLE_PRICE6.dmc', 'GROUP_PRICE6.dmc',
						'TABLE_PRICE7.dmc', 'GROUP_PRICE7.dmc','TABLE_PRICE8.dmc', 'GROUP_PRICE8.dmc', 'TABLE_PRICE9.dmc', 'GROUP_PRICE9.dmc',
						'TABLE_PRICE10.dmc', 'GROUP_PRICE10.dmc' */ 
					);

		// Definitionen einlesen
		for ( $i = 0; $i < count ( $files_prices  ); $i++ ) {
				$defName = substr($files_prices[$i],0,-4);
				$dateihandle = fopen("./conf/definitions/".$files_prices[$i],"r");
				$defValue = fread($dateihandle, 100);
				
				// echo"$defName=$defValue<br>";
				define($defName , trim($defValue));
				fclose($dateihandle);
		} // end for
						
		// Log und Debug		
		$files_debug = array ( 'DEBUGGER.dmc', 'LOG_FILE.dmc', 'IMAGE_LOG_FILE.dmc','PRINT_POST.dmc','LOG_ROTATION.dmc', 'LOG_ROTATION_VALUE.dmc'
					/*'LOG_DATEI.dmc',   */
					);
				
		// Definitionen einlesen
		for ( $i = 0; $i < count ( $files_debug  ); $i++ ) {
				$defName = substr($files_debug[$i],0,-4);
				$dateihandle = fopen("./conf/definitions/".$files_debug[$i],"r");
				$defValue = fread($dateihandle, 100);
				// echo"$defName=$defValue<br>";
				define($defName , trim($defValue));
				fclose($dateihandle);
		} // end for

		// Spezielle Statusoperationen
		$files_status = array ( 'STATUS_WRITE_ART_BEGIN_DETELE_ART.dmc', 'STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART.dmc', 'STATUS_WRITE_ART_BEGIN_DETELE_ART_VARIANTS.dmc', 'STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART_VARIANTS.dmc'); //, 'STATUS_WRITE_ART_DETAILS_BEGIN.dmc','STATUS_WRITE_ART_END.dmc', 'STATUS_WRITE_ART_DETAILS_END.dmc');
				
		// Definitionen einlesen
		for ( $i = 0; $i < count ( $files_status  ); $i++ ) {
				$defName = substr($files_status[$i],0,-4);
				$dateihandle = fopen("./conf/definitions/".$files_status[$i],"r");
				$defValue = fread($dateihandle, 100);
				// echo"$defName=$defValue<br>";
				define($defName , trim($defValue));
				fclose($dateihandle);
		} // end for
		
// SONDERFUNKTIONEN 
// mappings durchführen
define('MAP_MANUFACTURER',false); 	// Farben Mappen
define('MAP_PRODUCT_GROUPS',false); 	// Farben Mappen
define('MAP_COLORS',false); 			// Produktgruppen Mappen
define('MAP_PRODUCT_GROUPS_BY_ARTNR',false); 	// Farben Mappen
define('MAP_PRODUCT_COLORS_BY_ARTNR',false); 	// Farben Mappen
// SONDERFUNKTIONEN extend products )(automatische generierung von conf und simples aus einem artikelsatz durchführen
define('EXTENDED_PRODUCTS',false); 	// function dmc_write_extended.php verwenden
define('EXTENDED_PRODUCTS_SIZE',false); 	// function dmc_write_extended.php verwenden
define('ATTR_GROESSEN','size'); 			// attributbezeichnung fuer groessen		
define('ATTR_FARBEN','color@manufacturer_color'); 			// attributbezeichnung fuer farben		
// SONDERFUNKTIONEN Rundungen durchführen
define('ROUND_PRICES',false); 	// Übergebene Preise hinter dem Kommata ändern
define('PRICE_END','99'); 		// Preis hinter Nachkommastelle, z.B 99 für 33,99 Euro	
// SONDERFUNKTIONEN IDs erforderlich fuer Fremdsprachenmodul 
define('ATTR_ID_CATEGORY_NAME',33);    // ID der Category Bezeichnung in Fremdsprache in catalog_category_entity_varchar???
// Nur bei Bedarf ändern
define ('IMAGE_FOLDER', './upload_images/');	
// Nur für Doumentenmanagement verwendet
define ('PDF_FOLDER', '../media/pdf/');	
?>
