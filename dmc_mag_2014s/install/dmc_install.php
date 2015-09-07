<head>
		<title>Installation dmc</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15" />
		<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
		<link type="text/css" rel="stylesheet" href="install/css/stylesheet.css" />
		<!--[if gte IE 9]>
		  <style type="text/css">
			#main.gradient, #main .gradient.button, #main .gradient.button.red, #main .gradient.button.green {
			   filter: none;
			}
		  </style>
		<![endif]-->
		<script type="text/javascript" src="../gm/javascript/jquery/jquery.js"></script>
	</head>
<?php

	defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );
	
	include('./conf/definitions/de.inc.php');

	//if( (strpos(strtolower(SHOPSYSTEM), 'veyton') === false) && (strpos(strtolower(SHOPSYSTEM), 'presta') === false) )
	//	$current_template=dmc_get_shop_config_value('CURRENT_TEMPLATE');
	
	// Shopspezifische Einbindung von Definitionen
	$files_std = array (  'CAT_ROOT.dmc', 'GENERATE_CAT_ID.dmc', 'CAT_DEVIDER.dmc', 'ATTRIBUTE_SET.dmc',
				 'STD_ART_SET_GROUP.dmc',  'PRODUCTS_EXTRA_PIC_EXTENSION.dmc','ATTACH_IMAGES.dmc','UPDATE_IMAGES.dmc',
				  'STD_CUSTOMER_GROUP.dmc', 'STD_CUSTOMER_WEBSITE.dmc', 'STD_CUSTOMER_STORE.dmc', 'STD_CUSTOMER_STORE_VIEW.dmc');
			
	// Kundengruppenpreise		
	$files_prices = array ( 'MAIN_PRICE_ATTRIBUTE_ID.dmc','SPECIAL_PRICE_CATEGORY.dmc',
							'CUST_PRICE_GROUP1.dmc','CUST_PRICE_GROUP2.dmc','CUST_PRICE_GROUP3.dmc','CUST_PRICE_GROUP4.dmc'
						/*'TABLE_PRICE1.dmc', 'GROUP_PRICE1.dmc','TABLE_PRICE2.dmc', 'GROUP_PRICE2.dmc', 'TABLE_PRICE3.dmc', 'GROUP_PRICE3.dmc',
						'TABLE_PRICE4.dmc', 'GROUP_PRICE4.dmc','TABLE_PRICE5.dmc', 'GROUP_PRICE5.dmc', 'TABLE_PRICE6.dmc', 'GROUP_PRICE6.dmc',
						'TABLE_PRICE7.dmc', 'GROUP_PRICE7.dmc','TABLE_PRICE8.dmc', 'GROUP_PRICE8.dmc', 'TABLE_PRICE9.dmc', 'GROUP_PRICE9.dmc',
						'TABLE_PRICE10.dmc', 'GROUP_PRICE10.dmc' */ 
					);

	// Log und Debug		
	$files_debug = array ( 'DEBUGGER.dmc', 'LOG_FILE.dmc', 'IMAGE_LOG_FILE.dmc','PRINT_POST.dmc','LOG_ROTATION.dmc', 'LOG_ROTATION_VALUE.dmc'
					/*'LOG_DATEI.dmc',   */
					);
	
	// Spezielle Statusoperationen
	$files_status = array ( 'STATUS_WRITE_ART_BEGIN_DETELE_ART.dmc', 'STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART.dmc', 'STATUS_WRITE_ART_BEGIN_DETELE_ART_VARIANTS.dmc', 'STATUS_WRITE_ART_BEGIN_DEAKTIVATE_ART_VARIANTS.dmc'); //, 'STATUS_WRITE_ART_DETAILS_BEGIN.dmc','STATUS_WRITE_ART_END.dmc', 'STATUS_WRITE_ART_DETAILS_END.dmc');
	
	//  Einbindung von API und DB Definitionen
	$files_db = array ( 'SOAP_CLIENT.dmc','SHOP_VERSION.dmc', 'STORE_ID.dmc',  'DB_SERVER.dmc', 'DATABASE.dmc','DB_USER.dmc','DB_PWD.dmc','DB_TABLE_PREFIX.dmc');
	
	if ((isset($_POST['user']))and(isset($_POST['password']))) {
	//	if (checkLogin()) 
		$ok=true;
			// showDefinitions ();
	//	else 
	//		getLogin('failed');
	} else {
		// User oder Password nicht angegeben
	//	getLogin('new');
	}
	

