<?php
/*******************************************************************************************
*                                                                                          									*
*  dmc_export_categories for magento shop											*
*  Copyright (C) 2010 DoubleM-GmbH.de											*
*                                                                                          									*
*         31.08.10  Release							*
*******************************************************************************************/

/*
define('SET_TIME_LIMIT',0);   
define('CHARSET','iso-8859-1');
define('VALID_DMC',true);		// zugriff auf includes

include ('definitions.inc.php');
// include needed functions
include('dmc_db_functions.php');     
include('dmc_functions.php');     
*/
defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );

	function dmc_export_categories($client, $session) {

	//$Magento_Export_Filter= array();
	if (defined('SET_TIME_LIMIT')) { @set_time_limit(0);}
  
if (DEBUGGER>=1)
			{
				$daten = "\n***********************************************************************\n";
				$daten .= "******************* dmconnector export categories".date("YmdHis")." ****\n";
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
		
// Restrictions
	$Magento_Shop_ID = STORE_ID;
	// $Magento_Export_Filter= array();
		   
	// Schrittfolge
	$AnzahlKategorien = MAX_CATEGORIES_EXPORT;
	$STARTZEIT = time();
	
	// var_dump($products_list);
	
	$schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
				'<CATEGORIES_EXPORT_HEADER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<GENERATOR_INFO>' . "dmconnector".'</GENERATOR_INFO>' . "\n".
						'<GENERATOR_VERSION>' . "magento-".$version_datum.'</GENERATOR_VERSION>' . "\n".
						// todo 	aktuelles datum
						'<GENERATION_DATE>' . $order_infos[created_at] .'</GENERATION_DATE>' . "\n".	
						'<EXPORT_STARTED>' .date("d.m.Y H:i:s") .'</EXPORT_STARTED>'. "\n".					
					'</CONTROL_INFO>' . "\n".
				'</CATEGORIES_EXPORT_HEADER>' . "\n".
                 '<CATEGORIES>' . "\n";
    
	
	// Bis zu 200 Kategiren prüfen
	for ($i=0;$i<$AnzahlKategorien;$i++){
		try {		 					
				// Versuche Kategorie aufzurufen
			    $category_infos = array();
				$category_infos = $client->call($session, 'catalog_category.info', $i);
				if (DEBUGGER>=1)
					fwrite($dateihandle, "- Kategorie ".$category_infos['name']." id ".$category_infos['category_id']." vater ".$category_infos['parent_id']."\n");	
			
				// Ebenenbegrenzug? Root Cat ist level 1
				if ($category_infos['level'] <= MAX_CATEGORY_LEVEL && $category_infos['level']>1 && $category_infos['name']!='') {
					// if ($http://www.hs-vertrieb24.de/magento41401/dmconnector_export.php?action=categories_export&user=dmconnector&password=AH150265==1) fwrite($dateihandle,"api authentification, ->  get session token = ".$session);	
					// Kategorie oberste Ebene 
					if ($category_infos['parent_id'] == CAT_ROOT) { 
						$hauptartikelgruppe=$category_infos['category_id'];
						$parent_id=MAIN_ERP_CATEGORY;
					} else {
						$hauptartikelgruppe=dmc_get_category_first_level_id($category_infos['category_id']);
						$parent_id=$category_infos['parent_id'];
					}	
					// Unterkategorien vorhanden?
					if ($category_infos['children'] == '') 
						$has_subcategories='0';
					else 
						$has_subcategories='-1';
					
					// $category_infos['level'] ist nicht immer korrekt, daher Ebenenlevel ermitteln
					$ebenen=substr_count($category_infos['path'],'/')-1;
				
					$schema  .= '<CATEGORIES_DATA>' . "\n" .
						 		 '<ID>' . $category_infos['category_id'] . '</ID>' . "\n" .
								 '<PARENT_ID>' .$hauptartikelgruppe. '</PARENT_ID>' . "\n" .
					             '<MAIN_PARENT_ID>' . $parent_id . '</MAIN_PARENT_ID>' . "\n" .
					             '<LEVEL>' . $ebenen. '</LEVEL>' . "\n" .
								 '<PATH>' . $category_infos['path'] . '</PATH>' . "\n" .
								// '<children>' . $category_infos['children'] . '</children>' . "\n" .
					             '<HAS_SUBCATEGORIES>' . $has_subcategories . '</HAS_SUBCATEGORIES>' . "\n" .
					             '<IMAGE_URL></IMAGE_URL>' . "\n" .
					             '<SORT_ORDER>' . $category_infos['position']. '</SORT_ORDER>' . "\n" .
					             '<DATE_ADDED>' . $category_infos['created_at']. '</DATE_ADDED>' . "\n" .
					             '<LAST_MODIFIED>' . $category_infos['updated_at']. '</LAST_MODIFIED>' . "\n";
					$schema .= "<CATEGORIES_DESCRIPTION ID='1' CODE='DE' NAME='DEUTSCH'>\n";
				    $schema .= "<NAME>" . (sonderzeichen2html($category_infos['name'])) . "</NAME>" . "\n";
				    $schema .= "<HEADING_TITLE></HEADING_TITLE>" . "\n";
				   if(EXPORT_CATEGORY_DESC) $schema .= "<DESCRIPTION>" . sonderzeichen2html(($category_infos['description'])) . "</DESCRIPTION>" . "\n";
				   if(EXPORT_CATEGORY_DESC) $schema .= "<META_TITLE>" . sonderzeichen2html(($category_infos['meta_title'])) . "</META_TITLE>" . "\n";
				   if(EXPORT_CATEGORY_DESC) $schema .= "<META_DESCRIPTION>" . sonderzeichen2html(($category_infos['meta_description'])) . "</META_DESCRIPTION>" . "\n";
				   if(EXPORT_CATEGORY_DESC) $schema .= "<META_KEYWORDS>" . sonderzeichen2html(($category_infos['meta_keywords'])). "</META_KEYWORDS>" . "\n";
				    $schema .= "</CATEGORIES_DESCRIPTION>\n";
				/*	try {		 
					// Versuche Kategorie-Produkte aufzurufen
						// $assignedProducts = $client->call($sessionId, 'category.assignedProducts', array($category_infos['category_id'], $Magento_Shop_ID)); // 0 = StoreId
						$assignedProducts = $client->call($sessionId, 'category.assignedProducts', array($category_infos['category_id'], 1)); // 0 = StoreId
						// var_dump($assignedProducts);
						for ($j=0;$j<count($assignedProducts);$j++){
							$schema .="<PRODUCTS ID='" . $assignedProducts[$j]['product_id'] ."'></PRODUCTS>" . "\n";
						} // end for $j
					} catch (SoapFault $e) {
							// Keine Kategorie vorhanden 
							fwrite($dateihandle, "keine produkte ".$i);	
				
					} */
				  /*    // Produkte in dieser Categorie auflisten
					    $prod2cat_query = '';
					                                       
					    while ($prod2cat )
					    {
					      $schema .="<PRODUCTS ID='" . $prod2cat["products_id"] ."'></PRODUCTS>" . "\n";
					    } */
					$schema .= '</CATEGORIES_DATA>' . "\n";
				} // end if ($category_infos['level'] <= MAX_CATEGORY_LEVEL) {
		} catch (SoapFault $e) {
				// Keine Kategorie vorhanden
				fwrite($dateihandle, "- Keine Kategorie Nummer ".$i." vorhanden.\n");	
		}
	} // end for $i
	
	
	
	$schema .= '</CATEGORIES>' . 
				'<CATEGORIES_EXPORT_FOOTER>' . "\n".
					'<CONTROL_INFO>' . "\n".					  
						'<EXPORT_FINISHED>' .date("d.m.Y H:i:s") .'</EXPORT_FINISHED>'."\n" .
						'<EXPORT_DURATION>' . (time() - $STARTZEIT) .' seconds</EXPORT_DURATION>'."\n" .						
					'</CONTROL_INFO>' . "\n".
				'</CATEGORIES_EXPORT_FOOTER>' . "\n\n";
				
	

  $schema .= '</CATEGORIES>' . "\n";
        
	// Print XML
	echo $schema;
				
 // Close the session 
      $client->endSession($session);
	  
	} // end function dmc_export_products
?>