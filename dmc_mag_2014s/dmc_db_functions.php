
<?php
/********************************************************************************************
*                                                                                          	*
*  dmConnector for magento shop																*
*  dmc_db_functions.php																		*
*  Datenbank Funktionalitaeten 																*
*  Copyright (C) 2010 DoubleM-GmbH.de														*
*                                                                                          	*
*   15.02.2009 - funtion dmc_get_shortdescription   							 			*
*   06.06.2009 - funtion dmc_get_incr_id_by_cust_id   										*
*	27.07.2009 - function get_first_order_id() 												*
*	27.07.2009 - function get_last_order_id() 												*
*	27.07.2009 - function get_order_date_by_incr_id() 										*
*	26.08.2009 - function dmc_category_exists() 											*
*	16.11.2010 - function dmc_sql_query() 													*
*	16.06.2011 - function dmc_get_flat_attibute_value() 									*
*	27.06.2011 - function get_option_id_by_attribute_code_and_entity() 						*
*	29.06.2011 - function dmc_get_website_id_by_store_view() 								*
*   13.12.2011 - function dmc_get_kelch_kundennummer_by_cust_id 							*
* 	18.01.2012 - 	Optimierung, dass Varianten mit Preis 0 oder '' keinen abweichenden 	*
*					Preis zugewiesen bekommen												*
* 	06.04.2012 - function dmc_sql_select_value($value, $table, $where) um 1 Wert zu geben	*
*	06.04.2012 - function dmc_get_attribute_code_by_attribute_name zur Validierung			*
*	20.09.2012 - function dmc_get_product_category_ids - rueckgabe von Cat IDs durch , getr *
*	18.02.2013 - function dmc_get_customer_attribute_value 									*
*				-> Wert eines Kundenattributes eines Kunden ermitteln						*
*	29.08.2013 - function dmc_db_connect2				 									*
*				-> Verbindung zu zweiter Datenbank aufnehmen								*
*	17.09.2013 - function get_option_id_by_attribute_code_and_option_value					* 
*				-> bisher: ermittelt die ID der Option 										*
*				-> 17.09.2013: und legt "select" option an, sofern noch nicht existent		*
*	12.11.2013 - function dmc_get_group_id_by_cust_id - GruppenID eines Kunden ermitteln    *
*																							*
*	15.11.2013 - function dmc_set_products_status - Produkt Status fuer StoreView aendern   *
*	19.11.2013 - function dmc_set_products_visibility - Sichtbarkeit fuer StoreView aendern *
*	30.04.2014 - function dmc_db_backup - Funktion zum Sichern der Shopdatenbank			* 
*********************************************************************************************/

defined( 'VALID_DMC' ) or die( 'Direct Access to this location is not allowed.' );
	//echo "Hallo";
