<?php

/******************************************************************************************
*                                                                                          					*
*  dm.connector export for xtc tec shop									*
*  Copyright (C) 2011 DoubleM-GmbH.de									*
*                                                                                     					*
*******************************************************************************************/

// Bitte geben Sie hier die Magento-Datenbank ein
define('DB_SERVER','localhost');
define('DB_DATABASE','hsvertrieb');
define('DB_SERVER_USERNAME','root');
define('DB_SERVER_PASSWORD','');
define('DB_TABLE_PREFIX','');

$connection=mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD) or die
("Verbindungsversuch fehlgeschlagen");

mysql_select_db(DB_DATABASE, $connection) or die("Konnte die Datenbank nicht
waehlen.");

/* select 'dmc_invoice_create' AS Freifeld1, 'Rechnung' AS Belegart, B.Belegnummer, B.Datum, K.EMail, B.Anrede, B.Name, B.Vorname, B.Zusatz, B.Strasse, B.Land, B.Plz, B.Ort, B.EuroNetto AS GesamtpreisNetto, B.EuroBrutto AS GesamtpreisBrutto, '' AS Freifeld11, '' AS Freifeld12 FROM BELEG AS B INNER JOIN KUNDEN AS K ON B.Adressnummer = K.Nummer WHERE (B.BELEG_ID > 410) AND (B.Belegtyp = 'R')
*/
$sql = "CREATE TABLE IF NOT EXISTS `dmc_billings_header` (
	customer_shop_id int(10) NULL,
	Belegart varchar(50) NULL,
	Belegnummer varchar(100) NULL,
	Datum varchar(15) NULL,
	Email varchar(50) NULL,
	Anrede varchar(50) NULL,
	Name varchar(50) NULL,
	Vorname varchar(50) NULL,
	Zusatz varchar(64) NULL,
	Strasse varchar(64) NULL,
	Land varchar(3) NULL,
	Plz varchar(24) NULL,
	Ort varchar(40) NULL,
	link varchar(64) NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());

/* Select 'order_pos_create', Belegnummer, Artikelnummer,  Bezeichnung, Menge, Einzelpreis,  Gesamtpreis as Gesamtpreis, '' as Steuersatz, '' as Rabattsatz, '' as Freifeld15, '' as Freifeld16, '' as Freifeld17, '' as Freifeld18, '' as Freifeld19 FROM BELEGP WHERE (Belegtyp = 'R') AND (Belegnummer LIKE 'AR00%')
*/
$sql = "CREATE TABLE IF NOT EXISTS `dmc_billings_pos` (
	Belegnummer varchar(100) NULL,
	Artikelnummer varchar(15) NULL,
	Bezeichnung varchar(255) NULL,
	Menge double NULL,
	Einzelpreis double NULL,
	Gesamtpreis double NULL,
	Steuersatz double NULL,
	Rabattsatz double NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
";

$db_erg = mysql_query($sql) or die("Anfrage fehlgeschlagen: " . mysql_error());
				   
			   echo"Tabelle angelegt";
?>