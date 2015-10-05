<?php

/******************************************************************************************
*                                                                                          					*
*  dm.connector export for xtc tec shop									*
*  Copyright (C) 2009 DoubleM-GmbH.de									*
*                                                                                          					*
*******************************************************************************************/

// Erweitert am 220109 um PRODUCTS_POS
// Erweitert am 120209 um COUNTRY_ISO_CODE
// Erweitert am 200209 um Rabatt Modul ot_discount und Bonus Madul ot_bonus_fee und Vokasse modul ot_payment
// Erweiterung um Update Order Status
// Erweiterung um Abfrage Aulsandskunden /EG Kunden / Netto Kunden

// dmc configure

/*require('../includes/application_top_export.php');

require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
require_once(DIR_FS_INC . 'xtc_redirect.inc.php');
require_once(DIR_FS_INC . 'xtc_rand.inc.php');
*/


// TODO :
// WENN NICHT VORHANDEN
// ->  ALTER TABLE `zencart`.`categories_description` ADD COLUMN `categories_meta_description` VARCHAR(255) NULL DEFAULT '256' ;
 	// Database Definitions
if (is_file('../includes/configure.php')) include ('../includes/configure.php');
		else include ('../../includes/configure.php');
		
$connection=mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD) or die
("Verbindungsversuch fehlgeschlagen");