#	error_reporting(E_ALL);
#	ini_set('display_errors', 1);

	function dmc_get_attribute_code_by_attribute_name($type_id,$attribute_name) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// Eventuell vom dmC generierter Code
		/* $seo_text = $attribute_name;
		
		$d1 = array(" ","Ã„", "Ã–", "Ãœ", "Ã¤" , "ö", "ü", "ÃŸ","Ä", "Ö", "Ü", "ä" , "ö", "ü", "ß","<",">","#","\"","'","Â´",",","&","Â²","?",";");
		$d2 = array("_", "Ae","Oe","Ue","ae","oe","ue","sz","Ae","Oe","Ue","ae","oe","ue","sz","_","_","_","_","_","_","_","_","2","_","_");
		$seo_text = str_replace($d1, $d2, $seo_text);		 
		$d1 =  array(' ', 'Ã­', 'Ã½', 'ÃŸ', 'ö', 'Ã´', 'Ã³', 'Ã²', 'Ã¤', 'Ã¢', 'Ã ', 'Ã¡', 'Ã©', 'Ã¨', 'ü', 'Ãº', 'Ã¹', 'Ã±', 'ÃŸ', 'Â²', 'Â³', '@', 'â‚¬', '$');
		$d2 = array('_', 'i', 'y', 's', 'oe', 'o', 'o', 'o', 'ae', 'a', 'a', 'a', 'e', 'e', 'ue', 'u', 'u', 'n', 'ss', '2', '3', 'at', 'eur', 'usd');
		$seo_text = str_replace($d1, $d2, $seo_text);
		$d1 =  array('&amp;', '&quot;', '&', '"', "'", 'Â¸', '`',  '(', ')', '[', ']', '<', '>', '{', '}', '.', ':', ',', ';', '!', '?', '+', '*', '=', 'Âµ', '#', '~', '"', 'Â§', '%', '|', 'Â°', '^');
		$seo_text = str_replace($d1, '', $seo_text);
		$d1 =  array('/', 'Ã˜', 'Â°', '-');
		$seo_text = str_replace($d1, '_', $seo_text);
		$seo_text = str_replace(array('----', '---', '--'), '_', $seo_text);
		$seo_text = strtolower($seo_text);
		*/
		// Ueber dmc_art_functions
		$seo_text=dmc_generate_attribute_code($seo_text);
		if ($seo_text=="") $seo_text="dmc_f_".$attribute_name;
		// code ermitteln
		$query = "SELECT DISTINCT attribute_code as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE entity_type_id = '".$type_id."' AND (attribute_code = '".$attribute_name."' OR attribute_code = 'dmc_".strtolower($attribute_name)."' OR attribute_code = '".$seo_text."')";	
		
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_attribute_code_by_attribute_name - SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
			// if attribute_code not exists check for frontend_label
			$query = "SELECT DISTINCT attribute_code as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE frontend_label = '".$attribute_name."' AND entity_type_id = '".$type_id."' ";
			if (DEBUGGER==99) 		fwrite($dateihandle, "Alternative 1-dmc_get_attribute_code_by_attribute_name-SQL= ".$query." .\n");		
			$sql_query = mysql_query($query);	
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
				$query = "SELECT DISTINCT attribute_code as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE frontend_label = '".$attribute_name."' AND entity_type_id = '".$type_id."' ";
				mysql_query("SET NAMES 'utf8'", $link);
				if (DEBUGGER==99) 	fwrite($dateihandle, "ALT2-dmc_get_attribute_code_by_attribute_name-SQL= ".$query." .\n");		
				$sql_query = mysql_query($query);	
				$TEMP_ID = mysql_fetch_array($sql_query);				
				if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') 
					$id = -1;	
				else {
					$id = $TEMP_ID['id'];
				}
			} else {
				$id = $TEMP_ID['id'];
			}
			
		} else {
			$id = $TEMP_ID['id'];	
		}
		if (DEBUGGER==99) 	fwrite($dateihandle, "ERGEBNIS = ".$id." .\n");		
				
		// close db
		 dmc_db_disconnect($link);	
		return $id;	
	} // end function dmc_get_attribute_code_by_attribute_name
	
	
	function dmc_get_value_from_where($where) {
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		
		$query = "SELECT DISTINCT entity_id as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity_varchar where ".$where;	
		if (DEBUGGER==99) fwrite($dateihandle, "\dmc_get_value_from_where-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$cat_id = -1;
		else
			$cat_id = $TEMP_ID['cat_id'];
		
		// close db
		dmc_db_disconnect($link);
		
		return $cat_id;	
	} // end function get_value_from_where
	
	function dmc_db_connect() {
		
		global  $dateihandle;
		
		$link = mysql_connect(DB_SERVER, DB_USER, DB_PWD);
		if (!$link) {
			fwrite($dateihandle, "dmc_db_connect - Verbindung zu Datenbank fehlgeschlagen: ". mysql_error()."\n");
			die('keine Verbindung möglich: ' . mysql_error());
		} 
		mysql_select_db(DATABASE);
		
		return $link;
	} // end function
	
	// Verbindung zu zweiter Datenbank aufnehmen
	function dmc_db_connect_db2() {
		
		global  $dateihandle;
		
		$link = mysql_connect(DB_SERVER2, DB_USER2, DB_PWD2);
		if (!$link) {
			fwrite($dateihandle, "dmc_db_connect2 - Verbindung zu Datenbank fehlgeschlagen: ". mysql_error()."\n");
			die('keine Verbindung möglich: ' . mysql_error());
		} 
		mysql_select_db(DATABASE2);
		
		return $link;
	} // end function
	
	
	function dmc_db_disconnect($link) {
		mysql_close($link);		
	} // end function

	// Wert eines Attributes basierend auf flat products tabelle	ermitteln
	function dmc_get_flat_attibute_value($sku,$attribute,$store_view) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
			
		$query = "SELECT DISTINCT ".$attribute." as attribute_value from ".DB_TABLE_PREFIX."catalog_product_flat_".$store_view." WHERE sku='".$sku."'";	
		if (DEBUGGER==99)  fwrite($dateihandle, "\dmc_get_flat_attibute_value-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['attribute_value']=='' || $TEMP_ID['attribute_value']=='null')
			$attribute_value = '';
		else
			$attribute_value = $TEMP_ID['attribute_value'];
		
		// close db
		dmc_db_disconnect($link);
		
		return $attribute_value;	
	} // end function
	
	function dmc_get_highest_id($id_column,$table) {
	
		// Open DB
		$link=dmc_db_connect();
	
		$query = "SELECT max(".$id_column.") as total from ".DB_TABLE_PREFIX."".$table;
		
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_highest_id-SQL= ".$query." .\n");		
		
		$sql_query = mysql_query($query);				
		
		$TEMP_ID = mysql_fetch_array($sql_query);	
			
		if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
			$highest_id = 1;
		else
			$highest_id = $TEMP_ID['total'];
			
		// close db
		dmc_db_disconnect($link);
		
		return $highest_id;	
	} // end function
	
	function dmc_get_next_id($id_column,$table) {
	
		// Open DB
		$link=dmc_db_connect();
	
		$query = "SELECT max(".$id_column.") as total from ".DB_TABLE_PREFIX."".$table;
		
		// if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_highest_id-SQL= ".$query." .\n");		
		
		$sql_query = mysql_query($query);				
		
		$TEMP_ID = mysql_fetch_array($sql_query);	
			
		if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
			$highest_id = 0;
		else
			$highest_id = $TEMP_ID['total'];
		
		// nextID = highestID+1
		$highest_id++;
		// close db
		dmc_db_disconnect($link);
		
		return $highest_id;	
	} // end function dmc_get_next_id
	
	function dmc_get_category_id($where) {
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		
		$query = "SELECT DISTINCT entity_id as cat_id FROM ".DB_TABLE_PREFIX."catalog_category_entity_varchar WHERE ".$where;	
		if (DEBUGGER==99) fwrite($dateihandle, "\ndmc_get_category_id-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$cat_id = -1;
		else
			$cat_id = $TEMP_ID['cat_id']; 
		
		// close db
		dmc_db_disconnect($link);
		
		return $cat_id;	
	} // end function dmc_get_category_id
	
	function dmc_category_exists($cat_id) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
			
		$query = "SELECT DISTINCT entity_id as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity_text where value='".$cat_id."'";	
		if (DEBUGGER==99) fwrite($dateihandle, "\ndmc_get_category_id-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$cat_id = -1;
		else
			$cat_id = $TEMP_ID['cat_id'];
		
		// close db
		dmc_db_disconnect($link);
		
		return $cat_id;	
	} // end function
	
	function dmc_get_entity_type_id_by_entity_type_code($type_code) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// entity_type_id ermitteln
		$query = "SELECT DISTINCT entity_type_id as id from ".DB_TABLE_PREFIX."eav_entity_type as id where entity_type_code= '".$type_code."'";	
		
		if (DEBUGGER==99) fwrite($dateihandle, "\dmc_get_entity_type_id_by_entity_type_code-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
			$id = -1;
		else
			$id = $TEMP_ID['id'];	
		// close db
		dmc_db_disconnect($link);	
		if (DEBUGGER==99) fwrite($dateihandle, "Result= ".$id." .\n");
		return $id;	
	} // end function dmc_get_entity_type_id_by_entity_type_code
	
	
	function dmc_get_attribute_id_by_attribute_code($type_id,$attribute_code) {
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
	//	fwrite($dateihandle, "dmc_get_attribute_id_by_attribute_code - 306".'\n');
		// Eventuell vom dmC generierter Code
		$seo_text = $attribute_code;
		// aus functions/products/dmc_art_functions.php
		$seo_text = dmc_generate_attribute_code($seo_text);
		//fwrite($dateihandle, "dmc_get_attribute_id_by_attribute_code - 303 $seo_text".'\n');
		// attribute_id ermitteln
		//$query = "SELECT DISTINCT attribute_id as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE entity_type_id = '".$type_id."' AND (attribute_code = '".$attribute_code."' OR attribute_code = '".$seo_text."')";	
		$query = "SELECT DISTINCT MIN(attribute_id) AS id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE ".
			"(attribute_code = '".$attribute_code."' OR attribute_code = '".$seo_text."' OR attribute_code = 'dmc_".$attribute_code."')  AND entity_type_id = '".$type_id."' ";	
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_functions - dmc_get_attribute_id_by_attribute_code-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
			// if attribute_code not exists check for frontend_label
		//	$query = "SELECT DISTINCT attribute_id as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE frontend_label = '".$attribute_code."' AND entity_type_id = '".$type_id."' ";
			$query = "SELECT DISTINCT attribute_id as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE frontend_label = '".$attribute_code."'  ";
			if (DEBUGGER==99) 			fwrite($dateihandle, "Alternative1 - dmc_get_attribute_id_by_attribute_code-SQL= ".$query." .\n");		
			$sql_query = mysql_query($query);	
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
				$query = "SELECT DISTINCT attribute_id as id FROM ".DB_TABLE_PREFIX."eav_attribute WHERE frontend_label = '".$attribute_code."' AND entity_type_id = '".$type_id."' ";
				mysql_query("SET NAMES 'utf8'", $link);
				if (DEBUGGER==99) 					fwrite($dateihandle, "Alternative2 - dmc_get_attribute_id_by_attribute_code-SQL= ".$query." .\n");		
				$sql_query = mysql_query($query);	
				$TEMP_ID = mysql_fetch_array($sql_query);				
				if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') 
					$id = -1;	
				else {
					$id = $TEMP_ID['id'];
				}
			} else {
				$id = $TEMP_ID['id'];
			}
			
		} else {
			$id = $TEMP_ID['id'];	
		}
		if (DEBUGGER==99) 	fwrite($dateihandle, "ERGEBNIS = ".$id." .\n");		
				
		// close db
		 dmc_db_disconnect($link);	
		return $id;	
	} // end function dmc_get_entity_type_id_by_entity_type_code
	
	function dmc_get_cat_keywords($Category_Father_ID) {
	
		global $dateihandle, $store_id;
		if ($store_id=="") $store_id=0;		// wird hier nicht verwendet
		// entity ID für categorie  emitteln - Std = 3
		$type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_category");
		// Attribut ID für meta Keyword ermitteln  - Std = 37
		$attribut_id=dmc_get_attribute_id_by_attribute_code($type_id,"meta_keywords");
		$attribut_id2=dmc_get_attribute_id_by_attribute_code($type_id,"meta_description");
		
		$Category_Father_ID =  str_replace("\\", "\\\\", $Category_Father_ID);
		
		// Open DB
		$link=dmc_db_connect();
		
		// wenn nicht vorhanden
		if ($attribut_id <> -1) {
			$query = "SELECT DISTINCT entity_id as id FROM ".DB_TABLE_PREFIX."catalog_category_entity_text ".
					 "WHERE entity_type_id = '$type_id' AND (attribute_id =$attribut_id)  AND (value='".$Category_Father_ID."' OR value LIKE '".$Category_Father_ID.",%' OR value LIKE '%,".$Category_Father_ID.",%' OR value LIKE '%,".$Category_Father_ID."') ORDER BY entity_id DESC";	// AND store_id=".$store_id."
			if (DEBUGGER>=99) 
				fwrite($dateihandle, "dmc_db_functions dmc_get_cat_keywords-SQL= ".$query." -> ");		
			$sql_query = mysql_query($query);					
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
				// Erweiterung zu zweitem Attribute aus catalog_category_entity_varchar
				$query = "SELECT DISTINCT entity_id as id FROM ".DB_TABLE_PREFIX."catalog_category_entity_varchar ".
					 "WHERE entity_type_id = '$type_id' AND (attribute_id=$attribut_id OR attribute_id=$attribut_id2)  AND value='$Category_Father_ID' ORDER BY entity_id DESC";	// AND store_id=".$store_id."
				// fwrite($dateihandle, "\dmc_get_import_category-SQL= ".$query." .\n");	
				$sql_query = mysql_query($query);					
				$TEMP_ID = mysql_fetch_array($sql_query);				
				if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
					$Category_Father_ID = -1;
				} else {
					$Category_Father_ID = $TEMP_ID['id'];	
				}
			} else {
				$Category_Father_ID = $TEMP_ID['id'];
			}
		} else {
			$Category_Father_ID=-1;
		}
		if ($Category_Father_ID=='') $Category_Father_ID=-1;
		if (DEBUGGER==99) fwrite($dateihandle, " result= ".$Category_Father_ID." .\n");		
		
		// close db
		dmc_db_disconnect($link);
		
		return $Category_Father_ID;	
	} // end function
	
	function dmc_get_catid_by_name($main_cat_name,$cat_name) {
	
		global $dateihandle;
		
		$link=dmc_db_connect();
		
		// Wenn  Haupt-Kategorie angeben
		if ($main_cat_name!='') {
			// Haupt Kategorie ID
			$query = "SELECT DISTINCT entity_id as id FROM ".DB_TABLE_PREFIX."catalog_category_flat_store_1 ".
					 "WHERE name='$main_cat_name'";	
			if (DEBUGGER>=99) 
				fwrite($dateihandle, "\dmc_get_catid_by_name-SQL= ".$query." .\n");		
			$sql_query = mysql_query($query);					
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				$Category_Father_ID = -1;
			else
				$Category_Father_ID = $TEMP_ID['id']; 
		}
		
		// Haupt Kategorie ID ermitteln
		if ($Category_Father_ID <> -1) {
			// Haupt Kategorie ID
			$query = "SELECT DISTINCT entity_id as id FROM ".DB_TABLE_PREFIX."catalog_category_flat_store_1 ".
					 "WHERE name='$cat_name' AND path like '%/".$Category_Father_ID."/%'";	
		} else {
			$query = "SELECT DISTINCT entity_id as id FROM ".DB_TABLE_PREFIX."catalog_category_flat_store_1 ".
					 "WHERE name='$cat_name'";	
		}
		if (DEBUGGER>=99) 
			fwrite($dateihandle, "\dmc_get_catid_by_name-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				$Category_ID = CAT_ROOT;
		else
				$Category_ID = $TEMP_ID['id']; 
		
		
		if (DEBUGGER==99) fwrite($dateihandle, "result= ".$Category_Father_ID." .\n");		
			
		// close db
		dmc_db_disconnect($link);
		
		return $Category_ID;	
	} // end function
	
	function dmc_update_category_id($old, $new) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_update_category_id\n");	*/				
			
		$setWhere = "SET entity_id = '".$new."' WHERE entity_id = '".$old."'"; 
		$setWhere2 = "SET category_id = '".$new."' WHERE category_id = '".$old."'";
		$query = array();
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity_datetime ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity_decimal ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity_int ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity_text ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_entity_varchar ".$setWhere;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_product ".$setWhere2;
		$query[] = "UPDATE ".DB_TABLE_PREFIX."catalog_category_product_index ".$setWhere2;
		// url rewrite
		$query[] = "UPDATE ".DB_TABLE_PREFIX."core_url_rewrite SET id_path='category/".$new."', target_path='catalog/category/view/id/".$new."' where category_id=".$new."  ";
		
		foreach($query AS $doquery)
		{
			if (DEBUGGER==99) fwrite($dateihandle, "dmc_update_category_id-SQL= ".$doquery." .\n");
			$sql_query = mysql_query($doquery);
		} // end foreach
		
		// close db
		dmc_db_disconnect($link);
		
	} // end function
	
	function dmc_sql_update($table, $what, $where) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				
		
		$query	= "UPDATE ".DB_TABLE_PREFIX."".$table;	
		$query	.= " SET ".$what;	
		$query	.= " WHERE ".$where;			
	
		$doquery = $query; // if no array
		// foreach($query AS $doquery)
		//{
		mysql_query("SET NAMES 'utf8'", $link);
			if (DEBUGGER==99) fwrite($dateihandle, "dmc_sql_update-SQL= ".$doquery." .\n");
			$sql_query = mysql_query($doquery);
		//} // end foreach
		
		// close db
		dmc_db_disconnect($link);		
	} // end function dmc_sql_update
	
	function dmc_sql_select_value($value, $table, $where) {
		global  $dateihandle;
		// Open DB, if not opened
		if (isset($link)) {
			if (!$link) 
				$link=dmc_db_connect();
		} else {
			$link=dmc_db_connect();
		}
		
		$query	= "SELECT ".$value." AS wert FROM ".DB_TABLE_PREFIX."".$table;	
		$query	.= " WHERE ".$where."; ";	
		if (DEBUGGER>=99) fwrite($dateihandle, "dmc_sql_select_value-SQL= ".$query." ");
		$sql_query = mysql_query($query);				
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['wert']=='' || $TEMP_ID['wert']=='null')
				$wert = "";
			else
				$wert  = $TEMP_ID['wert'];
		}
		if (DEBUGGER>=99) fwrite($dateihandle, "-> ".$wert." .\n");		
		// close db
		dmc_db_disconnect($link);	
		return $wert;		
	} // end function dmc_sql_select_value
	
	function dmc_sql_select_value_db2($value, $table, $where) {
		global  $dateihandle;
		// Open DB, if not opened
		if (!$link) 
			$link=dmc_db_connect_db2();
		
		$query	= "SELECT ".$value." AS wert FROM ".DB_TABLE_PREFIX."".$table;	
		$query	.= " WHERE ".$where."; ";	
		if (DEBUGGER>=99) fwrite($dateihandle, "dmc_sql_select_value-SQL= ".$query." ");
		$sql_query = mysql_query($query);				
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['wert']=='' || $TEMP_ID['wert']=='null')
				$wert = "";
			else
				$wert  = $TEMP_ID['wert'];
		}
		if (DEBUGGER>=99) fwrite($dateihandle, "-> ".$wert." .\n");		
		// close db
		dmc_db_disconnect($link);	
		return $wert;		
	} // end function dmc_sql_select_value
	
	function dmc_sql_insert($table, $columns, $values) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		
		$query	= "INSERT INTO ".DB_TABLE_PREFIX."".$table;	
		$query	.= " ".$columns." ";	
		$query	.= " values ".$values;			
	
		$doquery = $query; // if no array
