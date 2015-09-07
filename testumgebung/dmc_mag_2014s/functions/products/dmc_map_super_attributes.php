<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_map_super_attributes.php														*
*  inkludiert von dmc_write_art.php 										
*  Artikel übergebene Variablen ermitteln									*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012 - neu
11.01.2013 - keine "leeren" Attribute prüfen
*/

		if (DEBUGGER>=1) fwrite($dateihandle,"** dmc_map_super_attributes -> $SuperAttr**\n");

		if ($SuperAttr=="") {
			$Superattribute[0]=STD_SUPER_ATTRIBUTE_ID;
		} else  {
			// Wenn letztes Zeichen ein @ - > entfernen
			$Superattribute[0]=$SuperAttr;
			if (substr($SuperAttr,-1)=='@') {
				$SuperAttr = substr($SuperAttr,0,strlen($SuperAttr)-1);
			}
			// Pruefen auf mehrere Superattribute
			$pos = strpos ( $SuperAttr, '@' );
			if ($pos===false) $Superattribute[0]=$SuperAttr;
			else $Superattribute = explode ( '@', $SuperAttr);
			
			for ( $i = 0; $i < count ( $Superattribute ); $i++ )
			{
				if ($Superattribute[$i] != '') { // Wenn Wert gesetzt
					if (DEBUGGER>=1) fwrite($dateihandle,"** Superattribute = ".$Superattribute[$i]."**\n");
					// GGfls Code ermitteln, sofern nicht Code sondern Klartext angegeben ist, z.B. Höhe auf hoehe?
					$SuperattributeCode[$i]=dmc_get_attribute_code_by_attribute_name($attr_type_id,$Superattribute[$i]);		
					// AttributeIDs ermitteln
					$SuperattributeID[$i]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$SuperattributeCode[$i]);
					// konnte nicht ermittelt werden -> Standard=80
					//if ($SuperattributeID[$i]==-1) $SuperattributeID[$i]=80;
					// Wenn nicht vorhanden -> anlegen
					if (DEBUGGER>=1) fwrite($dateihandle,"** Superattribute ID= ".$SuperattributeID[$i]."**\n");
					if ($SuperattributeID[$i]==-1) {
						if (DEBUGGER>=1) fwrite($dateihandle,"** Superattribute wird angelegt**\n");
						if (is_file('../../userfunctions/products/dmc_create_superattribute.php')) 
							include ('../../userfunctions/products/dmc_create_superattribute.php');
						else if (is_file('./userfunctions/products/dmc_create_superattribute.php')) 
							include ('./userfunctions/products/dmc_create_superattribute.php');
						else if (is_file('../../functions/products/dmc_create_superattribute.php')) 
							include ('../../functions/products/dmc_create_superattribute.php');
						else include ('./functions/products/dmc_create_superattribute.php');
						
						if (DEBUGGER>=1) fwrite($dateihandle,"** Superattribute ist angelegt**\n");
						// Anstelle Klartext soll nun der Attribute Code verwendet werden...
						// $Superattribute[$i]=dmc_get_attribute_code_by_attribute_name($attr_type_id,$Superattribute[$i]);		
						$SuperattributeID[$i]=dmc_get_attribute_id_by_attribute_code($attr_type_id,$Superattribute[$i]);		
					} // end Merkmal anlegen	
					if (DEBUGGER>=1) fwrite($dateihandle,"** neu Superattribute ID= ".$SuperattributeID[$i]." mit Code=".$Superattribute[$i]."**\n");
				} // end if
				// if (DEBUGGER>=1) fwrite($dateihandle, "Superattribute=".$Superattribute[$i]."/".$SuperattributeID[$i]."\n");
			} // end for
		} // end if superattribute
		
		
?>
	