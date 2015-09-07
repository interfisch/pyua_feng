<?php
/****************************************************************************************
*                                                              							*
*  dmConnector  for magento shop														*
*  dmc_write_cat.php																	*
*  Kategorie schreiben																	*
*  Copyright (C) 2011-12 DoubleM-GmbH.de												*
*                                                                  						*
*****************************************************************************************/
/*
30.03.09
- Kategorie Zuordnung über Eintrag keywords der Kategorie
11.01.11
- Kompatibilität 1.4 include_in_menu
17.01.11
-  Vaterkategorie, generieren z.B.  1.2.2 Acer waere Vater 1.2
18.10.11
- Unterstuetzung von Sortierreichenfolge
01.03.12
- Unterstuetzung von neuer Übergabe Kategoriewerte
23.04.2012
- Unterstuetzung der Verarbeitung von Kategoriebildern
*/

defined('VALID_DMC') or die( 'Direct Access to this location is not allowed.' );

	function dmc_write_cat($StoreView='default',$client, $sessionId) {
		global	$dateihandle;
		
		if (DEBUGGER>=1) fwrite($dateihandle, "*** dmc_write_cat ***\n");
		
		// Post ermitteln		
		$Kategorie_ID = str_replace("'", "", $_POST['Artikel_Kategorie_ID'] );
		$Kategorie_Vater_ID =  str_replace("'", "", $_POST['Kategorie_Vater_ID'] );
		$Kategorie_Bezeichnung =  html_entity_decode (($_POST["Kategorie_Name1"]), ENT_NOQUOTES);
		$Kategorie_Bezeichnung  = str_replace("'", "", $Kategorie_Bezeichnung );
		$Kategorie_Beschreibung =  html_entity_decode (($_POST["Kategorie_Beschreibung1"]), ENT_NOQUOTES);
		// Quick-Fix: ersetzten von Zeilenumbruch durch <BR>
		$Kategorie_Beschreibung  = str_replace("'", "", $Kategorie_Beschreibung );
		$Kategorie_Beschreibung  = str_replace('\n', '<br>', $Kategorie_Beschreibung );
		$Aktiv =  str_replace("'", "",$_POST["Kategorie_Aktiv"]);
		 
		// Nur wenn übergeben
		if (isset($_POST['Kategorie_Bild'])) $Kategorie_Bild = html_entity_decode (($_POST["Kategorie_Bild"]), ENT_NOQUOTES); else $Kategorie_Bild = '';
		// Wenn Bilddatei Verzeichnis enthaelt, dann das Bild separieren
		if (strpos($Kategorie_Bild, "\\") !== false) {
			$Kategorie_Bild=substr($Kategorie_Bild,(strrpos($Kategorie_Bild,"\\")+1),254); 
		} 
		// Wenn Bild verarbeitet werden soll, pruefen ob existent und kopieren in Ordner
		if (DEBUGGER>=1) fwrite($dateihandle, " Pruefen auf Bild: ".IMAGE_FOLDER . $Kategorie_Bild." \n");	
			// fwrite($dateihandle, " Pruefen auf Bild: ".IMAGE_FOLDER . $Kategorie_Bild." \n");			
		if ($Kategorie_Bild !='') 
			if (file_exists(IMAGE_FOLDER . $Kategorie_Bild)) {
				// Datei in Ordner kopieren
				copy(IMAGE_FOLDER . $Kategorie_Bild , "./../media/catalog/category/".$Kategorie_Bild);
				if (DEBUGGER>=1) fwrite($dateihandle, "copy(IMAGE_FOLDER . $Kategorie_Bild , ./../media/catalog/category/.$Kategorie_Bild)\n");				
			} else { 
				$Kategorie_Bild=''; // Kein Bild zuordnen
			} // end if file_exists
				
		if (isset($_POST['Kategorie_Sortierung'])) $Kategorie_Sortierung = html_entity_decode (($_POST["Kategorie_Sortierung"]), ENT_NOQUOTES); else $Kategorie_Sortierung = '';
		// Nicht verwendet bei Magento, da Kategorie-ID
		if (isset($_POST['Kategorie_MetaK'])) $Kategorie_MetaK = html_entity_decode (($_POST["Kategorie_MetaK"]), ENT_NOQUOTES); else $Kategorie_MetaK = '';
		if (isset($_POST['Kategorie_MetaD'])) $Kategorie_MetaD = html_entity_decode (($_POST["Kategorie_MetaD"]), ENT_NOQUOTES); else $Kategorie_MetaD = '';
		if (isset($_POST['Kategorie_MetaT'])) $Kategorie_MetaT = html_entity_decode (($_POST["Kategorie_MetaT"]), ENT_NOQUOTES); else $Kategorie_MetaT = '';
		// Nicht verwendet bei Magento
		if (isset($_POST['Kategorie_Suchbegriffe'])) $Kategorie_Suchbegriffe = html_entity_decode (($_POST["Kategorie_Suchbegriffe"]), ENT_NOQUOTES); else $Kategorie_Suchbegriffe = '';
		// Nicht verwendet bei Magento
		if (isset($_POST['Kategorie_SEO'])) $Kategorie_SEO = html_entity_decode (($_POST["Kategorie_SEO"]), ENT_NOQUOTES); else $Kategorie_SEO = '';
		if (isset($_POST['Kategorie_Sprache_Store'])) $Kategorie_Sprache_Store = html_entity_decode (($_POST["Kategorie_Sprache_Store"]), ENT_NOQUOTES); else $Kategorie_Sprache_Store = '';
		if (isset($_POST['KategorieFF1'])) $KategorieFF1 = html_entity_decode (($_POST["KategorieFF1"]), ENT_NOQUOTES); else $KategorieFF1 = '';
		if (isset($_POST['KategorieFF2'])) $KategorieFF2 = html_entity_decode (($_POST["KategorieFF2"]), ENT_NOQUOTES); else $KategorieFF2 = '';
		
		// Überprüfen, ob eine Sortierreihenfolge angegeben ( nach altem Muster )
		if (preg_match('/@/', $Aktiv)) {
			$werte = explode ( '@', $Aktiv);
			$Aktiv = $werte[0];
			$sortkey = $werte[1];
		} else {
			// Standard = keine besondere Sortierung
			$sortkey=0;
		} // endif
		
		if ($Aktiv =='') $Aktiv =1;
		
		if ($Kategorie_Sortierung<>'')		// ( nach neuem Muster )
			$sortkey= $Kategorie_Sortierung;
		
		// Sortkey aufbereiten
		$sortkey  = str_replace(".", "", $sortkey );
		$sortkey  = str_replace("A", "99", $sortkey );
					fwrite($dateihandle, "7\n");

		if (DEBUGGER>=1) fwrite($dateihandle, "Kategorie_ID = ".$Kategorie_ID."(Vater=$Kategorie_Vater_ID) mit Name ".$Kategorie_Bezeichnung."\n");
	
		// Categorie anlegen
		//$categorie_parent_id='1';
		//$categorie_name='Specials';
		//$StoreView='default';
		//     int $parentId - ID of parent category		
		// array $categoryData - category data ( array(’attribute_code’?‘attribute_value’ )
		//  mixed $storeView - store view ID or code (optional)
		
		// Eindeutiger URL_Key aus Namen und von WaWi übermittelter ID
		//$url_key=$categorie_name."_55";
		
		// root id aus db core_config_data holen
		if ($Kategorie_Vater_ID=='0') $Kategorie_Vater_ID=CAT_ROOT;		// 3 = Root Categorie
		
		// Check if category already exists (-1 if not)
		if (!GENERATE_CAT_ID)
			$cat_id=dmc_get_category_id("entity_id='".$Kategorie_ID."'");	
		else 
			$cat_id=dmc_category_exists($Kategorie_ID);	
			
		if (DEBUGGER>=1) fwrite($dateihandle, "Category already exists = ".$cat_id."\n");
		if (DEBUGGER>=1) fwrite($dateihandle, "Category_Father_ID = ".$Kategorie_Vater_ID."\n");
		// Category_Father_ID basierend auf meta_keywords der bisherigen kategorien ermitteln, wenn nicht root ID
		if($Kategorie_Vater_ID<>CAT_ROOT) {
			if (trim($Kategorie_Vater_ID)=='generate') {
				if (DEBUGGER>=1) fwrite($dateihandle, "100 - generate Category_Father_ID\n");
				$pos = strrpos($Kategorie_ID, ".");
				if ($pos === false) { 
				    // nicht gefunden ...
					$Kategorie_Vater_ID=CAT_ROOT;
				} else {
					// Vaterkategorie, generieren z.B.  1.2.2 Acer waere Vater 1.2
					$Kategorie_Vater_ID=substr($Kategorie_ID,0,$pos);
					if (DEBUGGER>=1) fwrite($dateihandle, "\nCategory_Father_ID generated = ".$Kategorie_Vater_ID."\n");
				}
			}
			
			if (!GENERATE_CAT_ID)
				$Kategorie_Vater_ID = dmc_get_category_id("entity_id='".$Kategorie_Vater_ID."'");	
			else 
				// $Kategorie_Vater_ID = dmc_get_cat_keywords($Kategorie_Vater_ID) ;
				$Kategorie_Vater_ID = dmc_category_exists($Kategorie_Vater_ID) ;
				
			fwrite($dateihandle, "\nCategory_Father_ID=".$Kategorie_Vater_ID." AND root=".CAT_ROOT."\n");
		}
		// Wenn Vater Kategorie nicht vorhanden, nehme Root Kategorie
		if ($Kategorie_Vater_ID==-1) $Kategorie_Vater_ID=CAT_ROOT;		// 3 = Root Categorie
		
		if ($cat_id==-1){		
			// Create new category
			try {
				//Versionsabfrage auf > (Magento 1.3)
				//Aktualisierung auf 1.4 - 10.01.2011
				if (SHOP_VERSION>1.3) { 
					$insertCatData =  array( 
								'name'=>$Kategorie_Bezeichnung,
								'meta_title'=>$Kategorie_Bezeichnung,
								'description'=>$Kategorie_Beschreibung,			// Kategorie Beschreibung aus übergebendem System
								'meta_title'=>$Kategorie_MetaT,			// Kategorie ID aus übergebendem System
								'meta_description'=>$Kategorie_MetaD,			// Kategorie ID aus übergebendem System
								'meta_keywords'=>$Kategorie_ID,			// Kategorie ID aus übergebendem System
								'available_sort_by'=> "name",
								'default_sort_by'=> "name",
								'listtype'=> "Name",
								'is_active'=>$Aktiv,
								'include_in_menu'=>1,
								'display_mode' => "PRODUCTS",
								'is_anchor' => 1,
								'position' => $sortkey
								//'url_key'=>$url_key
							); //end array 
					if ($Kategorie_Bild !='') $insertCatData['image']=$Kategorie_Bild;

					$newCategoryId = $client->call(
				    $sessionId, 'category.create',
				    array( 
					    $Kategorie_Vater_ID,			        
							$insertCatData,
						$Kategorie_Sprache_Store,
						) ////end array [aussen]
					); //end $newCategoryId
				} else {
					$newCategoryId = $client->call(
				    $sessionId, 'category.create',
				    array( 
					    $Kategorie_Vater_ID,			        
				        array( 
								'name'=>$Kategorie_Bezeichnung,
								'meta_title'=>$Kategorie_Bezeichnung,
								'description'=>$Kategorie_Beschreibung,			// Kategorie Beschreibung aus übergebendem System
								'meta_keywords'=>$Kategorie_ID,			// Kategorie ID aus übergebendem System
								'available_sort_by'=> "name",
								'default_sort_by'=> "name",
								'image'	=> $Kategorie_Bild,
								'is_active'=>$Aktiv,
								'position' => $sortkey
								//'url_key'=>$url_key
							), //end array [innen]
							$Kategorie_Sprache_Store,
						
						) ////end array [aussen]
					); //end $newCategoryId
				} //ende else-if
				
				if (DEBUGGER>=1) fwrite($dateihandle, "Category created with ID = ".$newCategoryId." AND 'meta_keywords'=>$Kategorie_ID \n");
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle,'Category creation failed:\n'.$e);		    
			}
		
			// $newData = array('url_key'=>$url_key);
			// update created category on default store view
			// $client->call($sessionId, 'category.update', array($newCategoryId, $newData, $StoreView));			
			
			// Update CategorieID magento to CategorieID from dmconnector
			// Check if dmconnector  CategorieID already exists  as magento CategorieID (-1 if not)
			$cat_id=dmc_get_category_id("entity_id='".$Kategorie_ID."'");	
			if (DEBUGGER>=1) fwrite($dateihandle, "Category Status = ".$cat_id."\n");
			if ($cat_id==-1){		
				// Update categoryID 
				if (DEBUGGER>=1) fwrite($dateihandle, "Update categoryID \n");
				// ID Der Kategorie ermitteln = true, bei false wird die von der WaWi übergebene Kategorie ID direkt verwendet
				// if (!GENERATE_CAT_ID) dmc_update_category_id($newCategoryId, $Kategorie_ID);
			} else {
				// Update existing magento categoryID to highest id first
				$oldToNew=dmc_get_highest_id('entity_id','catalog_category_entity');
				$oldToNew++;				
				// if (!GENERATE_CAT_ID) dmc_update_category_id($Kategorie_ID, $oldToNew);
				// Update categoryID 
				// if (!GENERATE_CAT_ID) dmc_update_category_id($newCategoryId, $Kategorie_ID);
			}
			
		} else {
			// Update existing category	
/*			
			try {	
				if (SHOP_VERSION>1.3) { 		
					$updateCatData  = array( 
									'name'=>$Kategorie_Bezeichnung,
									//'meta_title'=>$Kategorie_Bezeichnung,
									'description'=>$Kategorie_Beschreibung,			// Kategorie Beschreibung aus übergebendem System
									//'meta_keywords'=>$Kategorie_ID,			// Kategorie ID aus übergebendem System
									'available_sort_by'=> "name",
									'default_sort_by'=> "name",
									'is_active'=>$Aktiv,
									'position' => $sortkey
									//'url_key'=>$url_key
								); //end array
					if ($Kategorie_Bild !='') $updateCatData['image']=$Kategorie_Bild;

					$erfolg = $client->call(
				    $sessionId,
				    'category.update',
				    array(
						$cat_id,
						$updateCatData,
						$Kategorie_Sprache_Store,
						
							) ////end array [aussen]
						); //end $erfolg - update
				} else {
					$erfolg = $client->call(
				    $sessionId,
				    'category.update',
				    array(
						$cat_id,			        
				        array( 
								'name'=>$Kategorie_Bezeichnung,
								//'meta_title'=>$Kategorie_Bezeichnung,
								//'description'=>$Kategorie_Beschreibung,			// Kategorie Beschreibung aus übergebendem System
								//'meta_keywords'=>$Kategorie_ID,			// Kategorie ID aus übergebendem System
								'available_sort_by'=> "Name",
								'default_sort_by'=> "Name",
								'is_active'=>$Aktiv,
								'position' => $sortkey
								//'url_key'=>$url_key
							), //end array [innen]
							$Kategorie_Sprache_Store,
						
						) ////end array [aussen]
					); //end $erfolg - update
				} //ende else-if
				
				if (DEBUGGER>=1) {
					fwrite($dateihandle, "Category $cat_id updated with success = ".$erfolg." for Category_Father_ID=".$Kategorie_Vater_ID."\n");
				} // Ende Ausgabe
			} catch (SoapFault $e) {
				if (DEBUGGER>=1) fwrite($dateihandle,'Category update failed:\n'.$e);		    
			} //ende try-catch
*/
		} // end else
		
		// Update categorie for path - must be 1/CatId  instead of /CatId - bug of magento
		/* $newPath= '1/'.$Kategorie_ID;
		$newData = array('path'=>$newPath
						);
		$client->call($sessionId, 'category.update', array($Kategorie_ID, $newData)); //, $StoreView));	
*/		
		// Move categorie  if not parent - bug of magento
	//	if ($Kategorie_Vater_ID>1)	$client->call($sessionId, 'catalog_category.move', array($Kategorie_ID, $Kategorie_Vater_ID));												
		return $cat_id;	
	} // end function
?>