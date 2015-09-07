<?php
/****************************************************************************
*                                                                        	*
*  dmConnector for Magento shop												*
*  dmc_generate_manuf_id.php												*
*  inkludiert von dmc_write_art.php 										*
*  Hersteller ID Magento ermitteln											*
*  Copyright (C) 2012 DoubleM-GmbH.de										*
*                                                                       	*
*****************************************************************************/
/*
02.03.2012
- neu
11.01.2013
- Abfrage auf $attr_type_id integriert
*/
 
		$Hersteller_Name=$Hersteller_ID;
		$Hersteller_ID = get_option_id_by_attribute_code_and_option_value('manufacturer', $Hersteller_Name, $store_id);
		$attr_type_id=dmc_get_entity_type_id_by_entity_type_code("catalog_product");
		$manuf_attributeid=dmc_get_attribute_id_by_attribute_code($attr_type_id,'manufacturer');
		if ($manuf_attributeid==-1) $manuf_attributeid=73;
	
		// ansonsten ggfls anlegen
		if ($Hersteller_ID=="") {
			// Neue Optionsid ermitteln
			$optionsid = dmc_get_next_id("option_id","eav_attribute_option_value");
								
			// Attribute zu Option verknuepfen
			dmc_sql_insert("eav_attribute_option", "(option_id, attribute_id, sort_order)","(".$optionsid.", ".$manuf_attributeid." , 0)");							
			// Options Wert eintragen
			dmc_sql_insert("eav_attribute_option_value", "(value_id, option_id, store_id, value)","(NULL, '".$optionsid."', '".$store_id."', '".$Hersteller_Name."')");				
			$Hersteller_ID = $optionsid;
		}
		
	
?>
	