//			if (DEBUGGER>=99) 
			fwrite($dateihandle, "dmc_sql_insert-SQL= ".$doquery." .\n");
			mysql_query("SET NAMES 'utf8'", $link);
			// mysql_query("SET CHARACTER SET 'utf8'", $link);
			//mysql_real_escape_string($doquery, $link);
				if (mysql_query($doquery)) fwrite($dateihandle, " ist eingetragen\n");
				else fwrite($dateihandle, "Fehler: NICHT eingetragen: ". mysql_errno() . ": " . mysql_error() . "\n");;
		
		// close db
		dmc_db_disconnect($link);		
	} // end function dmc_sql_insert
	
	function dmc_sql_delete($table, $where) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				

		$query	= "DELETE FROM ".DB_TABLE_PREFIX."".$table;	
		$query	.= " WHERE ".$where." ";	
		
		$doquery = $query; // if no array
		if (DEBUGGER==1)  fwrite($dateihandle, "dmc_sql_delete-SQL= ".$doquery." ");
			if (mysql_query($doquery)) fwrite($dateihandle, "gelöscht\n");
			else fwrite($dateihandle, "Fehler: " . mysql_error() . "\n");;
		
		// close db
		dmc_db_disconnect($link);		
	} // end function dmc_sql_delete
	
	function dmc_sql_query($query) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				

		$doquery = $query; // if no array
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_sql_query-SQL= ".$doquery." ");
		$result = mysql_query("SET NAMES 'utf8'", $link);

			if (mysql_query($doquery)) fwrite($dateihandle, " ausgefuehrt.\n");
			else fwrite($dateihandle, "Fehler: NICHT gelöscht: ". mysql_errno() . ": " . mysql_error() . "\n");;
		
		// close db
		dmc_db_disconnect($link);	
		return $result;
	} // end function dmc_sql_query
	
	function dmc_sql_query_db2($query) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect_db2();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				

		$doquery = $query; // if no array
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_sql_query_db2-SQL= ".$doquery." ");
		$result = mysql_query("SET NAMES 'utf8'", $link);

			if (mysql_query($doquery)) fwrite($dateihandle, " ausgefuehrt.\n");
			else fwrite($dateihandle, "Fehler: NICHT gelöscht: ". mysql_errno() . ": " . mysql_error() . "\n");;
		
		// close db
		dmc_db_disconnect($link);	
		return $result;
	} // end function dmc_sql_query_db2
	
	function dmc_db_fetch_array($query) {
		return mysql_fetch_array($query, MYSQL_ASSOC);
	}
	
	function dmc_get_id($id_column,$table,$where) {
		
		global  $dateihandle;
		
		$query = "SELECT ".$id_column." as nummer from ".DB_TABLE_PREFIX."".$table." WHERE ".$where;
		
		$link=dmc_db_connect();
		
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_id-SQL= ".$query." .\n");

		$result = mysql_query ( $query );
		$TEMP_ID = mysql_fetch_array($result);				
			if ($TEMP_ID['nummer']=='' || $TEMP_ID['nummer']=='null')
				// IF no ID -> Product not available
				$o_id = "";
			else
				$o_id  = $TEMP_ID['nummer'];
				
		dmc_db_disconnect($link);
		
		return $o_id;	
	} // end function dmc_get_id
	
	function dmc_get_id_by_artno($artno) {
	
		global  $dateihandle;
		
		$query = "SELECT entity_id AS id from ".DB_TABLE_PREFIX."catalog_product_entity";
		$query .= " WHERE sku ='".$artno."'";
		$link=dmc_db_connect();
		//	$dateiname=LOG_FILE;	
		//$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==1)  fwrite($dateihandle, "dmc_get_id_by_artno-SQL= ".$query." .\n");
			
		
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> Product not available
				$id = "";
			else
				$id  = $TEMP_ID['id'];
		}		

		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_id_by_artno
	
	function dmc_get_website_id_by_store_view($store_id) {
	
		global  $dateihandle;
		
		$query = "SELECT website_id as id from ".DB_TABLE_PREFIX."core_store";
		$query .= " WHERE store_id ='".$store_id."'";
		$link=dmc_db_connect();
		//	$dateiname=LOG_FILE;	
		//$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==1)  fwrite($dateihandle, "dmc_get_website_id_by_store_view-SQL= ".$query." .\n");
			
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> Product not available
				$id = "";
			else
				$id  = $TEMP_ID['id'];
		}		

		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_website_id_by_store_view
	
	function dmc_get_id_by_email($email) {
	
		global $debugger, $dateihandle;
		
		$query = "SELECT entity_id as id from ".DB_TABLE_PREFIX."customer_entity";
		$query .= " WHERE email ='".$email."'";
		$link=dmc_db_connect();
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_id_by_email-SQL= ".$query." .\n");
			
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> cusomer not available
				$id = "";
			else
				$id  = $TEMP_ID['id'];
		}		

		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_id_by_email 
	
	function dmc_get_adressid_by_cust_id($cust_id) {
	
		global $debugger, $dateihandle;
		
		$query = "SELECT entity_id as id from ".DB_TABLE_PREFIX."customer_address_entity";
		$query .= " WHERE increment_id ='".$cust_id."'";
		$link=dmc_db_connect();
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_adressid_by_cust_id-SQL= ".$query." .\n");
		
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> cusomer not available
				$id = "";
			else
				$id  = $TEMP_ID['id'];
		}		

		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_adressid_by_cust_id 
		
	function dmc_get_incr_id_by_cust_id($entity_id) {
	
		global  $dateihandle;
		
		$query = "SELECT increment_id as id from ".DB_TABLE_PREFIX."customer_entity";
		$query .= " WHERE entity_id =".$entity_id."";
		$link=dmc_db_connect();
		//	$dateiname=LOG_FILE;	
		// $dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_incr_id_by_cust_id-SQL= ".$query." ... ");
		// echo "dmc_get_incr_id_by_cust_id-SQL= ".$query." .\n";
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> Product not available
				$id = $entity_id;
			else {
				$id  = $TEMP_ID['id'];
				// Führende 000 entfernen
				$id=substr ($id,3,9);
				}
		}		

		if (DEBUGGER==99)  fwrite($dateihandle, " Ergebnis = ".$id." .\n");
		
		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_incr_id_by_cust_id
	
	function dmc_get_incr_id_by_adress_id($entity_id) {
	
		global  $dateihandle;
		
		$query = "SELECT increment_id as id from ".DB_TABLE_PREFIX."customer_address_entity";
		$query .= " WHERE entity_id =".$entity_id."";
		$link=dmc_db_connect();
		//	$dateiname=LOG_FILE;	
		//$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_incr_id_by_adress_id-SQL= ".$query." ... ");
		// echo "dmc_get_incr_id_by_adress_id-SQL= ".$query." .\n";
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null')
				// IF no ID -> Product not available
				$id = $entity_id;
			else {
				$id  = $TEMP_ID['id']; 
				// Führende 000 entfernen
				$id=substr ($id,3,9);
				}
		}		

		if (DEBUGGER==99)  fwrite($dateihandle, " Ergebnis = ".$id." .\n");
		
		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_incr_id_by_adress_id
	
	// GruppenID eines Kunden ermitteln   
	function dmc_get_group_id_by_cust_id($entity_id) {
		global  $dateihandle;
		
		$query = "SELECT group_id AS id from ".DB_TABLE_PREFIX."customer_entity";
		$query .= " WHERE entity_id =".$entity_id."";
		$link=dmc_db_connect();
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_group_id_by_cust_id-SQL= ".$query." ... ");
		$sql_query = mysql_query($query);				
		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['id']=='' || $TEMP_ID['id']=='null') {
				// IF no ID -> Product not available
				$id = "";
			} else {
				$id  = $TEMP_ID['id'];				
			}
		}		

		if (DEBUGGER==99)  fwrite($dateihandle, " Ergebnis = ".$id." .\n");
		
		dmc_db_disconnect($link);
			
		return $id;	
	} // end function dmc_get_group_id_by_cust_id
	
	// ermittelt die ID der Option / 17092013: und legt "select" option an, sofern noch nicht existent
	function get_option_id_by_attribute_code_and_option_value($attribute_code, $option_value, $store_id) {
	
		global  $dateihandle;
		
		// hier Attribute Options Bezeichnung immer auf ADMIN Store Abfragen
		// daher:
		$store_id=0;
		
		if ($option_value!="") {
			$link=dmc_db_connect();
			
			// if attribute has a text field value there is no option id -> use the option value
			$query = "SELECT ea.frontend_input as input_type FROM ".DB_TABLE_PREFIX."eav_attribute as ea ".
					 "WHERE ( ea.attribute_code='".$attribute_code."')";
		//	if (DEBUGGER>=99) fwrite($dateihandle, "get_option_id_by_attribute_code_and_option_value-SQL 1 = ".$query." .\n");
			mysql_query("SET NAMES 'utf8'", $link);
			$result = mysql_query ( $query );
			$TEMP_ID = mysql_fetch_array($result);		
			// IF feldtyp<> select, means no options_value_id may be gotten
			// and the erp value should be taken
			if ($TEMP_ID['input_type']=='select') {
				// take the option value instead of an id from the database
				$o_id  = $option_value;
				// not text or boolean ...
				$query = "SELECT eaov.option_id AS nummer FROM ".DB_TABLE_PREFIX."eav_attribute as ea, ".DB_TABLE_PREFIX."eav_attribute_option as eao, ".DB_TABLE_PREFIX."eav_attribute_option_value AS eaov ";
				$query .= "WHERE (ea.attribute_id = eao.attribute_id AND eao.option_id = eaov.option_id ";
				$query .= "AND ea.attribute_code='".$attribute_code."' ";
				$query .= "AND eaov.value='".$option_value."' AND eaov.store_id=".$store_id.") ";
				
				//$dateiname=LOG_FILE;	
				//$dateihandle = fopen($dateiname,"a");
			//	if (DEBUGGER>=99) fwrite($dateihandle, "get_option_id_by_attribute_code_and_option_value-SQL 2 = ".$query." ... -> ");
				$result = mysql_query ( $query );
				$TEMP_ID = mysql_fetch_array($result);		
				if (DEBUGGER>=99) fwrite($dateihandle, " ".$TEMP_ID['nummer']." .\n");
				if ($TEMP_ID['nummer']=='' || $TEMP_ID['nummer']=='null') {
						// if probably the attribute_code not exists check for frontend_label
						$query = "SELECT eaov.option_id as nummer FROM ".DB_TABLE_PREFIX."eav_attribute as ea, ".DB_TABLE_PREFIX."eav_attribute_option as eao, ".DB_TABLE_PREFIX."eav_attribute_option_value as eaov ";
						$query .= "WHERE ea.attribute_id = eao.attribute_id AND eao.option_id = eaov.option_id ";
						$query .= "AND ea.frontend_label='".$attribute_code."' ";
						$query .= "AND ea.source_model='eav/entity_attribute_source_table' ";
						$query .= "AND eaov.value='".$option_value."' AND eaov.store_id=".$store_id."";
						mysql_query("SET NAMES 'utf8'", $link);
					//	if (DEBUGGER==99) fwrite($dateihandle, "get_option_id_by_attribute_code_and_option_value-SQL 3 = ".$query." .\n");
						$result = mysql_query ( $query );
						$TEMP_ID = mysql_fetch_array($result);				
						if ($TEMP_ID['nummer']=='' || $TEMP_ID['nummer']=='null') {
							// IF no ID -> Product not available 
							$o_id = "";
							// Schritt 1: Anlage eine Verknüpfung Attribute auf Option in eav_attribute_option mit Ermittlung einer neuen OptionsID
							$option_id = dmc_get_highest_id("option_id","eav_attribute_option")+1;
							dmc_sql_insert("eav_attribute_option", 
									"(option_id, attribute_id, sort_order)", 
									"(".$option_id.", ".dmc_get_attribute_id_by_attribute_code('4',$attribute_code).", 0)");
								
							// Schritt 2: Anlage der zur neuen OptionsID gehörenden Option in eav_attribute_option_value
							dmc_sql_insert("eav_attribute_option_value", 
								"(option_id, store_id, value)", 
								"(".$option_id.", ".$store_id.", '".$option_value."')");
					 
						} else
							$o_id  = $TEMP_ID['nummer'];
				} else {
						$o_id  = $TEMP_ID['nummer'];
				} // end if
			} else {
				// take the option value instead of an id from the database
				$o_id  = $option_value;
			} // end if
			
			if ($attribute_code=="base_price_unit") $o_id = $option_value;
			if ($attribute_code=="base_price_base_unit") $o_id = $option_value;
			
			// if (DEBUGGER>=99) fwrite($dateihandle, "get_option_id_by_attribute_code_and_option_value - Ergebnis = ".$o_id."\n");
			
			dmc_db_disconnect($link);
		} else {
			$o_id="";
		}
		return $o_id;	
	} // end function get_option_id_by_attribute_code_and_option_value
	
	// Ermittelt die option ID fuer das product -  (wichtig fuer zuordnung Fremdsprachen-Werte)
	function get_option_id_by_attribute_code_and_entity($attribute_code, $entity_id, $attribute_id) {
	
		global $dateihandle, $store_id;
		 
		$link=dmc_db_connect();

		$option_value = trim ($option_value);
				
		// if attribute has a text field value there is no option id -> use the option value
		$query = "SELECT ea.backend_type as backend_type FROM ".DB_TABLE_PREFIX."eav_attribute as ea ".
				 "WHERE ( ea.attribute_code='".$attribute_code."' AND  ea.frontend_input='text')";
		if (DEBUGGER==1) fwrite($dateihandle, "get_option_id_by_attribute_code_and_entity-SQL 1 = ".$query." .\n");
		mysql_query("SET NAMES 'utf8'", $link);
		$result = mysql_query ( $query );
		$TEMP_ID = mysql_fetch_array($result);		
		// IF feldtyp=text, means no options_value_id may be gotten
		// and the erp value should be taken
		if ($TEMP_ID['backend_type']=='text' || $TEMP_ID['backend_type']=='varchar') {
			// take the option value instead of an id from the database
			$o_id  = $TEMP_ID['backend_type'];
		} else {
			// option id von main ermitteln (store_id=0)
			$query = "SELECT eaov.option_id as nummer FROM ".DB_TABLE_PREFIX."eav_attribute as ea, ".DB_TABLE_PREFIX."eav_attribute_option as eao, ".
					DB_TABLE_PREFIX."eav_attribute_option_value as eaov, ".DB_TABLE_PREFIX."catalog_product_entity_int AS cpei ";
			$query .= "WHERE (ea.attribute_id = eao.attribute_id AND eao.option_id = eaov.option_id AND cpei.value=eao.option_id ";
			$query .= "AND ea.attribute_code='".$attribute_code."' AND cpei.entity_id=".$entity_id." AND cpei.attribute_id=".$attribute_id." ";
			$query .= "AND eaov.store_id=0) ";
									
			if (DEBUGGER==1) fwrite($dateihandle, "get_option_id_by_attribute_code_and_entity-SQL 2 = ".$query." .\n");
			mysql_query("SET NAMES 'utf8'", $link);
			$result = mysql_query ( $query );
			$TEMP_ID = mysql_fetch_array($result);		
			if ($TEMP_ID['nummer']=='' || $TEMP_ID['nummer']=='null') {
				// product has not this option
				$o_id  = "";
			} else {
				$o_id  = $TEMP_ID['nummer'];
			} // end if
		} // end if 
		
		if (DEBUGGER==1) fwrite($dateihandle, "Result = ".$o_id."\n");
		
		dmc_db_disconnect($link);
		
		return $o_id;	
	} // end function get_option_id_by_attribute_code_and_entity
	
	function dmc_get_shortdescription($id) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// attribute_id		506 ShortDesc bei mir
		//				58 bei flashnet
		// attribute_id		104 Name
		// attribute_id		97 Desc
		
		$query = "SELECT DISTINCT value as short_desc from ".DB_TABLE_PREFIX."catalog_product_entity_text where attribute_id=58 and entity_id = ".$id;	
		// if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_shortdescription-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['short_desc']=='' || $TEMP_ID['short_desc']=='null')
			$short_desc = "";
		else
			$short_desc = $TEMP_ID['short_desc'];
		
		// close db
		dmc_db_disconnect($link);
		
		return $short_desc;	
	} // end function dmc_get_shortdescription
	
	function dmc_get_eav_attribute_option_value_option_id_by_value($attribute_id, $wert,$store_id) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
	
		$query = "SELECT DISTINCT eaov.option_id from ".DB_TABLE_PREFIX."eav_attribute_option_value AS eaov INNER JOIN ".DB_TABLE_PREFIX."eav_attribute_option AS eao ON (eaov.option_id=eao.option_id) WHERE eao.attribute_id=".$attribute_id." AND eaov.store_id=".$store_id." AND eaov.value= '".$wert."' ";	
		if (DEBUGGER==1) fwrite($dateihandle, "dmc_get_eav_attribute_option_value_by_value-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['option_id']=='' || $TEMP_ID['option_id']=='null')
			$option_id = "";
		else
			$option_id = $TEMP_ID['option_id'];
		
		// close db
		dmc_db_disconnect($link);
		
		return $option_id;	
	} // end function dmc_get_eav_attribute_option_value_by_value
	
	// Ueberpruefe ob Datensatz vorhanden - Check if entry exits
	function dmc_entry_exits($column, $table, $where) {
	
		global  $dateihandle;
		
		$query = "SELECT ".$column." as total from ".DB_TABLE_PREFIX."".$table." where ".$where." ";
		
		$link=dmc_db_connect();
		
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_entry_exits-SQL= ".$query." .\n");		
		
		mysql_query("SET NAMES 'utf8'", $link);
		try {
			$sql_query = mysql_query($query);				
		} catch (Exception $e) {
			if (DEBUGGER>=99) fwrite($dateihandle, "dmc_entry_exits - 915 - Error:\n".$e->getMessage()."\n");
			$fehler="table not exists";
			return $fehler;
		}
		
		$TEMP_ID = mysql_fetch_array($sql_query);	
			
		if (DEBUGGER==99) fwrite($dateihandle, "Result = temp_id total=".$TEMP_ID['total'].".\n");
		
		if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
			$exists = false;			
		else
			$exists = true;
		
		dmc_db_disconnect($link);
		
		return $exists;	
	} // end function dmc_entry_exits
								
	// Ueberpruefe ob Attribute vorhanden - Check if attribute exits
	function dmc_attribute_exists($attribute) {
	
		global  $dateihandle;
		
		$query = "SELECT attribute_id as total from ".DB_TABLE_PREFIX."eav_attribute where attribute_code = '".$attribute."'";
		
		$link=dmc_db_connect();
		
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_attribute_exists-SQL= ".$query." .\n");		
		
		mysql_query("SET NAMES 'utf8'", $link);
		$sql_query = mysql_query($query);				
		
		$TEMP_ID = mysql_fetch_array($sql_query);	
			
			if (DEBUGGER==99) fwrite($dateihandle, "Result: temp_id total=".$TEMP_ID['total'].".\n");
		
		if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null') {
			// if attribute_code not exists check for frontend_label
			$query = "SELECT attribute_id as total from ".DB_TABLE_PREFIX."eav_attribute where frontend_label = '".$attribute."' AND source_model = 'eav/entity_attribute_source_table' ";
			mysql_query("SET NAMES 'utf8'", $link);
			$sql_query = mysql_query($query);	
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null') 
				$exists = false;	
			else {
				$exists = true;
				if (DEBUGGER==99) fwrite($dateihandle, "Attribute exists by frontend_label .\n");
			}
		} else
			$exists = true;
		
		dmc_db_disconnect($link);
		
		return $exists;	
	} // end function dmc_attribute_exists
	
	function dmc_attach_simple_to_configurable($simple_id,$conf_id) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
	
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_attach_simple_to_configurable($simple_id,$conf_id)\n");				
		
		$super_row = mysql_query('select product_id, parent_id from '.DB_TABLE_PREFIX.'catalog_product_super_link".
					" where product_id='.$simple_id.' AND  parent_id ='.$conf_id);
        if($super_retrieved = mysql_fetch_array($super_row)) {
			// Zuordnung existent
			$angelegt=false;
          } else {
			// Zuordnung anlegen
				// fwrite($dateihandle, "db_func 979 - SQL = insert into ".DB_TABLE_PREFIX."catalog_product_super_link (product_id, parent_id)".
				//	" values ('$simple_id','$conf_id)'\n");
              mysql_query('insert into '.DB_TABLE_PREFIX.'catalog_product_super_link (product_id, parent_id) values ('.$simple_id.', '.$conf_id.')');
			  $angelegt=true;
		}
		// ******************************
		
		// close db
		dmc_db_disconnect($link);	
			
		return $angelegt;	
	} // end function dmc_attach_simple_to_configurable
	
	function dmc_attach_simple_to_configurable_prices($simple_id,$conf_id,$attribute_id,$preis) {
	
		global  $dateihandle, $store_id;
		// Open DB
		$link=dmc_db_connect();
	
		if (DEBUGGER==99)	fwrite($dateihandle, "dmc_attach_simple_to_configurable_prices($simple_id,$conf_id,$attribute_id,$preis)\n");
			// Wenn der Preis der Variante = 0 oder '', dann KEINE Differenz -> 0 eintragen
		if ($preis==0 || $preis=='') {
			$differenz=0;
			if (DEBUGGER==99) fwrite($dateihandle, "Kein Preis der Variante angegeben, daher Preis diff=".$differenz." .\n");	
		} else { // if ($preis==0 || $preis='') {
			// Ãœberprüfen, ob Preis des Simple Products vom Configurable abweicht
			// Fehlermeldung mysql loggen
			fwrite($dateihandle, mysql_error());
			// Haupt-Preis Attribute-ID ermitteln
			$query = "SELECT p.value as preis FROM ".DB_TABLE_PREFIX."catalog_product_entity_decimal p, ".DB_TABLE_PREFIX."eav_attribute attr where p.entity_id = ".$conf_id." and p.store_id = ".$store_id." and p.attribute_id=attr.attribute_id AND attr.attribute_code = 'price' AND attr.backend_model = 'catalog/product_attribute_backend_price'";
			if (DEBUGGER==99) fwrite($dateihandle, "\catalog_product_entity_decimal-SQL= ".$query." .\n");		
			$sql_query = mysql_query($query);					
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['preis']=='' || $TEMP_ID['preis']=='null')
			// Preis vom Configurable
				$preis_conf = -1;
			else {
				// Preis vom Configurable
				$preis_conf = $TEMP_ID['preis'];
			}
			
			if ($preis_conf == -1) $differenz = 0;
				else $differenz = $preis-$preis_conf;
			if (DEBUGGER==99) fwrite($dateihandle, "Preis =$preis - Preis conf=$preis_conf Preis diff=".$differenz." .\n");		
			if ($differenz<0.1 && $differenz>-0.1) $differenz = 0;
		}
		if ($preis_conf <> -1) {
			// product_super_attribute_id ermitteln conf
			if (DEBUGGER==99) fwrite($dateihandle, 'SELECT product_super_attribute_id as id FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute WHERE product_id ='.$conf_id.' and attribute_id ='.$attribute_id.'');		
		
			$sql_query = mysql_query('SELECT product_super_attribute_id as id FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute WHERE product_id ='.$conf_id.' and attribute_id ='.$attribute_id.'');
			$result_query = mysql_fetch_array($sql_query);
			
			if($result_query['id'] != '') $product_super_attribute_id = $result_query['id'];
			// value_index ermitteln simple 
			if (DEBUGGER==99) fwrite($dateihandle, 'SELECT value AS id FROM '.DB_TABLE_PREFIX.'catalog_product_entity_int where entity_id='.$simple_id.' AND store_id = '.$store_id.' and attribute_id ='.$attribute_id.'');		
			$sql_query = mysql_query('SELECT value AS id FROM '.DB_TABLE_PREFIX.'catalog_product_entity_int where entity_id='.$simple_id.' AND store_id = '.$store_id.' and attribute_id ='.$attribute_id.'');
			$result_query = mysql_fetch_array($sql_query);
			if($result_query['id'] != '' )  {
				$value_index = $result_query['id'];
			} else {
				// Abfangroutine: Wenn nicht in der Int Tabelle, dann in der Varchar.
				
				$sql_query = mysql_query('SELECT value AS id FROM '.DB_TABLE_PREFIX.'catalog_product_entity_varchar where entity_id='.$simple_id.' AND store_id = '.$store_id.' and attribute_id ='.$attribute_id.'');
				$result_query = mysql_fetch_array($sql_query);
				if($result_query['id'] != '' )  {
					$value_index = $result_query['id'];
				} else {
					// FEHLER: Kein Wert
					if (DEBUGGER==99) fwrite($dateihandle, "Fehler: Kein Wert für $value_index ermittelbar. Preisdifferenz kann nicht geschrieben werden");
					$value_index = -1;
				}
			}
			
			if ($value_index>0) {
				// Zuordnung existent, daher vorher löschen
				if (DEBUGGER==99) fwrite($dateihandle, 'SELECT value_index as id FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute_pricing WHERE product_super_attribute_id = '.$product_super_attribute_id.' AND value_index = '.$value_index.'');		
				$sql_query = mysql_query('SELECT value_index as id FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute_pricing WHERE product_super_attribute_id = '.$product_super_attribute_id.' AND value_index = '.$value_index.'');
				$result_query = mysql_fetch_array($sql_query);
				if (DEBUGGER==99) fwrite($dateihandle, 'DELETE FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute_pricing WHERE product_super_attribute_id = '.$product_super_attribute_id.' AND value_index = '.$value_index.'');		
				if($result_query['id'] != '' ) mysql_query('DELETE FROM '.DB_TABLE_PREFIX.'catalog_product_super_attribute_pricing WHERE product_super_attribute_id = '.$product_super_attribute_id.' AND value_index = '.$value_index.'');		
				// Zuordnung mit Preis setzen
				if (DEBUGGER==99) fwrite($dateihandle, "SQL = INSERT INTO ".DB_TABLE_PREFIX."catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value) VALUES ($product_super_attribute_id, $value_index,0, $differenz)\n");
				mysql_query('INSERT INTO '.DB_TABLE_PREFIX.'catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value) VALUES ('.$product_super_attribute_id.', '.$value_index.',0, '.$differenz.')');  
			}
		} // end if differenz
		// ******************************
		// close db
		dmc_db_disconnect($link);	
			
		return $angelegt;	
	} // end function dmc_attach_simple_to_configurable_prices
	
	function get_first_order_id($store_id,$order_status,$last_order, $from_incr_id) {
		
		global  $dateihandle;
		
		$query = "SELECT MIN(so.increment_id) as total FROM ".DB_TABLE_PREFIX."sales_order as so, ".DB_TABLE_PREFIX."sales_order_varchar AS sov, ".DB_TABLE_PREFIX."eav_attribute AS ea , ".DB_TABLE_PREFIX."eav_entity_type AS eet WHERE sov.entity_id = so.entity_id AND sov.attribute_id = ea.attribute_id AND ea.entity_type_id = eet.entity_type_id AND ea.attribute_code = 'status' AND eet.entity_model = 'sales/order' AND sov.value='".$order_status."' AND so.store_id=".$store_id ." AND so.created_at > '".$last_order."' AND so.increment_id >".$from_incr_id." LIMIT 0 , 10"; 
		
		$link=dmc_db_connect();
		
		$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==99) fwrite($dateihandle, "get_first_order_id-SQL= ".$query." .\n");

		$result = mysql_query ( $query );
		$TEMP_ID = mysql_fetch_array($result);				
			if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
				// IF no ID -> Product not available
				$o_id = "";
			else
				$o_id  = $TEMP_ID['total'];
				
		dmc_db_disconnect($link);
		
		return $o_id;	
	} // end function get_first_order_id
	
	function get_last_order_id($store_id,$order_status,$last_order, $from_incr_id) {
		
		global  $dateihandle;
		
		$query = "SELECT so.increment_id as total FROM ".DB_TABLE_PREFIX."sales_order as so, ".DB_TABLE_PREFIX."sales_order_varchar AS sov, ".DB_TABLE_PREFIX."eav_attribute AS ea , ".DB_TABLE_PREFIX."eav_entity_type AS eet WHERE sov.entity_id = so.entity_id AND sov.attribute_id = ea.attribute_id AND ea.entity_type_id = eet.entity_type_id AND ea.attribute_code = 'status' AND eet.entity_model = 'sales/order' AND sov.value='".$order_status."' AND so.store_id=".$store_id ." AND so.created_at > '".$last_order."' AND so.increment_id >".$from_incr_id." ORDER BY so.increment_id LIMIT 0 , 10"; 
		
		$link=dmc_db_connect();
		
		//$dateiname=LOG_FILE;	
		//$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==99) fwrite($dateihandle, "get_last_order_id-SQL= ".$query." .\n");

		$result = mysql_query ( $query );
		$TEMP_ID = mysql_fetch_array($result);	
		
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		    $o_id = $row[0];  
			// fwrite($dateihandle, "while-o_id= ".$o_id."\n");
		}
				fwrite($dateihandle, "END-o_id= ".$o_id."\n");
		dmc_db_disconnect($link);
		
		return $o_id;	
	} // end function get_last_order_id
	
	function get_order_date_by_incr_id($last_order_id) {
		
		global  $dateihandle;
		
		$query = "SELECT date_add(created_at, interval 1 SECOND) as total FROM ".DB_TABLE_PREFIX."sales_order WHERE increment_id =".$last_order_id." "; 
		
		$link=dmc_db_connect();
		
		//$dateiname=LOG_FILE;	
		//$dateihandle = fopen($dateiname,"a");
		if (DEBUGGER==99) fwrite($dateihandle, "get_order_date_by_incr_id-SQL= ".$query." .\n");

		$result = mysql_query ( $query );
		$TEMP_ID = mysql_fetch_array($result);				
			if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
				// IF no ID -> Product not available
				$o_id = "";
			else
				$o_id  = $TEMP_ID['total'];
				
		dmc_db_disconnect($link);
		
		return $o_id;	
	} // end function get_order_date_by_incr_id
	
	function dmc_get_session_id() {

		global  $dateihandle;

		// Open DB
		$link=dmc_db_connect();
	
		$query = "SELECT sessid as total from ".DB_TABLE_PREFIX."api_session where logdate = (select max(logdate) FROM ".DB_TABLE_PREFIX."api_session)";
	
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_session_id-SQL= ".$query." .\n");		
		
		$sql_query = mysql_query($query);				
		
		$TEMP_ID = mysql_fetch_array($sql_query);	
			
		if ($TEMP_ID['total']=='' || $TEMP_ID['total']=='null')
			$session_id = 0;
		else
			$session_id = $TEMP_ID['total'];
			
		// close db
		dmc_db_disconnect($link);
		
		return $session_id;	
	} // end function
	
	function clean_cybersource_token() {
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				
		
		$query	= "UPDATE ".DB_TABLE_PREFIX."sales_flat_invoice";	
		$query	.= " SET cybersource_token=''";	
		$query	.= " WHERE cybersource_token is null";			
	
	if (DEBUGGER==99) fwrite($dateihandle, "clean_cybersource_token -> $query\n");		
	
		$doquery = $query; // if no array
		$sql_query = mysql_query($doquery);
		
		// close db
		dmc_db_disconnect($link);		
	} // end clean_cybersource_token
	
	function set_cybersource_token($value, $invoice_id) {
	
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		/*$dateiname=LOG_FILE;	
		$dateihandle = fopen($dateiname,"a");
		fwrite($dateihandle, "dmc_sql_update\n");	*/				
		
		$query	= "UPDATE ".DB_TABLE_PREFIX."sales_flat_invoice";	
		$query	.= " SET cybersource_token='".$value."'";	
		$query	.= " WHERE increment_id='".$invoice_id."'";			
	
		$doquery = $query; // if no array
		// foreach($query AS $doquery)
		//{
		mysql_query("SET NAMES 'utf8'", $link);
			if (DEBUGGER==99) fwrite($dateihandle, "set_cybersource_token-SQL= ".$doquery." .\n");
			$sql_query = mysql_query($doquery);
		//} // end foreach
		
		// close db
		dmc_db_disconnect($link);		
	} // end set_cybersource_token
	
	// Abgerufene Bestellung in Datenbank schreiben
	function set_dmc_invoice($value, $invoice_id) {
	
		global  $dateihandle;
		// Open DB
		
		// Pruefe ob Eintrag in Datenbank vorhanden
		$where="invoice_id ='".$invoice_id."' AND status <>''";
		// Rechnung bereits abgerufen -> Bestellung nicht mit abrufen
		if (dmc_get_id('id','dmc_invoices',$where)<>'') {
			$query	= "UPDATE ".DB_TABLE_PREFIX."dmc_invoices";	
			$query	.= " SET cybersource_token='".$value."'";	
			$query	.= " WHERE increment_id=".$invoice_id;	
		} else {
			$query	= "INSERT INTO ".DB_TABLE_PREFIX."dmc_invoices";	
			$query	.= " (invoice_id, status)";	
			$query	.= " VALUES ('".$invoice_id."', '".$value."')";	
		}
	
		$link=dmc_db_connect();

		$doquery = $query; // if no array
		// foreach($query AS $doquery)
		//{
		mysql_query("SET NAMES 'utf8'", $link);
		
		if (mysql_query($doquery) && DEBUGGER>=99) fwrite($dateihandle, "set_dmc_invoice-SQL= ".$doquery."eingetragen\n");
			else fwrite($dateihandle, "Fehler: set_dmc_invoice-SQL= ".$doquery." NICHT eingetragen: ". mysql_errno() . ": " . mysql_error() . "\n");;
		
		//} // end foreach
		
		// close db
		dmc_db_disconnect($link);		
	} // end set_dmc_invoice
	
	// Path der uebergeordenten Ebene MAX_CATEGORY_LEVEL ermitteln
	function dmc_get_category_upper_level_id($subcatid) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// Path der Kategorie ermitteln
		// z.B. $path='1/2/10/14/112/115/126';
		$query = "SELECT DISTINCT path as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity where entity_id = ".$subcatid."";	
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$path = -1;
		else
			$path = $TEMP_ID['cat_id'];
	
		if (DEBUGGER==99) fwrite($dateihandle, "- dmc_get_category_upper_level_id-SQL1= ".$query." -> Result=$path.\n");		
	
		if ($path<>-1) {
			$ebenen=substr_count($path,'/')-1;
			// Path auf Level MAX_CATEGORY_LEVEL
			$maxebenen=MAX_CATEGORY_LEVEL+2;
			//if ($ebenen<$maxebenen)
			//	$maxebenen=$ebenen;
			if ($ebenen>$maxebenen) {
				$pos=0;
				for ($i=0;$i<$maxebenen;$i++) {
					$pos = strpos($path, '/', $pos+1);
				}
				// Path der uebergeordenten Ebene MAX_CATEGORY_LEVEL ermitteln
				$path=substr($path,0,$pos);
			}
				
			$query = "SELECT DISTINCT entity_id as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity where path = '".$path."'";	
			if (DEBUGGER==99) fwrite($dateihandle, "- dmc_get_category_upper_level_id-SQL2= ".$query." .\n");		
			$sql_query = mysql_query($query);					
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
				$cat_id = -1;
			else
				$cat_id = $TEMP_ID['cat_id'];
		} else
			$cat_id = -1;
		
		// close db
		dmc_db_disconnect($link);
		
		return $cat_id;	
	} // end function dmc_get_category_upper_level_id
	
	// Der KAtegorie zurgehoerige Hauptartikelgruppe (Ebene 1) ermitteln
	function dmc_get_category_first_level_id($subcatid) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// Path der Kategorie ermitteln
		// z.B. $path='1/2/10/14/112/115/126';
		$query = "SELECT DISTINCT path as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity where entity_id = ".$subcatid."";	
		// if (DEBUGGER==99) 
		fwrite($dateihandle, "- dmc_get_category_first_level_id-SQL1= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$path = -1;
		else
			$path = $TEMP_ID['cat_id'];
	
		if ($path<>-1) {
			$pos=0;
			for ($i=0;$i<3;$i++) {
				$pos = strpos($path, '/', $pos+1);
			}
			// Path der uebergeordenten Ebene MAX_CATEGORY_LEVEL ermitteln
			$path=substr($path,0,$pos);
			
				
			$query = "SELECT DISTINCT entity_id as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity where path = '".$path."'";	
			if (DEBUGGER==99) fwrite($dateihandle, "- dmc_get_category_first_level_id-SQL2= ".$query." .\n");		
			$sql_query = mysql_query($query);					
			$TEMP_ID = mysql_fetch_array($sql_query);				
			if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
				$cat_id = -1;
			else
				$cat_id = $TEMP_ID['cat_id'];
		} else
			$cat_id = -1;
		
		// close db
		dmc_db_disconnect($link);
		
		return $cat_id;	
	} // end function dmc_get_category_first_level_id
	
	// Der Kategorie zurgehoerige Kategoriepfad 1/2/10/14/112/115/126 ermitteln
	function dmc_get_category_path_ids($subcatid) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		
		// Path der Kategorie ermitteln
		// z.B. $path='1/2/10/14/112/115/126';
		$query = "SELECT DISTINCT path as cat_id from ".DB_TABLE_PREFIX."catalog_category_entity where entity_id = ".$subcatid."";	
		// if (DEBUGGER==99) 
		fwrite($dateihandle, "- dmc_get_category_first_level_id-SQL1= ".$query." .\n");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['cat_id']=='' || $TEMP_ID['cat_id']=='null')
			$path = "";
		else
			$path = $TEMP_ID['cat_id'];	
		
		// close db
		dmc_db_disconnect($link);
		
		return $path;	
	} // end function dmc_get_category_first_level_id
	
	//  Kategorien eines Produktes uber Datenbank ermitteln -> rueckgabe IDs getrennt mit ,
	function dmc_get_product_category_ids($product_id) {
		global  $dateihandle;
		// Open DB
		$link=dmc_db_connect();
		$cat_ids="";
		$query = "SELECT category_id AS cat_id FROM ".DB_TABLE_PREFIX."catalog_category_product WHERE product_id=".$product_id." ORDER by position";	
		if (DEBUGGER==99) fwrite($dateihandle, "\dmc_get_product_category_ids-SQL= ".$query." .\n");		
		$sql_query = mysql_query($query);		
		while ($TEMP_ID = mysql_fetch_array($sql_query)) {
			if ($TEMP_ID['cat_id']<>'' && $TEMP_ID['cat_id']<>'null')
				// erster wert
				if ($cat_ids=="")
					$cat_ids  = $TEMP_ID['cat_id'];
				else
					$cat_ids  .= ",".$TEMP_ID['cat_id'];
		}
	
		// close db
		dmc_db_disconnect($link);
		
		return $cat_ids;	
	} // end function dmc_get_category_path_ids
	
	// Wert eines Kundenattributes eines Kunden ermitteln
	function dmc_get_customer_attribute_value($attribute_name, $customer_id) {
		global  $dateihandle;
		
		$query = "SELECT value AS wert FROM ".DB_TABLE_PREFIX."customer_entity_varchar WHERE attribute_id = (SELECT attribute_id FROM `eav_attribute`";
		$query .= " WHERE attribute_code='".$attribute_name."') AND entity_id=".$customer_id;
		$link=dmc_db_connect();
		if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_customer_attribute_value-SQL= ".$query." .\n");
		$sql_query = mysql_query($query);				
		
		while ($ERGEBNIS = mysql_fetch_array($sql_query)) {
			if ($ERGEBNIS['wert']=='' || $ERGEBNIS['wert']=='null') {
				// Kein Wert, pruefen auf andere Tabelle oder leere Rückgabe
				$query = "SELECT value AS wert FROM ".DB_TABLE_PREFIX."customer_entity_text WHERE attribute_id = (SELECT attribute_id FROM `eav_attribute`";
				$query .= " WHERE attribute_code='".$attribute_name."') AND entity_id=".$customer_id;
				$link=dmc_db_connect();
				if (DEBUGGER==99)  fwrite($dateihandle, "dmc_get_customer_attribute_value2-SQL= ".$query." .\n");
				$sql_query = mysql_query($query);				
				while ($ERGEBNIS = mysql_fetch_array($sql_query)) {
					if ($ERGEBNIS['wert']=='' || $ERGEBNIS['wert']=='null') {
						// Kein Wert, pruefen auf andere Tabelle oder leere Rückgabe
						$wert = '';
					} else {
						$wert  = $ERGEBNIS['wert'];
					}
				}	
			} else { 
				$wert  = $ERGEBNIS['wert'];
			}
		}		

		if (DEBUGGER==99)  fwrite($dateihandle, "wert= ".$wert." .\n");
		
		dmc_db_disconnect($link);
			
		return $wert;	
	} // end function dmc_get_customer_attribute_value
	
	// CatIds ergaenzen / notwendig bei AvS product import
	function dmc_attach_cat_ids($artID, $Kategorie_IDs,$Sortierung) {
		global  $dateihandle,$store_id;
		DEFINE (ATTACH_UPPER_CAT_IDS, true);
		//if (DEBUGGER==99) 
		fwrite($dateihandle, "dmc_attach_cat_ids.\n");
		if ($Sortierung=="" || $Sortierung=="0") $Sortierung=1;
		
		// Alte Verknuepfungen loeschen
		dmc_sql_delete("catalog_category_product", "product_id=".$artID);
		dmc_sql_delete("catalog_category_product_index", "product_id=".$artID);
	
		for ($i=0; $i<sizeof($Kategorie_IDs); $i++) {
			fwrite($dateihandle, "... eintragen (category_id, product_id, position):(".$Kategorie_IDs[$i].", ".$artID.", ".$Sortierung.")"."\n");
			if ($Kategorie_IDs[$i]<>'' && $Kategorie_IDs[$i]<>"-1") {
			//	fwrite($dateihandle, "... eintragen2 (category_id, product_id, position):(".$Kategorie_IDs[$i].", ".$artID.", ".$Sortierung.")"."\n");
				dmc_sql_insert("catalog_category_product", 
								"(category_id, product_id, position)", 
							"(".$Kategorie_IDs[$i].", ".$artID.", ".$Sortierung.")");
				dmc_sql_insert("catalog_category_product_index", 
								"(category_id, product_id, position, is_parent, store_id, visibility)", 
								"(".$Kategorie_IDs[$i].", ".$artID.", ".$i.", 1, ".($store_id+1).", 4)");
			}
			if (ATTACH_UPPER_CAT_IDS) {
				// ubergeordnete KategorieIds ergaenzen
				$upper_cat_ids = dmc_get_category_path_ids($Kategorie_IDs[$i]); // zb 1/2/481/533/534
				//fwrite($dateihandle, "1608=".substr($upper_cat_ids,strpos($upper_cat_ids, '/', 2)+1,255).'\n');				
				$add_cat_ids = explode('/', substr($upper_cat_ids,strpos($upper_cat_ids, '/', 2)+1,255));	// ab dem 2ten Ziffer suchen und / ersetzen	
			//	fwrite($dateihandle, "1610=Anzahl Zusatzkategorien=".sizeof($add_cat_ids)."mit 1=".$add_cat_ids[0]." und 2=".$add_cat_ids[1].'\n');
				for ($j=0; $j<sizeof($add_cat_ids); $j++) {
					if ($add_cat_ids[$j]!="" && $add_cat_ids[$j]!="-1") {
						fwrite($dateihandle, "... ATTACH_UPPER_CAT_IDS (category_id, product_id, position):(".$add_cat_ids[$j].", ".$artID.", ".$Sortierung.")"."\n");
						dmc_sql_insert("catalog_category_product", 
										"(category_id, product_id, position)", 
									"(".$add_cat_ids[$j].", ".$artID.", ".$Sortierung.")");
						dmc_sql_insert("catalog_category_product_index", 
										"(category_id, product_id, position, is_parent, store_id, visibility)", 
										"(".$add_cat_ids[$j].", ".$artID.", ".$i.", 1, ".($store_id+1).", 4)");
					}
				}
			}
		}
		return true;	
	}
	
	function dmc_get_product_attribute_value($artid,$attribute_name) {
	
		global  $dateihandle;
		
		// Open DB
		$link=dmc_db_connect();
		// $artid=dmc_get_id_by_artno($sku);
		// code ermitteln 
		
		$query = "SELECT cpev.value AS wert FROM ".DB_TABLE_PREFIX."catalog_product_entity_varchar AS cpev INNER JOIN ".DB_TABLE_PREFIX."eav_attribute AS ea ON ea.attribute_id = cpev.attribute_id WHERE ea.attribute_code = '".$attribute_name."' AND cpev.entity_id=".$artid;
		
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_get_product_attribute_value - SQL= ".$query." -> ");		
		$sql_query = mysql_query($query);					
		$TEMP_ID = mysql_fetch_array($sql_query);				
		if ($TEMP_ID['wert']=='' || $TEMP_ID['wert']=='null') {
			$wert = "";
		} else {
			$wert = $TEMP_ID['wert'];
		}
		if (DEBUGGER==99) 	fwrite($dateihandle, "ERGEBNIS = ".$wert." .\n");		
				
		// close db
		 dmc_db_disconnect($link);	
		return $wert;	
	} // end function dmc_get_product_attribute_value
	
	// Produkt Status fuer StoreView aendern
	function dmc_set_products_status($artid, $status, $store_view) {
	
		global  $dateihandle;
		// Open DB
		
		// Produkt aktivieren
		$ATTRIBUTE_STATUS = 273;		// Standard = 84;
		// 1 aktiv, 2 deaktiv
		if ($status==0 || $status==2)
			$status=2;
		else
			$status=1;
		
		// Pruefe ob Eintrag in Datenbank vorhanden
		$table = "catalog_product_entity_int";		
		$where="entity_id ='".$artid."' AND store_id = '".$store_view."' AND attribute_id=".$ATTRIBUTE_STATUS;
		// ATTRIBUTE ID FUER STATUS
				
		// Status fuer Storevie bereits vorhanden ODER deault???
		if (dmc_get_id('value',$table,$where)<>'') {
			$what = "value = '".$status."'";
			$where = "attribute_id = ".$ATTRIBUTE_STATUS." AND store_id=".$store_view." AND entity_id=".$artid;
			dmc_sql_update($table, $what, $where);
		} else {
			dmc_sql_insert( $table, 
							" (entity_id, store_id, attribute_id, value)", 
							"('".$artid."','".$store_view."','".$ATTRIBUTE_STATUS."', '".$status."')");		
		}
	
		// close db
		dmc_db_disconnect($link);		
	} // end dmc_set_products_status
	
		// Produkt Sichtbarkeit fuer StoreView aendern
	function dmc_set_products_visibility($artid, $sichtbarkeit, $store_view) {
	
		global  $dateihandle;
		// Open DB
		
		// Produkt aktivieren
		$ATTRIBUTE_VISIBILITY = 526;		// Standard = ?;
		// 1 unsichtbar, 4 sichtbar
		if ($sichtbarkeit==0)
			$sichtbarkeit=1;
		
		// Pruefe ob Eintrag in Datenbank vorhanden
		$table = "catalog_product_entity_int";		
		$where="entity_id ='".$artid."' AND store_id = '".$store_view."' AND attribute_id=".$ATTRIBUTE_VISIBILITY;
		// ATTRIBUTE ID FUER Sichtbarkeit
				
		// Sichtbarkeit fuer Storevie bereits vorhanden ODER default???
		if (dmc_get_id('value',$table,$where)<>'') {
			$what = "value = '".$sichtbarkeit."'";
			$where = "attribute_id = ".$ATTRIBUTE_VISIBILITY." AND store_id=".$store_view." AND entity_id=".$artid;
			dmc_sql_update($table, $what, $where);
		} else {
			dmc_sql_insert( $table, 
							" (entity_id, store_id, attribute_id, value)", 
							"('".$artid."','".$store_view."','".$ATTRIBUTE_VISIBILITY."', '".$sichtbarkeit."')");		
		}
	
		// close db
		dmc_db_disconnect($link);		
	} // end dmc_set_products_visibility
	
	// Funktion zum Sichern der Shopdatenbank
	function dmc_db_backup () {
	
		global  $dateihandle;
		if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_backup\n ");		

		$zeitstart = microtime(true); 

		@set_time_limit(0);
		// Weitere Konfigurationen
		$downloadlink_erstellen = false;
		$bestaetigungsmail_senden = false;
		$bestaetigungsmail_adresse = "info@mobilize.de";
		$bestaetigungsmail_betreff = "[BACKUP] Ihre Shopdatenbank";
		
		$sql_file = "backups/db_dump_" . DATABASE . "_" . date('Ymd_Hi') . ".sql";

		####################################################################
		################## AB HIER BITTE NICHTS MEHR AENDERN!!! ################

		if ( file_exists($sql_file) or file_exists($sql_file . ".gz") )
		{
			if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_backup - FEHLER beim Sichern der Datenbank:: Das zu erstellende Dump existiert bereits!\n ");	
			die("FEHLER beim Sichern der Datenbank:: Das zu erstellende Dump existiert bereits!");
		}

		## dump erstellen
		fwrite($dateihandle, "dmc_db_backup - mysqldump --host=".DB_SERVER." --user=".DB_USER." --password=".DB_PWD." ".DATABASE." >$sql_file");	
		
			
//		exec("mysqldump -u ".DB_USER." -p'".DB_PWD."' --quick --allow-keywords --add-drop-table --complete-insert --quote-names ".DATABASE." >$sql_file");
//		exec("mysqldump --user=".DB_USER." --password=".DB_PWD." --quick --allow-keywords --add-drop-table --complete-insert --quote-names ".DATABASE." >$sql_file");
		//exec("mysqldump --host=".DB_SERVER." --user=".DB_USER." --password=".DB_PWD." -S /tmp/mysql5.sock ".DATABASE." >$sql_file");
		exec("mysqldump --host=".DB_SERVER." --user=".DB_USER." --password=".DB_PWD." ".DATABASE." >$sql_file");
		//exec("gzip $sql_file");


		### groee ermitteln
		$datei = $sql_file . ".gz";
		$size = filesize($datei);
		$i = 0;
		while ( $size > 1024 )
		{
			$i++;
			$size = $size / 1024;
		}
		$fileSizeNames = array(" Bytes", " KiloBytes", " MegaBytes", " GigaBytes", " TerraBytes");
		$size = round($size,2);
		$size = str_replace(".", ",", $size);
		$groesse = "$size $fileSizeNames[$i]";

		### nachricht erstellen
		$message = "Ihr Backup der Datenbank <b>" . DATABASE . "</b> wurde durchgefuehrt.<br>";
		$message .= "Die Groessee des erstellten Dumps betraegt <b>" . $groesse . "</b>.<br>";

		if ($downloadlink_erstellen)
		{
			$link = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
			$link = str_replace(basename(__FILE__),$datei,$link);
			$message .= "Downloadlink: <a href=" . $link . ">" . $datei . "</a>";
		}

		## nachricht ausgeben
		// if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_backup - $message \n ");		
			

		### mail versenden
		$message = str_replace("<br>", "\r\n", $message);
		$message = str_replace("<b>", "", $message);
		$message = str_replace("</b>", "", $message);
		if ($bestaetigungsmail_senden)
		{
			if(!preg_match( '/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/' , $bestaetigungsmail_adresse))
			{
				if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_backup - FEHLER: Mail konnte nicht versendet werden, da die Adresse ung&uuml;ltig ist!\n ");	
			}
			else
			{
				mail($bestaetigungsmail_adresse, $bestaetigungsmail_betreff,
				$message,"From: backupscript@{$_SERVER['SERVER_NAME']}\r\n" . "Reply-To: backupscript@{$_SERVER['SERVER_NAME']}\r\n")
				or die("FEHLER: Mail konnte wegen eines unbekannten Fehlers nicht versendet werden");				
			}
		}
		$dauer = microtime(true) - $zeitstart; 

		if (DEBUGGER==99) fwrite($dateihandle, "dmc_db_backup - ".DATABASE." gesichert in $sql_file in $dauer s \n ");					
	} // end dmc_db_backup
	
	
?>