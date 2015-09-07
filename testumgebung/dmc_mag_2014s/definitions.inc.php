<?php
/*******************************************************************************************
*                                                                                          									*
*  definitions for magento shop												*
*  Copyright (C) 2008-2011 DoubleM-GmbH.de										*
*                                                                                          									*
*******************************************************************************************/
defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );






// decrepaded








// WICHTIG Bitte geben Sie die URL f�r die SOAP API Schnittstelle Ihres Shops ein (Vergleiche: http://www.fantastisch.info/index.php/api/?wsdl)
define('SOAP_CLIENT','http://www.fantastisch.info/api/soap/?wsdl');	// ***
// define('VALID_DMC',true);		// zugriff zu includes

// WICHTIG Bitte geben Sie hier die Magento-Datenbank ein
define('DB_SERVER','localhost');
define('DATABASE','magento16');
define('DB_USER','root');
define('DB_PWD','password');
define('DB_TABLE_PREFIX','');
 
// WICHTIG Bitte geben Sie hier die ID der Magento Root Categorie ein
define('CAT_ROOT',3);			//  KategorieID einer Standard Kategorie - NICHT die RootCat
define('GENERATE_CAT_ID',true);	// ID Der Kategorie ermitteln = true, bei false wird die von der WaWi �bergebene Kategorie ID direkt verwendet
define('CAT_DEVIDER',',');		// Trenner fuer mehrere Kategorien

// Shop Version
define('SHOP_VERSION',1.6);					// Magento Shop Version

// Bitte geben Sie hier die ID des Magento Attribut Sets ein, unter welchem die Artikel angelegt werden sollen
define('ATTRIBUTE_SET',4);					// Standard *** ATTRIBUTE_SET
define('STD_SUPER_ATTRIBUTE_ID',80);		// Standard Super-ATTRIBUTE_id fuer die Anlage von configurable products , z.b. 80 - color
define('MAIN_PRICE_ATTRIBUTE_ID',60);		// Haupt Preis Attibute ID f�r Preis vom CONF Produkt -> catalog_product_entity_decimal
define('STD_CUSTOMER_GROUP',1);				// Standard Kundengruppe bei der Anlage von Kunden
define('STD_ART_SET_GROUP','Meta Information');		// Standard Artikel-Set-Gruppe zur Zuordnung neue angelegter (Super)Attribute

// Aktionspreis-Artikel in eine Angebotskategorie
define('SPECIAL_PRICE_CATEGORY',2802);	// KategorieID der Angebotskategorie oder 0 oder 2802 f�r Startseitenartikel fuer nicht

// Kundengruppenpreise integrieren, wenn Preis 1-4 mit Schnittstelle uebertragen werden
define('CUST_PRICE_GROUP1','');	// Kundengruppe angebegen, wenn Artikel_Preis1 zuzuordnen ist	
define('CUST_PRICE_GROUP2','');	// Kundengruppe angebegen, wenn Artikel_Preis1 zuzuordnen ist
define('CUST_PRICE_GROUP3','');	// Kundengruppe angebegen, wenn Artikel_Preis1 zuzuordnen ist
define('CUST_PRICE_GROUP4','');	// Kundengruppe angebegen, wenn Artikel_Preis1 zuzuordnen ist

// Bitte geben Sie hier die ID des Magento Stores ein
define('STORE_ID',0);			// ID des Stores

// Bilder 
define ('PRODUCTS_EXTRA_PIC_EXTENSION', '_');
define ('ATTACH_IMAGES',true);			// Bilder verarbeiten
define ('UPDATE_IMAGES',true);			// Bestehende Bilder Updaten?

// debug modus
define('DEBUGGER',99); 		// debug modus: 0-aus, 1-standard, 99-incl Datenbank
define('LOG_FILE','./logs/dmconnector_log_magento.txt'); // dateiname Debug Datei
define('IMAGE_LOG_FILE','./logs/dmconnector_log_magento_images.txt'); // bei Bedarf: Dateiname Debug Datei fue fehlende Bilder
define('PRINT_POST',true); 			// �bergebene Daten loggen	
define('LOG_ROTATION','size'); 		// LOG nach ... l�schen - Werte - '' -> aus / time -> nach Zeit in Tagen / size -> nach gr�sse in Megabyte
define('LOG_ROTATION_VALUE',5); 	// ZAHLEN-Wert nach Zeit in Tagen / nach gr�sse in Megabyte

// dmconnector version
$version_datum = '06.09.2013';

// SONDERFUNKTIONEN mappings durchf�hren
define('MAP_MANUFACTURER',false); 	// Farben Mappen
define('MAP_PRODUCT_GROUPS',false); 	// Farben Mappen
define('MAP_COLORS',false); 			// Produktgruppen Mappen
define('MAP_PRODUCT_GROUPS_BY_ARTNR',false); 	// Farben Mappen
define('MAP_PRODUCT_COLORS_BY_ARTNR',false); 	// Farben Mappen

// SONDERFUNKTIONEN extend products )(automatische generierung von conf und simples aus einem artikelsatz durchf�hren
define('EXTENDED_PRODUCTS',false); 	// function dmc_write_extended.php verwenden
define('EXTENDED_PRODUCTS_SIZE',false); 	// function dmc_write_extended.php verwenden
define('ATTR_GROESSEN','size'); 			// attributbezeichnung fuer groessen		
define('ATTR_FARBEN','color@manufacturer_color'); 			// attributbezeichnung fuer farben		

// SONDERFUNKTIONEN Rundungen durchf�hren
define('ROUND_PRICES',false); 	// �bergebene Preise hinter dem Kommata �ndern
define('PRICE_END','99'); 		// Preis hinter Nachkommastelle, z.B 99 f�r 33,99 Euro	

// SONDERFUNKTIONEN IDs erforderlich fuer Fremdsprachenmodul
define('ENTITY_TYPE_ID',4);			// Typ ID fuer Artikel Texte
define('ATTR_ID_NAME',60);                // ID der Artikel Bezeichnung in Fremdsprache
define('ATTR_ID_KURZTEXT',62);         // ID des Artikel Kurztext in Fremdsprache
define('ATTR_ID_LANGTEXT',61);         // ID des Artikel LANGTEXT in Fremdsprache
define('ATTR_ID_META_TITLE',71);     // ID des Artikel Meta Titels in Fremdsprache
define('ATTR_ID_META_KEYW',72);   // ID der Artikel Meta Keywords in Fremdsprache
define('ATTR_ID_META_DESC',73);    // ID der Artikel Meta Description in Fremdsprache
define('ATTR_ID_CATEGORY_NAME',33);    // ID der Category Bezeichnung in Fremdsprache in catalog_category_entity_varchar???
// Nur bei Bedarf �ndern Weitere Bilder aus dem upload_images Verzeichnis k�nne zugeordnet werden, Z.b. Hauptbilder 1234.jpg und Zusatzbilder 1234_1.jpg etc
define ('IMAGE_FOLDER', './upload_images/');	
// Nur f�r Doumentenmanagement verwendet
define ('PDF_FOLDER', '../media/pdf/');	
// Weitere Sonderfunktionen 
define ('PRODUCT_DELETE_FIRST',false);	// Artikel vor dem Schreiben l�schen?
define('SET_TIME_LIMIT',0);   
define('CHARSET','iso-8859-1');

?>