function checkLoginWWW ()
{
	$ok=false; 
	// checkIfRegistered
	// Definition einlesen
		$dateihandle = fopen("./conf/definitions/DMC_U.dmc","r");
		$dUser = fread($dateihandle, 100);
		fclose($dateihandle);
		$dateihandle = fopen("./conf/definitions/DMC_P.dmc","r");
		$dPw = fread($dateihandle, 100);
		fclose($dateihandle);
		// Compare 
		if ($_POST['user']==$dUser && md5($_POST['password'])==$dPw)
			$ok=true;
	return $ok;
} // end function checkLogin 

function getLogin ($rcm)
{
	global $DMC_TEXT;
	$printHtml = '<html><head><link rel=stylesheet type="text/css" href="css/stylesheet.css"></head><body><h3>dmConnector Login</h3><br>';
	
	if ($rcm=='failed') echo'<font color=red>'.$DMC_TEXT['LOGIN_FAILED'].'</font><br>';
	$printHtml .='<table>';
		$printHtml .= 					'<form name="login" action="'.$_SERVER['PHP_SELF'].'" method="post">'.
										//'<form action="'.$_SERVER['PHP_SELF'].'" method="post" id="'.$Key.'">'.
										//'<input type="hidden" name="action" value="dmc_install">'.
										'<tr>'.
											'<td>User:</td>'.
											'<td><input name="user" type="text" id="user" size="20" value="" /></td>'.
										'</tr>'.	
										'<tr>'.
											'<td>Password:</td>'.
											'<td><input name="password" type="password" id="password" size="20" value="" /></td>'.
										'</tr>'.	
										'<tr>'.
											'<td><input name="action" type="hidden" value="dmc_install" />&nbsp;</td>'.
											'<td><input type="submit" name="submit" value="login"></td>'.
										'</tr>';
	$printHtml .='</table></body></html>';
	echo $printHtml; 
} // end function getLogin 

function writeDefinitions ()
{
	// checkIfDefinition
	 foreach ($_POST as $Key => $Value)
	  {
		// Wenn Definition - nicht user etc, dann datei vorhanden und wert in datei aendern
		$filename = './conf/definitions/'.$Key.".dmc";
		if ($Key=='DMC_P') $Value=md5($Value);
		if (file_exists($filename)) {
			$dateihandle = fopen($filename,"w");
			fwrite($dateihandle,trim($Value));
			fclose($dateihandle);
		} 
	 }
}

//--------- Werte aus Definitionsdatei ausgeben ------------------------------

