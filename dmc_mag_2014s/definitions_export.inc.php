<?php
/*******************************************************************************************
*                                                                                          									*
*  dm.connector export for magento shop											*
*  Copyright (C) 2008 DoubleM-GmbH.de											*
*                                                                                          									*
*******************************************************************************************/
defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

// Bitte geben Sie hier den Order Status von den abzurufenden Bestellungen aus  Magento Stores ein
define('ORDER_STATUS','processing');			
define('GET_ORDERS_FROM','2014-06-20');

define('USE_ORDER_ID',true);	// Datei order_id.txt beschreiben?
// Export Rechnungen an Stelle von Bestellungen
define('EXPORT_INVOICES',false);		// Rechnungen an Stelle von Bestellungen exportieren? true/false	
// Bitte geben Sie hier ggfls einen zweiten Order Status von den abzurufenden Bestellungen aus  Magento Stores ein
define('ORDER_STATUS2','');			
define('UPDATE_ORDER_STATUS',true);		// Order Status wahrend Bestellabruf ändern ? true/false	
define('UPDATE_ORDER_STATUS_ERP',false);		// Order Status nach Bestellabruf ändern ? true/false	
// Bitte geben Sie hier den Order Status von abgerufenen Bestellungen ein
define('NEW_ORDER_STATUS','complete');	// Status nach Abruf
define('NEW_ORDER_STATUS_ERP','complete');		//  Erfolgreich on der WaWi eingelesen
define('NEW_ORDER_STATUS_FAILED','pending');	// Nicht oder fehlerhaft  on der WaWi eingelesen
define('NOTIFY_CUSTOMER',false);		// Kunde über geänderten Order Status nach Bestellabruf informieren ? true/false	

// Maximale Kategorieebenen
define('MAX_CATEGORY_LEVEL',4);
// ANZAHL maximal zu exportierender Kategorien
define('MAX_CATEGORIES_EXPORT',1000);
// ANZAHL maximal zu exportierender Produkte
define('MAX_PRODUCTS_EXPORT',1000);
define('FIRST_PRODUCTS_EXPORT',1000);	// Erstes anzurufendes Produkt
define('STD_WAWI_CAT_ID',999);	//Standard Kategorie der Warenwirtschaft, wenn Artikel in Magento nicht zugeordnet

// WaWi Bezeichnung Kategorie oberste Ebene , z.B. 0 oder EMPTY fuer Office Line
define('MAIN_ERP_CATEGORY','EMPTY');
// Kategoriebeschreibungen exportieren
define('EXPORT_CATEGORY_DESC',false);

// Weitere
define('SHIPPING_AS_PRODUCT',false);			// Versandkosten als Artikel? 
define('SHIPPING_DISCOUNTED',false);			// Rabatte auch auf Versandkosten  
define('WAWI_NAME','NAV');			// Export zu Warenwirtschaftssystem / 'europa3000' oder andere

// Copie der Bestellungen per eMail als Status
define('ORDER_COPY_EMAIL',false);   
define('ORDER_COPY_EMAIL_FROM','info@mobilize.de');   
define('ORDER_COPY_EMAIL_TO','info@mobilize.de');   
define('ORDER_BACKUP_FILE','./order_backup/bestellung_');   

// SONDERFUNKTIONEN - In der Regel überflüssig
define('COPY_ORDER_IN_DATEBASE',false);
define('DB_SERVER2','');
define('DATABASE2','');
define('DB_USER2','root');
define('DB_PWD2','');

// Export LOG Datei
define('LOG_FILE_EXPORT','/var/www/pyua/dmc_mag_2014s/logs/dmconnector_log_magento_export.txt'); 

?>