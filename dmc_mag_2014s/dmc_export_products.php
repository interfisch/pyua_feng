<?php
/********************************************************************************
*                                                                               *
*  dmc_export_products for magento shop											*
*  Copyright (C) 2009 DoubleM-GmbH.de											*
*                                                                               *
*         14.08.09  Release														*
* 15.12.2010 Erweiterung um PRODUCT_CATEGORY_KEYORDS 							*
* ... POOLSANA nur numerische Artikelnummern exportieren						*
* POOLSANA Kurzbeschreibung 249 Zeichen 										*
********************************************************************************/

/*
define('SET_TIME_LIMIT',0);   
define('CHARSET','iso-8859-1');
define('VALID_DMC',true);		// zugriff auf includes

include ('definitions.inc.php');
// include needed functions
include('dmc_db_functions.php');     
include('dmc_functions.php');     
// 18.08.2011 implementation des stepweisen abrufes durch variable step vom java programm
*/
defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_export_products($client, $session) {


	$debugger=1;

	if ($debugger==1)
			{
				$daten = "\n************************************************************************\n";
				$daten .= "************************* dmc export products *************************\n";
				$daten .= "***********************************************************************\n";
				$daten .= "****".date("d.m.Y H:i:s")."***\n";
				$dateiname=LOG_FILE;	
				$dateihandle = fopen($dateiname,"a");
				fwrite($dateihandle, $daten);				
			}
			

	// Letztes Datum vom Produktabruf ermitteln
	/*$dateihandleOrderID = fopen("./product_export_date.txt","r");
	$last_export = fread($dateihandleOrderID, 20);
	fclose($dateihandleOrderID);
	*/
	// Restrictions
	$Magento_Shop_ID = STORE_ID;
	// $Magento_Export_Filter= array();
	//$Magento_Export_Filter= array(
								//    'sku' => array('like'=>'zol%')
								//);
	// Letzte Abgerufene Bestellung ermitteln
		$dateihandleOrderID = fopen("./products_export_date.txt","r");
		$last_exported = fread($dateihandleOrderID, 20);
		fclose($dateihandleOrderID);
		
		$Magento_Export_Filter= array(
									//  'updated_at'=>array('from'=>$last_exported),
								    'sku' => array('like'=>'%'),
							//		'status' => array('eq'=>'1')
								);
	
	// Schrittfolge
	// $AnzahlAbruf = 100;
	$STARTZEIT = time(); 
	
	$products_list = array();
	$products_list=$client->call($session, 'product.list', array($Magento_Export_Filter));;
	// var_dump($products_list);
	
	$schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
				'<PRODUCTS_EXPORT_HEADER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<GENERATOR_INFO>' . "dmconnector".'</GENERATOR_INFO>' . "\n".
						'<GENERATOR_VERSION>' . "magento-".$version_datum.'</GENERATOR_VERSION>' . "\n".
						// todo 	aktuelles datum
						'<GENERATION_DATE>' . $order_infos[created_at] .'</GENERATION_DATE>' . "\n".	
						'<NO_OF_PRODUCTS>' .count($products_list) .'</NO_OF_PRODUCTS>'. "\n".
						'<EXPORT_STARTED>' .date("d.m.Y H:i:s") .'</EXPORT_STARTED>'. "\n".					
					'</CONTROL_INFO>' . "\n".
				'</PRODUCTS_EXPORT_HEADER>' . "\n".
                 '<PRODUCTS>' . "\n";
    
	
	/*$products_infos = $client->call($session, 'product.info', '12232857');
	var_dump($products_infos); */
	if ($debugger==1) fwrite($dateihandle,"--------->  Size of products_list: ".count($products_list)."\n\n");
	
	// Datum des zuletzt abgerufenen Produktes ermitteln
	// if (count($products_list)>0 && $last_export_id !='') $last_export_abgerufen=get_order_date_by_incr_id($last_export_id);
	$count_products=sizeof($products_list);
	
	$FIRST_PRODUCTS_EXPORT=FIRST_PRODUCTS_EXPORT;
	if (MAX_PRODUCTS_EXPORT<$count_products)
		$count_products=MAX_PRODUCTS_EXPORT+FIRST_PRODUCTS_EXPORT;
		
	
	$dateihandleOrderID = fopen("./products_export_no.txt","r");
	$anzahl_export_produkte = fread($dateihandleOrderID, 20);
	fclose($dateihandleOrderID);
	
	if ($anzahl_export_produkte==1000) {
		// maximal 1000 Produkte exportieren ob dem ersten
		$FIRST_PRODUCTS_EXPORT=0;
		$count_products=3000;
		//Anzahl Export Produkte auf 999n seten
		$dateihandleOrderID = fopen("./products_export_no.txt","w");
		fwrite($dateihandleOrderID, '999');		
		fclose($dateihandleOrderID);		
	}
	
	if ($anzahl_export_produkte==999) {
		// maximal 1000 Produkte exportieren ob dem ersten
		$FIRST_PRODUCTS_EXPORT=2999;
		$count_products=count($products_list);
		//Anzahl Export Produkte auf 999n seten
		$dateihandleOrderID = fopen("./products_export_no.txt","w");
		fwrite($dateihandleOrderID, '1000');		
		fclose($dateihandleOrderID);		
	}
	
	if ($debugger>=1) fwrite($dateihandle,"--------->  Products to Export $count_products of ".count($products_list)."\n\n");
	
	$FIRST_PRODUCTS_EXPORT=0;
	$count_products=count($products_list);
	// Aenderungen stepweiser abruf, z.B. step=3, maxsteps=50 -> schritt 3 von 50 schritten fuer Artikel abrufen
	$step = isset($_POST['step']) ?  $_POST['step'] : $_GET['step'];
	$maxsteps = isset($_POST['maxsteps']) ?  $_POST['maxsteps'] : $_GET['maxsteps'];
	if ($maxsteps=='') $maxsteps=40;
	if ($debugger==1) fwrite($dateihandle,"135 Step Nr ".$step." von max ".$maxsteps.".\n");
	if ($step!='') {
		if ($count_products<$maxsteps) {
			$products_per_step=$count_products;
			// $count_products bleibt gleich
			// zweiten step nicht mehr durchführen
			if ($step>1) $FIRST_PRODUCTS_EXPORT=$count_products; 
			else $FIRST_PRODUCTS_EXPORT=1;
		} else { 
			// abrunden
			$products_per_step=floor($count_products/$maxsteps);					// z.B.  11,7 = 585/50;
			// Wenn keine "gerade" schrittweite, dann ein produkt mehr pro schritt
			if ($products_per_step<floor($count_products/$maxsteps))
				$products_per_step=$products_per_step+1;
			if ($step==1) {
				$FIRST_PRODUCTS_EXPORT=1;				
				$count_products=$products_per_step-1;							// z.B. 10=11-1 
			} else {
				$FIRST_PRODUCTS_EXPORT = $step * $products_per_step;			// z.B. 22=2*11
				$count_products=(($step+1) * $products_per_step)-1;				// z.B. 32=3*11-1
			}
			// Beim letzten Step die restlichen Produkte
			if ($step==$maxsteps-1)
				$count_products=count($products_list);
		}
		if ($debugger==1) fwrite($dateihandle,"152 Stepweiser Abruf: Produkte ab Nummer ".$FIRST_PRODUCTS_EXPORT." bis ".$count_products);
	}
	//  $count_products=$FIRST_PRODUCTS_EXPORT+8;
	$FIRST_PRODUCTS_EXPORT=$FIRST_PRODUCTS_EXPORT-1;
	for ($i=$FIRST_PRODUCTS_EXPORT;$i<$count_products;$i++){
	// Restrictions: Only from one ShopID	
	// $products_list[$i][sku]='test45678';
	// Restrictions: Only from one ShopID	... POOLSANA nur numerische Artikelnummern exportieren
	if ( // ($products_list[$i][store_id] == $Magento_Shop_ID) && 
	 ($products_list[$i][sku]!='76902')
	&& ($products_list[$i][sku]!='76903')
	&& ($products_list[$i][sku]!='76904')
	&& ($products_list[$i][sku]!='76905')
	&& ($products_list[$i][sku]!='440014')
	&& ($products_list[$i][sku]!='430297')  ){ // && ($products_list[$i][status] == $Magento_Shop_Status)) {
	
		if ($debugger==1) fwrite($dateihandle,"\nBEARBEITET WIRD Produkt Nummer ".$i." mit sku".$products_list[$i][sku]);
			
		// get  Order Infos Array by increment_id s from the Order List Array by API Call
		$products_infos = array();
		$products_infos = $client->call($session, 'product.info', $products_list[$i][sku]);
		
		// KATEGORIE DETAILS ERMITTELN
		//$where="entity_id=".$products_infos[categories][0]." AND attribute_ID=31";
		//$kategoriename=dmc_get_category_id($where);
	
		// $products_infos = $client->call($session, 'sales_order.info', $products_list[$i][increment_id]);
		//$products_infos = $client->call($session, 'product.info', $products_list[$i][sku]);
		// Products Date in Datei speichern, wenn Datum neuer  als letztes TODO
		/*if ($products_list[$i][created_at]>=$last_export) {
			// Letzte (höchste) OrderID speichern
			$last_export=$products_list[$i][created_at];
			// 1 sekunde addieren
			if (substr($last_export, -1)<>9) $last_export = substr($last_export, 0,-1).(substr($last_export, -1)+1);  
			else $last_export = substr($last_export, 0,-2).(substr($last_export, -2)+1);	
			if ($noupdate_order_date!=1) {
				$dateihandleOrderID = fopen("./order_id.txt","w");
				fwrite($dateihandleOrderID, $last_export);
				fclose($dateihandleOrderID);
			}
		} // end if */
		
		// Wenn Kategorien nur zu einem bestimmten Level exportiert werden, die dem Produkt uebergeordnete Kategorien des Levels ermitteln
		if (MAX_CATEGORY_LEVEL > 0)
			$products_infos[categories][0]=dmc_get_category_upper_level_id($products_infos[categories][0]);
			
		// Keyword der Kategory ermitteln, wenn nicht vorhanden, dann empty
		$cat_keyword_id=dmc_get_cat_keywords($products_infos[categories][0]);
		
		// Standard Warenwirtschafts Kategorie, wenn Kategorie(zuordnung) nicht vorhanden
		if ($cat_keyword_id==-1) $cat_keyword_id=STD_WAWI_CAT_ID;
		
		if ($cat_keyword_id<>'-1') $cat_keyword_id='EMPTY';
		
		switch ($products_infos[visibility]) {
			CASE '0':
				$products_infos[visibility]='0';				
				break;
			CASE '1':
				$products_infos[visibility]='1';				
				break;
			CASE '2':
				$products_infos[visibility]='2';				
				break;
			CASE '3':
				$products_infos[visibility]='3';				
				break;
			DEFAULT:
				$products_infos[visibility]='4';				
				break;
		}//ende $products_infos[visibility]) 
		
	    $schema  .= 	
			'<PRODUCT_INFO>' . "\n" . 
			'<PRODUCT_DATA>' . "\n" .
			'<PRODUCT_EXPORT_NO>'.($i+1).'</PRODUCT_EXPORT_NO>' . "\n" .
			'<PRODUCT_ID>'.$products_infos[product_id].'</PRODUCT_ID>' . "\n" .
			'<PRODUCT_MODEL>'.sonderzeichen2html($products_infos[sku]).'</PRODUCT_MODEL>' . "\n" .
			'<PRODUCT_DEEPLINK>'. sonderzeichen2html($products_infos[url_path]).'</PRODUCT_DEEPLINK>' . "\n" .
			'<PRODUCT_ATTRIBUTE_SET>'. $products_infos[set].'</PRODUCT_ATTRIBUTE_SET>' . "\n" .
			'<PRODUCT_TYPE>'. $products_infos[type].'</PRODUCT_TYPE>' . "\n" .
			// Main Categorie - others to do
			'<PRODUCT_CATEGORIES>' . $products_infos[categories][0]  . '</PRODUCT_CATEGORIES>' . "\n" .
			'<PRODUCT_CATEGORY_KEYORDS>' . $cat_keyword_id  . '</PRODUCT_CATEGORY_KEYORDS>' . "\n" .
			// Main WEBSITE - others to do
			'<PRODUCT_WEBSITES>' . $products_infos[websites][0]   . '</PRODUCT_WEBSITES>' . "\n" .
			'<PRODUCT_DATE_ADDED>' . $products_infos[created_at] . '</PRODUCT_DATE_ADDED>' . "\n" .
			'<PRODUCT_LAST_MODIFIED>' . $products_infos[updated_at] . '</PRODUCT_LAST_MODIFIED>' . "\n" .
			'<PRODUCT_DATE_AVAILABLE></PRODUCT_DATE_AVAILABLE>' . "\n" .
			// Fremdsprachen to do (anderer Store)
			"<PRODUCT_DESCRIPTION ID='1' CODE='de' NAME='deutsch'>\n".
			"<NAME>" . substr(sonderzeichen2html($products_infos[name]),0,250) . "</NAME>" . "\n" .
			"<NAME_SHORT>" . substr(sonderzeichen2html($products_infos[name]),0,49) . "</NAME_SHORT>" . "\n" .
			"<NAME_SHORT_2>" . substr(sonderzeichen2html($products_infos[name]),49,99) . "</NAME_SHORT_2>" . "\n" .
			"<URL>" . sonderzeichen2html($products_infos[url_path]) . "</URL>" . "\n" .
			"<DESCRIPTION>" . sonderzeichen2html($products_infos[description]). "</DESCRIPTION>" . "\n". 
			"<SHORT_DESCRIPTION>" . sonderzeichen2html(short_text(substr($products_infos[short_description],0,248))) . "</SHORT_DESCRIPTION>" . "\n".
			"<DESCRIPTION_ASCII>" . sonderzeichen2html(strip_tags($products_infos[description])). "</DESCRIPTION_ASCII>" . "\n". 
			"<META_TITLE>" .  sonderzeichen2html($products_infos[meta_title]). "</META_TITLE>" . "\n".
			"<META_DESCRIPTION>" .  sonderzeichen2html($products_infos[meta_description]). "</META_DESCRIPTION>" . "\n".
			"<META_KEYWORDS>" .  sonderzeichen2html($products_infos[meta_keyword]) . "</META_KEYWORDS>" . "\n".
			"</PRODUCT_DESCRIPTION>\n".
			'<PRODUCT_WEIGHT>' .$products_infos[weight] . '</PRODUCT_WEIGHT>' . "\n" .
			'<PRODUCT_STATUS>' . $products_infos[status] . '</PRODUCT_STATUS>' . "\n" .
			'<PRODUCT_TAX_CLASS_ID>' . $products_infos[tax_class_id] . '</PRODUCT_TAX_CLASS_ID>' . "\n"  .
			// '<PRODUCT_TAX_RATE>' . xtc_get_tax_rate($products_infos['products_tax_class_id']) . '</PRODUCT_TAX_RATE>' . "\n"  .
			'<PRODUCT_VISIBILITY>' . $products_infos[visibility] . '</PRODUCT_VISIBILITY>' . "\n" .
			'<PRODUCT_OLD_ID>' . $products_infos[old_id] . '</PRODUCT_OLD_ID>' . "\n" .
			'<PRODUCT_HAS_OPTIONS>' . $products_infos[has_options] . '</PRODUCT_HAS_OPTIONS>' . "\n" .
			// ATTRIBUTE - todo - je nach attribute set
			'<MANUFACTURERS_ID>' . $products_infos[manufacturer] . '</MANUFACTURERS_ID>' . "\n" .
			'<PRODUCT_COLOR>' . sonderzeichen2html($products_infos[color]) . '</PRODUCT_COLOR>' . "\n" .
			'<PRODUCT_PRICE>' . $products_infos[price] . '</PRODUCT_PRICE>' . "\n".
			'<PRODUCT_COSTS>' . $products_infos[cost] . '</PRODUCT_COSTS>' . "\n".
			// todo tier_prices und special prices
			'<PRODUCT_IMAGE>' . $products_infos[image_label] . '</PRODUCT_IMAGE>' . "\n".
			'<PRODUCT_IMAGE_SMALL>' . $products_infos[small_image_label] . '</PRODUCT_IMAGE_SMALL>' . "\n".
			'<PRODUCT_IMAGE_THUMBNAIL>' . $products_infos[thumbnail_label] . '</PRODUCT_IMAGE_THUMBNAIL>' . "\n".
			'<PRODUCT_QUANTITY></PRODUCT_QUANTITY>' . "\n" .
			'</PRODUCT_DATA>' . "\n" .
			'</PRODUCT_INFO>' . "\n";
								 
	} // endif Restrictions
	} // end for products list
	
	
	$schema .= '</PRODUCTS>' . 
				'<PRODUCTS_EXPORT_FOOTER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<EXPORT_FINISHED>' .date("d.m.Y H:i:s") .'</EXPORT_FINISHED>'."\n" .
						'<EXPORT_DURATION>' . (time() - $STARTZEIT) .' seconds</EXPORT_DURATION>'."\n" .						
					'</CONTROL_INFO>' . "\n".
				'</PRODUCTS_EXPORT_FOOTER>' . "\n\n";
        
	// Print XML
	fwrite($dateihandle, "Schema wird ausgegeben -> Export beeandet.\n\n");
	echo $schema;
				
 // Close the session 
      $client->endSession($session);
	  
	} // end function dmc_export_products
?>