function showDefinitions ($user,$password)
{
	global $DMC_TEXT, $files_std, $files_debug, $files_prices, $files_status, $files_db, $user, $password, $PHP_SELF;
	$Url = $PHP_SELF . "?action=dmc_install&user=" . $user . "&password=rcm";// . base64_encode($password);
	$seite=$_GET['site'];
	// Ueberpruefe uebergebene Werte und trage in Definitionsdateien ein.
	writeDefinitions();
		
	// Definitionen einlesen
	if ($seite=='test') {
		// test modus seite ausgeben
		testmodus();
	} else if ($seite=='5') {
		for ($i = 0; $i < count($files_db); $i++) 
			$files[$i]=$files_db[$i];
	} else if ($seite=='4') {
		for ($i = 0; $i < count($files_status); $i++) 
			$files[$i]=$files_status[$i];
	} else if ($seite=='3') {
		for ($i = 0; $i < count($files_debug); $i++) 
			$files[$i]=$files_debug[$i];
	} else if ($seite=='2') {
		for ($i = 0; $i < count($files_prices); $i++) 
			$files[$i]=$files_prices[$i];
	} else {
		for ($i = 0; $i < count($files_std); $i++) 
			$files[$i]=$files_std[$i];
    
	}
	
	for ( $i = 0; $i < count ( $files  ); $i++ ) {
		$defName = substr($files[$i],0,-4);
		$dateihandle = fopen("./conf/definitions/".$files[$i],"r");
		$defValue = fread($dateihandle, 100);
		// echo"$defName=$defValue<br>";
		$definition[$defName] = trim($defValue);
		fclose($dateihandle);
	} // end for

	
	$printHtml = '<html><head><style type="text/css">
form { background-image:url(background.gif); padding:20px; border:6px solid #ddd; font-size:8px; font-family:arial;}
td, input, select, textarea { font-size:13px; font-family:Verdana,sans-serif; font-weight:bold; }
input, select, textarea { color:#00c; }
.fehler { background-color:red; }

.Bereich, .Feld { background-color:#ffa; width:300px; border:6px solid #ddd; }
.Auswahl { background-color:#dff; width:300px; border:6px solid #ddd; }
.Check, .Radio { background-color:#ddff; border:1px solid #ddd; }
.Button { background-color:#aaa; color:#fff; width:200px; border:6px solid #ddd; }
</style>
</head><body><h3>'.$DMC_TEXT['CONFIGURE_HEADER'].'</h3><br>';
	$printHtml .='<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=1">Grundeinstellungen</a>  </div>
	<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=2">Preise</a> 	</div>
	<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=3">Debug</a> </div>
	<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=4">Sondereinstellungen</a> </div>
	<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=5">Shopeinstellung</a> </div>
	<div id="choose" style="width:20%;float:left"><a href="'.$Url.'&site=test">Testmodus</a> </div>';
	
	$printHtml .='<br /><table>';
	$printHtml .= '<tr><th>'.$DMC_TEXT['TYPE'].'</th><th>'.$DMC_TEXT['NEW_VALUE'].'</th><th>'.$DMC_TEXT['CHANGE'].'</th><th>&nbsp;</th></tr>';
	foreach ($definition as $Key => $Value)
	  {
	
						$printHtml .= 	'<tr>'.
										'<form name="'.$Key.'" action="'.$_SERVER['PHP_SELF'].'" method="post">'.
										//'<form action="'.$_SERVER['PHP_SELF'].'" method="post" id="'.$Key.'">'.
										'<input type="hidden" name="action" value="dmc_install">'.
										'<input type="hidden" name="user" value="'.$user.'">'.
										'<input type="hidden" name="password" value="rcm'. base64_encode($password).'">'.
										'<input type="hidden" name="site" value="'.$seite.'">'.
										'<td>'.$DMC_TEXT[$Key].'</td>';
										
						if ($Value=="true")
							$printHtml .= 	'<td><select name="'.$Key.'" ><option value="true" selected>Ja</option><option value="false" >Nein</option></select></td>';
						else if ($Value=="false")
							$printHtml .= 	'<td><select name="'.$Key.'" ><option value="true">Ja</option><option value="false" selected>Nein</option></select></td>';
						else if (substr($Value,0,4)=="http")
							$printHtml .= 	'<td><input name="'.$Key.'" type="text" id="'.$Key.'" size="'.(strlen($Value)+5).'" value="'.$Value.'" /><a href="'.$Value.'" alt="'.$DMC_TEXT['CHECK'].'" target="_blank"> '.$DMC_TEXT['CHECK'].' </a></td>';
						else if ($Key == "WAWI") {
							$printHtml .= 	'<td><select name="'.$Key.'" >';
							if ($Value=='easywinart') $printHtml .='<option value="easywinart" selected>EasyWinArt</option>'; else $printHtml .='<option value="easywinart" >EasyWinArt</option>';
							if ($Value=='pck') $printHtml .='<option value="pck" selected>PC-Kaufmann</option>'; else $printHtml .='<option value="pck" >PC-Kaufmann</option>';
							if ($Value=='selectline') $printHtml .='<option value="selectline" selected>SelectLine</option>'; else $printHtml .='<option value="selectline" >SelectLine</option>';
							if ($Value=='') $printHtml .='<option value="" selected>anderes</option>'; else $printHtml .='<option value="" >anderes</option>';
							$printHtml .= 	'</select></td>';
							$wawi=$Value;	// fuer weitere Verwendung
						} else if ($Key == "SHOPSYSTEM") {
							$printHtml .= 	'<td><select name="'.$Key.'" >';
							if ($Value=='gambio') $printHtml .='<option value="gambio" selected>Gambio 2007</option>'; else $printHtml .='<option value="gambio" >Gambio</option>';
							if ($Value=='gambiogx') $printHtml .='<option value="gambiogx2" selected>Gambio GX</option>'; else $printHtml .='<option value="gambiogx" >Gambio GX</option>';
							if ($Value=='gambiogx2') $printHtml .='<option value="gambiogx2" selected>Gambio GX 2</option>'; else $printHtml .='<option value="gambiogx2" >Gambio GX 2</option>';
							if ($Value=='hhg') $printHtml .='<option value="hhg" selected>HHG Multistore</option>'; else $printHtml .='<option value="hhg" >HHG Multistore</option>';
							if ($Value=='xtcmodified') $printHtml .='<option value="xtcmodified" selected>modified</option>'; else $printHtml .='<option value="xtcmodified" >modified</option>';
							if ($Value=='myoos') $printHtml .='<option value="presta" selected>myOOS</option>'; else $printHtml .='<option value="myoos" >myOOS</option>';
							if ($Value=='presta') $printHtml .='<option value="presta" selected>PrestaShop</option>'; else $printHtml .='<option value="presta" >PrestaShop</option>';
							if ($Value=='veyton') $printHtml .='<option value="veyton" selected>Veyton</option>'; else $printHtml .='<option value="veyton" >Veyton</option>';
							if ($Value=='virtuemart') $printHtml .='<option value="virtuemart" selected>Virtuemart</option>'; else $printHtml .='<option value="virtuemart" >Virtuemart</option>';
							if ($Value=='xtc') $printHtml .='<option value="xtc" selected>xt:Commerce</option>'; else $printHtml .='<option value="xtc" >xt:Commerce</option>';
							if ($Value=='xtcmodified') $printHtml .='<option value="xtcmodified" selected>xtc:modified</option>'; else $printHtml .='<option value="xtcmodified" >xtc:modified</option>';
							if ($Value=='zencart') $printHtml .='<option value="zencart" selected>Zen Cart</option>'; else $printHtml .='<option value="zencart" >Zen Cart</option>';
							$printHtml .= 	'</select></td>';
							$shop=$Value;	// fuer weitere Verwendung
						} else if ($Key =="DMC_FOLDER") {
							// pruefen ob verzeichnis existent
							if (!is_dir($Value)) 
								$printHtml .= 	'<td class="fehler"><input name="'.$Key.'" type="text" id="'.$Key.'" size="'.(strlen($Value)+5).'" value="'.$Value.'" /></td>';
							else
								$printHtml .= 	'<td><input name="'.$Key.'" type="text" id="'.$Key.'" size="'.(strlen($Value)+5).'" value="'.$Value.'" /></td>';
						
						}	
						else if ($Key =="DMC_P")
							$printHtml .= 	'<td><input name="'.$Key.'" type="password" id="'.$Key.'" size="20" value="'.$Value.'" /></td>';
						else if (strlen($Value)<=20)
							$printHtml .= 	'<td><input name="'.$Key.'" type="text" id="'.$Key.'" size="20" value="'.$Value.'" /></td>';
						else 				
							$printHtml .= 	'<td><input name="'.$Key.'" type="text" id="'.$Key.'" size="'.(strlen($Value)+5).'" value="'.$Value.'" /></td>';
						
						// Button
						$printHtml .= 	'<td><input type="submit" name="submit" value="'.$DMC_TEXT['CHANGE'].'"></td>'.
										'</form>'.
										'<td>&nbsp;&nbsp;&nbsp;<a href="javascript: void(0)" onclick="window.open(\'install/desc.php?option='.$Key.'\', \'desc\', \'width=500, height=350\'); return false;"><b>?</b></a> </td>'.
										'</tr>';
										
				
										
	} // end foreach
		// Passwortgeneratur
	$printHtml .= '<tr><td>&nbsp;</td><td><a href="javascript: void(0)" onclick="window.open(\'getpwd.php\', \'desc\', \'width=400, height=300\'); return false;"><b>'.$DMC_TEXT['GETPWD'].'</b></a></td><td>&nbsp;</td><td>&nbsp;</td></tr>';
	$printHtml .='</table></body></html>';
	echo $printHtml; 
	/* select  configuration_value from configuration where configuration_key='CURRENT_TEMPLATE'
<br><b>M&ouml;gliche Parameter :</b><br><br>
<a href="<? echo $Url; ?>&action=version">Ausgabe XML Scriptversion</a><br>
<br>
<a href="<? echo $Url; ?>&action=manufacturers_export">Ausgabe XML Manufacturers</a><br>
<a href="<? echo $Url; ?>&action=categories_export">Ausgabe XML Categories</a><br>
<a href="<? echo $Url; ?>&action=products_export">Ausgabe XML Products</a><br>
<a href="<? echo $Url; ?>&action=customers_export">Ausgabe XML Customers</a><br>
<a href="<? echo $Url; ?>&action=customers_newsletter_export">Ausgabe XML Customers-Newsletter</a><br>
<br>
<a href="<? echo $Url; ?>&action=orders_export">Ausgabe XML Orders</a><br>
<br>
<a href="<? echo $Url; ?>&action=config_export">Ausgabe XML Shop-Config</a><br>
<br>
<a href="<? echo $Url; ?>&action=update_tables">MySQL-Tabellen aktualisieren</a><br>
</body>
</html>'
<?*/

}	// end function showDefinitions

function testmodus() {
		// testmodus Seite ausgeben
	echo "<html><head><style type=\"text/css\">
	form { background-image:url(background.gif); padding:20px; border:6px solid #ddd; font-size:8px; font-family:arial;}
	td, input, select, textarea { font-size:13px; font-family:Verdana,sans-serif; font-weight:bold; }
	input, select, textarea { color:#00c; }
	.fehler { background-color:red; }

	.Bereich, .Feld { background-color:#ffa; width:300px; border:6px solid #ddd; }
	.Auswahl { background-color:#dff; width:300px; border:6px solid #ddd; }
	.Check, .Radio { background-color:#ddff; border:1px solid #ddd; }
	.Button { background-color:#aaa; color:#fff; width:200px; border:6px solid #ddd; }
	</style>
	</head><body><h3>dmConnector - Konfiguration</h3><br><div id=\"choose\" style=\"width:20%;float:left\">
	<a href=\"?action=dmc_install&user=dmconnect0r&password=rcm&site=1\">Grundeinstellungen</a>  </div>
	<div id=\"choose\" style=\"width:20%;float:left\"><a href=\"?action=dmc_install&user=dmconnect0r&password=rcm&site=2\">Preise</a> 	</div>
	<div id=\"choose\" style=\"width:20%;float:left\"><a href=\"?action=dmc_install&user=dmconnect0r&password=rcm&site=3\">Debug</a> </div>
	<div id=\"choose\" style=\"width:20%;float:left\"><a href=\"?action=dmc_install&user=dmconnect0r&password=rcm&site=4\">Sondereinstellungen</a> </div>
	<div id=\"choose\" style=\"width:20%;float:left\"><a href=\"?action=dmc_install&user=dmconnect0r&password=rcm&site=5\">Shopeinstellung</a> </div>
	<br />
	<form name=\"create\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
	<table> 
			<tr>
				<th>Art</th><th>Wert</th><th>&nbsp;</th>
			</tr>	
			<input type=\"hidden\" name=\"action\" value=\"write_artikel\">
			<input type=\"hidden\" name=\"user\" value=\"dmconnect0r\">
			<input type=\"hidden\" name=\"password\" value=\"dmc2013\">
			<input type=\"hidden\" name=\"site\" value=\"3\">
		
			<input type=\"hidden\" name=\"action\" value=\"write_artikel\">
			<input type=\"hidden\" name=\"ExportModus\" value=\"NoOverwrite\">
			<tr>
				<td>
					Artikeltyp
				</td>
				<td>
					<select name=\"Artikel_ID\" ><option value=\"simple\" selected>simple</option><option value=\"configurable\" >configurable</option><option value=\"grouped\" >grouped</option></select>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikelnummer
				</td>
				<td>
					<input name=\"Artikel_Artikelnr\" type=\"text\" id=\"Artikel_Artikelnr\" size=\"20\" value=\"12345678\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Bezeichnung
				</td>
				<td>
					<input name=\"Artikel_Bezeichnung1\" type=\"text\" id=\"Artikel_Bezeichnung1\" size=\"20\" value=\"Testartikel\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Bezeichnung
				</td>
				<td>
					<input name=\"Artikel_Bezeichnung1\" type=\"text\" id=\"Artikel_Bezeichnung1\" size=\"20\" value=\"Schöner Testartikel\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Langtext
				</td>
				<td>
					<input name=\"Artikel_Text1\" type=\"text\" id=\"Artikel_Text1\" size=\"20\" value=\"Dies ist der Längere Text - Kantext.\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Kurztext
				</td>
				<td>
					<input name=\"Artikel_Kurztext1\" type=\"text\" id=\"Artikel_Kurztext1\" size=\"20\" value=\"Testartikel\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Kategorie ID
				</td>
				<td>
					<input name=\"Artikel_Kategorie_ID\" type=\"text\" id=\"Artikel_Kategorie_ID\" size=\"20\" value=\"3\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Hersteller
				</td>
				<td>
					<input name=\"Hersteller_ID\" type=\"text\" id=\"Hersteller_ID\" size=\"20\" value=\"\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Menge Bestand
				</td>
				<td>
					<input name=\"Artikel_Menge\" type=\"text\" id=\"Artikel_Menge\" size=\"20\" value=\"5\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Preis
				</td>
				<td>
					<input name=\"Artikel_Preis\" type=\"text\" id=\"Artikel_Preis\" size=\"20\" value=\"19.90\" />
				</td>
				<td>&nbsp;</td>
			</tr>";
			for ($i=0;$i<=4;$i++)  {
			echo "<tr>
				<td>
					Preis $i (bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_Preis<?php echo $i; ?>\" type=\"text\" id=\"Artikel_Preis_<?php echo $i; ?>\" size=\"20\" value=\"1".(9-$i).".90\" />
				</td>
				<td>&nbsp;</td>
			</tr>";
			}
			echo "<tr>
				<td>
					Gewicht
				</td>
				<td>
					<input name=\"Artikel_Gewicht\" type=\"text\" id=\"Artikel_Gewicht\" size=\"20\" value=\"1.90\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Status
				</td>
				<td>
					<select name=\"Artikel_Status\" ><option value=\"4\" selected>Sichtbar (4)</option><option value=\"3\" >Sichtbar (3)</option><option value=\"2\" >Nicht sichtbar (2)</option><option value=\"2\" >Nicht sichtbar (1)</option></select>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Steuersatz ID
				</td>
				<td>
					<input name=\"Artikel_Steuersatz\" type=\"text\" id=\"Artikel_Steuersatz\" size=\"20\" value=\"2\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Bilddatei (in upload_images)
				</td>
				<td>
					<input name=\"Artikel_Bilddatei\" type=\"text\" id=\"Artikel_Bilddatei\" size=\"20\" value=\"test.jpg\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Verpackungseinheit
				</td>
				<td>
					<input name=\"Artikel_VPE\" type=\"text\" id=\"Artikel_Bilddatei\" size=\"20\" value=\"Stück\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Lieferstatus
				</td>
				<td>
					<input name=\"Artikel_Lieferstatus\" type=\"text\" id=\"Artikel_Lieferstatus\" size=\"20\" value=\"2-3 Tage\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Startseite (zB 7 für 7 Tage)
				</td>
				<td>
					<input name=\"Artikel_Startseite\" type=\"text\" id=\"Artikel_Startseite\" size=\"20\" value=\"0\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					SkipImages 
				</td>
				<td>
					<input name=\"SkipImages\" type=\"text\" id=\"SkipImages\" size=\"20\" value=\"0\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					SprachID (bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_TextLanguage1\" type=\"text\" id=\"Artikel_TextLanguage1\" size=\"20\" value=\"2\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel_MetaTitle1 (bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_MetaTitle1\" type=\"text\" id=\"Artikel_MetaTitle1\" size=\"20\" value=\"Meta Title\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel_MetaDescription1 (bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_MetaKeywords1\" type=\"text\" id=\"Artikel_MetaKeywords1\" size=\"20\" value=\"Meta Keywords\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel_MetaKeywords1 (bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_MetaDescription1\" type=\"text\" id=\"Artikel_MetaDescription1\" size=\"20\" value=\"Meta Description\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			 <tr>
				<td>
					Super Attribute (bei configurable)
				</td>
				<td>
					<input name=\"Artikel_URL1\" type=\"text\" id=\"Artikel_URL1\" size=\"20\" value=\"\" />
				</td>
				<td>&nbsp;</td>
			</tr><tr>
				<td>
					Status (Aktiv=! oder 0)
				</td>
				<td>
					<input name=\"Aktiv\" type=\"text\" id=\"Aktiv\" size=\"20\" value=\"1\" />
				</td>
				<td>&nbsp;</td>
			</tr><tr>
				<td>
					Attribut Set ID
				</td>
				<td>
					<input name=\"Aenderungsdatum\" type=\"text\" id=\"Aenderungsdatum\" size=\"20\" value=\"3\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Variante von (bei simples bei Bedarf)
				</td>
				<td>
					<input name=\"Artikel_Variante_Von\" type=\"text\" id=\"Artikel_Variante_Von\" size=\"20\" value=\"\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
					Artikel Attribute(e)
				</td>
				<td>
					<input name=\"Artikel_Merkmal\" type=\"text\" id=\"Artikel_Merkmal\" size=\"20\" value=\"color@material\" />
				</td>
				<td>&nbsp;</td>
			</tr><tr>
				<td>
					Artikel Attributwert(e)
				</td>
				<td>
					<input name=\"Artikel_Auspraegung\" type=\"text\" id=\"Artikel_Auspraegung\" size=\"20\" value=\"grün@Leder\" />
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><input type=\"submit\" name=\"submit\" value=\"Anlegen\"></td>
			</tr>
		</table>
		</form>
	</body>
	</html> ";
 
} // end test modus


	
?>