mysql_select_db(DB_DATABASE, $connection) or die("Konnte die Datenbank nicht
waehlen.");

/* 06.09.2013

Erweiterung  für Sage Classic Line
$sql = "CREATE TABLE IF NOT EXISTS `dmc_erp_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artnr` varchar(20) NOT NULL DEFAULT '',
  `artnr_erp` varchar(20) NOT NULL DEFAULT '',
  `maufacturer_erp` varchar(63) NOT NULL DEFAULT '',
  `variante_von` varchar(20) NOT NULL DEFAULT '',
  `merkmale` varchar(255) NOT NULL DEFAULT '',
  `auspraegungen` varchar(255) NOT NULL DEFAULT '',
  `preis` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());
	*/		   
		//	   echo"Tabelle products_dmc angelegt.<br>";

		

*/
/*28.08.2013
- decrepated
$sql = "CREATE TABLE IF NOT EXISTS `products_dmc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artnr` varchar(20) NOT NULL DEFAULT '',
  `artnr_neu` varchar(20) NOT NULL DEFAULT '',
  `variante_von` varchar(20) NOT NULL DEFAULT '',
  `merkmale` varchar(255) NOT NULL DEFAULT '',
  `auspraegungen` varchar(255) NOT NULL DEFAULT '',
  `preis` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());
	*/		   
		//	   echo"Tabelle products_dmc angelegt.<br>";

		
$sql = "CREATE TABLE IF NOT EXISTS `dmc_handelsstueckliste` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artnr` varchar(20) NOT NULL DEFAULT '',
  `set_artnr` varchar(20) NOT NULL DEFAULT '',
  `set_position` int NULL DEFAULT 0,
  `menge` double NOT NULL DEFAULT 1,
  `einheit` varchar(20) NULL DEFAULT '',
  `preis` double NOT NULL DEFAULT '0',
  `mwst` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());
			   
			//   echo"Tabelle products_dmc angelegt.<br>";
			   /*

$sql = "CREATE TABLE IF NOT EXISTS `dmc_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(1024) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `defaultvalue` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `subtype` varchar(255) NOT NULL DEFAULT '',
  `shop` varchar(255) NOT NULL DEFAULT '',
  `shopversion` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());
			   
			   echo"Tabelle dmc_config angelegt.<br>";
		

$sql = "INSERT INTO `dmc_config` ( `attribute`, `name`, `desc`, `value`, `defaultvalue`, `type`, `subtype`, `shop`, `shopversion`) VALUES
( 'DMC_VERSION_YEAR', 'Versions-Jahr', '', '2011', '2011', 'internal', '', 'all', ''),
( 'DMC_VERSION_MONTH', 'Versions-Monat', '', '08', '08', 'internal', '', 'all', ''),
( 'DMC_VERSION_DAY', 'Versions-Tay', '', '22', '22', 'internal', '', 'all', ''),
( 'SHOPSYSTEM', 'Shopsystem', 'Das eingesetzte Shopsystem.', 'gambio', 'gambio', 'all', '', 'all', ''),
( 'SHOPSYSTEM_VERSION', 'Version des Shops', '', 'gx2', 'gx2', 'all', '', 'all', ''),
( 'WAWI', 'Warenwirtschaftssystem', 'Das angebundene Shopsystem.', 'selectline', 'selectline', 'all', '', 'all', ''),
( 'CHARSET', 'Charset', 'Der Zeichensatz des Shops.', 'UTF-8', 'UTF-8', 'all', '', 'all', ''),
( 'PRODUCT_TEMPLATE', 'Produkt-Template', 'Die Standard-Vorlage für Artikeldetailseite.', 'select .. from ...', '', 'erp2shop', 'products', 'all', ''),
( 'OPTIONS_TEMPLATE', 'Optionen-Template', 'Die Standard-Vorlage für Artikelattribute.', 'product_options_selection.html', 'product_options_selection.html', 'erp2shop', 'products', 'all', ''),
( 'EXTENDED_PRODUCTS_SIZE', 'Modul Extended Products size', '', 'false', 'false', 'internal', '', 'all', ''),
( 'UPDATE_ORDER_STATUS_ERP', 'Order Status Update von WaWi', 'Den Bestellstatus ändern nachdem die Bestellung in die Warenwirtschaft geschrieben wurde.', 'false', 'false', 'shop2erp', 'orders', 'all', ''),
( 'NEW_ORDER_STATUS_ERP', 'Neuer Order-Status', 'Neuer Bestellstatus im Shop nachdem die Bestellung in die Warenwirtschaft geschrieben wurde.', '2', '2', 'shop2erp', 'orders', 'all', ''),
( 'NEW_ORDER_STATUS_FAILED', 'Fehler-Order-Status', 'Neuer Bestellstatus im Shop nachdem die Bestellung <b>nicht in die Warenwirtschaft geschrieben werden konnte</b>.', '1', '1', 'shop2erp', 'orders', 'all', ''),
( 'NOTIFY_CUSTOMER_ERP', 'Status-&Auml;nderung an Kunde', 'Kunden über die Statusänderung der Bestellung informieren.', 'false', 'false', 'shop2erp', 'orders', 'all', ''),
( 'GM_OPTIONS_TEMPLATE', 'Optionen-Template Übersicht', 'Die Standard-Vorlage für die Artikelattribute in der Übersicht.', 'product_options_selection.html', 'product_options_selection.html', 'erp2shop', 'products', 'gambio', 'gx|gx2'),
( 'GM_SHOW_weight', 'Gewicht anzeigen', 'Produkt Gewicht auf der Artikelseite anzeigen.', '1', 'a1', 'erp2shop', 'products', 'gambio', 'gx|gx2'),
( 'GM_SHOW_QTY_INFO', 'Bestand anzeigen', 'Produkt Bestand auf der Artikelseite anzeigen.', '1', '1', 'erp2shop', 'products', 'gambio', 'gx|gx2'),
( 'GM_SHOW_CHEAPER', 'Woanders g&uuml;nstiger anzeigen', '\"Woanders günstiger?\"-Modul anzeigen:', '0', '0', 'erp2shop', 'products', 'gambio', 'gx|gx2'),
( 'GM_SITEMAP_ENTRY', 'Sitemap Eintrag', 'Produkt in die Sitemap aufnehmen.', '0', '0', 'erp2shop', 'products', 'gambio', 'gx|gx2'),
( 'GM_SITEMAP_PRIORITY', 'Sitemap Priorit&auml;t', 'Die Priorität in der Sitemap', '0.0', '0.0', 'erp2shop', 'products', 'gambio', 'gx2'),
( 'GM_SITEMAP_FREQUENZ', 'Sitemap &Auml;nderungsfrequenz', 'Änderungsfrequenz in der Sitemap', 'immer', 'immer', 'erp2shop', 'products', 'gambio', 'gx2'),
( 'GM_PRICE_STATUS', 'Artikel-Preisstatus', 'Preisstatus des Artikels, z.B. normal, \"Preis auf Anfrage\" oder \"nicht käuflich\".', 'normal', 'normal', 'erp2shop', 'products', 'gambio', 'gx2'),
( 'HHG_OPTION_SELECT_TEMPLATE', 'Optionen-Template Übersicht', 'Die Standard-Vorlage für die Artikelattribute in der Übersicht.', 'slave_products_selection.html', 'slave_products_selection.html', 'erp2shop', 'products', 'hhg', ''),
( 'HHG_OPTION_PRODUCT_TEMPLATE', 'Optionen-Template Ansicht', 'Die Standard-Vorlage für die Artikelattribute in der Artikelansicht.', 'slave_products_selection.html', 'slave_products_dropdown.html', 'erp2shop', 'products', 'hhg', ''),
( 'PRODUCTS_OWNER', 'Produkt Owner', 'Besitzer des Produktes.', '1', '1', 'erp2shop', 'products', 'hhg', ''),
( 'PRODUCTS_DETAILS', 'Produkt Details', 'Details des Produktes.', 'Details', 'Details', 'erp2shop', 'products', 'hhg', ''),
( 'PRODUCTS_SPECS', 'Produkt Specs', 'Spezifikationen des Produktes.', 'Artikeldaten', 'Artikeldaten', 'erp2shop', 'products', 'hhg', ''),
( 'PRODUCTS_OWNER', 'Produkt Specs', 'Spezifikationen des Produktes.', 'Artikeldaten', 'Artikeldaten', 'erp2shop', 'products', 'hhg', ''),
( 'STORE_ALL', 'Alle Stores', 'Anzeige in allen Stores.', 'true', 'true', 'erp2shop', 'products', 'hhg', ''),
( 'STORE_ID', 'ID des Stores', 'ID des Standard-Stores.', '1', '1', 'erp2shop', 'products', 'hhg', ''),
( 'CATEGORIES_TEMPLATE', 'Kategorie-Template', 'Die Standard-Vorlage für die Kategorieübersicht.', 'categorie_listing.html', 'categorie_listing.html', 'erp2shop', 'category', 'all', ''),
( 'CATEGORIES_TEMPLATE', 'Artikelübersichts-Template', 'Die Standard-Vorlage für die Produkt-Liste in der Kategorie.', 'product_listing_v1.html', 'product_listing_v1.html', 'erp2shop', 'category', 'all', ''),
( 'PRODUCTS_SORTING', 'Artikel-Sortierung', 'Sortierungsfeld für die Sortierung der Produkte in der Produkt-Liste der Kategorie.', 'p.products_price', 'p.products_price', 'erp2shop', 'products', 'all', ''),
( 'PRODUCTS_SORTING2', 'Sortierung ASC/DESC', 'Sortierung aufsteigend (ASC) oder absteigend (DESC).', 'ASC', 'ASC', 'erp2shop', 'products', 'all', ''),
( 'KATEGORIE_TRENNER', '\\', 'Trennung von Kategorienamen, z.B. Angebot\Notebooks\Acer.', 'ASC', 'ASC', 'erp2shop', 'internal', 'all', ''),
( 'PRODUCTS_EXTRA_PIC_PATH', 'Extra Bilderverzeichnis', 'Zusätzliches Upload Images Verzeichnis (absoluter Pfad).', 'html/gx2/images/product_images/original_images/', '<?php echo getwd().\"/images/product_images/original_images/\"; ?>', 'erp2shop', 'products', 'all', ''),
( 'PRODUCTS_EXTRA_PIC_NAME', 'Extra Bildbezeichnung.', 'Aufbau Bildbezeichnung des Zusatzbildes: ARTIKELBILD, wenn basierend auf Name des Artikelbildes, ARTIKELNUMMER, wenn basierend auf Artrikelnummer.', 'ARTIKELNUMMER', 'ARTIKELNUMMER', 'erp2shop', 'products', 'all', ''),
( 'PRODUCTS_EXTRA_PIC_EXTENSION', 'Extra Bildextension.', 'Extension der Bilddateien, Z.B. \"_\" für \"_1.jpg\".', '_', '_', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_0', 'Sichtbar für Gruppe Admin.', 'Sichtbarkeit setzen für Gruppe 0 (Admin). Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '1', '1', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_1', 'Sichtbar für Kundengruppe 1.', 'Sichtbarkeit setzen für Kundengruppe 1. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '1', '1', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_2', 'Sichtbar für Kundengruppe 2.', 'Sichtbarkeit setzen für Kundengruppe 2. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '1', '1', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_3', 'Sichtbar für Kundengruppe 3.', 'Sichtbarkeit setzen für Kundengruppe 3. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '1', '1', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_4', 'Sichtbar für Kundengruppe 4.', 'Sichtbarkeit setzen für Kundengruppe 4. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_5', 'Sichtbar für Kundengruppe 5.', 'Sichtbarkeit setzen für Kundengruppe 5. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_6', 'Sichtbar für Kundengruppe 6.', 'Sichtbarkeit setzen für Kundengruppe 6. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_7', 'Sichtbar für Kundengruppe 7.', 'Sichtbarkeit setzen für Kundengruppe 7. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_8', 'Sichtbar für Kundengruppe 8.', 'Sichtbarkeit setzen für Kundengruppe 8. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PERMISSION_9', 'Sichtbar für Kundengruppe 9.', 'Sichtbarkeit setzen für Kundengruppe 9. Werte 1 - sichtbar, 0 - nicht sichtbar, oder <b>kein Eintrag wenn Gruppe nicht existent.</b>', '','', 'erp2shop', 'products', 'all', ''),
( 'FSK18', 'Artikel FSK18', 'Artikel sichbar nur für Kunden mit Kennzeichen FSK18.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'UPDATE_BEZ', 'Update Bezeichnung', 'Bezeichnung bei bestehenden Artikel ändern lassen.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'UPDATE_DESC', 'Update Beschreibung', 'Beschreibung bei bestehenden Artikel ändern lassen.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'UPDATE_SHORT_DESC', 'Update Kurztext', 'Kurztext bei bestehenden Artikel ändern lassen.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'UPDATE_PROD_TO_CAT', 'Update Kategoriezuordnungen', 'Artikel-Kategoriezuordnungen bei bestehenden Artikel ändern lassen.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'UPDATE_CATEGORY', 'Update Kategorie', 'Änderungen von bestehenden Kategorien zulassen.', 'true', 'true', 'erp2shop', 'category', 'all', ''),
( 'UPDATE_CATEGORY_DESC', 'Update Beschreibung', 'Beschreibungen bei bestehenden Kategorien ändern lassen.', 'true', 'true', 'erp2shop', 'category', 'all', ''),
( 'DELETE_INACTIVE_PRODUCT', 'Produkte löschen', 'Inaktive Artikel löschen.', 'true', 'true', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE1', 'Preistabelle Kundengruppe 1.', 'Shop-Tabelle für Preise der Kundengruppe 1 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', 'personal_offers_by_customers_status_1','personal_offers_by_customers_status_1', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE1', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '1','1', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE2', 'Preistabelle Kundengruppe 2.', 'Shop-Tabelle für Preise der Kundengruppe 1 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', 'personal_offers_by_customers_status_2','personal_offers_by_customers_status_2', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE2', 'Preis Kundengruppe 2.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '2','2', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE3', 'Preistabelle Kundengruppe 3.', 'Shop-Tabelle für Preise der Kundengruppe 3 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', 'personal_offers_by_customers_status_3','personal_offers_by_customers_status_3', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE3', 'Preis Kundengruppe 3.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '3','3', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE4', 'Preistabelle Kundengruppe 4.', 'Shop-Tabelle für Preise der Kundengruppe 4 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_4', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE4', 'Preis Kundengruppe 4.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE5', 'Preistabelle Kundengruppe 5.', 'Shop-Tabelle für Preise der Kundengruppe 5 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_5', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE5', 'Preis Kundengruppe 5.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE6', 'Preistabelle Kundengruppe 6.', 'Shop-Tabelle für Preise der Kundengruppe 6 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_6', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE6', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE7', 'Preistabelle Kundengruppe 7.', 'Shop-Tabelle für Preise der Kundengruppe 7 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_7', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE7', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE8', 'Preistabelle Kundengruppe 8.', 'Shop-Tabelle für Preise der Kundengruppe 8 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_8', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE8', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE9', 'Preistabelle Kundengruppe 9.', 'Shop-Tabelle für Preise der Kundengruppe 9 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_9', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE9', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),
( 'TABLE_PRICE10', 'Preistabelle Kundengruppe 10.', 'Shop-Tabelle für Preise der Kundengruppe 10 (personal_offers_by_customers_status_...) oder <b>kein Eintrag, wenn Gruppe nicht existent.</b>', '','personal_offers_by_customers_status_10', 'erp2shop', 'products', 'all', ''),
( 'GROUP_PRICE10', 'Preis Kundengruppe 1.', 'Zuzuordnender Preis aus Warenwirtschaft an Shop-Kundengruppe. Preis ID 1-4 oder <b>kein Eintrag wenn kein Preis zugeorndnet werden soll.</b>', '4','4', 'erp2shop', 'products', 'all', ''),

( 'TABLE_PRODUCTS_XSELL', 'Tabellenname Cross-Sell', '', 'personal_xsell', 'personal_xsell', 'internal', '', 'all', ''),
( 'TABLE_SPECIALS', 'Tabellenname Aktionen', '', 'specials', 'specials', 'internal', '', 'all', ''),

( 'DEBUGGER', 'DEBUGGER-Modus', 'Modus des Debugging (0,1 oder 99)', '99', '99', 'all', '', 'all', ''),
( 'LOG_DATEI', 'Log-Dateiname', 'Name der Log-Datei (Beachten Sie die Zugriffsrechte).', './dmconnector_log.txt', './dmconnector_log.txt', 'all', '', 'all', ''),
( 'IMAGE_LOG_FILE', 'Log-Dateiname Images ', 'Name der Log-Datei für Bilder (Beachten Sie die Zugriffsrechte).', './dmconnector_log_images.txt', './dmconnector_log_images.txt', 'all', '', 'all', ''),
( 'PRINT_POST', 'Übermittelte Daten debuggen.', 'PRINT_POST (0 oder 1)', '1', '1', 'all', '', 'all', ''),
( 'LOG_ROTATION', 'Log Rotation Modus', 'Modus für das Löschen der LOG-Datei des Debugging. Werte \"time\" -> nach Zeit in Tagen oder \"size\" -> nach Grösse in Megabyte', 'size', 'size', 'all', '', 'all', ''),
( 'LOG_ROTATION_VALUE', 'Log Rotation Wert', 'Wert für das Löschen der LOG-Datei des Debugging. Z.B. 5 für 5 Tage oder 5 Megabyte', '5', '5', 'all', '', 'all', '');";

*/
?>